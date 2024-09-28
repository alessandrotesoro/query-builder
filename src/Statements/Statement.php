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

use Sematico\Baselibs\QueryBuilder\Clauses\Raw;
use Sematico\Baselibs\QueryBuilder\Exceptions\InvalidQueryException;
use Sematico\Baselibs\QueryBuilder\Clauses\Where;

/**
 * Base statement class.
 *
 * This abstract class serves as a foundation for all SQL statement classes in the WPDBBuilder.
 * It defines common methods that all statement classes should implement.
 */
abstract class Statement {

	/**
	 * The table name.
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * The parameters for the query.
	 *
	 * @var array
	 */
	protected $params = [];

	/**
	 * The WordPress database object.
	 *
	 * @var \wpdb
	 */
	private $wpdb;

	/**
	 * The Where clause object.
	 *
	 * @var Where
	 */
	protected $where;

	/**
	 * The Raw clause object.
	 *
	 * @var Raw
	 */
	protected $raw;

	/**
	 * Constructor.
	 *
	 * @param \wpdb $wpdb The WordPress database object.
	 */
	public function __construct( \wpdb $wpdb ) {
		$this->wpdb  = $wpdb;
		$this->where = new Where( $this );
		$this->raw   = new Raw( $this );
	}

	/**
	 * Get the wpdb object.
	 *
	 * @return \wpdb The wpdb object.
	 */
	public function get_wpdb(): \wpdb {
		return $this->wpdb;
	}

	/**
	 * Set the table name.
	 * The database prefix is automatically added.
	 *
	 * @param string $table The table name.
	 * @return $this
	 */
	public function table( string $table ): self {
		$this->table = $this->wpdb->prefix . $table;
		return $this;
	}

	/**
	 * Get the table name, with the database prefix.
	 *
	 * @return string The table name.
	 */
	public function get_table(): string {
		return $this->table;
	}

	/**
	 * Set multiple params at a time from an associative array.
	 *
	 * @param array $params The parameters to set.
	 * @return $this
	 */
	public function set_params( array $params ): self {
		foreach ( $params as $key => $value ) {
			$this->set_param( $key, $value );
		}

		return $this;
	}

	/**
	 * Set a single parameter.
	 *
	 * @param string $key The parameter name.
	 * @param mixed  $value The parameter value.
	 * @return $this
	 */
	public function set_param( string $key, $value ): self {
		$this->params[ $key ] = $value;
		return $this;
	}

	/**
	 * Get the parameters that will be used in the query.
	 *
	 * @return array The parameters.
	 */
	public function get_params(): array {
		$params = $this->params;

		// Add parameters from the Where clause
		if ( $this->where) {
			$params = array_merge( $params, $this->where->get_params() );
		}

		// Add parameters from the Raw clause
		if ( $this->raw) {
			$params = array_merge( $params, $this->raw->get_params() );
		}

		return $params;
	}

	/**
	 * Validate the query.
	 *
	 * This method should be implemented by child classes to perform
	 * specific validation checks on the query before it's executed.
	 *
	 * @throws InvalidQueryException If the query is invalid or incomplete.
	 * @return void
	 */
	abstract protected function validate(): void;

	/**
	 * Convert the query into an SQL string.
	 *
	 * This method should be implemented by child classes to generate
	 * the final SQL string representation of the query.
	 *
	 * @throws InvalidQueryException If the query is invalid or incomplete.
	 * @return string The generated SQL query string.
	 */
	abstract public function to_sql(): string;

	/**
	 * Prepare the SQL query with WordPress's prepare method.
	 *
	 * @param string $sql The SQL query with placeholders.
	 * @return string The prepared SQL query.
	 */
	protected function prepare_sql( string $sql ): string {
		if ( empty( $this->params ) ) {
			return $sql;
		}
		return $this->wpdb->prepare( $sql, array_values( $this->params ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Execute the query and return the result.
	 *
	 * @return mixed The query result.
	 * @throws InvalidQueryException If the query is invalid.
	 */
	public function execute() {
		$this->validate();
		$sql = $this->prepare_sql( $this->to_sql() );
		return $this->wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Fetch a single row from the result set.
	 *
	 * @param int $fetch_style Optional. The fetch style (ARRAY_A or ARRAY_N).
	 * @return array|null The fetched row or null if no rows are available.
	 * @throws InvalidQueryException If the query is invalid.
	 */
	public function fetch_row( $fetch_style = ARRAY_A ) {
		$this->validate();
		$sql = $this->prepare_sql( $this->to_sql() );
		return $this->wpdb->get_row( $sql, $fetch_style ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Fetch all rows from the result set.
	 *
	 * @param int $fetch_style Optional. The fetch style (ARRAY_A or ARRAY_N).
	 * @return array The fetched rows.
	 * @throws InvalidQueryException If the query is invalid.
	 */
	public function fetch_all( $fetch_style = ARRAY_A ) {
		$this->validate();
		$sql = $this->prepare_sql( $this->to_sql() );
		return $this->wpdb->get_results( $sql, $fetch_style ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Fetch a single column from the result set.
	 *
	 * @param int $column_offset Optional. 0-indexed number of the column to retrieve.
	 * @return array|null The column values or null if no rows are available.
	 * @throws InvalidQueryException If the query is invalid.
	 */
	public function fetch_column( $column_offset = 0 ) {
		$this->validate();
		$sql = $this->prepare_sql( $this->to_sql() );
		return $this->wpdb->get_col( $sql, $column_offset ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Get the number of rows affected by the query.
	 *
	 * @return int The number of rows affected.
	 */
	public function get_affected_rows(): int {
		return $this->wpdb->rows_affected;
	}

	/**
	 * Get the ID of the last inserted row.
	 *
	 * @return int|string|null The last insert ID or null if not applicable.
	 */
	public function last_insert_id() {
		return $this->wpdb->insert_id;
	}

	/**
	 * Start a WHERE condition for the specified column.
	 *
	 * @param string $column The column name to add a condition for.
	 * @return Where
	 */
	public function where( string $column ): Where {
		return $this->where->where( $column );
	}

	/**
	 * Start an AND WHERE condition for the specified column.
	 *
	 * @param string $column The column name to add a condition for.
	 * @return Where
	 */
	public function and_where( string $column ): Where {
		return $this->where->and_where( $column );
	}

	/**
	 * Start an OR WHERE condition for the specified column.
	 *
	 * @param string $column The column name to add a condition for.
	 * @return Where
	 */
	public function or_where( string $column ): Where {
		return $this->where->or_where( $column );
	}

	/**
	 * Start a grouped WHERE condition for the specified column.
	 *
	 * @param callable $callback A function that builds the grouped conditions
	 * @param string   $operator The operator to use for the group ('AND' or 'OR', default is 'AND')
	 * @return Statement
	 */
	public function group_where( callable $callback, string $operator = 'AND' ): Statement {
		return $this->where->group_where( $callback, $operator );
	}

	/**
	 * Add a raw SQL fragment to the query.
	 *
	 * @param string $sql The raw SQL fragment to add.
	 * @param array  $bindings Optional. An array of values to be bound to the SQL fragment.
	 * @return Statement
	 */
	public function raw( string $sql, array $bindings = [] ): Statement {
		return $this->raw->add_raw( $sql, $bindings );
	}

	/**
	 * End the query.
	 *
	 * @return Statement
	 */
	public function end(): Statement {
		return $this;
	}
}
