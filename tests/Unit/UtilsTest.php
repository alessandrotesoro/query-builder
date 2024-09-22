<?php

use Sematico\Baselibs\QueryBuilder\Utils;

/**
 * Test case for Utils::is_associative_array() with associative arrays.
 */
test(
	'is_associative_array returns true for associative arrays',
	function () {
		$associativeArray = [
			'key1' => 'value1',
			'key2' => 'value2',
			'key3' => 'value3',
		];
		expect( Utils::is_associative_array( $associativeArray ) )->toBeTrue();
	}
);

/**
 * Test case for Utils::is_associative_array() with indexed arrays.
 */
test(
	'is_associative_array returns false for indexed arrays',
	function () {
		$indexedArray = [ 'value1', 'value2', 'value3' ];
		expect( Utils::is_associative_array( $indexedArray ) )->toBeFalse();
	}
);

/**
 * Test case for Utils::is_associative_array() with non-array values.
 */
test(
	'is_associative_array returns false for non-array values',
	function () {
		expect( Utils::is_associative_array( 'string' ) )->toBeFalse();
		expect( Utils::is_associative_array( 123 ) )->toBeFalse();
		expect( Utils::is_associative_array( null ) )->toBeFalse();
		expect( Utils::is_associative_array( new stdClass() ) )->toBeFalse();
	}
);

/**
 * Test case for Utils::is_associative_array() with mixed key types.
 */
test(
	'is_associative_array returns false for mixed key types',
	function () {
		$mixedArray = [
			'key1' => 'value1',
			'value2',
			'key3' => 'value3',
		];
		expect( Utils::is_associative_array( $mixedArray ) )->toBeFalse();
	}
);

/**
 * Test case for Utils::is_associative_array() with empty arrays.
 */
test(
	'is_associative_array returns true for empty arrays',
	function () {
		expect( Utils::is_associative_array( [] ) )->toBeTrue();
	}
);
