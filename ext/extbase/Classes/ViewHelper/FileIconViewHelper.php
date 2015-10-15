<?php
namespace LPC\VinbernAktuell\ViewHelpers;

/**
 * Class FileIconViewHelper
 * @package LPC\VinbernAktuell\ViewHelpers
 * A view helper for showing filetype-image by file extension
 */
class FileIconViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper {

	/**
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
	 * @inject
	 */
	protected $configurationManager;

	/** @var string  */
	protected $tagName = 'img';

	/**
	 * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
	 */
	public function initializeArguments() {
		$this->registerArgument('filePath', 'string', 'Gets the filetype by this Extension', true);
	}

	/**
	 * @return string
	 */
	public function render() {
		$filePath = $this->arguments['filePath'];
		$settings = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);

		// get file extension
		$fileExtension = end(explode('.', $filePath));

		// set image path together
		$imgPath = $settings['fileicon']['path'].'/'.$fileExtension.'.'.$settings['fileicon']['extension'];

		if (!file_exists($_SERVER['DOCUMENT_ROOT'].$imgPath)) {
			$imgPath = $settings['fileicon']['path'].'/default.'.$settings['fileicon']['extension'];
		}

		// html tag
		$this->tag->addAttribute('src', $imgPath);

		return $this->tag->render();
	}
}