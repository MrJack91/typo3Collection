<?php
namespace LPC\VinbernAktuell\ViewHelpers;

/**
 * Class SizeOverwritter
 * @package LPC\VinbernAktuell\ViewHelpers
 * A view helper to overwrite size attributes in xml tags. (e.g. iframe)
 */
class SizeOverwriterViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
	 */
	public function initializeArguments() {
		$this->registerArgument('width', 'string', 'defines the width-attr of the tag', false);
		$this->registerArgument('height', 'string', 'defines the height-attr of the tag', false);
	}

	public function render() {
		$newHeight = intval($this->arguments['height']);
		$newWidth = intval($this->arguments['width']);
		$content = $this->renderChildren();

		// read the height and width from tag
		preg_match_all('/(width|height)="([^"]*)"/', $content, $matches);

		$oldWidth = intval($matches[2][0]);
		$oldHeight = intval($matches[2][1]);
		$ratio = $oldWidth / $oldHeight;

		if ($oldWidth > 0 && $oldHeight > 0 && $ratio > 0) {
			if ($newHeight > 0 && $newWidth == 0) {
				// only height was set
				$newWidth = round($newHeight * $ratio);
			} elseif ($newWidth > 0 && $newHeight == 0) {
				// only width was set
				$newHeight = round($newWidth / $ratio);
			}
		}

		if ($newWidth > 0 && $newHeight > 0) {
			$content = preg_replace('/(width=")[^"]*(")/', '${1}'.$newWidth.'${2}', $content);
			$content = preg_replace('/(height=")[^"]*(")/', '${1}'.$newHeight.'${2}', $content);
		}

		return $content;
	}
}