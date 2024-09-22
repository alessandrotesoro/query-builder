<?php

use Sematico\Baselibs\QueryBuilder\Statements\Delete;
use Sematico\Baselibs\QueryBuilder\Exceptions\InvalidQueryException;

beforeEach(
	function () {
		global $wpdb;
		$wpdb         = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'prepare' )->andReturnUsing(
			function () {
				$args  = func_get_args();
				$query = array_shift( $args );

				// From WPDB::prepare method
				$query = str_replace( "'%s'", '%s', $query ); // in case someone mistakenly already singlequoted it
				$query = str_replace( '"%s"', '%s', $query ); // doublequote unquoting
				$query = preg_replace( '|(?<!%)%f|', '%F', $query ); // Force floats to be locale unaware
				$query = preg_replace( '|(?<!%)%s|', "'%s'", $query ); // quote the strings, avoiding escaped strings like %%s

				return vsprintf( $query, $args );
			}
		);

		$this->wpdb   = $wpdb;
		$this->delete = new Delete( $wpdb );
	}
);

test(
	'basic delete',
	function () {
		$sql = $this->delete->from( 'users' )->to_sql();
		expect( $sql )->toBe( 'DELETE FROM wp_users' );
	}
);

test(
	'delete with alias',
	function () {
		$sql = $this->delete->from( 'users', 'u' )->to_sql();
		expect( $sql )->toBe( 'DELETE FROM wp_users AS u' );
	}
);

test(
	'delete with where clause',
	function () {
		$sql = $this->delete
		->from( 'users' )
		->where( 'id' )->is( 1 );

		$prepared_sql = $sql->to_sql();
		$params       = $sql->get_params();

		expect( $prepared_sql )->toBe( "DELETE FROM wp_users WHERE (id = '1')" );
		expect( $params )->toBe( [ 1 ] );
	}
);

test(
	'delete with multiple where clauses',
	function () {
		$sql = $this->delete
			->from( 'users' )
			->where( 'id' )->is( 1 )
			->and_where( 'status' )->is( 'active' );

		$prepared_sql = $sql->to_sql();
		$params       = $sql->get_params();

		expect( $prepared_sql )->toBe( "DELETE FROM wp_users WHERE (id = '1') AND (status = 'active')" );
		expect( $params )->toBe( [ 1, 'active' ] );
	}
);

test(
	'delete with multiple where clauses and OR condition',
	function () {
		$sql = $this->delete
			->from( 'users' )
			->where( 'id' )->is( 1 )
			->or_where( 'email' )->is( 'test@example.com' );

		$prepared_sql = $sql->to_sql();
		$params       = $sql->get_params();

		expect( $prepared_sql )->toBe( "DELETE FROM wp_users WHERE (id = '1') OR (email = 'test@example.com')" );
		expect( $params )->toBe( [ 1, 'test@example.com' ] );
	}
);

test(
	'delete with IN condition',
	function () {
		$sql = $this->delete
			->from( 'users' )
			->where( 'id' )->in( [ 1, 2, 3 ] );

		$prepared_sql = $sql->to_sql();
		$params       = $sql->get_params();

		expect( $prepared_sql )->toBe( "DELETE FROM wp_users WHERE (id IN ('1', '2', '3'))" );
		expect( $params )->toBe( [ 1, 2, 3 ] );
	}
);

test(
	'delete with NOT IN condition',
	function () {
		$sql = $this->delete
			->from( 'users' )
			->where( 'status' )->not_in( [ 'active', 'pending' ] );

		$prepared_sql = $sql->to_sql();
		$params       = $sql->get_params();

		expect( $prepared_sql )->toBe( "DELETE FROM wp_users WHERE (status NOT IN ('active', 'pending'))" );
		expect( $params )->toBe( [ 'active', 'pending' ] );
	}
);

test(
	'delete with BETWEEN condition',
	function () {
		$sql = $this->delete
			->from( 'orders' )
			->where( 'created_at' )->between( '2023-01-01', '2023-12-31' );

		$prepared_sql = $sql->to_sql();
		$params       = $sql->get_params();

		expect( $prepared_sql )->toBe( "DELETE FROM wp_orders WHERE (created_at BETWEEN '2023-01-01' AND '2023-12-31')" );
		expect( $params )->toBe( [ '2023-01-01', '2023-12-31' ] );
	}
);

test(
	'delete with IS NULL condition',
	function () {
		$sql = $this->delete
			->from( 'users' )
			->where( 'last_login' )->is_null();

		$prepared_sql = $sql->to_sql();
		$params       = $sql->get_params();

		expect( $prepared_sql )->toBe( 'DELETE FROM wp_users WHERE (last_login IS NULL)' );
		expect( $params )->toBe( [ 'NULL' ] );
	}
);

test(
	'delete with complex conditions',
	function () {
		$sql = $this->delete
			->from( 'products' )
			->where( 'category' )->is( 'electronics' )
			->and_where( 'price' )->greater_than( 100 )
			->and_where( 'stock' )->less_than( 10 )
			->or_where( 'discontinued' )->is( true );

		$prepared_sql = $sql->to_sql();
		$params       = $sql->get_params();

		expect( $prepared_sql )->toBe( "DELETE FROM wp_products WHERE (category = 'electronics') AND (price > '100') AND (stock < '10') OR (discontinued = '1')" );
		expect( $params )->toBe( [ 'electronics', 100, 10, true ] );
	}
);

test(
	'delete with grouped conditions',
	function () {
		$sql = $this->delete
			->from( 'orders' )
			->where( 'status' )->is( 'pending' )
			->group_where(
				function ( $query ) {
					$query->where( 'total' )->greater_than( 1000 )
					->or_where( 'is_priority' )->is( true );
				}
			);

		$prepared_sql = $sql->to_sql();
		$params       = $sql->get_params();

		expect( $prepared_sql )->toBe( "DELETE FROM wp_orders WHERE (status = 'pending') AND ((total > '1000' OR is_priority = '1'))" );
		expect( $params )->toBe( [ 'pending', 1000, true ] );
	}
);

test(
	'delete with raw condition',
	function () {
		$sql = $this->delete
			->from( 'users' )
			->where( 'last_login' )->is_null()
			->raw( 'AND DATEDIFF(NOW(), created_at) > %s', [ 30 ] );

		$prepared_sql = $sql->to_sql();
		$params       = $sql->get_params();

		expect( $prepared_sql )->toBe( "DELETE FROM wp_users WHERE (last_login IS NULL) AND DATEDIFF(NOW(), created_at) > '30'" );
		expect( $params )->toBe( [ 'NULL', 30 ] );
	}
);
