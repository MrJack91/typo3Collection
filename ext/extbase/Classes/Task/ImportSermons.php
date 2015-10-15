<?php
/*
register in register in ext_localconf.php:
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['LPC\LpcSermons\Task\ImportSermons'] = array(
	'extension' => $_EXTKEY,
	'title' => 'LLL:EXT:lpc_sermons/Resources/Private/Language/locallang.xlf:tx_lpcsermons.task.importSermons.title',
	'description' => 'LLL:EXT:lpc_sermons/Resources/Private/Language/locallang.xlf:tx_lpcsermons.task.importSermons.description',
	'additionalFields' => 'LPC\LpcSermons\Task\ImportSermonsFieldProvider'
);
*/

namespace LPC\LpcSermons\Task;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ImportSermons
 * @author Michael Hadorn <michael.hadorn@laupercomputing.ch>
 * @package TYPO3
 * @subpackage
 * @package LPC\LpcSermons\Task
 */
class ImportSermons extends \TYPO3\CMS\Scheduler\Task\AbstractTask {

	/** var \TYPO3\CMS\Extbase\Object\ObjectManager */
	protected $objectManager;

	/* settings */
	public $sourcepath = '';
	public $targetpath = '';
	public $pid = '';
	public $podcastid = '';

	/** @var \Lpc\LpcSermons\Domain\Repository\SermonRepository */
	protected $sermonRepository;
	/** @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager */
	protected $persistenceManager;
	/** @var \Lpc\LpcSermons\Domain\Repository\PodcastRepository */
	protected $podcastRepository;

	/** @var \Lpc\LpcSermons\Utility\FileUtility */
	protected $fileUtility;

	/**
	 * This is the main method that is called when a task is executed
	 * It MUST be implemented by all classes inheriting from this one
	 * Note that there is no error handling, errors and failures are expected
	 * to be handled and logged by the client implementations.
	 * Should return TRUE on successful execution, FALSE on error.
	 *
	 * @return boolean Returns TRUE on successful execution, FALSE on error
	 */
	public function execute() {
		$this->init();
		$this->handlePaths();
		$this->importSermons($this->sourcepath);
		return true;
	}

	/**
	 * init
	 */
	protected function init() {
		$this->objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		$this->persistenceManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager');
		$this->sermonRepository = $this->objectManager->get('Lpc\LpcSermons\Domain\Repository\SermonRepository');
		$this->podcastRepository = $this->objectManager->get('Lpc\LpcSermons\Domain\Repository\PodcastRepository');
		$this->fileUtility = $this->objectManager->get('Lpc\LpcSermons\Utility\FileUtility');
	}

	/**
	 * Handles the paths
	 */
	protected function handlePaths() {
		$this->sourcepath = PATH_site . $this->sourcepath;
		$this->targetpath = PATH_site . $this->targetpath;

		foreach (array($this->sourcepath, $this->targetpath) as $path) {
			self::makeDir($path);
		}
	}

