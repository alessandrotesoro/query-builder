<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Base statement class.
 *
 * @package   Sematico\Baselibs\QueryBuilder
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright 2024 Sematico
 * @license   MIT
 */

namespace Sematico\Baselibs\QueryBuilder\Statements;

use Sematico\Baselibs\QueryBuilder\Clauses\Distinct;
use Sematico\Baselibs\QueryBuilder\Exceptions\InvalidQueryException;
use Sematico\Baselibs\QueryBuilder\Clauses\GroupBy;
use Sematico\Baselibs\QueryBuilder\Clauses\Limit;
use Sematico\Baselibs\QueryBuilder\Clauses\Offset;
use Sematico\Baselibs\QueryBuilder\Clauses\Having;
use Sematico\Baselibs\QueryBuilder\Clauses\Join;
use Sematico\Baselibs\QueryBuilder\Clauses\OrderBy;
use Sematico\Baselibs\QueryBuilder\Traits\HasJoins;

/**
 * Select statement class.
 *
 * This class represents a SELECT SQL statement and provides methods to build and execute the query.
 */
class Select extends Statement {

	use HasJoins;

	/**
	 * The columns to select.
	 *
	 * @var string[]
	 */
	private $columns = [];

	/**
	 * The OrderBy clause object.
	 *
	 * @var OrderBy
	 */
	protected $order_by;

	/**
	 * The Offset clause object.
	 *
	 * @var Offset
	 */
	protected $offset;

	/**
	 * The Limit clause object.
	 *
	 * @var Limit
	 */
	protected $limit;

	/**
	 * The Join clause object.
	 *
	 * @var Join
	 */
	protected $join;

	/**
	 * The Having clause object.
	 *
	 * @var Having
	 */
	protected $having;

	/**
	 * The GroupBy clause object.
	 *
	 * @var GroupBy
	 */
	protected $group_by;

	/**
	 * The Distinct clause object.
	 *
	 * @var Distinct
	 */
	protected $distinct;

	/**
	 * Constructor.
	 *
	 * @param \wpdb $wpdb The WordPress database object.
	 */
	public function __construct( \wpdb $wpdb ) {
		parent::__construct( $wpdb );

		$this->order_by = new OrderBy( $this );
		$this->offset   = new Offset( $this );
		$this->limit    = new Limit( $this );
		$this->join     = new Join( $this );
		$this->having   = new Having( $this );
		$this->group_by = new GroupBy( $this );
		$this->distinct = new Distinct( $this );
	}

	/**
	 * Get the columns.
	 *
	 * @return string[] The columns.
	 */
	public function get_columns(): array {
		return $this->columns;
	}

	/**
	 * Specify the columns to select.
	 *
	 * @param string|array ...$columns The columns to select. Each column can be:
	 *                                 - A string: the name of the column
	 *                                 - An associative array: [column => alias, ...]
	 *                                 If no columns are specified, defaults to '*'.
	 * @return self
	 * @throws \InvalidArgumentException If a column specification is invalid.
	 */
	public function set_columns( ...$columns ): self {
		$this->columns = [];

		if ( ! $columns ) {
			$this->add_column( '*' );
		} else {
			foreach ( $columns as $column ) {
				if ( is_string( $column ) ) {
					$this->add_column( $column );
				} elseif ( is_array( $column )) {
					$this->add_columns_from_array( $column );
				} else {
					throw new \InvalidArgumentException( 'Argument should be a string or array' );
				}
			}
		}

		return $this;
	}

	/**
	 * Add a column to the SELECT statement.
	 *
	 * @param string      $column_name The name of the column to add.
	 * @param string|null $alias       Optional. The alias to give to the column.
	 * @throws \InvalidArgumentException If the column name or alias is not valid.
	 */
	private function add_column( string $column_name, ?string $alias = null ): self {
		if ( $alias ) {
			$column_name = "$column_name AS $alias";
		}

		$this->columns[] = $column_name;

		return $this;
	}

	/**
	 * Add columns from an array.
	 *
	 * @param array $columns An array of columns. Can be in the format:
	 *                       [column1, column2, column3 => alias3, ...]
	 */
	private function add_columns_from_array( array $columns ): self {
		foreach ( $columns as $key => $value ) {
			if ( is_int( $key ) ) {
				$this->add_column( $value );
			} else {
				$this->add_column( $key, $value );
			}
		}

		return $this;
	}

	/**
	 * Specify the table from which to select
	 *
	 * @param string      $table the table name
	 * @param string|null $alias (optional) the alias to give to the table
	 * @return $this
	 */
	public function from( string $table, string $alias = null ): self {
		if ( $alias ) {
			$table = "$table AS $alias";
		}

		return $this->table( $table );
	}

	/**
	 * Add a COUNT aggregate function to the query.
	 *
	 * @param string      $column The column to count.
	 * @param string|null $alias Optional alias for the result.
	 * @return self Fluent interface.
	 */
	public function count( string $column = '*', ?string $alias = null ): self {
		return $this->add_aggregate_function( 'COUNT', $column, $alias );
	}

	/**
	 * Add a SUM aggregate function to the query.
	 *
	 * @param string      $column The column to sum.
	 * @param string|null $alias Optional alias for the result.
	 * @return self Fluent interface.
	 */
	public function sum( string $column, ?string $alias = null ): self {
		return $this->add_aggregate_function( 'SUM', $column, $alias );
	}

	/**
	 * Add an AVG aggregate function to the query.
	 *
	 * @param string      $column The column to average.
	 * @param string|null $alias Optional alias for the result.
	 * @return self Fluent interface.
	 */
	public function avg( string $column, ?string $alias = null ): self {
		return $this->add_aggregate_function( 'AVG', $column, $alias );
	}

