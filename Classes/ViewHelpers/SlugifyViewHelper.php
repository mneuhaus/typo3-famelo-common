<?php
namespace Famelo\FameloCommon\ViewHelpers;

/**
 * Slugify view helper to generate dom IDs/url slugs
 */
class SlugifyViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper {
	/**
	 * Slugifies a value
	 *
	 * @param string $value The value to be processed
	 * @param array $replace The replace array (keys will be replaced with values)
	 * @param string $group
	 * @param string $as
	 * @return string $value
	 */
	public function render($value = NULL, $phoenticReplacements = array(), $group = NULL, $as = 'slug') {
		if ($value === NULL) {
			$value = $this->renderChildren();
			return \Famelo\FameloCommon\String::slugify($value, $phoenticReplacements, $group);
		}

		$value = \Famelo\FameloCommon\String::slugify($value, $phoenticReplacements, $group);

		$this->templateVariableContainer->add($as, $value);
		$output = $this->renderChildren();
		$this->templateVariableContainer->remove($as);

		if (strlen($output) > 0) {
			return $output;
		}

		return $value;
	}
}
