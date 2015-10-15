<?php
/**
 * Lauper Computing
 * User: Michael Hadorn <michael.hadorn@laupercomputing.ch>
 * Date: 09/10/15
 * Time: 09:45
 */

/**
 * Class (imported from lpc_sermons)
 * @author Michael Hadorn <michael.hadorn@laupercomputing.ch>
 * @package TYPO3
 */
class tx_koolsermons_importsermons_addFields implements tx_scheduler_AdditionalFieldProvider {

	/**
	 * Gets additional fields to render in the form to add/edit a task
	 *
	 * @param array $taskInfo Values of the fields from the add/edit task form
	 * @param tx_scheduler_Task $task The task object being edited. Null when adding a task!
	 * @param tx_scheduler_Module $schedulerModule Reference to the scheduler backend module
	 * @return array A two dimensional array, array('Identifier' => array('fieldId' => array('code' => '', 'label' => '', 'cshKey' => '', 'cshLabel' => ''))
	 */
	public function getAdditionalFields(array &$taskInfo, $task, tx_scheduler_Module $schedulerModule) {

		$additionalFields = array();

		// sourcepath
		$fieldName = 'tx_scheduler[sourcepath]';
		$fieldId = 'task_tx_lpcsermons_sourcepath';
		$fieldValue = $task->sourcepath;
		$fieldHtml = '<input type="text" name="' . $fieldName . '" id="' . $fieldId . '" value="' . htmlspecialchars($fieldValue) . '" size="40" />';
		$additionalFields[$fieldId] = array(
			'code' => $fieldHtml,
			'label' => 'Quellpfad',
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
			'label' => 'Zielpfad',
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
			'label' => 'PID (generell: "1" oder pro Sprache: "de=>1;en=>2")',
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
			'label' => 'Podcast ID (generell: "1" oder pro Sprache: "de=>1;en=>2")',
			'cshKey' => '_txlpcsermons',
			'cshLabel' => $fieldId
		);

		return $additionalFields;
	}

	/**
	 * Validates the additional fields' values
	 *
	 * @param array $submittedData An array containing the data submitted by the add/edit task form
	 * @param tx_scheduler_Module $schedulerModule Reference to the scheduler backend module
	 * @return boolean TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
	 */
	public function validateAdditionalFields(array &$submittedData, tx_scheduler_Module $schedulerModule) {
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
	 * @param tx_scheduler_Task $task Reference to the scheduler backend module
	 * @return void
	 */
	public function saveAdditionalFields(array $submittedData, tx_scheduler_Task $task) {
		$task->sourcepath = $submittedData['sourcepath'];
		$task->targetpath = $submittedData['targetpath'];
		$task->pid = $submittedData['pid'];
		$task->podcastid = $submittedData['podcastid'];
	}

}
 