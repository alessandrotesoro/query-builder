<?php // phpcs:ignore WordPress.Files.FileName
/**
 * GroupBy class.
 *
 * @package   Sematico\Baselibs\QueryBuilder
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright 2024 Sematico
 * @license   GPL-3.0-or-later
 */

namespace Sematico\Baselibs\QueryBuilder\Clauses;

use InvalidArgumentException;
use Sematico\Baselibs\QueryBuilder\Utils;
use Sematico\Baselibs\QueryBuilder\Statements\Statement;
/**
 * GroupBy class
 *
 * This class provides functionality for adding GROUP BY clauses to SQL queries.
 * It allows for flexible grouping of results based on specified columns.
 */
class GroupBy extends Clause {

	/**
	 * @var string[] An array of columns to group by in the SQL query.
	 */
	protected $group_by_columns = [];

	/**
	 * Add one or more columns to the GROUP BY clause.
	 *
	 * This method allows adding columns to group by in various formats:
	 * - As individual string arguments
	 * - As an associative array for more complex grouping expressions
	 *
	 * @param mixed ...$group_by_columns The columns to add to the GROUP BY clause.
	 * @return Statement Returns the parent Statement object for method chaining.
	 * @throws InvalidArgumentException If any group_by_column is not a string or associative array.
	 */
	public function group_by( ...$group_by_columns ): Statement {
		foreach ( $group_by_columns as $group_by_column ) {
			if ( Utils::is_associative_array( $group_by_column ) ) {
				$conditions = [];

				foreach ( $group_by_column as $key => $value ) {
					$conditions[] = "$key $value";
					$this->set_param( $key, $value );
				}

				return $this->group_by( ...$conditions );
			}
			if ( ! is_string( $group_by_column ) ) {
				throw new InvalidArgumentException( 'group_by_columns must be strings' );
			}
		}

		if ( $group_by_columns ) {
			$this->group_by_columns[] = implode( ', ', $group_by_columns );
		}

		return $this->statement;
	}

	/**
	 * Check if the group by clause is empty by looking at
	 * the "group_by_columns" array.
	 *
	 * @return bool True if the group by clause is empty, false otherwise.
	 */
	public function is_empty(): bool {
		return empty( $this->group_by_columns );
	}

	/**
	 * Convert the current GROUP BY clause into an SQL string.
	 *
	 * This method generates the SQL string for the GROUP BY clause
	 * based on the columns added via the group_by() method.
	 *
	 * @return string The generated SQL for the GROUP BY clause.
	 */
	public function to_sql(): string {
		if ( $this->is_empty() ) {
			return '';
		}
		$group_by_columns = implode( ', ', $this->group_by_columns );
		return "GROUP BY $group_by_columns";
	}
}
