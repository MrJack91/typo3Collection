<?php

namespace Lpc\LpcPrayer\Utility;

/**
 * Class PasswordUtility
 * @author Michael Hadorn <michael.hadorn@laupercomputing.ch>
 * @package TYPO3
 * @subpackage
 * @package Lpc\LpcPrayer\Utility
 */
class PasswordUtility {

	/**
	 * Creates an easy-to-remember mnemonic password.
	 * All passwords created by this method follow the scheme "every consonant is followed by a vowel" (e.g. "rexegubo")
	 *
	 * @param   integer     required length of password (optional, default is 8)
	 * @return  string      mnemonic password
	 * @author  Rainer Kuhn
	 */
	public static function create($length=8) {

		$consonantArr  = array('b','c','d','f','g','h','j','k','l','m','n','p','r','s','t','v','w','x','y','z');
		$vowelArr  = array('a','e','i','o','u');
		$password = '';

		if (!is_int($n = $length/2)) {
			$n = (integer)$length/2;
			$password .= $vowelArr[rand(0, 4)];
		}

		for ($i=1; $i<=$n; $i++) {
			$password .= $consonantArr[rand(0, 19)];
			$password .= $vowelArr[rand(0, 4)];
		}

		return $password;

	}

}
