<?php
namespace Lpc\LpcMessageboard\Validation\Validator;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 punkt.de GmbH
 *  Authors:
 *    Christian Herberger <herberger@punkt.de>,
 *    Ursula Klinger <klinger@punkt.de>,
 *    Daniel Lienert <lienert@punkt.de>,
 *    Joachim Mathes <mathes@punkt.de>
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
 ***************************************************************/
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Zora-Card-Id Validator
 *
 * @package pt_extbase
 * @subpackage Domain\Validator
 */
class MessageValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator {

	const CAPTCHA_SESSION_KEY = 'tx_captcha_string';

	protected $acceptsEmptyValues = FALSE;

	/**
	 * @var string
	 */
	protected $captchaString;

	/**
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
	 * @inject
	 */
	protected $configurationManager = null;

	/**
	 * Object Manager
	 *
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 * @inject
	 */
	protected $objectManager;


	/**
	 *
	 * @param \Lpc\LpcMessageboard\Domain\Model\Message $message
	 * @return bool TRUE if the value is valid, FALSE if an error occurred
	 */
	protected function isValid($message) {

		$captchaCheck = true;

		// load flex settings
		/*
		$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		$this->configurationManager = $objectManager->get('TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface');
		*/
		$settings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);

		$captchaString = $message->getCaptcha();

		// check captcha
		if (ExtensionManagementUtility::isLoaded('captcha') && $settings['captcha']) {
			session_start();
			if ($captchaString != $_SESSION[self::CAPTCHA_SESSION_KEY]) {
				$error = $this->objectManager->get('TYPO3\CMS\Extbase\Validation\Error', '', 1389545453);
				$this->result->forProperty('captcha')->addError($error);
				$this->addError('LpcMessageboard.CaptchaStringValidator.InputStringWrong', 1340029430);
				$captchaCheck = false;
			}
		}

		return $captchaCheck;
	}

}