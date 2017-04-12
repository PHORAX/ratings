<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Dmitry Dulepov [netcreators] <dmitry@typo3.org>
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
 * This class contains API for ratings. There are two ways to use this API:
 * <ul>
 * <li>Call {@link getRatingValue} to obtain rating value and process it yourself</li>
 * <li>Call {@link getRatingDisplay} to format and display rating value along with a control to change rating</li>
 * </ul>
 *
 * @author	Dmitry Dulepov [netcreators] <dmitry@typo3.org>
 * @package	TYPO3
 * @subpackage	tx_ratings
 */
class tx_ratings_api {

	/**
	 * Instance of TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
	 *
	 * @var TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
	 */
	protected $cObj;

	/** @var  t3lib_DB */
	protected $databaseHandle;

	/**
	 * Creates an instance of this class
	 *
	 */
	public function __construct() {
		$this->cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer');
		$this->cObj->start('', '');
		$this->databaseHandle = $GLOBALS['TYPO3_DB'];
	}

	/**
	 * Fetches data and calculates rating value for $ref. Rating values are from
	 * 0 to 100.
	 *
	 * @param	string		$ref	Reference to item in TYPO3 "datagroup" format (like tt_content_10)
	 * @param	array		$conf	Configuration array
	 * @return	float		Rating value (from 0 to 100)
	 */
	public function getRatingValue($ref, $conf = null) {
		if (is_null($conf)) {
			$conf = $this->getDefaultConfig();
		}
		$rating = $this->getRatingInfo($ref);
		return max(0, 100*(floatval($rating['rating'])-intval($conf['minValue']))/(intval($conf['maxValue'])-intval($conf['minValue'])));
	}

	/**
	 * Retrieves default configuration of ratings.
	 * Uses plugin.tx_ratings_pi1 from page TypoScript template
	 *
	 * @return	array		TypoScript configuration for ratings
	 */
	public function getDefaultConfig() {
		return $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_ratings_pi1.'];
	}

