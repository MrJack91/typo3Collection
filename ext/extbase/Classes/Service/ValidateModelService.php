<?php

namespace Lpc\LpcDonation\Service;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * ValidateModelService class
 * Helper for the validation of a model
 *
 */
class ValidateModelService implements \TYPO3\CMS\Core\SingletonInterface
{

	/** @var array db fields */
	protected $mandatoryFields;

	/** @var array collection of errors */
	protected $errors = array();

	/**
	 * Checks
	 * @param $model
	 * @param $mandatoryFields
	 * @return array with errors (empty if no errors)
	 */
	public function validate($model, $mandatoryFields) {
		$this->mandatoryFields = explode(',', $mandatoryFields);

		$props = $this->getPropertyNames();

		foreach ($props as $db => $prop) {
			$val = $model->{'get'.$prop}();
			if (strlen($val) == 0) {
				$errors[$prop] = $db;
			}
		}
		return $errors;
	}

	/**
	 * convert the db names to extbase model name convention
	 * @return array
	 */
	protected function getPropertyNames() {
		$props = array();
		foreach ($this->mandatoryFields as $fieldName) {
			$propName = '';
			$nameParts = explode('_', $fieldName);
			foreach ($nameParts as $namePart) {
				$propName .= ucfirst($namePart);
			}
			$props[$fieldName] = $propName;
		}
		return $props;
	}

	/*
	public function getErrorTexts() {
		foreach ($this->errors as $error) {
			LocalizationUtility::translate()
		}
	}
	*/

}