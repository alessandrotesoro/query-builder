<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Distinct class.
 *
 * This class provides functionality to add DISTINCT clauses to SQL queries.
 *
 * @package   Sematico\Baselibs\QueryBuilder
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright 2024 Sematico
 * @license   GPL-3.0-or-later
 */

namespace Sematico\Baselibs\QueryBuilder\Clauses;

use Sematico\Baselibs\QueryBuilder\Statements\Statement;

/**
 * Class Distinct
 *
 * Provides methods to set and convert DISTINCT clauses for SQL queries.
 */
class Distinct extends Clause {

	/**
	 * Set the query to use DISTINCT.
	 *
	 * @return Statement Returns the parent Statement object for method chaining.
	 */
	public function distinct(): Statement {
		$this->set_param( 'distinct', true );
		return $this->statement;
	}

	/**
	 * Convert the current DISTINCT setting to an SQL string.
	 *
	 * @return string The generated SQL DISTINCT clause, or an empty string if not set.
	 */
	public function to_sql(): string {
		$params = $this->get_params();
		return isset( $params['distinct'] ) && $params['distinct'] ? 'DISTINCT ' : '';
	}

	/**
	 * Check if the distinct clause is empty by looking at
	 * the "distinct" parameter.
	 *
	 * @return bool True if the distinct clause is empty, false otherwise.
	 */
	public function is_empty(): bool {
		$params = $this->get_params();
		return ! isset( $params['distinct'] ) || ! $params['distinct'];
	}
}
