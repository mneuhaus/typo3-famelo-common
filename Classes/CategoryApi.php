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
 * CategoryApi
 */
class CategoryApi {

	public static function getCategories($categoryUids, $recursive = FALSE) {
		$categories = array();
		foreach (explode(',', $categoryUids) as $baseCategory) {
			$category = static::getCategory($baseCategory);
			if ($recursive) {
				$category['children'] = static::getChildCategories($baseCategory);
			}
			$categories[] = $category;
		}
		return $categories;
	}

	public static function getChildCategories($parentUids, $children = array()) {
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			'sys_category',
			'parent IN (' . $parentUids . ')' .
			$GLOBALS['TSFE']->cObj->enableFields('sys_category'),
			'',
			'',
			'',
			'uid'
		);
		$children = array_replace($children, (array) $rows);
		if (count($rows) > 0) {
			static::getChildCategories(implode(',', array_values($rows)), $children);
		}
		return $children;
	}

	public static function getCategory($uid) {
		$row = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
			'*',
			'sys_category',
			'uid IN (' . $uid . ')' .
			$GLOBALS['TSFE']->cObj->enableFields('sys_category')
		);
		return $row;
	}

	public static function getRelatedCategories($uids, $field, $tablenames = 'tt_content') {
		return $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'sys_category.*',
			'sys_category, sys_category_record_mm',
			'
				sys_category.uid = sys_category_record_mm.uid_local
				AND fieldname = "' . $field . '"
				AND tablenames = "' . $tablenames . '"
				AND uid_foreign IN (' . $uids . ') ' .
				$GLOBALS['TSFE']->cObj->enableFields('sys_category')
		);
	}

}