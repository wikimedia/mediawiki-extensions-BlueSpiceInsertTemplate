<?php

/**
 * InsertTemplate extension for BlueSpice
 *
 * Dialog to insert templates.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
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
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
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
		$this->setHook( 'VisualEditorConfig' );
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

}
