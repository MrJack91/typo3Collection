<?php

namespace Lpc\LpcSermons\Service;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * FlexFormService class
 * Helpers for the flexform generate process
 *
 */
class FlexFormService implements \TYPO3\CMS\Core\SingletonInterface
{

	protected $dbRows = array(
		'title',
		'preacher',
		'image',
		'date',
		'description',
		'language',
		'reference',
		'url',
		'videourl',
		'resources',
		'player',
		'download',
		'duration',
		'filesize',
	);

	public function selectTableFields($config)
	{
		// fill db fields
		foreach ($this->dbRows as $row) {
			$colName = LocalizationUtility::translate('tx_lpcsermons.sermon.'.$row, 'lpc_sermons') ?
					LocalizationUtility::translate('tx_lpcsermons.sermon.'.$row, 'lpc_sermons') : $row;
			$config['items'][] = array($colName, $row);
		}

		//Set maxitems for select according to number of eventgroups
		$config['config']['maxitems'] = count($config['items']);

		return $config;
	}
}