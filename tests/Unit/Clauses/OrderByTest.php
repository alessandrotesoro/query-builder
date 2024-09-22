<?php

use Sematico\Baselibs\QueryBuilder\Clauses\OrderBy;
use Sematico\Baselibs\QueryBuilder\Statements\Statement;

beforeEach(
	function () {
		$this->statement = Mockery::mock( Statement::class );
		$this->orderBy   = new OrderBy( $this->statement );
	}
);

it(
	'adds a single order by clause',
	function () {
		$this->orderBy->order_by( 'column', 'ASC' );
		expect( $this->orderBy->to_sql() )->toBe( 'ORDER BY column ASC' );
	}
);

it(
	'adds multiple order by clauses',
	function () {
		$this->orderBy->order_by( 'column1', 'ASC' );
		$this->orderBy->order_by( 'column2', 'DESC' );
		expect( $this->orderBy->to_sql() )->toBe( 'ORDER BY column1 ASC, column2 DESC' );
	}
);

it(
	'defaults to ASC when direction is not specified',
	function () {
		$this->orderBy->order_by( 'column' );
		expect( $this->orderBy->to_sql() )->toBe( 'ORDER BY column ASC' );
	}
);

it(
	'throws an exception for invalid direction',
	function () {
		$this->orderBy->order_by( 'column', 'INVALID' );
	}
)->throws( InvalidArgumentException::class, 'Direction should be either ASC or DESC' );

it(
	'returns an empty string when no order by clauses are added',
	function () {
		expect( $this->orderBy->to_sql() )->toBe( '' );
	}
);

it(
	'is empty when no order by clauses are added',
	function () {
		expect( $this->orderBy->is_empty() )->toBeTrue();
	}
);

it(
	'is not empty when order by clauses are added',
	function () {
		$this->orderBy->order_by( 'column' );
		expect( $this->orderBy->is_empty() )->toBeFalse();
	}
);

it(
	'returns the statement object for chaining',
	function () {
		$result = $this->orderBy->order_by( 'column' );
		expect( $result )->toBe( $this->statement );
	}
);
