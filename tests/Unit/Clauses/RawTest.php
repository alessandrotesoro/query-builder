<?php

use Sematico\Baselibs\QueryBuilder\Clauses\Raw;
use Sematico\Baselibs\QueryBuilder\Statements\Statement;

beforeEach(
	function () {
		global $wpdb;
		$this->wpdb      = $wpdb;
		$this->statement = Mockery::mock( Statement::class );
		$this->statement->shouldReceive( 'get_wpdb' )->andReturn( $this->wpdb );
		$this->raw = new Raw( $this->statement );
	}
);

afterEach(
	function () {
		Mockery::close();
	}
);

it(
	'adds a raw SQL fragment without bindings',
	function () {
		$result = $this->raw->add_raw( 'SELECT * FROM table' );

		expect( $result )->toBe( $this->statement );
		expect( $this->raw->to_sql() )->toBe( 'SELECT * FROM table' );
	}
);

it(
	'adds a raw SQL fragment with bindings',
	function () {
		$this->wpdb->shouldReceive( 'prepare' )
		->with( 'SELECT * FROM table WHERE id = %d', [ 1 ] )
		->andReturn( 'SELECT * FROM table WHERE id = 1' );

		$result = $this->raw->add_raw( 'SELECT * FROM table WHERE id = %d', [ 1 ] );

		expect( $result )->toBe( $this->statement );
		expect( $this->raw->to_sql() )->toBe( 'SELECT * FROM table WHERE id = 1' );
	}
);

it(
	'adds multiple raw SQL fragments',
	function () {
		$this->wpdb->shouldReceive( 'prepare' )
		->with( 'SELECT * FROM table1 UNION SELECT * FROM table2 WHERE id = %d', [ 5 ] )
		->andReturn( 'SELECT * FROM table1 UNION SELECT * FROM table2 WHERE id = 5' );

		$this->raw->add_raw( 'SELECT * FROM table1' );
		$this->raw->add_raw( 'UNION SELECT * FROM table2 WHERE id = %d', [ 5 ] );

		expect( $this->raw->to_sql() )->toBe( 'SELECT * FROM table1 UNION SELECT * FROM table2 WHERE id = 5' );
	}
);

it(
	'returns an empty string for to_sql when no fragments are added',
	function () {
		expect( $this->raw->to_sql() )->toBe( '' );
	}
);

it(
	'is not empty after adding a fragment',
	function () {
		expect( $this->raw->is_empty() )->toBeTrue();

		$this->raw->add_raw( 'SELECT * FROM table' );

		expect( $this->raw->is_empty() )->toBeFalse();
	}
);

it(
	'merges bindings with existing params',
	function () {
		$this->raw->add_raw( 'SELECT * FROM table WHERE id = %d', [ 1 ] );
		$this->raw->add_raw( 'AND name = %s', [ 'John' ] );

		expect( $this->raw->get_params() )->toBe( [ 1, 'John' ] );
	}
);
