<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Raw class.
 *
 * This class provides functionality to add raw SQL fragments to SQL queries.
 *
 * @package   Sematico\Baselibs\QueryBuilder
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright 2024 Sematico
 * @license   GPL-3.0-or-later
 */

namespace Sematico\Baselibs\QueryBuilder\Clauses;

use Sematico\Baselibs\QueryBuilder\Statements\Statement;

/**
 * Class Raw
 *
 * Provides methods to add raw SQL fragments to SQL queries.
 */
class Raw extends Clause {

	/**
	 * Array to store raw SQL fragments.
	 *
	 * @var array
	 */
	protected $raw_fragments = [];

	/**
	 * Add a raw SQL fragment to the query.
	 *
	 * @param string $sql The raw SQL fragment to add.
	 * @param array  $bindings Optional. An array of values to be bound to the SQL fragment.
	 * @return Statement
	 */
	public function add_raw( string $sql, array $bindings = [] ): Statement {
		$this->raw_fragments[] = [
			'sql'      => $sql,
			'bindings' => $bindings,
		];
		$this->params          = array_merge( $this->params, $bindings );
		return $this->statement;
	}

	/**
	 * Convert the raw SQL fragments to a SQL string.
	 *
	 * @return string The generated SQL string for the raw fragments.
	 */
	public function to_sql(): string {
		if ( empty( $this->raw_fragments ) ) {
			return '';
		}

		$sql_parts = [];
		foreach ( $this->raw_fragments as $fragment ) {
			$sql_parts[] = $fragment['sql'];
		}
		return $this->prepare_sql( implode( ' ', $sql_parts ) );
	}

	/**
	 * Check if the Raw clause is empty.
	 *
	 * @return bool
	 */
	public function is_empty(): bool {
		return empty( $this->raw_fragments );
	}
}
