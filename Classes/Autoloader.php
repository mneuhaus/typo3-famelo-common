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
class Autoloader {
	public static function autoloadFlexforms($_EXTKEY) {
		global $TCA, $_EXTKEY;

		$flexFormPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/FlexForms/';
		$extensionName = \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($_EXTKEY);

		$flexForms = \TYPO3\CMS\Core\Utility\GeneralUtility::getFilesInDir($flexFormPath, 'xml');

		foreach ($flexForms as $FlexForm) {
			$fileKey = str_replace('.xml', '', $FlexForm);
			$pluginSignature = strtolower($extensionName . '_' . $fileKey);

			$TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,recursive';
			$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';

			\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/' . $fileKey . '.xml');
		}
	}
}
