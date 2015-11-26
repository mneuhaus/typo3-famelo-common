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
class ObjectStorageSorter {
	public static function sort($subject, $property) {
		if (is_object($subject) && method_exists($subject, 'toArray')) {
			$subject = $subject->toArray();
		}

		usort($subject, function($left, $right) use ($property) {
			$leftProperty = \TYPO3\CMS\Extbase\Reflection\ObjectAccess::getPropertyPath($left, $property);
			$rightProperty = \TYPO3\CMS\Extbase\Reflection\ObjectAccess::getPropertyPath($right, $property);
			return intval($leftProperty) > intval($rightProperty);
		});
		return $subject;
	}

	public static function getSorting($table, $uid) {
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'sorting',
			$table,
			'uid= "' . $uid . '" ' . $GLOBALS['TSFE']->sys_page->enableFields($table),
			'',
			'sorting'
		);
		if (count($rows) > 0) {
			return $rows[0]['sorting'];
		}
	}
}
?>
