<?php

use Sematico\Baselibs\QueryBuilder\Clauses\Join;
use Sematico\Baselibs\QueryBuilder\Statements\Select;

beforeEach(
	function () {
		$this->statement = mock( Select::class );
		$this->join      = new Join( $this->statement );

		// Mock global $wpdb
		global $wpdb;
		$wpdb         = mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		// Set up the statement mock to return itself for method chaining
		$this->statement->shouldReceive( 'join' )->andReturn( $this->statement );
		$this->statement->shouldReceive( 'left_join' )->andReturn( $this->statement );
		$this->statement->shouldReceive( 'right_join' )->andReturn( $this->statement );
	}
);

test(
	'adding inner join',
	function () {
		$this->join->join( 'users', 'u', 'posts.user_id', '=', 'u.id' );
		expect( $this->join->to_sql() )->toBe( 'INNER JOIN wp_users AS u ON posts.user_id = u.id' );
	}
);

test(
	'adding left join',
	function () {
		$this->join->left_join( 'comments', 'c', 'posts.id', '=', 'c.post_id' );
		expect( $this->join->to_sql() )->toBe( 'LEFT JOIN wp_comments AS c ON posts.id = c.post_id' );
	}
);

test(
	'adding right join',
	function () {
		$this->join->right_join( 'categories', 'cat', 'posts.category_id', '=', 'cat.id' );
		expect( $this->join->to_sql() )->toBe( 'RIGHT JOIN wp_categories AS cat ON posts.category_id = cat.id' );
	}
);

test(
	'adding multiple joins',
	function () {
		$this->join->join( 'users', 'u', 'posts.user_id', '=', 'u.id' );
		$this->join->left_join( 'comments', 'c', 'posts.id', '=', 'c.post_id' );

		expect( $this->join->to_sql() )->toBe(
			'INNER JOIN wp_users AS u ON posts.user_id = u.id ' .
				'LEFT JOIN wp_comments AS c ON posts.id = c.post_id'
		);
	}
);

test(
	'empty join clause returns empty string',
	function () {
		expect( $this->join->to_sql() )->toBe( '' );
	}
);

test(
	'throws exception for invalid join type',
	function () {
		$reflectionClass = new ReflectionClass( Join::class );
		$method          = $reflectionClass->getMethod( 'add_join' );
		$method->setAccessible( true );

		expect( fn() => $method->invokeArgs( $this->join, [ 'INVALID JOIN', 'users', 'u', 'posts.user_id', '=', 'u.id' ] ) )
		->toThrow( InvalidArgumentException::class, 'Invalid join type' );
	}
);
