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

	public static function getCategories($categoryUids, $includeChildren = FALSE) {
		$categories = array();
		foreach (explode(',', $categoryUids) as $baseCategory) {
			$category = static::getCategory($baseCategory);
			if ($includeChildren) {
				$category['children'] = static::getChildCategories($baseCategory);
			}
			$categories[] = $category;
		}
		return $categories;
	}

	public static function expandCategoryList($categoryUids) {
		$keys = array_keys(static::getChildCategories($categoryUids));
		$keys[] = $categoryUids;
		return implode(',', $keys);
	}

	public static function getChildCategories($parentUids, $children = array()) {
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			'sys_category',
			'parent IN (' . $parentUids . ') AND sys_language_uid = 0' .
			$GLOBALS['TSFE']->cObj->enableFields('sys_category'),
			'',
			'',
			'',
			'uid'
		);
		$children = array_replace($children, (array) $rows);
//		if (count($rows) > 0) {
//			$children = static::getChildCategories(implode(',', array_keys($rows)), $children);
//		}
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

	public function getItemsByCategories($categories, $tableName, $field = NULL, $limit = 10, $offset = 0) {
		if (is_array($categories)) {
			foreach ($categories as $key => $category) {
				if (is_array($category)) {
					$categories[$key] = $category['uid'];
				}
			}
			$categories = implode(',', $categories);
		}
		$query = $tableName . '.uid = sys_category_record_mm.uid_foreign
		AND tablenames = "' . $tableName . '"
		AND uid_local IN (' . $categories . ') ' .
		$GLOBALS['TSFE']->cObj->enableFields($tableName);

		if ($field !== NULL) {
			$query .= ' AND fieldname = "' . $field . '"';
		}

		return $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			$tableName . '.*',
			$tableName . ', sys_category_record_mm',
			$query,
			$tableName . '.uid',
			'',
			$offset . ',' . $limit
		);
	}

	public static function permutate() {
		$groups = array();
		foreach (func_get_args() as $argument) {
			if (is_string($argument)) {
				$argument = explode(',', $argument);
			}
			$groups[] = $argument;
		}

		$permutations = [];
		$iteration = 0;

		while (1) {
			$num = $iteration++;
			$pick = array();

			foreach($groups as $group) {
				$r = $num % count($group);
				$num = ($num - $r) / count($group);
				$pick[] = $group[$r];
			}

			if ($num > 0) break;

			$permutations[] = $pick;
		}

		$categories = array();
		foreach ($permutations as $key => $value) {
			$categories[] = '(' . implode(' AND ', $value) . ')';
		}
		return implode(' OR ', $categories);
	}

}
