<?php
/**
 * Created by PhpStorm.
 * User: jkonrad
 * Date: 3/21/17
 * Time: 11:00 AM
 */

class BSApiInsertTemplateDataStore extends BSApiExtJSStoreBase {

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