	/**
	 * Add a MIN aggregate function to the query.
	 *
	 * @param string      $column The column to find the minimum of.
	 * @param string|null $alias Optional alias for the result.
	 * @return self Fluent interface.
	 */
	public function min( string $column, ?string $alias = null ): self {
		return $this->add_aggregate_function( 'MIN', $column, $alias );
	}

	/**
	 * Add a MAX aggregate function to the query.
	 *
	 * @param string      $column The column to find the maximum of.
	 * @param string|null $alias Optional alias for the result.
	 * @return self Fluent interface.
	 */
	public function max( string $column, ?string $alias = null ): self {
		return $this->add_aggregate_function( 'MAX', $column, $alias );
	}

	/**
	 * Add an aggregate function to the query.
	 *
	 * @param string      $aggregate_function The aggregate function (COUNT, SUM, AVG, MIN, MAX).
	 * @param string      $column The column to apply the function to.
	 * @param string|null $alias Optional alias for the result.
	 * @return self Fluent interface.
	 */
	private function add_aggregate_function( string $aggregate_function, string $column, ?string $alias ): self {
		$aggregate_column = "{$aggregate_function}({$column})";
		if ($alias) {
			$aggregate_column .= " AS {$alias}";
		}
		// Instead of appending, we'll replace the columns array
		$this->columns = [ $aggregate_column ];
		return $this;
	}

	/**
	 * Set the query to use DISTINCT.
	 *
	 * @return Statement Returns the parent Statement object for method chaining.
	 */
	public function distinct(): self {
		$this->distinct->distinct();
		return $this;
	}

	/**
	 * Set the limit for the query.
	 *
	 * @param int $limit The number of rows to limit the query to.
	 * @return self Returns the parent Statement object for method chaining.
	 */
	public function limit( int $limit ): self {
		$this->limit->limit( $limit );
		return $this;
	}

	/**
	 * Set the offset for the query.
	 *
	 * @param int $offset The number of rows to offset the query by.
	 * @return self Returns the parent Statement object for method chaining.
	 */
	public function offset( int $offset ): self {
		$this->offset->offset( $offset );
		return $this;
	}

	/**
	 * Set the group by clause for the query.
	 *
	 * @param string|array $columns The columns to group by.
	 * @return self Returns the parent Statement object for method chaining.
	 */
	public function group_by( $columns ): self {
		$this->group_by->group_by( $columns );
		return $this;
	}

	/**
	 * Add a HAVING condition to the query.
	 *
	 * @param string $condition The HAVING condition.
	 * @param mixed  $value     The value to compare with.
	 * @return self
	 */
	public function having( string $condition, $value ): self {
		$this->having->having( $condition, $value );
		return $this;
	}

	/**
	 * Add an AND HAVING condition to the query.
	 *
	 * @param string $condition The HAVING condition.
	 * @param mixed  $value     The value to compare with.
	 * @return self
	 */
	public function and_having( string $condition, $value ): self {
		$this->having->and_having( $condition, $value );
		return $this;
	}

	/**
	 * Add an OR HAVING condition to the query.
	 *
	 * @param string $condition The HAVING condition.
	 * @param mixed  $value     The value to compare with.
	 * @return self
	 */
	public function or_having( string $condition, $value ): self {
		$this->having->or_having( $condition, $value );
		return $this;
	}

	/**
	 * Add an ORDER BY clause to the query.
	 *
	 * @param string      $column    The name of the column to order by.
	 * @param string|null $direction Optional. The sorting direction, either 'ASC' or 'DESC'. Default is 'asc'.
	 * @return self
	 */
	public function order_by( string $column, string $direction = 'asc' ): self {
		$this->order_by->order_by( $column, $direction );
		return $this;
	}

	/**
	 * Add a raw SQL fragment to the query.
	 *
	 * @param string $sql The raw SQL fragment to add.
	 * @param array  $bindings Optional. An array of values to be bound to the SQL fragment.
	 * @return Statement
	 */
	public function add_raw( string $sql, array $bindings = [] ): self {
		$this->raw->add_raw( $sql, $bindings );
		return $this;
	}

	/**
	 * Validate the query.
	 *
	 * @throws InvalidQueryException If the query is invalid or incomplete.
	 */
	protected function validate(): void {
		if ( ! $this->columns ) {
			throw new InvalidQueryException( 'No columns specified' );
		}

		if ( ! $this->table ) {
			throw new InvalidQueryException( 'No table specified' );
		}
	}

	/**
	 * Convert the query into an SQL string.
	 *
	 * @return string The complete SQL SELECT statement.
	 */
	public function to_sql(): string {
		$this->validate();

		$distinct = $this->distinct->to_sql();
		$columns  = implode( ', ', $this->get_columns() );
		$sql      = "SELECT {$distinct}{$columns} FROM $this->table";

		if ( ! $this->join->is_empty() ) {
			$sql .= ' ' . $this->join->to_sql();
		}

		if ( ! $this->where->is_empty() ) {
			$sql .= ' ' . $this->where->to_sql();
		}

		if ( ! $this->group_by->is_empty() ) {
			$sql .= ' ' . $this->group_by->to_sql();
		}

		if ( ! $this->having->is_empty() ) {
			$sql .= ' ' . $this->having->to_sql();
		}

		if ( ! $this->order_by->is_empty() ) {
			$sql .= ' ' . $this->order_by->to_sql();
		}

		if ( ! $this->limit->is_empty() ) {
			$sql .= ' ' . $this->limit->to_sql();
		}

		if ( ! $this->offset->is_empty() ) {
			$sql .= ' ' . $this->offset->to_sql();
		}

		if ( ! $this->raw->is_empty() ) {
			$sql .= ' ' . $this->raw->to_sql();
		}

		return $this->prepare_sql( $sql );
	}
}
