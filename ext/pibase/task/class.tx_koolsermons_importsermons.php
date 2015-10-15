<?php

/*
register in ext_localconf.php
// register scheduler
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_koolsermons_importsermons'] = array(
    'extension'        => $_EXTKEY,
    'title'            => 'Import Sermons / Predigen importieren',
    'description'      => 'Dieser Task importiert Predigten anhand von MP3 Dateien auf dem Server.',
    'additionalFields' => 'tx_koolsermons_importsermons_addfields'
);
*/

/**
 * Class ImportSermons (imported from lpc_sermons)
 * @author Michael Hadorn <michael.hadorn@laupercomputing.ch>
 * @package TYPO3
 */
class tx_koolsermons_importsermons extends tx_scheduler_Task {

	/** @var GetId3 */
	protected $getId3;

	/* settings */
	public $sourcepath = '';
	public $targetpath = '';
	public $pid = '';
	public $podcastid = '';

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
		/*
		if(t3lib_extMgm::isLoaded('t3getid3')) require_once(t3lib_extMgm::extPath('t3getid3').'getid3/getid3.php');
		else die('GetID3() Library not loaded! Please install extension t3getid3.');
		*/
		$this->getId3 = t3lib_div::makeInstance('getid3');
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
				/** @var DateTime $dateTime */
				$dateTime = DateTime::createFromFormat('Ymd', $date);
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

				$pid = $this->getNumberSpecifiedPerAttribute($this->pid, $language);
				if ($pid === false) {
					// skip this entry without pid
					continue;
				}

				// move file && add to fileadmin
				$targetPath = $this->targetpath . $language . '/';
				$mp3FilePath = str_replace(PATH_site, '', $targetPath) . $file;
				self::makeDir($targetPath);
				rename($fullSrcPath, $targetPath . $file);

				// analyze mp3 file
				$mp3Info = $this->getId3->analyze($targetPath . $file);
				$id3Title = $mp3Info['tags']['id3v2']['title'][0];
				$id3Artist = $mp3Info['tags']['id3v2']['artist'][0];

				// podcast
				$podId = $this->getNumberSpecifiedPerAttribute($this->podcastid, $language);
				if ($podId === false) {
					$podId = '';
				}

				// create record
				$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_koolsermons_sermons', array(
					'pid' => $pid,
					'tstamp' => time(),
					'crdate' => time(),
					'language' => $language,
					'podcast_id' => $podId,
					'title' => $id3Title,
					'speaker' => $id3Artist,
					'date' => $dateTime->getTimestamp(),
					'mp3file' => $mp3FilePath,

				));
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


