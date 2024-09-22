<?php

use Sematico\Baselibs\QueryBuilder\Clauses\GroupBy;
use Sematico\Baselibs\QueryBuilder\Statements\Statement;

beforeEach(
	function () {
		$this->statement = mock( Statement::class );
		$this->groupBy   = new GroupBy( $this->statement );
	}
);

test(
	'group_by adds single column',
	function () {
		$result = $this->groupBy->group_by( 'column1' );

		expect( $result )->toBe( $this->statement );
		expect( $this->groupBy->to_sql() )->toBe( 'GROUP BY column1' );
	}
);

test(
	'group_by adds multiple columns',
	function () {
		$this->groupBy->group_by( 'column1', 'column2', 'column3' );

		expect( $this->groupBy->to_sql() )->toBe( 'GROUP BY column1, column2, column3' );
	}
);

test(
	'group_by handles associative array',
	function () {
		$this->groupBy->group_by(
			[
				'column1' => 'ASC',
				'column2' => 'DESC',
			]
		);

		expect( $this->groupBy->to_sql() )->toBe( 'GROUP BY column1 ASC, column2 DESC' );
	}
);

test(
	'group_by throws exception for non-string input',
	function () {
		$this->groupBy->group_by( 123 );
	}
)->throws( InvalidArgumentException::class, 'group_by_columns must be strings' );

test(
	'is_empty returns true when no columns added',
	function () {
		expect( $this->groupBy->is_empty() )->toBeTrue();
	}
);

test(
	'is_empty returns false when columns are added',
	function () {
		$this->groupBy->group_by( 'column1' );
		expect( $this->groupBy->is_empty() )->toBeFalse();
	}
);

test(
	'to_sql returns empty string when no columns added',
	function () {
		expect( $this->groupBy->to_sql() )->toBe( '' );
	}
);

test(
	'to_sql returns correct SQL for multiple group_by calls',
	function () {
		$this->groupBy->group_by( 'column1' );
		$this->groupBy->group_by( 'column2', 'column3' );

		expect( $this->groupBy->to_sql() )->toBe( 'GROUP BY column1, column2, column3' );
	}
);
