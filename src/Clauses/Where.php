<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Where trait.
 *
 * This trait provides functionality to add WHERE clauses to SQL queries.
 *
 * @package   Sematico\Baselibs\QueryBuilder
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright 2024 Sematico
 * @license   GPL-3.0-or-later
 */

namespace Sematico\Baselibs\QueryBuilder\Clauses;

use InvalidArgumentException;
use Sematico\Baselibs\QueryBuilder\Statements\Statement;

/**
 * Trait Where
 *
 * Provides methods to add and manage WHERE clauses in SQL queries.
 */
class Where extends Clause {

	/**
	 * @var array[] Array of WHERE conditions grouped by AND/OR.
	 */
	protected $where_clauses = [
		[
			'type'       => 'AND',
			'conditions' => [],
		],
	];

	/**
	 * @var string The current condition type (AND/OR).
	 */
	protected $current_condition_type = 'AND';

	/**
	 * @var string|null The current column name for WHERE conditions.
	 */
	protected $current_column = null;

	/**
	 * Start a WHERE condition for the specified column.
	 *
	 * @param string $column The column name to add a condition for.
	 * @return $this Fluent interface.
	 */
	public function where( string $column ): self {
		$this->current_column = $column;
		return $this;
	}

	/**
	 * Add an AND condition to the WHERE clause.
	 *
	 * @param string ...$conditions The conditions to add with AND.
	 * @return $this Fluent interface.
	 */
	public function and_where( string ...$conditions ): self {
		$this->current_condition_type = 'AND';
		$this->where( ...$conditions );
		return $this;
	}

	/**
	 * Add an OR condition to the WHERE clause.
	 *
	 * @param string ...$conditions The conditions to add with OR.
	 * @return $this Fluent interface.
	 */
	public function or_where( string ...$conditions ): self {
		$this->current_condition_type = 'OR';
		$this->where( ...$conditions );
		return $this;
	}

	/**
	 * Add an equality condition to the query.
	 *
	 * @param mixed $value The value to compare with.
	 * @return Statement
	 * @throws InvalidArgumentException If the column is not specified.
	 */
	public function is( $value ): Statement {
		return $this->add_condition( '=', $value );
	}

	/**
	 * Add a not equal condition to the query.
	 *
	 * @param mixed $value The value to compare with.
	 * @return Statement
	 * @throws InvalidArgumentException If the column is not specified.
	 */
	public function is_not( $value ): Statement {
		return $this->add_condition( '!=', $value );
	}

	/**
	 * Add a less than condition to the query.
	 *
	 * @param mixed $value The value to compare with.
	 * @return self Fluent interface.
	 * @throws InvalidArgumentException If the column is not specified.
	 */
	public function less_than( $value ): Statement {
		return $this->add_condition( '<', $value );
	}

	/**
	 * Add a greater than condition to the query.
	 *
	 * @param mixed $value The value to compare with.
	 * @return Statement
	 * @throws InvalidArgumentException If the column is not specified.
	 */
	public function greater_than( $value ): Statement {
		return $this->add_condition( '>', $value );
	}

	/**
	 * Add a greater than or equal to condition to the query.
	 *
	 * @param mixed $value The value to compare with.
	 * @return Statement
	 * @throws InvalidArgumentException If the column is not specified.
	 */
	public function at_least( $value ): Statement {
		return $this->add_condition( '>=', $value );
	}

	/**
	 * Add a less than or equal to condition to the query.
	 *
	 * @param mixed $value The value to compare with.
	 * @return Statement
	 * @throws InvalidArgumentException If the column is not specified.
	 */
	public function at_most( $value ): Statement {
		return $this->add_condition( '<=', $value );
	}

	/**
	 * Add a condition to the query using the specified operator.
	 *
	 * @param string $operator The operator to use (e.g., '=', '!=', '<', '>', '>=', '<=').
	 * @param mixed  $value    The value to compare with.
	 * @throws InvalidArgumentException If the column is not specified.
	 * @return Statement
	 */
	protected function add_condition( string $operator, $value ): Statement {
		if ( $this->current_column === null ) {
			throw new InvalidArgumentException( 'Column must be specified before the operator' );
		}

		$condition = "{$this->current_column} {$operator} %s";

		// Always add a new group for OR conditions
		if ($this->current_condition_type === 'OR') {
			$this->where_clauses[] = [
				'type'       => 'OR',
				'conditions' => [ $condition ],
			];
		} else {
			// For AND conditions, add to the last group if it's an AND group, otherwise create a new AND group
			$last_group_index = count( $this->where_clauses ) - 1;
			if ($last_group_index < 0 || $this->where_clauses[ $last_group_index ]['type'] !== 'AND') {
				$this->where_clauses[] = [
					'type'       => 'AND',
					'conditions' => [],
				];
				++$last_group_index;
			}
			$this->where_clauses[ $last_group_index ]['conditions'][] = $condition;
		}

		$this->params[]       = $value;
		$this->current_column = null;

		return $this->statement;
	}

