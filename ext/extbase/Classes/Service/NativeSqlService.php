<?php

namespace Lpc\LpcPrayer\Service;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * NativeSqlService class
 * Does native sql queries
 *
 */
class NativeSqlService implements \TYPO3\CMS\Core\SingletonInterface
{

	/** @var bool is debug enabled */
	protected static $debug = false;

	/**
	 * NativeSqlService constructor.
	 */
	public function __construct() {
		// enable debug automatic if local developement detected
		$baseUrl = GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
		$baseUrl = trim($baseUrl, '/');
		if (substr($baseUrl, -6) == '.local') {
			$this->setDebug(true);
		}
	}

	/**
	 * @param $select
	 * @param $from
	 * @param $where
	 * @param $groupBy
	 * @param $orderBy
	 * @param $limit
	 * @return null
	 */
	public static function select($select, $from, $where = '', $groupBy = '', $orderBy = '', $limit = '') {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where, $groupBy, $orderBy, $limit);
		self::checkDebug();
		return $res;
	}

	/**
	 * @param $res
	 * @return mixed
	 */
	public static function goThrough($res) {
		return $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
	}

	/**
	 * @param $table
	 * @param $fieldValues
	 * @return mixed
	 */
	public static function insert($table, $fieldValues) {
		$GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $fieldValues);
		self::checkDebug();
		$newId = $GLOBALS['TYPO3_DB']->sql_insert_id();
		return $newId;
	}

	/**
	 * @param $table
	 * @param $where
	 * @param $fieldValues
	 * @return mixed
	 */
	public static function update($table, $where, $fieldValues) {
		$return = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, $fieldValues);
		self::checkDebug();
		return $return;
	}

	/**
	 * @param $table
	 * @param $where
	 * @return mixed
	 */
	public static function delete($table, $where) {
		$return = $GLOBALS['TYPO3_DB']->exec_DELETEquery($table, $where);
		self::checkDebug();
		return $return;
	}

	// ****** FUNCTIONS: creates Qeries ********************************************************************************

	/**
	 * Builds a select statement and returns it
	 * @param $select
	 * @param $from
	 * @param string $where
	 * @param string $groupBy
	 * @param string $orderBy
	 * @param string $limit
	 * @return mixed
	 */
	public static function selectStatement($select, $from, $where = '', $groupBy = '', $orderBy = '', $limit = '') {
		return $GLOBALS['TYPO3_DB']->SELECTquery($select, $from, $where, $groupBy, $orderBy, $limit);
	}

	/**
	 * Builds an insert statement and returns it
	 * @param $table
	 * @param $fieldValues
	 * @return mixed
	 */
	public static function insertStatement($table, $fieldValues) {
		return $GLOBALS['TYPO3_DB']->INSERTquery($table, $fieldValues);
	}

	/**
	 * Builds an update statement and returns it
	 * @param $table
	 * @param $where
	 * @param $fieldValues
	 * @return mixed
	 */
	public static function updateStatement($table, $where, $fieldValues) {
		return $GLOBALS['TYPO3_DB']->UPDATEquery($table, $where, $fieldValues);
	}

	/**
	 * Builds an delete statement and returns it
	 * @param $table
	 * @param $where
	 * @return mixed
	 */
	public static function deleteStatement($table, $where) {
		return $GLOBALS['TYPO3_DB']->DELETEquery($table, $where);
	}


	// ****** PROTECTED FUNCTIONS **************************************************************************************

	/**
	 * check if debug information should be shown
	 */
	protected static function checkDebug() {
		if (self::$debug) {
			echo $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery . "\n<br>\n";
		}
	}

	// ****** GETTER AND SETTERS ***************************************************************************************

	/**
	 * @return boolean
	 */
	public static function isDebug() {
		return self::$debug;
	}

	/**
	 * @param boolean $debug
	 */
	public static function setDebug($debug) {
		self::$debug = $debug;
		if ($debug) {
			$debug = 1;
		}
		$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = $debug;
	}
}