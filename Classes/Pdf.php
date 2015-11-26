<?php
namespace Famelo\FameloCommon;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Marc Neuhaus <mneuhaus@famelo.com>, Famelo OHG
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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

require_once(PATH_typo3conf . 'ext/melos_rtb/Resources/Private/PHP/mpdf/mpdf.php');

/**
 *
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Pdf {
	/*
	 * @var string
	 */
	protected $templatePath = '@package/Resources/Private/Pdfs/@document.html';

	/*
	 * @var string
	 */
	protected $layoutRootPath = '@package/Resources/Private/Layouts/';

	/*
	 * @var string
	 */
	protected $partialRootPath = '@package/Resources/Private/Partials/';

	/**
	 * @var string
	 */
	protected $document = 'Standard';

	/**
	 * @var string
	 */
	protected $package = NULL;

	/**
	 * The view
	 *
	 * @var \TYPO3\CMS\Fluid\View\StandaloneView
	 */
	protected $view;

	/**
	 * @var string
	 */
	protected $options = array(
		'encoding' => '',
		'format' => 'A4',
		'orientation' => 'P',
		'fontSize' => 0,
		'font' => '',
		'marginLeft' => 0,
		'marginRight' => 0,
		'marginTop' => 0,
		'marginBottom' => 0,
		'marginHeader' => 0,
		'marginFooter' => 0
	);

	public function __construct($document, $request = NULL) {
		$this->view = new \TYPO3\CMS\Fluid\View\StandaloneView();
		if ($request !== NULL) {
			$this->view->setRequest($request);
			// $this->view->getRequest()->setControllerExtensionName($this->request->getControllerExtensionName());
			// $this->view->getRequest()->setPluginName($this->request->getPluginName());
		}

		$this->setDocument($document);
		$this->templatePath = PATH_typo3conf . 'ext/' . $this->templatePath;
		$this->layoutRootPath = PATH_typo3conf . 'ext/' . $this->layoutRootPath;
		$this->partialRootPath = PATH_typo3conf . 'ext/' . $this->partialRootPath;
	}

	public function setDocument($document) {
		$parts = explode(':', $document);
		if (count($parts) > 1) {
			$this->package = $parts[0];
			$this->document = $parts[1];
		} else {
			$this->document = $document;
		}
		return $this;
	}



	public function render() {
		$replacements = array(
			'@package' => $this->package,
			'@document' => $this->document
		);
		$template = str_replace(array_keys($replacements), array_values($replacements), $this->templatePath);
		$this->view->setTemplatePathAndFilename($template);

		$layoutRootPath = str_replace(array_keys($replacements), array_values($replacements), $this->layoutRootPath);
		$this->view->setLayoutRootPath($layoutRootPath);

		$partialRootPath = str_replace(array_keys($replacements), array_values($replacements), $this->partialRootPath);
		$this->view->setPartialRootPath($partialRootPath);

		$this->view->setFormat('html');

		// $this->view->getRequest()->setControllerPackageKey($this->package);

		return $this->view->render();
	}

	public function send($filename = NULL, $htmlFooter = NULL) {
		$content = $this->render();
		$previousErrorReporting = error_reporting(0);
        $pdf = $this->createMpdfInstance();
        $pdf->WriteHTML($content);
        $pdf->SetHTMLFooter($htmlFooter);
        $output = $pdf->Output($filename, 'i');
		error_reporting($previousErrorReporting);
	}

	public function download($filename = NULL, $htmlFooter = NULL) {
		$content = $this->render();
		$previousErrorReporting = error_reporting(0);
		$pdf = $this->createMpdfInstance();
        $pdf->WriteHTML($content);
        $pdf->SetHTMLFooter($htmlFooter);
        $output = $pdf->Output($filename, 'd');
		error_reporting($previousErrorReporting);
	}

	public function save($filename, $htmlFooter = NULL) {
		$content = $this->render();
		$previousErrorReporting = error_reporting(0);
		$pdf = $this->createMpdfInstance();
        $pdf->WriteHTML($content);
        $pdf->SetHTMLFooter($htmlFooter);
        $pdf->Output($filename, 'f');
		error_reporting($previousErrorReporting);
	}

	public function createMpdfInstance() {
		return new \mPDF(
			$this->options['encoding'],
			$this->options['format'],
			$this->options['fontSize'],
			$this->options['font'],
			$this->options['marginLeft'],
			$this->options['marginRight'],
			$this->options['marginTop'],
			$this->options['marginBottom'],
			$this->options['marginHeader'],
			$this->options['marginFooter'],
			$this->options['orientation']
		);
	}

	public function assign($key, $value) {
		$this->view->assign($key, $value);
		return $this;
	}

	public function assignMultiple(array $values) {
		foreach ($values as $key => $value) {
			$this->assign($key, $value);
		}
		return $this;
	}
}
?>