	/**
	 * Add an IN condition to the query.
	 *
	 * @param array $values The values to use in the IN condition.
	 * @return Statement
	 * @throws InvalidArgumentException If the column is not specified.
	 */
	public function in( array $values ): Statement {
		if ( $this->current_column === null ) {
			throw new InvalidArgumentException( 'Column must be specified before the IN condition' );
		}
		$placeholders = implode( ', ', array_fill( 0, count( $values ), '%s' ) );
		$condition    = "{$this->current_column} IN ($placeholders)";

		$last_group_index = count( $this->where_clauses ) - 1;
		if ( $this->current_condition_type !== $this->where_clauses[ $last_group_index ]['type'] ) {
			$this->where_clauses[] = [
				'type'       => $this->current_condition_type,
				'conditions' => [],
			];
			++$last_group_index;
		}

		$this->where_clauses[ $last_group_index ]['conditions'][] = $condition;
		$this->params = array_merge( $this->params, $values );

		$this->current_column = null;
		return $this->statement;
	}

	/**
	 * Add a NOT IN condition to the query.
	 *
	 * @param array $values The values to use in the NOT IN condition.
	 * @return Statement
	 * @throws InvalidArgumentException If the column is not specified.
	 */
	public function not_in( array $values ): Statement {
		if ( $this->current_column === null ) {
			throw new InvalidArgumentException( 'Column must be specified before the NOT IN condition' );
		}

		$placeholders = implode( ', ', array_fill( 0, count( $values ), '%s' ) );
		$condition    = "{$this->current_column} NOT IN ($placeholders)";

		$last_group_index = count( $this->where_clauses ) - 1;
		if ( $this->current_condition_type !== $this->where_clauses[ $last_group_index ]['type'] ) {
			$this->where_clauses[] = [
				'type'       => $this->current_condition_type,
				'conditions' => [],
			];
			++$last_group_index;
		}

		$this->where_clauses[ $last_group_index ]['conditions'][] = $condition;
		$this->params = array_merge( $this->params, $values );

		$this->current_column = null;
		return $this->statement;
	}

	/**
	 * Add a BETWEEN condition to the query.
	 *
	 * @param mixed $start The start value of the range.
	 * @param mixed $end The end value of the range.
	 * @return Statement
	 * @throws InvalidArgumentException If the column is not specified.
	 */
	public function between( $start, $end ): Statement {
		if ( $this->current_column === null ) {
			throw new InvalidArgumentException( 'Column must be specified before the BETWEEN condition' );
		}

		$condition = "{$this->current_column} BETWEEN %s AND %s";

		$last_group_index = count( $this->where_clauses ) - 1;
		if ( $this->current_condition_type !== $this->where_clauses[ $last_group_index ]['type'] ) {
			$this->where_clauses[] = [
				'type'       => $this->current_condition_type,
				'conditions' => [],
			];
			++$last_group_index;
		}

		$this->where_clauses[ $last_group_index ]['conditions'][] = $condition;
		$this->params[] = $start;
		$this->params[] = $end;

		$this->current_column = null;
		return $this->statement;
	}

	/**
	 * Add an IS NULL condition to the query.
	 *
	 * @return Statement
	 * @throws InvalidArgumentException If the column is not specified.
	 */
	public function is_null(): Statement {
		return $this->add_condition( 'IS', 'NULL' );
	}

	/**
	 * Add an IS NOT NULL condition to the query.
	 *
	 * @return Statement
	 * @throws InvalidArgumentException If the column is not specified.
	 */
	public function is_not_null(): Statement {
		return $this->add_condition( 'IS NOT', 'NULL' );
	}

	/**
	 * Add a raw WHERE clause to the query.
	 *
	 * @param string $rawCondition The raw SQL condition to add.
	 * @param array  $params       Optional parameters for the condition.
	 * @return Statement
	 */
	public function where_raw( string $rawCondition, array $params = [] ): Statement {
		$last_group_index = count( $this->where_clauses ) - 1;
		if ($this->current_condition_type !== $this->where_clauses[ $last_group_index ]['type']) {
			$this->where_clauses[] = [
				'type'       => $this->current_condition_type,
				'conditions' => [],
			];
			++$last_group_index;
		}

		$this->where_clauses[ $last_group_index ]['conditions'][] = $rawCondition;
		$this->params = array_merge( $this->params, $params );

		return $this->statement;
	}

