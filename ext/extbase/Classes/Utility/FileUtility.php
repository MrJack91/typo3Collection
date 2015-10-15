<?php

namespace Lpc\LpcSermons\Utility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class CastUtility
 * @author Michael Hadorn <michael.hadorn@laupercomputing.ch>
 * @package TYPO3
 * @subpackage
 * @package Lpc\LpcSermons\Utility
 */
class FileUtility {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 * @inject
	 */
	protected $objectManager = null;

	/**
	 * @var \TYPO3\CMS\Core\DataHandling\DataHandler
	 * @inject
	 */
	protected $tce = null;

	/**
	 * @var \TYPO3\CMS\Core\Resource\FileRepository
	 * @inject
	 */
	protected $fileRepository = null;

	/**
	 * Handles a file migration (old path on old system, and new target path)
	 * 	Files must be copied manually before!
	 * @param string $srcPathBase part of source $srcPathFile who will be ignored. (remain will be adapted to filesystem);
	 * 				"-" means: only use basename
	 * @param string $srcPathFile full path of source file (must not exist, only for build the correct path and name)
	 * @param string $tarPathBase storage identifier, where file will be moved
	 * @param int $uid uid of created entry
	 * @param int $pid pid of created
	 * @param string $tableName tablename of entry with this resource (uid)
	 * @param string $fieldName fieldname in tablename who saves files
	 * @return \TYPO3\CMS\Core\Resource\File|string file object if success (don't use it, reload extbase object and enjoy file), else error in textform
	 */
	public function handleFileMigration($srcPathBase, $srcPathFile, $tarPathBase, $uid, $pid, $tableName, $fieldName) {
		$return = null;
		if (strlen($srcPathFile) > 0) {
			$srcFilePath = trim($srcPathFile);

			if ($srcPathBase == '-') {
				// use only filename (ignore file path)
				$srcFilePathRel = basename($srcPathFile);
			} else {
				// adapt relative file path
				$srcFilePathRel = str_replace($srcPathBase, '', $srcFilePath);
			}

			// build combined identifier (guess on storage 1 -> fileadmin)
			$posFileadmin = strpos($tarPathBase, '/fileadmin/');
			if ($posFileadmin !== false) {
				$tarPathBase = substr($tarPathBase, $posFileadmin + 11);
			}
			$filePath = '1:'.$tarPathBase.$srcFilePathRel;
			$return = $this->addFile($filePath, $uid, $tableName, $fieldName, $pid);
		}
		return $return;
	}

	/**
	 * Add a existing file to type3 (insert into sys_file, index it, and make file reference)
	 * No reference id in return. Usually it is not necessary.
	 *    Workaround: reload extbase object, you will get the file (in the next request...)
	 * @param $filePath path of file to add (can be identifier with storage id - 1:/user_upload/..)
	 * @param int $uid uid of created entry
	 * @param int $pid pid of created
	 * @param string $tableName tablename of created entry (uid)
	 * @param string $fieldName fieldname in tablename of target file
	 * @return bool|\TYPO3\CMS\Core\Resource\File|string file object if successfull, else error (as string) or false
	 */
	public function addFile($filePath, $uid, $tableName, $fieldName, $pid) {

		$return = false;
		if (strlen($filePath) > 0) {
			// generate meta data (index)
			/** @var \TYPO3\CMS\Core\Resource\ResourceFactory $resFac */
			$resFac = $this->objectManager->get('\TYPO3\CMS\Core\Resource\ResourceFactory');
			try {
				$file = $resFac->retrieveFileOrFolderObject($filePath);
			} catch (\Exception $e) {
				return 'File missing: (uid_local: ['.$uid.'] ' . $filePath . ') ' . $e->getMessage();
			}

			// generate file references
			$data = array();
			$data['sys_file_reference']['NEW1234'] = array(
				'uid_local' => $file->getUid(),
				'uid_foreign' => $uid, // uid of your content record
				'tablenames' => $tableName,
				'fieldname' => $fieldName,
				'pid' => $pid, // parent id of the parent page
				'table_local' => 'sys_file',
			);
			$data[$tableName][$uid] = array($fieldName => 'NEW1234'); // set to the number of images?

			// @var \TYPO3\CMS\Core\DataHandling\DataHandler $tce
			// $tce = GeneralUtility::makeInstance('\TYPO3\CMS\Core\DataHandling\DataHandler'); // create TCE instance
			$this->tce->start($data, array(), 1);
			$this->tce->process_datamap();

			if ($this->tce->errorLog) {
				$return = 'TCE->errorLog: '.var_dump($this->tce->errorLog);
			} else {
				$return = $file;
			}
		}
		return $return;
	}

	/**
	 * Searches file references for use in extbase
	 * @param $tablename
	 * @param $tablefield
	 * @param $uid
	 * @return \TYPO3\CMS\Extbase\Domain\Model\FileReference[]
	 */
	public function getExtbaseFileReference($tablename, $tablefield, $uid) {
		/** @var \TYPO3\CMS\Core\Resource\FileReference[] $fileObjects */
		// $fileObjects = $this->fileRepository->findByRelation('tx_lpcsermons_domain_model_sermon', 'mp3file', $sermonUid);
		$fileObjects = $this->fileRepository->findByRelation($tablename, $tablefield, $uid);

		// get Imageobject information
		$extbaseFileReferences = array();
		foreach ($fileObjects as $file) {
			/** @var \TYPO3\CMS\Extbase\Domain\Model\FileReference $fileRefExtbase */
			$extbaseFileReference = GeneralUtility::makeInstance('\TYPO3\CMS\Extbase\Domain\Model\FileReference');
			$extbaseFileReference->setOriginalResource($file);
			$extbaseFileReferences[] = $extbaseFileReference;
		}
		return $extbaseFileReferences;
	}

}
