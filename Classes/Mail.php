<?php
namespace Famelo\FameloCommon;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2014 Marc Neuhaus <mneuhaus@famelo.com>, Famelo
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

/**
 */
class Mail extends \TYPO3\CMS\Core\Mail\MailMessage {
	/*
	 * @var string
	 */
	protected $templatePath = '@package/Resources/Private/Messages/@message.html';

	/**
	 * @var string
	 */
	protected $message = 'Standard';

	/**
	 * @var string
	 */
	protected $package = NULL;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Configuration\ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * The view
	 *
	 * @var Tx_Fluid_View_StandaloneView
	 */
	protected $view;

	public function __construct() {
		$this->view = new \TYPO3\CMS\Fluid\View\StandaloneView();
		parent::__construct();
		// $this->setFrom(array($messageSender['email'] => $messageSender['name']));
	}

	public function setMessage($message) {
		$parts = explode(':', $message);
		if (count($parts) > 1) {
			$this->package = $parts[0];
			$this->message = $parts[1];
		} else {
			$this->message = $message;
		}
		return $this;
	}

	public function send() {
		$this->setBody($this->render(), 'text/html');
		parent::send();
	}

	public function render() {
		$replacements = array(
			'@package' => $this->package,
			'@message' => $this->message
		);
		$template = str_replace(array_keys($replacements), array_values($replacements), $this->templatePath);
		//print_r($template);
		$this->view->setTemplatePathAndFilename(PATH_typo3conf . 'ext/' . $template);


		$gesucht = array("&lt;","&quot;","&gt;");
		$gefunden = array("<","'",">");
		$nv = str_replace($gesucht, $gefunden, $this->view->render());
		//print_r($nv);
		return $nv;
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
