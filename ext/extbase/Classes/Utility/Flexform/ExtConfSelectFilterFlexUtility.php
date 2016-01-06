<?php

namespace Lpc\LpcKoolEvents\Utility\Flexform;

/**
 * Class ExtConfSelectFilterFlexUtility
 * @author Michael Hadorn <michael.hadorn@laupercomputing.ch>
 * @package TYPO3
 * @subpackage
 * @package Lpc\LpcKoolEvents\Utility\Flexform
 */
class ExtConfSelectFilterFlexUtility implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * Let the extconf define, which items should be shown (empty means all. Else explicit list of allowed items by value)
	 * @param $config
	 * @return mixed
	 */
	public function filter($config) {
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['lpc_kool_events']);
		$enableLayouts = trim($extConf['enableLayouts']);

		// if empty, show all items
		if ($enableLayouts !== '') {
			// show only explicit allowed items
			$enabledLayouts = explode(',', $enableLayouts);
			foreach ($config['items'] as $key =>$item) {
				$action = $item[1];
				if (!in_array($action, $enabledLayouts)) {
					// remove from list
					unset($config['items'][$key]);
				}
			}
		}

		return $config;
	}
}