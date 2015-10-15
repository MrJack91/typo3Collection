<?php
/**
 * Lauper Computing
 * User: Michael Hadorn <michael.hadorn@laupercomputing.ch>
 * Date: 09/10/15
 * Time: 09:45
 */

namespace LPC\LpcSermons\Task;

/**
 * Class ImportSermonsFieldProvider
 * @author Michael Hadorn <michael.hadorn@laupercomputing.ch>
 * @package TYPO3
 * @subpackage
 * @package LPC\LpcSermons\Task
 */
class ImportSermonsFieldProvider implements \TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface {

	/**
	 * Gets additional fields to render in the form to add/edit a task
	 *
	 * @param array $taskInfo Values of the fields from the add/edit task form
	 * @param \TYPO3\CMS\Scheduler\Task\AbstractTask $task The task object being edited. Null when adding a task!
	 * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule Reference to the scheduler backend module
	 * @return array A two dimensional array, array('Identifier' => array('fieldId' => array('code' => '', 'label' => '', 'cshKey' => '', 'cshLabel' => ''))
	 */
	public function getAdditionalFields(array &$taskInfo, $task, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule) {

		$additionalFields = array();

		// sourcepath
		$fieldName = 'tx_scheduler[sourcepath]';
		$fieldId = 'task_tx_lpcsermons_sourcepath';
		$fieldValue = $task->sourcepath;
		$fieldHtml = '<input type="text" name="' . $fieldName . '" id="' . $fieldId . '" value="' . htmlspecialchars($fieldValue) . '" size="40" />';
		$additionalFields[$fieldId] = array(
			'code' => $fieldHtml,
			'label' => 'LLL:EXT:lpc_sermons/Resources/Private/Language/locallang.xlf:tx_lpcsermons.task.importSermons.settings.sourcepath',
			'cshKey' => '_txlpcsermons',
			'cshLabel' => $fieldId
		);

		// targetpath
		$fieldName = 'tx_scheduler[targetpath]';
		$fieldId = 'task_tx_lpcsermons_targetpath';
		$fieldValue = $task->targetpath;
		$fieldHtml = '<input type="text" name="' . $fieldName . '" id="' . $fieldId . '" value="' . htmlspecialchars($fieldValue) . '" size="40" />';
		$additionalFields[$fieldId] = array(
			'code' => $fieldHtml,
			'label' => 'LLL:EXT:lpc_sermons/Resources/Private/Language/locallang.xlf:tx_lpcsermons.task.importSermons.settings.targetpath',
			'cshKey' => '_txlpcsermons',
			'cshLabel' => $fieldId
		);

		// pid
		$fieldName = 'tx_scheduler[pid]';
		$fieldId = 'task_tx_lpcsermons_pid';
		$fieldValue = $task->pid;
		$fieldHtml = '<input type="text" name="' . $fieldName . '" id="' . $fieldId . '" value="' . htmlspecialchars($fieldValue) . '" size="40" />';
		$additionalFields[$fieldId] = array(
			'code' => $fieldHtml,
			'label' => 'LLL:EXT:lpc_sermons/Resources/Private/Language/locallang.xlf:tx_lpcsermons.task.importSermons.settings.pid',
			'cshKey' => '_txlpcsermons',
			'cshLabel' => $fieldId
		);

		// podcastid
		$fieldName = 'tx_scheduler[podcastid]';
		$fieldId = 'task_tx_lpcsermons_podcastid';
		$fieldValue = $task->podcastid;
		$fieldHtml = '<input type="text" name="' . $fieldName . '" id="' . $fieldId . '" value="' . htmlspecialchars($fieldValue) . '" size="40" />';
		$additionalFields[$fieldId] = array(
			'code' => $fieldHtml,
			'label' => 'LLL:EXT:lpc_sermons/Resources/Private/Language/locallang.xlf:tx_lpcsermons.task.importSermons.settings.podcastid',
			'cshKey' => '_txlpcsermons',
			'cshLabel' => $fieldId
		);

		return $additionalFields;
	}

	/**
	 * Validates the additional fields' values
	 *
	 * @param array $submittedData An array containing the data submitted by the add/edit task form
	 * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule Reference to the scheduler backend module
	 * @return boolean TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
	 */
	public function validateAdditionalFields(array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule) {
		$result = TRUE;

		// handle slashes
		foreach (array('sourcepath', 'targetpath') as $path) {
			$filepath = $submittedData[$path];
			if (strlen($filepath) > 0) {
				$submittedData[$path] = trim($filepath, '/').'/';
			}
		}

		return $result;
	}

	/**
	 * Takes care of saving the additional fields' values in the task's object
	 *
	 * @param array $submittedData An array containing the data submitted by the add/edit task form
	 * @param \TYPO3\CMS\Scheduler\Task\AbstractTask $task Reference to the scheduler backend module
	 * @return void
	 */
	public function saveAdditionalFields(array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task) {
		$task->sourcepath = $submittedData['sourcepath'];
		$task->targetpath = $submittedData['targetpath'];
		$task->pid = $submittedData['pid'];
		$task->podcastid = $submittedData['podcastid'];
	}

}
 