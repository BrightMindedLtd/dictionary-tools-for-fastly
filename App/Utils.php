<?php

namespace App;

class Utils
{
	/**
	 * Plucks a certain field out of each object in the list.
	 * This has the same functionality and prototype of array_column() (PHP 5.5) but also supports objects.
	 *
	 * @see https://developer.wordpress.org/reference/classes/wp_list_util/pluck/
	 *
	 * @param array      $list      List of objects or arrays.
	 * @param int|string $field     Field from the object to place instead of the entire object
	 * @param int|string $index_key Optional. Field from the object to use as keys for the new array.
	 *                              Default null.
	 * @return array Array of found values. If `$index_key` is set, an array of found values with keys
	 *               corresponding to `$index_key`. If `$index_key` is null, array keys from the original
	 *               `$list` will be preserved in the results.
	 */
	public static function pluck($list, $field, $index_key = null)
	{
		$newlist = array();

		foreach ($list as $value) {
			if (is_object($value)) {
				if (isset($value->$index_key)) {
					$newlist[$value->$index_key] = $value->$field;
				} else {
					$newlist[] = $value->$field;
				}
			} else {
				if (isset($value[$index_key])) {
					$newlist[$value[$index_key]] = $value[$field];
				} else {
					$newlist[] = $value[$field];
				}
			}
		}

		return $newlist;
	}
}