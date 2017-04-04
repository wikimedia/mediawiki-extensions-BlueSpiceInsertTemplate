<?php
/**
 * This class serves as a backend for the data store of the InsertTemplate
 * extension
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
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 */
class BSApiInsertTemplateTemplateStore extends BSApiExtJSStoreBase {

	protected $aData = [];

	/**
	 * The API call method to get templates.
	 *
	 * @param string $sQuery
	 * @return array Returns favorite templates or the result from the query.
	 */
	protected function makeData( $sQuery = '' ) {
		$aFavorites = $this->getConfig()->get( 'Favorites' );

		if( empty ( $sQuery ) && count( $aFavorites ) > 0 ) {
			$this->loadFavs( $aFavorites );
		}
		else {
			$this->loadAllTemplatesFromDB( $sQuery );
		}

		return $this->aData;
	}

	/**
	 * Returns the selected favorites.
	 */
	private function loadFavs( $aFavs ) {
		foreach( $aFavs as $sTitle ) {
			$oTemplateTitle = Title::makeTitle( NS_TEMPLATE, $sTitle );
			$oTemplate = new stdClass();

			$oTemplate->id = $oTemplateTitle->getArticleID();
			$oTemplate->name = $oTemplateTitle->getText();
			$oTemplate->code = $this->parseTemplate( $oTemplateTitle );

			$this->aData[] = $oTemplate;
		}
	}

	/**
	 * Returns all templates bei $sQuery.
	 *
	 * @param string $sQuery The query string.
	 */
	private function loadAllTemplatesFromDB( $sQuery = '' ) {
		$res = $this->getDB()->select(
			['p' => 'page'],
			'*',
			[
				'page_namespace' => NS_TEMPLATE,
			]
		);

		foreach( $res as $row ) {

			if( stripos( $row->page_title, str_replace( ' ', '_', $sQuery ) ) !== false || $sQuery == '' ) {
				$oTemplateTitle = Title::makeTitle( NS_TEMPLATE, $row->page_title );

				$oTemplate = new stdClass();

				$oTemplate->id = $oTemplateTitle->getArticleID();
				$oTemplate->name = $oTemplateTitle->getText();
				$oTemplate->code = $this->parseTemplate( $oTemplateTitle );

				$this->aData[] = $oTemplate;
			}
		}
	}

	/**
	 * @return GlobalVarConfig
	 */
	public function getConfig() {
		return new GlobalVarConfig( 'bsgInsertTemplate' );
	}

	/**
	 * Returns the template code with variables.
	 *
	 * @param $oTemplateTitle
	 * @return string
	 */
	private function parseTemplate( $oTemplateTitle ): string {
		$oWikiPage = WikiPage::factory( $oTemplateTitle );
		$oContent = $oWikiPage->getContent();

		$sWikiText = '{{' . $oTemplateTitle->getDBkey() . '}}';

		if ( $oContent instanceof WikitextContent ) {
			$sWikiText = $oContent->getNativeData();

			if ( preg_match_all( '/\{\{\{(.*?)\}\}\}/', $sWikiText, $aMatches ) !== false ) {

				$sWikiText = '{{' . $oTemplateTitle->getDBkey() . "\n";

				foreach ( $aMatches[ 1 ] as $sMatch ) {
					$aMatch = explode( '|', $sMatch );

					if ( count( $aMatch ) > 0 ) {
						$sWikiText .= '|' . $aMatch[ 0 ] . '=' . $aMatch[ 1 ] . "\n";
					} else {
						$sWikiText .= '|' . $sMatch . "=\n";
					}
				}

				$sWikiText .= '}}';
			}
		}

		return $sWikiText;
	}
}