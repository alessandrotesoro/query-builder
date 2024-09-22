<?php

use Sematico\Baselibs\QueryBuilder\Exceptions\InvalidQueryException;
use Sematico\Baselibs\QueryBuilder\Statements\Select;

beforeEach(
	function () {
		global $wpdb;
		$wpdb         = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'prepare' )->andReturnUsing(
			function ( $query, ...$args ) {
				if ( ! is_array( $args ) ) {
					$args = [ $args ];
				}

				// From WPDB::prepare method
				$query = str_replace( "'%s'", '%s', $query ); // in case someone mistakenly already singlequoted it
				$query = str_replace( '"%s"', '%s', $query ); // doublequote unquoting
				$query = preg_replace( '|(?<!%)%f|', '%F', $query ); // Force floats to be locale unaware
				$query = preg_replace( '|(?<!%)%s|', "'%s'", $query ); // quote the strings, avoiding escaped strings like %%s

				// Flatten the args array if it's nested
				$args = array_reduce(
					$args,
					function ( $carry, $item ) {
						return is_array( $item ) ? array_merge( $carry, $item ) : array_merge( $carry, [ $item ] );
					},
					[]
				);

				return vsprintf( $query, $args );
			}
		);

		$this->wpdb   = $wpdb;
		$this->select = new Select( $wpdb );
	}
);

it(
	'can create a basic select query',
	function () {
		$query = $this->select
		->set_columns( 'id', 'name' )
		->from( 'users' )
		->to_sql();

		expect( $query )->toBe( 'SELECT id, name FROM wp_users' );
	}
);

it(
	'can create a select query with where clause',
	function () {
		$query = $this->select
		->set_columns( '*' )
		->from( 'posts' )
		->where( 'post_status' )->is( 'publish' )
		->to_sql();

		expect( $query )->toBe( "SELECT * FROM wp_posts WHERE (post_status = 'publish')" );
	}
);

it(
	'can create a select query with multiple where clauses',
	function () {
		$query = $this->select
		->set_columns( 'id', 'title' )
		->from( 'posts' )
		->where( 'post_status' )->is( 'publish' )
		->and_where( 'post_type' )->is( 'post' )
		->to_sql();

		expect( $query )->toBe( "SELECT id, title FROM wp_posts WHERE (post_status = 'publish') AND (post_type = 'post')" );
	}
);

it(
	'can create a select query with order by',
	function () {
		$query = $this->select
		->set_columns( '*' )
		->from( 'users' )
		->order_by( 'registered_date', 'DESC' )
		->to_sql();

		expect( $query )->toBe( 'SELECT * FROM wp_users ORDER BY registered_date DESC' );
	}
);

it(
	'can create a select query with limit and offset',
	function () {
		$query = $this->select
		->set_columns( '*' )
		->from( 'comments' )
		->limit( 10 )
		->offset( 5 )
		->to_sql();

		expect( $query )->toBe( 'SELECT * FROM wp_comments LIMIT 10 OFFSET 5' );
	}
);

it(
	'can create a select query with join',
	function () {
		$query = $this->select
			->set_columns( 'posts.id', 'posts.title', 'users.user_nicename' )
			->from( 'posts' )
			->join( 'users', 'users', 'wp_posts.post_author', '=', 'wp_users.ID' )
			->to_sql();

		expect( $query )->toBe( 'SELECT posts.id, posts.title, users.user_nicename FROM wp_posts INNER JOIN wp_users AS users ON wp_posts.post_author = wp_users.ID' );
	}
);

it(
	'can create a select query with aggregate function',
	function () {
		$query = $this->select
			->count( 'id', 'total_users' )
			->from( 'users' )
			->to_sql();

		expect( $query )->toBe( 'SELECT COUNT(id) AS total_users FROM wp_users' );
	}
);

it(
	'throws an exception when no columns are specified',
	function () {
		$this->select
			->from( 'users' )
			->to_sql();
	}
)->throws( InvalidQueryException::class, 'No columns specified' );

it(
	'throws an exception when no table is specified',
	function () {
		$this->select
			->set_columns( '*' )
			->to_sql();
	}
)->throws( InvalidQueryException::class, 'No table specified' );

it(
	'can create a select query with group by and having',
	function () {
		$query = $this->select
		->set_columns( 'post_author', 'COUNT(*) as post_count' )
		->from( 'posts' )
		->group_by( 'post_author' )
		->having( 'COUNT(*) > %d', 5 )
		->to_sql();

		expect( $query )->toBe( 'SELECT post_author, COUNT(*) as post_count FROM wp_posts GROUP BY post_author HAVING COUNT(*) > 5' );
	}
);

it(
	'can create a select query with distinct',
	function () {
		$query = $this->select
		->distinct()
		->set_columns( 'post_status' )
		->from( 'posts' )
		->to_sql();

		expect( $query )->toBe( 'SELECT DISTINCT post_status FROM wp_posts' );
	}
);
