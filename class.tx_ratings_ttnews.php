<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Dmitry Dulepov (dmitry@typo3.org)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
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
 * This clas provides hook to tt_news to add extra markers.
 *
 * @author	Dmitry Dulepov <dmitry@typo3.org>
 * @package TYPO3
 * @subpackage comments
 */
class tx_ratings_ttnews {
	/**
	 * Processes comments-specific markers for tt_news
	 *
	 * @param	array		$markerArray	Array with merkers
	 * @param	array		$row	tt_news record
	 * @param	array		$lConf	Configuration array for current tt_news view
	 * @param	tx_ttnews	$pObj	Reference to parent object
	 * @return	array		Modified marker array
	 */
	public function extraItemMarkerProcessor(array &$markerArray, array &$row, &$lConf, &$pObj) {
		/* @var $pObj tx_ttnews */
		if ($row['tx_ratings_enable']) {
			$apiObj = t3lib_div::makeInstance('tx_ratings_api');
			$conf = $apiObj->getDefaultConfig();
			$conf['includeLibs'] = 'EXT:ratings/pi1/class.tx_ratings_pi1.php';
			$conf['ref'] = 'tt_news_' . $row['uid'];

			$cObj = t3lib_div::makeInstance('tslib_cObj');
			/* @var $cObj tslib_cObj */
			$cObj->start(array());
			$markerArray['###TX_RATINGS###'] = $cObj->cObjGetSingle('USER_INT', $conf);

			$cObj = t3lib_div::makeInstance('tslib_cObj');
			/* @var $cObj tslib_cObj */
			$cObj->start(array());
			$conf['mode'] = 'static';
			$markerArray['###TX_RATINGS_STATIC###'] = $cObj->cObjGetSingle('USER_INT', $conf);
		}
		else {
			$markerArray['###TX_RATINGS###'] = '';
			$markerArray['###TX_RATINGS_STATIC###'] = '';
		}
		return $markerArray;
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ratings/class.tx_ratings_ttnews.php']) {
	/** @noinspection PhpIncludeInspection */
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ratings/class.tx_ratings_ttnews.php']);
}

?>