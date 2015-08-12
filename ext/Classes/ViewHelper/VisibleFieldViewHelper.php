<?php
namespace LPC\VinbernAktuell\ViewHelpers;

/**
 * Class FileIconViewHelper
 * @package LPC\VinbernAktuell\ViewHelpers
 * A view helper for showing filetype-image by file extension
 */
class VisibleFieldViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * categoryRepository
	 *
	 * @var \LPC\VinbernAktuell\Domain\Repository\CategoryRepository
	 * @inject
	 */
	protected $categoryRepository = NULL;

	/**
	 * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
	 */
	public function initializeArguments() {
		$this->registerArgument('category', 'integer', 'defines the category who is showing', true);
		$this->registerArgument('field', 'string', 'defines the field to check of visibility', true);
	}

	public function render() {
		/** @var \LPC\VinbernAktuell\Domain\Model\Category $category */
		$category = $this->arguments['category'];
		$field = $this->arguments['field'];

		$cat = $this->categoryRepository->findByUid($category);

		return $cat->showField($field);
	}
}