<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Update statement class.
 *
 * @package   Sematico\Baselibs\QueryBuilder
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright 2024 Sematico
 * @license   MIT
 */

namespace Sematico\Baselibs\QueryBuilder\Statements;

use Sematico\Baselibs\QueryBuilder\Exceptions\InvalidQueryException;

/**
 * Update statement class.
 *
 * This class represents an SQL UPDATE statement and provides methods to build and execute it.
 */
class Update extends Statement {

	/**
	 * The data to update.
	 *
	 * @var array<string, mixed>
	 */
	private $data = [];

	/**
	 * Specify the table to update and execute the update.
	 *
	 * This method is used to specify the table to update and execute the update
	 * via the wpdb->update method without the need to call the execute method.
	 *
	 * @param string $table The name of the table.
	 * @return int|false The number of rows updated, or false on error.
	 */
	public function into( string $table ): int|false {
		$this->table( $table );
		$this->validate();

		global $wpdb;
		return $wpdb->update( $this->table, $this->data );
	}

	/**
	 * Specify the data to update.
	 *
	 * @param array<string, mixed> $data The data to update (column => value pairs).
	 * @return self
	 */
	public function data( array $data ): self {
		$this->data = $data;
		return $this;
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
		if ( empty( $this->data ) ) {
			throw new InvalidQueryException( 'No data specified for update' );
		}
		if ( $this->where->is_empty() ) {
			throw new InvalidQueryException( 'No WHERE clause specified for update' );
		}
	}

	/**
	 * Convert the query into an SQL string.
	 *
	 * @return string The complete SQL UPDATE statement.
	 */
	public function to_sql(): string {
		$this->validate();

		$set_parts = [];
		foreach ( $this->data as $column => $value ) {
			$set_parts[] = "$column = %s";
			$this->set_param( $column, $value );
		}

		$set_clause   = 'SET ' . implode( ', ', $set_parts );
		$where_clause = $this->where->to_sql();

		$sql = "UPDATE {$this->table} $set_clause $where_clause";

		if ( ! $this->raw->is_empty() ) {
			$sql .= ' ' . $this->raw->to_sql();
		}

		return $this->prepare_sql( $sql );
	}
}
