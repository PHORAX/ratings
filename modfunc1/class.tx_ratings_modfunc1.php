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
 * Module extension (addition to function menu) 'Ratings' for the 'ratings' extension.
 *
 * @author	Dmitry Dulepov [netcreators] <dmitry@typo3.org>
 * @package	TYPO3
 * @subpackage	tx_ratings
 */
class tx_ratings_modfunc1 extends \TYPO3\CMS\Backend\Module\AbstractFunctionModule {

	/**
	 * Returns the module menu
	 *
	 * @return	Array with menuitems
	 */
	function modMenu() {

		return array (
			"tx_ratings_modfunc1_check" => "",
		);
	}

	/**
	 * Main method of the module
	 *
	 * @return	string
	 */
	function main() {
		$theOutput = '';
		/** @var \TYPO3\CMS\Lang\LanguageService $language */
		$language = $GLOBALS['LANG'];

		$theOutput.= $this->pObj->doc->spacer(5);
		$theOutput.= $this->pObj->doc->section($language->getLL("title"),"Dummy content here...",0,1);

		$menu = array();
		$menu[] = \TYPO3\CMS\Backend\Utility\BackendUtility::getFuncCheck($this->wizard->pObj->id,"SET[tx_ratings_modfunc1_check]",$this->wizard->pObj->MOD_SETTINGS["tx_ratings_modfunc1_check"]).$language->getLL("checklabel");
		$theOutput.= $this->pObj->doc->spacer(5);
		$theOutput.= $this->pObj->doc->section("Menu",implode(" - ",$menu),0,1);

		return $theOutput;
	}
}



if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ratings/modfunc1/class.tx_ratings_modfunc1.php'])	{
	/** @noinspection PhpIncludeInspection */
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ratings/modfunc1/class.tx_ratings_modfunc1.php']);
}

?>