	/**
	 * Imports the sermons (will be called recursively per directory)
	 * @param $sourcePath
	 * @param string $language optional language (for sub dics)
	 * @param int $depth internal use
	 * @return bool
	 */
	protected function importSermons($sourcePath, $language = '', $depth = 0) {

		$sourcePath = '/' . trim($sourcePath, '/').'/';

		$autoSetLanguage = false;
		if ($language == '') {
			$autoSetLanguage = true;
		}
		$language = $this->checkLanguage($language);

		// aren't only wanted files (dirs, hidden files)
		$newFiles = scandir($sourcePath);

		foreach ($newFiles as $file) {
			// ignore hidden fields and special folders (current, top)
			if (strpos($file, '.') === 0) {
				continue;
			}

			$fullSrcPath = $sourcePath . $file;

			// check on file or dir
			if (is_dir($fullSrcPath)) {
				// avoid more than one level depth
				if ($depth == 0) {
					// directory -> import these
					$this->importSermons($fullSrcPath, $file, ++$depth);
				}
			} else {
				// file
				// skip not mp3 files
				if (!self::endsWith($file, '.mp3')) {
					continue;
				}

				$date = substr($file, 0, 8);
				/** @var \DateTime $dateTime */
				$dateTime = \DateTime::createFromFormat('Ymd', $date);
				$dateTime->setTime(0,0,0);

				// if language isn't set, search in file name
				if ($autoSetLanguage) {
					// search in filename
					$matches = array();
					preg_match('/\-(\S{2})\.mp3/', $file, $matches);
					$languageCondidate = '';
					if (count($matches) == 2) {
						$languageCondidate = $matches[1];
					}
					$language = $this->checkLanguage($languageCondidate);
				}

				/** @var \Lpc\LpcSermons\Domain\Model\Sermon $sermon */
				$sermon = $this->objectManager->get('Lpc\LpcSermons\Domain\Model\Sermon');
				$pid = $this->getNumberSpecifiedPerAttribute($this->pid, $language);
				if ($pid === false) {
					// skip this entry without pid
					continue;
				}
				$sermon->setPid($pid);
				$sermon->setDate($dateTime);
				$sermon->setLanguage($language);
				// podcast
				$podId = $this->getNumberSpecifiedPerAttribute($this->podcastid, $language);
				if ($podId !== false) {
					$sermon->setPodcast($this->podcastRepository->findByUid($podId));
				}
				$this->sermonRepository->add($sermon);

				$this->persistenceManager->persistAll();
				$sermonUid = $sermon->getUid();

				// handle file: move
				$targetPath = $this->targetpath . $language . '/';
				self::makeDir($targetPath);
				rename($fullSrcPath, $targetPath . $file);

				$this->fileUtility->handleFileMigration($sourcePath, $fullSrcPath, $targetPath, $sermonUid, $pid, 'tx_lpcsermons_domain_model_sermon', 'mp3file');
				$extbaseFileReferences = $this->fileUtility->getExtbaseFileReference('tx_lpcsermons_domain_model_sermon', 'mp3file', $sermonUid);

				// add file reference to sermon -> to load mp3 details
				if (count($extbaseFileReferences) == 1) {
					$sermon->setMp3file($extbaseFileReferences[0]);
				}
				$mp3Info = $sermon->loadMp3Details(true);
				$id3Title = $mp3Info['tags']['id3v2']['title'][0];
				$id3Artist = $mp3Info['tags']['id3v2']['artist'][0];

				$sermon->setTitle($id3Title);
				$sermon->setPreacher($id3Artist);
				$this->sermonRepository->update($sermon);
				$this->persistenceManager->persistAll();
			}
		}
	}

	/**
	 * Get a number (not 0) from a configuration string (general: [single number] or [key=>value;key=>value;...])
	 * @param $values configuration string
	 * @param $choice key of wanted value
	 * @return false|int false if invalid key and not general
	 */
	protected function getNumberSpecifiedPerAttribute($values, $choice) {
		$valueArr = explode(';', $values);
		$valueArr = array_filter($valueArr);
		$val = false;
		foreach ($valueArr as $value) {
			$configuration = explode('=>', $value);
			if (count($configuration) > 1) {
				$key = $configuration[0];
				if (strtolower($key) === strtolower($choice)) {
					$valTemp = intval($configuration[1]);
					break;
				}
			} else {
				// use single value as default value
				$valTemp = $configuration[0];
				break;
			}
		}
		// validate on number
		$valTemp = intval($valTemp);
		if ($valTemp > 0) {
			$val = $valTemp;
		}
		return $val;
	}

	/**
	 * @param $language
	 * @return bool
	 */
	protected function checkLanguage($language) {
		// check language
		if ($language == '') {
			$language = 'de';
		}
		$language = strtolower($language);
		if (! in_array($language, array('de', 'fr', 'en', 'it', 'es'))) {
			return false;
		}

		return $language;
	}

	/**
	 * Checks if the string ends with
	 * @param $haystack
	 * @param $needle
	 * @return bool
	 */
	protected static function endsWith($haystack, $needle) {
		// search forward starting from end minus needle length characters
		return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
	}

	/**
	 * Makes a dir if it not exists
	 * @param $path
	 */
	protected static function makeDir($path) {
		if (!file_exists($path)) {
			mkdir($path, 0777, true);
		}
	}

}


