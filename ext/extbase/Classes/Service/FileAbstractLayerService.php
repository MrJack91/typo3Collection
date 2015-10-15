<?php

namespace LPC\VinbernAktuell\Service;

/**
 * TypoScript Service class
 * helper function for the FAL (FileAbstractLayer)
 */
class FileAbstractLayerService implements \TYPO3\CMS\Core\SingletonInterface
{

	/**
	 * Get the url for web view from a FAL object.
	 * @param $uid of the element
	 * @param $name of the property field
	 * @param string $relation table name
	 * @param bool| $firstEntry get only the first entry. otherwise there will be an array as return value
	 * @return array|string url for all related files
	 */
	public static function getFalLinkByUid($uid, $name, $relation, $firstEntry = true) {
		$return = array();

		$fileRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\FileRepository');
		$fileObjects = $fileRepository->findByRelation($relation, $name, $uid);

		foreach ($fileObjects as $fileObject) {
			$return[] = self::getFalLinkByFileReference($fileObject, $firstEntry);
		}
		if ($firstEntry) {
			if (!empty($return)) {
				$return = $return[0];
			} else {
				$return = '';
			}
		}
		return $return;
	}

	/**
	 * Same should be done with: getPublicUrl()
	 * @param \TYPO3\CMS\Core\Resource\FileReference $fileObject
	 * @return string
	 */
	public static function getFalLinkByFileReference(\TYPO3\CMS\Core\Resource\FileReference $fileObject) {
		if (!empty($fileObject)) {
			$file = $fileObject->getOriginalFile()->getProperties();
			$folder = $fileObject->getOriginalFile()->getStorage()->getConfiguration();
			$folderBase = $folder['basePath'];
			// cut one slash after folder
			if (substr($folderBase, -1) == '/') {
				$folderBase = substr($folderBase,0,-1);
			}
			$return = $folderBase.$file['identifier'];
		}
		return $return;
	}

}