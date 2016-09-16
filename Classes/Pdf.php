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

use Famelo\FameloCommon\ViewHelpers\Pdf\CoverViewHelper;
use Famelo\PDF\Generator\PdfGeneratorInterface;
use Famelo\FameloCommon\ViewHelpers\Pdf\HeaderViewHelper;
use Famelo\FameloCommon\ViewHelpers\Pdf\FooterViewHelper;


 /**
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
 	protected $format;

 	/**
 	 * @var array
 	 */
 	protected $options = array();

 	/**
 	 *
 	 * @var string
 	 */
 	protected $defaultGenerator;

 	/**
 	 *
 	 * @var array
 	 */
 	protected $defaultGeneratorOptions;

 	/**
 	 * @var PdfGeneratorInterface
 	 */
 	protected $generator;

 	/**
 	 * @var string
 	 */
 	protected $templateSource;

	public function __construct($document, $format = 'A4', $request = NULL, $view = NULL) {
		$this->view = new \Famelo\FameloCommon\View\StandaloneView();
		if ($request !== NULL) {
			$this->view->setRequest($request);
		}
		if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['pdfGenerator'])) {
			throw new \Exception('you need to add a generator to your Localconfiguration!');
		}

		$this->defaultGenerator = $GLOBALS['TYPO3_CONF_VARS']['SYS']['pdfGenerator'];
		if (isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['pdfGeneratorOptions'])) {
			$this->defaultGeneratorOptions = $GLOBALS['TYPO3_CONF_VARS']['SYS']['pdfGeneratorOptions'];
		}

		$this->format = $format;
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

 	public function setTemplateSource($templateSource) {
 		$this->templateSource = $templateSource;
 	}

 	public function setFormat($format) {
 		$this->format = $format;
 	}

 	public function getGenerator() {
 		if (!$this->generator instanceof PdfGeneratorInterface) {
 			$this->generator = new $this->defaultGenerator($this->defaultGeneratorOptions, $this->view);
 		}
 		foreach ($this->options as $name => $value) {
 			$this->generator->setOption($name, $value);
 		}
 		return $this->generator;
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

		return $this->view->render();
 	}

 	public function setOptionsByViewHelper($generator) {
 		$viewHelperVariableContainer = $this->view->getViewHelperVariableContainer();
 		if ($viewHelperVariableContainer->exists(HeaderViewHelper::class, 'header')) {
 			$header = $viewHelperVariableContainer->get(HeaderViewHelper::class, 'header');
 			$generator->setHeader($header);
 		}
        $viewHelperVariableContainer = $this->view->getViewHelperVariableContainer();
        if ($viewHelperVariableContainer->exists(FooterViewHelper::class, 'footer')) {
            $footer = $viewHelperVariableContainer->get(FooterViewHelper::class, 'footer');
            $generator->setFooter($footer);
        }
        $viewHelperVariableContainer = $this->view->getViewHelperVariableContainer();
        if ($viewHelperVariableContainer->exists(CoverViewHelper::class, 'cover')) {
            $footer = $viewHelperVariableContainer->get(CoverViewHelper::class, 'cover');
            $generator->setCover($footer);
        }
 	}

 	public function setOption($name, $value) {
 		$this->options[$name] = $value;
 	}

 	public function send($filename = NULL) {
 		$content = $this->render();
 		$generator = $this->getGenerator();
 		$this->setOptionsByViewHelper($generator);
 		$generator->setFormat($this->format);
 		$generator->sendPdf($content, $filename);
 	}

 	public function download($filename = NULL) {
 		$content = $this->render();
 		$generator = $this->getGenerator();
 		$this->setOptionsByViewHelper($generator);
 		$generator->setFormat($this->format);
 		$generator->downloadPdf($content, $filename);
 	}

 	public function save($filename) {
 		$content = $this->render();
 		$generator = $this->getGenerator();
 		$this->setOptionsByViewHelper($generator);
 		$generator->setFormat($this->format);
 		$generator->savePdf($content, $filename);
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
