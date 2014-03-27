<?php
/**
 *	mrfb_template plugin
 *	@package mrfb_template
 *	@author Emmanuel Salomon @ManUtopiK
 *	@license GNU Affero General Public License, version 3 or late
 *	@link https://github.com/revenudebase/mrfb_template
 *
 * PHP functions library file
 *
 **/



/**
 * Store some javascript code to be executed when page change in full ajax.
 * @param  Array/String      $code      Some javascript code to be executed
 * @param  Boolean           $count     If true, return number of items
 * @return Boolean/Array                Return true when code is stored / A call with no var (mrfb_execute_js()) return an array of stored items, and clear this array.
 */
function mrfb_execute_js($code = null, $count = false) {
	if (!isset($_SESSION['js_code'])) {
		$_SESSION['js_code'] = array();
	}

	if (!$count) {
		if (!empty($code) && is_array($code)) {
			$_SESSION['js_code'] = array_merge($_SESSION['js_code'], $code);
			return true;
		} else if (!empty($code) && is_string($code)) {
			$_SESSION['js_code'][] = $code;
			return true;
		} else if (is_null($code)) {
			$returnarray = $_SESSION['js_code'];
			$_SESSION['js_code'] = array(); // clear variable fo this session
			return $returnarray;
		}
	} else {
		return count($_SESSION['js_code']);
	}
	return false;
}