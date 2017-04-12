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


define('TX_RATINGS_MIN', 0);
define('TX_RATINGS_MAX', 100);

/**
 * Plugin 'Ratings' for the 'ratings' extension.
 *
 * @author	Dmitry Dulepov [netcreators] <dmitry@typo3.org>
 * @package	TYPO3
 * @subpackage	tx_ratings
 */
class tx_ratings_pi1 extends \TYPO3\CMS\Frontend\Plugin\AbstractPlugin {
	var $prefixId      = 'tx_ratings_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_ratings_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'ratings';	// The extension key.
	var $pi_checkCHash = true;

	/**
	 * The main method of the PlugIn
	 *
	 * @param string $content: The PlugIn content
	 * @param array $conf: The PlugIn configuration
	 * @return string The content that is displayed on the website
	 */
	function main($content, $conf) {
		$this->mergeConfiguration($conf);

		if (!isset($this->conf['storagePid'])) {
			$this->pi_loadLL();
			return $this->pi_wrapInBaseClass($this->pi_getLL('no_ts_template'));
		}

		/* @var $api tx_ratings_api */
		$api = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_ratings_api');

		// adds possibility to change ref and so use this plugin with other plugins and not only pages
		if ($conf['flexibleRef']) {
			$conf['ref'] = $this->cObj->cObjGetSingle($conf['flexibleRef'], $conf['flexibleRef.']);
		}

		$content.= $api->getRatingDisplay($conf['ref'] ? $this->cObj->stdWrap($conf['ref'], $conf['ref' . '.']) : 'pages_' . $GLOBALS['TSFE']->id, $this->conf);

		return $this->pi_wrapInBaseClass($content);
	}


	/**
	 * Merges TS configuration with configuration from flexform (latter takes precedence).
	 *
	 * @param	array		$conf	Configuration from TS
	 * @return	void
	 */
	function mergeConfiguration($conf) {
		$this->conf = $conf;

		$this->fetchConfigValue('storagePid');
		$this->conf['storagePid'] = intval($this->conf['storagePid']);
		if ($this->conf['storagePid'] == 0) {
			$this->conf['storagePid'] = $GLOBALS['TSFE']->id;
		}
		$this->fetchConfigValue('templateFile');
	}

	/**
	 * Fetches configuration value from flexform. If value exists, value in
	 * <code>$this->conf</code> is replaced with this value.
	 *
	 * @param	string		$param	Parameter name. If <code>.</code> is found, the first part is section name, second is key (applies only to $this->conf)
	 * @return	void
	 */
	function fetchConfigValue($param) {
		$section = '';
		if (strchr($param, '.')) {
			list($section, $param) = explode('.', $param, 2);
		}
		$value = trim($this->pi_getFFvalue($this->cObj->data['pi_flexform'], $param, ($section ? 's' . ucfirst($section) : 'sDEF')));
		if (!is_null($value) && $value != '') {
			if ($section) {
				$this->conf[$section . '.'][$param] = $value;
			}
			else {
				$this->conf[$param] = $value;
			}
		}
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ratings/pi1/class.tx_ratings_pi1.php'])	{
	/** @noinspection PhpIncludeInspection */
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ratings/pi1/class.tx_ratings_pi1.php']);
}

?>
