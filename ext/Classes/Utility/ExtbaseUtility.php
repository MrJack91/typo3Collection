<?php

namespace Lpc\LpcDonation\Utility;

/**
 * Class ExtbaseUtility
 * @author Michael Hadorn <michael.hadorn@laupercomputing.ch>
 * @package TYPO3
 * @subpackage lpc_base
 * @package Lpc\LpcDonation\Utility
 */
class ExtbaseUtility {

	/**
	 * convert the db names into extbase model name convention
	 * @param array $fieldnames db fieldnames to convert
	 * @return array with db fieldname => prop name (without set/get)
	 */
	public static function getPropertyNames($fieldnames) {
		$props = array();
		foreach ($fieldnames as $fieldName) {
			$propName = '';
			$nameParts = explode('_', $fieldName);
			foreach ($nameParts as $namePart) {
				$propName .= ucfirst($namePart);
			}
			$props[$fieldName] = $propName;
		}
		return $props;
	}

}
