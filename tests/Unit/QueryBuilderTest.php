<?php

use Sematico\Baselibs\QueryBuilder\QueryBuilder;
use Sematico\Baselibs\QueryBuilder\Statements\Delete;
use Sematico\Baselibs\QueryBuilder\Statements\Insert;
use Sematico\Baselibs\QueryBuilder\Statements\Select;
use Sematico\Baselibs\QueryBuilder\Statements\Update;

beforeEach(
	function () {
		$this->wpdb      = Mockery::mock( '\wpdb' );
		$GLOBALS['wpdb'] = $this->wpdb;
	}
);

afterEach(
	function () {
		Mockery::close();
	}
);

test(
	'QueryBuilder can be instantiated',
	function () {
		$queryBuilder = new QueryBuilder();
		expect( $queryBuilder )->toBeInstanceOf( QueryBuilder::class );
	}
);

test(
	'select method returns a Select instance',
	function () {
		$queryBuilder = new QueryBuilder();
		$select       = $queryBuilder->select( 'column1', 'column2' );
		expect( $select )->toBeInstanceOf( Select::class );
	}
);

test(
	'insert method returns an Insert instance',
	function () {
		$queryBuilder = new QueryBuilder();
		$insert       = $queryBuilder->insert(
			[
				'column1' => 'value1',
				'column2' => 'value2',
			]
		);
		expect( $insert )->toBeInstanceOf( Insert::class );
	}
);

test(
	'update method returns an Update instance',
	function () {
		$queryBuilder = new QueryBuilder();
		$update       = $queryBuilder->update(
			[
				'column1' => 'new_value1',
				'column2' => 'new_value2',
			]
		);
		expect( $update )->toBeInstanceOf( Update::class );
	}
);

test(
	'delete method returns a Delete instance',
	function () {
		$queryBuilder = new QueryBuilder();
		$delete       = $queryBuilder->delete();
		expect( $delete )->toBeInstanceOf( Delete::class );
	}
);