	/**
	 * Add a raw OR WHERE clause to the query.
	 *
	 * @param string $rawCondition The raw SQL condition to add.
	 * @param array  $params       Optional parameters for the condition.
	 * @return Statement
	 */
	public function or_where_raw( string $rawCondition, array $params = [] ): Statement {
		$this->current_condition_type = 'OR';
		return $this->where_raw( $rawCondition, $params );
	}

	/**
	 * Add a grouped condition to the query.
	 *
	 * @param callable $callback A function that builds the grouped conditions
	 * @param string   $operator The operator to use for the group ('AND' or 'OR', default is 'AND')
	 * @return Statement
	 */
	public function group_where( callable $callback, string $operator = 'AND' ): Statement {
		$originalWhereClauses = $this->where_clauses;
		$originalParams       = $this->params;

		// Reset the where clauses to build the group
		$this->where_clauses = [
			[
				'type'       => 'AND',
				'conditions' => [],
			],
		];
		$this->params        = [];

		// Build the grouped conditions
		$callback( $this );

		// Extract the grouped conditions
		$groupedConditions = $this->where_clauses;
		$groupedParams     = $this->params;

		// Restore the original where clauses and params
		$this->where_clauses = $originalWhereClauses;
		$this->params        = $originalParams;

		// Normalize the operator
		$operator = strtoupper( $operator );
		if ( ! in_array( $operator, [ 'AND', 'OR' ], true ) ) {
			$operator = 'AND';
		}

		// Add the grouped conditions to the main where clauses
		$lastIndex = count( $this->where_clauses ) - 1;
		if ($operator === 'OR' || $this->current_condition_type === 'OR') {
			$this->where_clauses[] = [
				'type'       => $operator,
				'conditions' => [ '(' . $this->build_group_sql( $groupedConditions ) . ')' ],
			];
		} else {
			$this->where_clauses[ $lastIndex ]['conditions'][] = '(' . $this->build_group_sql( $groupedConditions ) . ')';
		}

		// Merge the grouped params with the main params
		$this->params = array_merge( $this->params, $groupedParams );

		return $this->statement;
	}

	/**
	 * Build SQL for a group of conditions.
	 *
	 * @param array $group The group of conditions
	 * @return string The SQL for the group
	 */
	protected function build_group_sql( array $group ): string {
		$sql_parts = [];

		foreach ($group as $index => $item) {
			$conditions = implode( ' AND ', $item['conditions'] );
			if ($index > 0) {
				$sql_parts[] = $item['type'] . ' ' . $conditions;
			} else {
				$sql_parts[] = $conditions;
			}
		}

		return implode( ' ', $sql_parts );
	}

	/**
	 * Convert the current WHERE conditions into an SQL string.
	 *
	 * This method combines all the WHERE conditions into a single SQL string.
	 *
	 * @return string The generated SQL WHERE clause.
	 */
	public function to_sql(): string {
		if ( empty( $this->where_clauses ) ) {
			return '';
		}

		$sql_parts = [];

		foreach ( $this->where_clauses as $index => $group ) {
			foreach ( $group['conditions'] as $condition ) {
				if ( $index > 0 || ! empty( $sql_parts ) ) {
					$sql_parts[] = $group['type'] . ' (' . $condition . ')';
				} else {
					$sql_parts[] = '(' . $condition . ')';
				}
			}
		}

		if ( empty( $sql_parts ) ) {
			return '';
		}

		$sql = implode( ' ', $sql_parts );
		$sql = $this->prepare_sql( "WHERE $sql" );

		// Replace 'NULL' with NULL
		$sql = str_replace( "'NULL'", 'NULL', $sql );

		return $sql;
	}

	/**
	 * Check if the where clause is empty by looking at
	 * the "conditions" array.
	 *
	 * @return bool True if the where clause is empty, false otherwise.
	 */
	public function is_empty(): bool {
		return empty( $this->where_clauses[0]['conditions'] );
	}

	/**
	 * Allow method chaining for where conditions.
	 *
	 * @param string $column The column name to add a condition for.
	 * @return $this
	 */
	public function __invoke( string $column ): self {
		return $this->where( $column );
	}

	/**
	 * Return the parent Statement object to allow chaining.
	 *
	 * @return Statement
	 */
	public function end(): Statement {
		return $this->statement;
	}
}
