<?php

use Sematico\Baselibs\QueryBuilder\Clauses\Offset;
use Sematico\Baselibs\QueryBuilder\Statements\Statement;

beforeEach(
	function () {
		$this->statement = Mockery::mock( Statement::class );
		$this->offset    = new Offset( $this->statement );

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
	}
);

afterEach(
	function () {
		Mockery::close();
	}
);

it(
	'sets offset and returns statement',
	function () {
		$result = $this->offset->offset( 10 );

		expect( $result )->toBe( $this->statement );
	}
);

it(
	'generates correct SQL for valid offset',
	function () {
		$this->offset->offset( 10 );

		expect( $this->offset->to_sql() )->toBe( 'OFFSET 10' );
	}
);

it(
	'returns empty string for null offset',
	function () {
		expect( $this->offset->to_sql() )->toBe( '' );
	}
);

it(
	'returns empty string for negative offset',
	function () {
		$this->offset->offset( -5 );

		expect( $this->offset->to_sql() )->toBe( '' );
	}
);

it(
	'is empty when offset is null',
	function () {
		expect( $this->offset->is_empty() )->toBeTrue();
	}
);

it(
	'is empty when offset is negative',
	function () {
		$this->offset->offset( -5 );

		expect( $this->offset->is_empty() )->toBeTrue();
	}
);

it(
	'is not empty when offset is valid',
	function () {
		$this->offset->offset( 10 );

		expect( $this->offset->is_empty() )->toBeFalse();
	}
);
