<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Delete statement class.
 *
 * @package   Sematico\Baselibs\QueryBuilder
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright 2024 Sematico
 * @license   MIT
 */

namespace Sematico\Baselibs\QueryBuilder\Statements;

use Sematico\Baselibs\QueryBuilder\Clauses\Join;
use Sematico\Baselibs\QueryBuilder\Exceptions\InvalidQueryException;
use Sematico\Baselibs\QueryBuilder\Traits\HasJoins;

/**
 * Delete statement class.
 */
class Delete extends Statement {

	use HasJoins;

	/**
	 * The Join clause object.
	 *
	 * @var Join
	 */
	protected $join;

	/**
	 * Constructor.
	 *
	 * @param \wpdb $wpdb The WordPress database object.
	 */
	public function __construct( \wpdb $wpdb ) {
		parent::__construct( $wpdb );
		$this->join = new Join( $this );
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
	 * Validate the query.
	 *
	 * @throws InvalidQueryException If the query is invalid or incomplete.
	 */
	protected function validate(): void {
		if ( ! $this->table ) {
			throw new InvalidQueryException( 'No table specified' );
		}
	}

	/**
	 * Convert the query into an SQL string.
	 *
	 * @return string The complete SQL DELETE statement.
	 */
	public function to_sql(): string {
		$this->validate();

		$sql = 'DELETE';

		if ( ! $this->join->is_empty() ) {
			$sql .= " $this->table FROM $this->table";
		} else {
			$sql .= " FROM $this->table";
		}

		if ( ! $this->join->is_empty() ) {
			$sql .= ' ' . $this->join->to_sql();
		}

		if ( ! $this->where->is_empty() ) {
			$sql .= ' ' . $this->where->to_sql();
		}

		if ( ! $this->raw->is_empty() ) {
			$sql .= ' ' . $this->raw->to_sql();
		}

		return $this->prepare_sql( $sql );
	}
}
