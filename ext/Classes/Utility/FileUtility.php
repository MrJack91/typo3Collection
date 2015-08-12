<?php

namespace Lpc\LpcSermons\Utility;

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
	 * Handels a file migration (old path on old system, and new target path)
	 * 	Files must be copied manually.
	 * @param string $srcPathBase base path of source will be cut
	 * @param string $srcPathFile full path of source file (old one)
	 * @param string $tarPathBase base path of target directory
	 * @param int $uid uid of created entry
	 * @param int $pid pid of created
	 * @param string $tableName tablename of created entry (uid)
	 * @param string $fieldName fieldname in tablename of target file
	 * @return bool|null|string true if successfull, else error in textform
	 */
	public function handleFileMigration($srcPathBase, $srcPathFile, $tarPathBase, $uid, $pid, $tableName, $fieldName) {

		$return = null;
		if (strlen($srcPathFile) > 0) {
			$srcFilePath = trim($srcPathFile);

			if ($srcPathBase == '-') {
				// use only filename
				$srcFilePathRel = basename($srcPathFile);
			} else {
				$srcFilePathRel = str_replace($srcPathBase, '', $srcFilePath);
			}

			// build combined identifier (guess on storage 1 -> fileadmin)
			$filePath = '1:'.$tarPathBase.$srcFilePathRel;

			$return = $this->addFile($filePath, $uid, $tableName, $fieldName, $pid);
		}
		return $return;
	}

	/**
	 * Add a existing file to type3 (insert into sys_file, index it, and make file reference)
	 * No reference id in return. Usually it is not necessary.
	 *    Workaround: reload extbase object, you will get the file
	 * @param $filePath path of file to add (can be identifier with storage id - 1:/user_upload/..)
	 * @param int $uid uid of created entry
	 * @param int $pid pid of created
	 * @param string $tableName tablename of created entry (uid)
	 * @param string $fieldName fieldname in tablename of target file
	 * @return bool|null|string true if successfull, else error in textform
	 */
	public function addFile($filePath, $uid, $tableName, $fieldName, $pid) {

		$return = null;
		if (strlen($filePath) > 0) {
			// generate meta data (index)
			/** @var \TYPO3\CMS\Core\Resource\ResourceFactory $resFac */
			$resFac = $this->objectManager->get('\TYPO3\CMS\Core\Resource\ResourceFactory');
			try {
				$file = $resFac->retrieveFileOrFolderObject($filePath);
			} catch (\Exception $e) {
				return 'File missing: (uid_local: '.$uid.' '.$fieldName.') '.$e->getMessage();
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
				$return = true;
			}
		}
		return $return;
	}

}
