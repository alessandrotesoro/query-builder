<?php

use Sematico\Baselibs\QueryBuilder\Clauses\Where;
use Sematico\Baselibs\QueryBuilder\Statements\Select;
use Sematico\Baselibs\QueryBuilder\Statements\Statement;

beforeEach(
	function () {
		$this->statement = Mockery::mock( Statement::class );
		$this->where     = new Where( $this->statement );

		// Mock global $wpdb
		global $wpdb;
		$wpdb = mock( 'wpdb' );
		$wpdb->shouldReceive( 'prepare' )->andReturnUsing(
			function ( $query, ...$args ) {
				$query = str_replace( "'%s'", '%s', $query );
				$query = str_replace( '"%s"', '%s', $query );

				// Handle different placeholder types
				foreach ( $args as $arg ) {
					$type = '%s'; // Default to string
					if ( is_int( $arg ) ) {
						$type = '%d';
					} elseif ( is_float( $arg ) ) {
						$type = '%f';
					} elseif ( is_bool( $arg ) ) {
						$type = '%d';
						$arg  = $arg ? 1 : 0;
					} elseif ( is_null( $arg ) ) {
						$arg = 'NULL';
					}

					$pos = strpos( $query, $type );
					if ( $pos !== false ) {
						if ( $type === '%f' ) {
							$arg = sprintf( '%.6f', $arg );
						} elseif ( $type === '%s' && $arg !== 'NULL' ) {
							$arg = "'" . addslashes( $arg ) . "'";
						}
						$query = substr_replace( $query, $arg, $pos, strlen( $type ) );
					}
				}
				return $query;
			}
		);

		// Set up the statement mock to return itself for method chaining
		$this->statement->shouldReceive( 'where' )->andReturn( $this->statement );
		$this->statement->shouldReceive( 'and_where' )->andReturn( $this->statement );
		$this->statement->shouldReceive( 'or_where' )->andReturn( $this->statement );
	}
);

test(
	'where clause with equality condition',
	function () {
		$this->where->where( 'column' )->is( 'value' );

		expect( $this->where->to_sql() )->toBe( "WHERE (column = 'value')" );
	}
);

test(
	'where clause with IN condition',
	function () {
		$this->where->where( 'column' )->in( [ 'value1', 'value2', 'value3' ] );

		expect( $this->where->to_sql() )->toBe( "WHERE (column IN ('value1', 'value2', 'value3'))" );
	}
);

test(
	'where clause with BETWEEN condition',
	function () {
		$this->where->where( 'column' )->between( 10, 20 );

		$sql    = $this->where->to_sql();
		$params = $this->where->get_params();

		expect( $sql )->toBe( 'WHERE (column BETWEEN %s AND %s)' );
		expect( $params )->toBe( [ 10, 20 ] );

		// Simple prepare function for testing
		$prepare = function ( $sql, ...$args ) {
			$i = 0;
			return preg_replace_callback(
				'/%s/',
				function ( $matches ) use ( $args, &$i ) {
					return "'" . $args[ $i++ ] . "'";
				},
				$sql
			);
		};

		$prepared_sql = $prepare( $sql, ...$params );
		expect( $prepared_sql )->toBe( "WHERE (column BETWEEN '10' AND '20')" );
	}
);

test(
	'where clause with NOT IN condition',
	function () {
		$this->where->where( 'column' )->not_in( [ 'value1', 'value2', 'value3' ] );

		expect( $this->where->to_sql() )->toBe( "WHERE (column NOT IN ('value1', 'value2', 'value3'))" );
	}
);

test(
	'where clause with IS NULL condition',
	function () {
		$this->where->where( 'column' )->is_null();

		expect( $this->where->to_sql() )->toBe( 'WHERE (column IS NULL)' );
	}
);

test(
	'where clause with not equal condition',
	function () {
		$this->where->where( 'column' )->is_not( 'value' );

		expect( $this->where->to_sql() )->toBe( "WHERE (column != 'value')" );
	}
);

test(
	'where clause with less than condition',
	function () {
		$this->where->where( 'column' )->less_than( 10 );

		$sql    = $this->where->to_sql();
		$params = $this->where->get_params();

		expect( $sql )->toBe( 'WHERE (column < %s)' );
		expect( $params )->toBe( [ 10 ] );

		// Simple prepare function for testing
		$prepare = function ( $sql, ...$args ) {
			$i = 0;
			return preg_replace_callback(
				'/%s/',
				function ( $matches ) use ( $args, &$i ) {
					return "'" . $args[ $i++ ] . "'";
				},
				$sql
			);
		};

		$prepared_sql = $prepare( $sql, ...$params );
		expect( $prepared_sql )->toBe( "WHERE (column < '10')" );
	}
);

