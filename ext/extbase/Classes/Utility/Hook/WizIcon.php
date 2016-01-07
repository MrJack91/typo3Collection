<?php
namespace Lpc\LpcKoolEvents\Utility\Hook;

use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class WizIcon
 * @author Michael Hadorn <michael.hadorn@laupercomputing.ch>
 * @package TYPO3
 * @subpackage
 * @package Lpc\LpcDonation\Utility\Hook
 * Example ext_tables.php: (IF BE ist nicht nötig)
 *      $TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['Lpc\LpcDonation\Utility\Hook\WizIcon'] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Classes/Utility/Hook/WizIcon.php';
 * 		add ressources to locallang
 * 		add wiz icon (24x24px)
 */
class WizIcon {

	/**
	 * Processing the wizard items array
	 *
	 * @param array $wizardItems
	 * @return array
	 */
	public function proc($wizardItems = array()) {
	    $translateBase = 'LLL:EXT:lpc_kool_events/Resources/Private/Language/locallang_be.xlf:wizIcon';

	    // hier den Extension-Name einfügen (ohne _) - obwohl extbase muss hier das pi1 angegeben werden
		$wizardItems['plugins_tx_lpcKoolEvents_pi1'] = array(
			'icon' => ExtensionManagementUtility::extRelPath('lpc_kool_events') . 'Resources/Public/Icons/ce_wiz.png', // Extension Key angeben (mit _)
			'title' => LocalizationUtility::translate($translateBase.'.title', 'lpcKoolEvents'),               // Ressoursce erstellen (wird via Extension Key ohne _ eingebunden)
			'description' => LocalizationUtility::translate($translateBase.'.description', 'lpcKoolEvents'),   // "
			'params' => '&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=lpckoolevents_event',          // Genauer Plugin Key angeben (kommt von registerPlugin(): Extension und Plugin Key mit _) -> kann in der Plugin Select List im Backend überprüft werden
			'tt_content_defValues' => array(
				'CType' => 'list',
			),
		);

		return $wizardItems;
	}
}