	/**
	 * Generates HTML code for displaying ratings.
	 *
	 * @param	string		$ref	Reference
	 * @param	array		$conf	Configuration array
	 * @return	string		HTML content
	 */
	public function getRatingDisplay($ref, $conf = null) {
		if (is_null($conf)) {
			$conf = $this->getDefaultConfig();
		}

		// Get template
		if ($GLOBALS['TSFE']) {
			// Normal call
			$template = $this->cObj->fileResource($conf['templateFile']);
		}
		else {
			// Called from ajax
			$template = @file_get_contents(PATH_site . $conf['templateFile']);
		}
		if (!$template) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::devLog('Unable to load template code from "' . $conf['templateFile'] . '"', 'ratings', 3);
			return '';
		}
		return $this->generateRatingContent($ref, $template, $conf);
	}

	/**
	 * Retrieves current IP address
	 *
	 * @return	string		Current IP address
	 */
	public function getCurrentIp() {
		if (preg_match('/^\d{2,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $_SERVER['HTTP_X_FORWARDED_FOR'])) {
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		return $_SERVER['REMOTE_ADDR'];
	}

	/**
	 * Checks if item was already voted by current user
	 *
	 * @param	string		$ref	Reference
	 * @return	boolean		true if item was voted
	 */
	public function isVoted($ref) {
		list($rec) = $this->databaseHandle->exec_SELECTgetRows('COUNT(*) AS t',
					'tx_ratings_iplog',
					' reference=' . $this->databaseHandle->fullQuoteStr($ref, 'tx_ratings_iplog') .
					' AND ip='. $this->databaseHandle->fullQuoteStr($this->getCurrentIp(), 'tx_ratings_iplog') .
					$this->enableFields('tx_ratings_iplog'));
		return ($rec['t'] > 0);
	}


	/**
	 * Calculates image bar width
	 *
	 * @param	int		$rating	Rating value
	 * @param	array		$conf	Configuration
	 * @return	int
	 */
	protected function getBarWidth($rating, $conf) {
		return intval($conf['ratingImageWidth']*$rating);
	}

	/**
	 * Fetches rating information for $ref
	 *
	 * @param string $ref	Reference in TYPO3 "datagroup" format (i.e. tt_content_10)
	 * @return array Array with two values: rating and count, which is calculated rating value and number of votes respectively
	 */
	protected function getRatingInfo($ref) {
		$recs = $this->databaseHandle->exec_SELECTgetRows('rating,vote_count',
					'tx_ratings_data',
					' reference=' . $this->databaseHandle->fullQuoteStr($ref, 'tx_ratings_data') . $this->enableFields('tx_ratings_data'));
		return (count($recs) ? $recs[0] : array('rating' => 0, 'vote_count' => 0));
	}

	/**
	 * Generates rating content for given $ref using $template HTML template
	 *
	 * @param	string		$ref	Reference in TYPO3 "datagroup" format (i.e. tt_content_10)
	 * @param	string		$template	HTML template to use
	 * @param	array		$conf	Configuration array
	 * @return	string		Generated content
	 */
	protected function generateRatingContent($ref, $template, array &$conf) {
		// Init language
		if ($GLOBALS['LANG'] instanceof \TYPO3\CMS\Lang\LanguageService) {
			$language = &$GLOBALS['LANG'];
		}
		else {
			$language = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Lang\LanguageService::class);
			$language->init($GLOBALS['TSFE']->lang);
		}
		/* @var $language \TYPO3\CMS\Lang\LanguageService */

		$siteRelPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('ratings');
		$rating = $this->getRatingInfo($ref);
		if ($rating['vote_count'] > 0) {
			$rating_value = $rating['rating']/$rating['vote_count'];
			$rating_str = sprintf($language->sL('LLL:EXT:ratings/locallang.xml:api_rating'), $rating_value, $conf['maxValue'], $rating['vote_count']);
		} else {
			$rating_value = 0;
			$rating_str = $language->sL('LLL:EXT:ratings/locallang.xml:api_not_rated');
		}

		if ($conf['mode'] == 'static' || (!$conf['disableIpCheck'] && $this->isVoted($ref))) {
			$subTemplate = $this->cObj->getSubpart($template, '###TEMPLATE_RATING_STATIC###');
			$links = '';
		} else {
			$subTemplate = $this->cObj->getSubpart($template, '###TEMPLATE_RATING###');
			$voteSub = $this->cObj->getSubpart($template, '###VOTE_LINK_SUB###');
			// Make ajaxData
			$confCopy = $conf;
			unset($confCopy['userFunc']);
			$confCopy['templateFile'] = $GLOBALS['TSFE']->tmpl->getFileName($conf['templateFile']);
			$data = serialize(array(
				'pid' => $GLOBALS['TSFE']->id,
				'conf' => $confCopy,
				'lang' => $GLOBALS['TSFE']->lang,
			));
			$ajaxData = base64_encode($data);
			// Create links
			$links = '';
			for ($i = $conf['minValue']; $i <= $conf['maxValue']; $i++) {
				$check = md5($ref . $i . $ajaxData . $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']);
				$links .= $this->cObj->substituteMarkerArray($voteSub, array(
					'###VALUE###' => $i,
					'###REF###' => $ref,
					'###PID###' => $GLOBALS['TSFE']->id,
					'###CHECK###' => $check,
					'###SITE_REL_PATH###' => $siteRelPath,
					'###AJAX_DATA###' => rawurlencode($ajaxData),
				));
			}
		}

		$markers = array(
			'###PID###' => $GLOBALS['TSFE']->id,
			'###REF###' => htmlspecialchars($ref),
			'###TEXT_SUBMITTING###' => $language->sL('LLL:EXT:ratings/locallang.xml:api_submitting'),
			'###TEXT_ALREADY_RATED###' => $language->sL('LLL:EXT:ratings/locallang.xml:api_already_rated'),
			'###BAR_WIDTH###' => $this->getBarWidth($rating_value, $conf),
			'###RATING###' => $rating_str,
			'###TEXT_RATING_TIP###' => $language->sL('LLL:EXT:ratings/locallang.xml:api_tip'),
			'###SITE_REL_PATH###' => $siteRelPath,
			'###VOTE_LINKS###' => $links,
			'###RAW_COUNT###' => $this->cObj->stdWrap($rating['vote_count'], $conf['voteCountStdWrap.']),
			'###REVIEW_COUNT###' => $this->cObj->stdWrap($rating['vote_count'], $conf['reviewCountStdWrap.']),
			'###RAW_VOTE###' => $this->cObj->stdWrap($rating['rating'], $conf['ratingVoteStdWrap.']),
			'###RAW_VOTE_MAX###' => $this->cObj->stdWrap($conf['maxValue'], $conf['ratingMaxValueStdWrap.']),
			'###RAW_VOTE_MIN###' => $this->cObj->stdWrap($conf['minValue'], $conf['ratingMinValueStdWrap.']),
		);
		return $this->cObj->substituteMarkerArray($subTemplate, $markers);
	}

	/**
	 * Implements enableFields call that can be used from regular FE and eID
	 *
	 * @param	string		$tableName	Table name
	 * @return	string		SQL
	 */
	public function enableFields($tableName) {
		if ($GLOBALS['TSFE']) {
			return $this->cObj->enableFields($tableName);
		}
		/* @var $sys_page \TYPO3\CMS\Frontend\Page\PageRepository */
		$sys_page = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\Page\PageRepository::class);

		return $sys_page->enableFields($tableName);
	}
}


if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ratings/class.tx_ratings_api.php'])	{
	/** @noinspection PhpIncludeInspection */
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ratings/class.tx_ratings_api.php']);
}

?>
