<?php
namespace Lpc\LpcDonation\Utility\Hook;

use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class WizIcon
 * @author Michael Hadorn <michael.hadorn@laupercomputing.ch>
 * @package TYPO3
 * @subpackage
 * @package Lpc\LpcDonation\Utility\Hook
 * Exmpale: (IF BE ist nicht nötig)
 *      $TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['Lpc\LpcDonation\Utility\Hook\WizIcon'] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Classes/Utility/Hook/WizIcon.php';
 */
class WizIcon {

	/**
	 * Processing the wizard items array
	 *
	 * @param array $wizardItems
	 * @return array
	 */
	public function proc($wizardItems = array()) {
	    // hier den Extension-Name einfügen (ohne _) - obwohl extbase muss hier das pi1 angegeben werden
		$wizardItems['plugins_tx_lpcDonation_pi1'] = array(
			'icon' => ExtensionManagementUtility::extRelPath('lpc_donation') . 'Resources/Public/Icons/ce_wiz.png', // Extension Key angeben (mit _)
			'title' => LocalizationUtility::translate('tx_lpcdonation.wizicon.title', 'lpcDonation'),               // Ressoursce erstellen (wird via Extension Key ohne _ eingebunden)
			'description' => LocalizationUtility::translate('tx_lpcdonation.wizicon.description', 'lpcDonation'),   // "
			'params' => '&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=lpcdonation_pi1',          // Genauer Plugin Key angeben (kommt von registerPlugin(): Extension und Plugin Key mit _) -> kann in der Plugin Select List im Backend überprüft werden
			'tt_content_defValues' => array(
				'CType' => 'list',
			),
		);

		return $wizardItems;
	}
}