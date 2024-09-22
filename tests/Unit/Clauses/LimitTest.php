<?php

use Sematico\Baselibs\QueryBuilder\Clauses\Limit;
use Sematico\Baselibs\QueryBuilder\Statements\Statement;

beforeEach(
	function () {
		$this->statement = Mockery::mock( Statement::class );
		$this->limit     = new Limit( $this->statement );
	}
);

it(
	'sets limit and returns statement',
	function () {
		$result = $this->limit->limit( 10 );

		expect( $result )->toBe( $this->statement );
	}
);

it(
	'generates correct SQL for valid limit',
	function () {
		$this->limit->limit( 10 );

		expect( $this->limit->to_sql() )->toBe( 'LIMIT 10' );
	}
);

it(
	'returns empty string for null limit',
	function () {
		expect( $this->limit->to_sql() )->toBe( '' );
	}
);

it(
	'returns empty string for negative limit',
	function () {
		$this->limit->limit( -5 );

		expect( $this->limit->to_sql() )->toBe( '' );
	}
);

it(
	'is empty when limit is null',
	function () {
		expect( $this->limit->is_empty() )->toBeTrue();
	}
);

it(
	'is empty when limit is negative',
	function () {
		$this->limit->limit( -5 );

		expect( $this->limit->is_empty() )->toBeTrue();
	}
);

it(
	'is not empty when limit is set to a positive value',
	function () {
		$this->limit->limit( 10 );

		expect( $this->limit->is_empty() )->toBeFalse();
	}
);

it(
	'is not empty when limit is set to zero',
	function () {
		$this->limit->limit( 0 );

		expect( $this->limit->is_empty() )->toBeFalse();
	}
);
