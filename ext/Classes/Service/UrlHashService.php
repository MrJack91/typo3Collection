<?php

namespace Lpc\LpcMessageboard\Service;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class UrlHashService
 *	Helps to get hash for actions via url (delete, confirm link)
 *
 *
 * @author Michael Hadorn <michael.hadorn@laupercomputing.ch>
 * @package TYPO3
 * @subpackage lpc_messageboard
 * @package Lpc\LpcMessageboard\Service
 */
class UrlHashService implements \TYPO3\CMS\Core\SingletonInterface
{

	/**
	 * @param $uid uid of element
	 * @param $op operation to identify what to do with them
	 * @return string the url params, starting with param (not with ?/&)
	 */
	public function getLink($uid, $op) {
		$hash = $this->calcHash($uid.$op);
		$url = 'uid='.$uid.'&op='.$op.'&lpcHash='.$hash;

		return $url;
	}

	/**
	 * search after hash and checks and returns the params
	 * @return array|bool false if nothing found, else array with (op, uid)
	 */
	public function validateLink() {
		$return = false;

		$lpcHash = GeneralUtility::_GET('lpcHash');

		if (strlen($lpcHash) > 0) {
			$uid = GeneralUtility::_GET('uid');
			$op = GeneralUtility::_GET('op');

			// check hash
			if ($this->calcHash($uid.$op) == $lpcHash) {
				$return = array(
					'op'	=> $op,
					'uid'	=> $uid
				);
			}
		}
		return $return;
	}

	public function getCurrentUrl() {
		$url = GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL');
		$url = strtok($url, '?');
		return $url;
	}

	/**
	 * @param $data the date to build hash
	 * @return string the hash
	 */
	protected function calcHash($data) {
		// use typo3 encryptionKey
		$encrKey = $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'];

		// and extension secret
		$extSecret = 'urlHashing_Lpc';

		$hash = hash('sha256', $encrKey.$data.$extSecret);
		return $hash;
	}
}