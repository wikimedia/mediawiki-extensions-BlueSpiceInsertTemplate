<?php

/**
 * @group medium
 * @group api
 * @group BlueSpice
 * @group BlueSpiceInsertTemplate
 */
class BSApiInsertTemplateTemplateStoreTest extends BSApiExtJSStoreTestBase {
	protected $iFixtureTotal = 3;

	protected function getStoreSchema() {
		return [
			'id' => [
				'type' => 'integer'
			],
			'name' => [
				'type' => 'string'
			],
			'code' => [
				'type' => 'string'
			]
		];
	}

	protected function createStoreFixtureData() {
		$this->insertPage( 'Template:Test1Page', '=== Dummy ===' );
		$this->insertPage( 'Template:Test2Page', '=== Dummy ===' );
		$this->insertPage( 'Template:DummyPage', '=== Dummy ===' );
		return 3;
	}

	protected function getModuleName() {
		return 'bs-inserttemplate-store';
	}

	public function provideSingleFilterData() {
		return [
			'Filter by name' => [ 'string', 'ct', 'name', 'Test', 2 ]
		];
	}

	public function provideMultipleFilterData() {
		return [
			'Filter by name and code' => [
				[
					[
						'type' => 'string',
						'comparison' => 'ct',
						'field' => 'name',
						'value' => 'Page'
					],
					[
						'type' => 'string',
						'comparison' => 'eq',
						'field' => 'code',
						'value' => '{{Test1Page}}'
					]
				],
				1
			]
		];
	}

	public function provideKeyItemData() {
		return[
			'Test page DummyPage: code' => [ "code", "{{DummyPage}}" ]
		];
	}
}