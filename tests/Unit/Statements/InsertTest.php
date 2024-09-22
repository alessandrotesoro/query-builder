<?php

use Sematico\Baselibs\QueryBuilder\Exceptions\InvalidQueryException;
use Sematico\Baselibs\QueryBuilder\Statements\Insert;

beforeEach(
	function () {
		global $wpdb;
		$wpdb         = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'prepare' )->andReturnUsing(
			function ( $query, $args ) {
				// From WPDB::prepare method
				$query = str_replace( "'%s'", '%s', $query ); // in case someone mistakenly already singlequoted it
				$query = str_replace( '"%s"', '%s', $query ); // doublequote unquoting
				$query = preg_replace( '|(?<!%)%f|', '%F', $query ); // Force floats to be locale unaware
				$query = preg_replace( '|(?<!%)%s|', "'%s'", $query ); // quote the strings, avoiding escaped strings like %%s

				return vsprintf( $query, $args );
			}
		);

		$this->wpdb   = $wpdb;
		$this->insert = new Insert( $wpdb );
	}
);

test(
	'insert data into a table',
	function () {
		$data = [
			'column1' => 'value1',
			'column2' => 'value2',
		];
		$sql  = $this->insert->table( 'test_table' )->data( $data );

		expect( $sql->to_sql() )->toBe( "INSERT INTO wp_test_table (column1, column2) VALUES ('value1', 'value2')" );
	}
);

test(
	'insert data with into() method',
	function () {
		$data = [
			'column1' => 'value1',
			'column2' => 'value2',
		];
		$this->wpdb->shouldReceive( 'insert' )->once()->with( 'wp_test_table', $data )->andReturn( 1 );

		$result = $this->insert->data( $data )->into( 'test_table' );

		expect( $result )->toBe( 1 );
	}
);

test(
	'throws exception when no table is specified',
	function () {
		$this->insert->data( [ 'column' => 'value' ] )->to_sql();
	}
)->throws( InvalidQueryException::class );

test(
	'throws exception when no data is specified',
	function () {
		$this->insert->table( 'test_table' )->to_sql();
	}
)->throws( InvalidQueryException::class );

test(
	'handles different data types correctly',
	function () {
		$data = [
			'string_col' => 'text',
			'int_col'    => 42,
			'float_col'  => 3.14,
			'bool_col'   => true,
			'null_col'   => null,
		];
		$sql  = $this->insert->table( 'test_table' )->data( $data );

		expect( $sql->to_sql() )->toBe( "INSERT INTO wp_test_table (string_col, int_col, float_col, bool_col, null_col) VALUES ('text', '42', '3.14', '1', NULL)" );
	}
);
