<?php

/**
 * InsertTemplate extension for BlueSpice
 *
 * Dialog to insert templates.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice for MediaWiki
 * For further information visit http://www.blue-spice.org
 *
 * @author     Josef Konrad <konrad@hallowelt.com>
 * @version    2.27.1
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Class for insert templates.
 */
class InsertTemplate extends BsExtensionMW {
	/**
	 * Initialize the InsertTemplate extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::' . __METHOD__ );

		$this->setHook( 'VisualEditorConfig' );
		$this->setHook( 'BSExtendedEditBarBeforeEditToolbar' );

		wfProfileOut( 'BS::' . __METHOD__ );
	}

	/**
	 * Hook Handler for VisualEditorConfig Hook
	 * @param array $aConfigStandard reference
	 * @param array $aConfigOverwrite reference
	 * @param array &$aLoaderUsingDeps reference
	 * @return boolean always true to keep hook alive
	 */
	public function onVisualEditorConfig( &$aConfigStandard, &$aConfigOverwrite, &$aLoaderUsingDeps ) {
		$aLoaderUsingDeps[] = 'ext.bluespice.insertTemplate';

		$iIndexStandard = array_search( 'unlink',$aConfigStandard["toolbar1"] );
		array_splice( $aConfigStandard["toolbar1"], $iIndexStandard + 1, 0, "bstemplate" );

		// Add context menu entry
		$aConfigStandard["contextmenu"] = str_replace('bsContextMenuMarker', 'bsContextMenuMarker bsContextTemplate', $aConfigStandard["contextmenu"] );
		return true;
	}

	/**
	 * Hook Handler to add the insert template button to the editor.
	 * @param array $aRows
	 * @param array $aButtonCfgs
	 * @return boolean always true to keep hook alive
	 */
	public function onBSExtendedEditBarBeforeEditToolbar( &$aRows, &$aButtonCfgs ) {
		$this->getOutput()->addModuleStyles( 'ext.bluespice.insertTemplate.styles' );
		$this->getOutput()->addModules( 'ext.bluespice.insertTemplate' );

		$aRows[0]['dialogs'][100] = 'bs-editButton-insertTemplate';

		$aButtonCfgs['bs-editButton-insertTemplate'] = array(
			'tip' => wfMessage( 'bs-insertTemplate-button-template-title' )->plain()
		);

		return true;
	}
}