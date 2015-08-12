<?php
namespace Lpc\LpcPrayer\ViewHelper;

/***************************************************************
*  Copyright notice
*
*  (c) 2012 Joachim Mathes <mathes@punkt.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
*
 *	STEPS
 * 	=====
 *		* copy files: ViewHelper, Validator
 *		* fix namespaces
 * 		* add flexform option
 *				<settings.captcha> ...
 *		* extend model with captcha set/get (as type string)
 *		* add @validate to controller or model
 * 				@validate $newMessage \Lpc\LpcPrayer\Validation\Validator\CaptchaValidator
 * 		* use in fluid
 *				{namespace lpc = Lpc\LpcPrayer\ViewHelper}
 *				<lpc:captcha></lpc:captcha>
 *		* add language texts
*
***************************************************************/
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Captcha
 *
 * @package pt_extbase
 * @subpackage ViewHelpers
 */
class CaptchaViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper {

	protected $tagName = 'img';

	/**
	 * @var string
	 */
	protected $captchaGeneratorPath;

	/**
	 * Initialize ViewHelper
	 *
	 * @return void
	 */
	public function initialize() {
		parent::initialize();
		$this->registerUniversalTagAttributes();
		if (ExtensionManagementUtility::isLoaded('captcha')) {
			$this->captchaGeneratorPath = ExtensionManagementUtility::siteRelPath('captcha') . 'captcha/captcha.php';
		}
	}

	/**
	 * Render
	 *
	 * @return string
	 */
	public function render() {
		$return = '';
		if (ExtensionManagementUtility::isLoaded('captcha')) {
			$this->tag->addAttribute('src', $this->captchaGeneratorPath);
			$return = $this->tag->render();
		}
		return $return;
	}
	
}