test(
	'where clause with greater than condition',
	function () {
		$this->where->where( 'column' )->greater_than( 20 );

		$sql    = $this->where->to_sql();
		$params = $this->where->get_params();

		expect( $sql )->toBe( 'WHERE (column > %s)' );
		expect( $params )->toBe( [ 20 ] );

		$prepared_sql = prepare_sql( $sql, ...$params );
		expect( $prepared_sql )->toBe( "WHERE (column > '20')" );
	}
);

test(
	'where clause with at least condition',
	function () {
		$this->where->where( 'column' )->at_least( 30 );

		$sql    = $this->where->to_sql();
		$params = $this->where->get_params();

		expect( $sql )->toBe( 'WHERE (column >= %s)' );
		expect( $params )->toBe( [ 30 ] );

		$prepared_sql = prepare_sql( $sql, ...$params );
		expect( $prepared_sql )->toBe( "WHERE (column >= '30')" );
	}
);

test(
	'where clause with at most condition',
	function () {
		$this->where->where( 'column' )->at_most( 40 );

		$sql    = $this->where->to_sql();
		$params = $this->where->get_params();

		expect( $sql )->toBe( 'WHERE (column <= %s)' );
		expect( $params )->toBe( [ 40 ] );

		$prepared_sql = prepare_sql( $sql, ...$params );
		expect( $prepared_sql )->toBe( "WHERE (column <= '40')" );
	}
);

test(
	'where clause with IS NOT NULL condition',
	function () {
		$this->where->where( 'column' )->is_not_null();

		expect( $this->where->to_sql() )->toBe( 'WHERE (column IS NOT NULL)' );
	}
);

test(
	'where clause with multiple AND conditions',
	function () {
		$this->where->where( 'column1' )->is( 'value1' );
		$this->where->and_where( 'column2' )->is( 'value2' );
		$this->where->and_where( 'column3' )->is( 'value3' );

		expect( $this->where->to_sql() )->toBe( "WHERE (column1 = 'value1') AND (column2 = 'value2') AND (column3 = 'value3')" );
	}
);

test(
	'where clause with multiple OR conditions',
	function () {
		$this->where->where( 'column1' )->is( 'value1' );
		$this->where->or_where( 'column2' )->is( 'value2' );
		$this->where->or_where( 'column3' )->is( 'value3' );

		expect( $this->where->to_sql() )->toBe( "WHERE (column1 = 'value1') OR (column2 = 'value2') OR (column3 = 'value3')" );
	}
);

test(
	'where clause with mixed AND and OR conditions',
	function () {
		$this->where->where( 'column1' )->is( 'value1' );
		$this->where->and_where( 'column2' )->is( 'value2' );
		$this->where->or_where( 'column3' )->is( 'value3' );
		$this->where->and_where( 'column4' )->is( 'value4' );

		expect( $this->where->to_sql() )->toBe( "WHERE (column1 = 'value1') AND (column2 = 'value2') OR (column3 = 'value3') AND (column4 = 'value4')" );
	}
);

test(
	'where clause with raw condition',
	function () {
		$this->where->where_raw( 'column = %s', [ 'value' ] );

		expect( $this->where->to_sql() )->toBe( "WHERE (column = 'value')" );
	}
);

test(
	'where clause with OR raw condition',
	function () {
		$this->where->where( 'column1' )->is( 'value1' );
		$this->where->or_where_raw( 'column2 LIKE %s', [ '%value2%' ] );

		expect( $this->where->to_sql() )->toBe( "WHERE (column1 = 'value1') OR (column2 LIKE '%value2%')" );
	}
);

test(
	'where clause with grouped conditions',
	function () {
		$this->where->where( 'column1' )->is( 'value1' );
		$this->where->group_where(
			function ( $query ) {
				$query->where( 'column2' )->is( 'value2' );
				$query->or_where( 'column3' )->is( 'value3' );
			}
		);

		expect( $this->where->to_sql() )->toBe( "WHERE (column1 = 'value1') AND ((column2 = 'value2' OR column3 = 'value3'))" );
	}
);

test(
	'where clause with empty conditions',
	function () {
		expect( $this->where->to_sql() )->toBe( '' );
		expect( $this->where->is_empty() )->toBeTrue();
	}
);

test(
	'where clause with multiple conditions of the same type',
	function () {
		$this->where->where( 'column1' )->is( 'value1' );
		$this->where->where( 'column2' )->is( 'value2' );
		$this->where->where( 'column3' )->is( 'value3' );

		expect( $this->where->to_sql() )->toBe( "WHERE (column1 = 'value1') AND (column2 = 'value2') AND (column3 = 'value3')" );
	}
);

test(
	'where clause with LIKE condition',
	function () {
		$this->where->where( 'column' )->where_raw( 'LIKE %s', [ '%value%' ] );

		expect( $this->where->to_sql() )->toBe( "WHERE (LIKE '%value%')" );
	}
);
