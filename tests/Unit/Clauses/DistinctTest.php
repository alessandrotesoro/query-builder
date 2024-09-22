<?php

use Sematico\Baselibs\QueryBuilder\Clauses\Distinct;
use Sematico\Baselibs\QueryBuilder\Statements\Statement;

beforeEach(
	function () {
		$this->statement = mock( Statement::class );
		$this->distinct  = new Distinct( $this->statement );
	}
);

test(
	'distinct method sets distinct parameter and returns statement',
	function () {
		$result = $this->distinct->distinct();

		expect( $result )->toBe( $this->statement );
		expect( $this->distinct->get_params()['distinct'] )->toBeTrue();
	}
);

test(
	'to_sql returns DISTINCT when set',
	function () {
		$this->distinct->distinct();
		expect( $this->distinct->to_sql() )->toBe( 'DISTINCT ' );
	}
);

test(
	'to_sql returns empty string when not set',
	function () {
		expect( $this->distinct->to_sql() )->toBe( '' );
	}
);

test(
	'is_empty returns true when distinct not set',
	function () {
		expect( $this->distinct->is_empty() )->toBeTrue();
	}
);

test(
	'is_empty returns false when distinct set',
	function () {
		$this->distinct->distinct();
		expect( $this->distinct->is_empty() )->toBeFalse();
	}
);
