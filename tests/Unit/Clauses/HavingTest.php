<?php

use Sematico\Baselibs\QueryBuilder\Clauses\Having;
use Sematico\Baselibs\QueryBuilder\Statements\Select;
use Sematico\Baselibs\QueryBuilder\Statements\Statement;

beforeEach(
	function () {
		$this->statement = mock( Select::class );
		$this->having    = new Having( $this->statement );

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
		$this->statement->shouldReceive( 'having' )->andReturn( $this->statement );
		$this->statement->shouldReceive( 'and_having' )->andReturn( $this->statement );
		$this->statement->shouldReceive( 'or_having' )->andReturn( $this->statement );
	}
);

test(
	'adding single having condition',
	function () {
		$this->having->having( 'COUNT(*) > %d', 5 );
		expect( $this->having->to_sql() )->toBe( 'HAVING COUNT(*) > 5' );
	}
);

test(
	'adding multiple AND having conditions',
	function () {
		$this->having->having( 'COUNT(*) > %d', 5 );
		$this->having->and_having( 'SUM(price) < %f', 100.50 );

		expect( $this->having->to_sql() )->toBe( 'HAVING COUNT(*) > 5 AND SUM(price) < 100.500000' );
	}
);

test(
	'adding multiple OR having conditions',
	function () {
		$this->having->having( 'COUNT(*) > %d', 5 );
		$this->having->or_having( 'SUM(price) < %f', 100.50 );

		expect( $this->having->to_sql() )->toBe( 'HAVING COUNT(*) > 5 OR SUM(price) < 100.500000' );
	}
);

test(
	'adding multiple mixed AND and OR having conditions',
	function () {
		$this->having->having( 'COUNT(*) > %d', 5 );
		$this->having->and_having( 'SUM(price) < %f', 100.50 );
		$this->having->or_having( 'AVG(rating) > %f', 4.5 );

		expect( $this->having->to_sql() )->toBe( 'HAVING COUNT(*) > 5 AND SUM(price) < 100.500000 OR AVG(rating) > 4.500000' );
	}
);

test(
	'empty having clause returns empty string',
	function () {
		expect( $this->having->to_sql() )->toBe( '' );
	}
);

test(
	'single having condition with integer value',
	function () {
		$this->having->having( 'COUNT(*) = %d', 10 );
		expect( $this->having->to_sql() )->toBe( 'HAVING COUNT(*) = 10' );
	}
);

test(
	'single having condition with float value',
	function () {
		$this->having->having( 'AVG(price) > %f', 99.99 );
		expect( $this->having->to_sql() )->toBe( 'HAVING AVG(price) > 99.990000' );
	}
);

test(
	'single having condition with string value',
	function () {
		$this->having->having( 'MAX(name) = %s', 'John' );
		expect( $this->having->to_sql() )->toBe( "HAVING MAX(name) = 'John'" );
	}
);

test(
	'multiple AND having conditions',
	function () {
		$this->having->having( 'COUNT(*) > %d', 5 );
		$this->having->and_having( 'SUM(price) < %f', 1000.00 );
		$this->having->and_having( 'AVG(rating) >= %f', 4.5 );

		expect( $this->having->to_sql() )->toBe( 'HAVING COUNT(*) > 5 AND SUM(price) < 1000.000000 AND AVG(rating) >= 4.500000' );
	}
);

test(
	'multiple OR having conditions',
	function () {
		$this->having->having( 'COUNT(*) < %d', 3 );
		$this->having->or_having( 'SUM(price) > %f', 5000.00 );
		$this->having->or_having( 'MIN(rating) <= %f', 2.0 );

		expect( $this->having->to_sql() )->toBe( 'HAVING COUNT(*) < 3 OR SUM(price) > 5000.000000 OR MIN(rating) <= 2.000000' );
	}
);

test(
	'complex mixed AND and OR having conditions',
	function () {
		$this->having->having( 'COUNT(*) > %d', 10 );
		$this->having->and_having( 'SUM(price) > %f', 1000.00 );
		$this->having->or_having( 'AVG(rating) < %f', 3.0 );
		$this->having->and_having( 'MAX(date) > %s', '2023-01-01' );

		expect( $this->having->to_sql() )->toBe( "HAVING COUNT(*) > 10 AND SUM(price) > 1000.000000 OR AVG(rating) < 3.000000 AND MAX(date) > '2023-01-01'" );
	}
);

test(
	'having condition with null value',
	function () {
		$this->having->having( 'column_name IS %s', null );
		expect( $this->having->to_sql() )->toBe( 'HAVING column_name IS NULL' );
	}
);

test(
	'having condition with boolean value',
	function () {
		$this->having->having( 'is_active = %d', true );
		expect( $this->having->to_sql() )->toBe( 'HAVING is_active = 1' );
	}
);

test(
	'throws exception for invalid operator',
	function () {
		expect( fn() => $this->having->having( 'COUNT(*) > %d', 5, 'INVALID' ) )
		->toThrow( InvalidArgumentException::class, 'Invalid operator. Must be AND or OR.' );
	}
);

test(
	'throws exception for empty condition',
	function () {
		expect( fn() => $this->having->having( '', 5 ) )
		->toThrow( InvalidArgumentException::class, 'HAVING condition must be a non-empty string.' );
	}
);
