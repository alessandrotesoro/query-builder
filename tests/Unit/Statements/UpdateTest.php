<?php

use Sematico\Baselibs\QueryBuilder\Exceptions\InvalidQueryException;
use Sematico\Baselibs\QueryBuilder\Statements\Update;

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
		$this->update = new Update( $wpdb );
	}
);

it(
	'can create an update statement',
	function () {
		$update = $this->update->table( 'users' )
			->data( [ 'user_email' => 'newemail@example.com' ] )
			->where( 'ID' )->is( 1 )
			->end();

		expect( $update->to_sql() )->toBe( "UPDATE wp_users SET user_email = 'newemail@example.com' WHERE (ID = '1')" );
	}
);

test(
	'throws exception when no table is specified',
	function () {
		$this->update->data( [ 'column' => 'value' ] )->to_sql();
	}
)->throws( InvalidQueryException::class );

test(
	'throws exception when no data is specified',
	function () {
		$this->update->table( 'users' )->to_sql();
	}
)->throws( InvalidQueryException::class );

test(
	'throws exception when no where is specified',
	function () {
		$this->update->table( 'users' )->data( [ 'column' => 'value' ] )->to_sql();
	}
)->throws( InvalidQueryException::class );

it(
	'can create an update statement with multiple columns',
	function () {
		$update = $this->update->table( 'users' )
		->data(
			[
				'user_email' => 'newemail@example.com',
				'user_name'  => 'New Name',
			]
		)
		->where( 'ID' )->is( 1 )
		->end();

		expect( $update->to_sql() )->toBe( "UPDATE wp_users SET user_email = 'newemail@example.com', user_name = 'New Name' WHERE (ID = '1')" );
	}
);

it(
	'can create an update statement with a complex where clause',
	function () {
		$update = $this->update
			->table( 'posts' )
			->data( [ 'post_status' => 'published' ] )
			->where( 'post_author' )->is( 5 )
			->and_where( 'post_date' )->greater_than( '2023-01-01' );

		expect( $update->to_sql() )->toBe( "UPDATE wp_posts SET post_status = 'published' WHERE (post_author = '5') AND (post_date > '2023-01-01')" );
	}
);


it(
	'can create an update statement with a raw clause',
	function () {
		$update = $this->update->table( 'comments' )
		->data( [ 'comment_approved' => 1 ] )
		->where( 'comment_post_ID' )->is( 10 )
		->raw( 'LIMIT 5' )
		->end();

		expect( $update->to_sql() )->toBe( "UPDATE wp_comments SET comment_approved = '1' WHERE (comment_post_ID = '10') LIMIT 5" );
	}
);
