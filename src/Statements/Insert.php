<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Insert statement class.
 *
 * @package   Sematico\Baselibs\QueryBuilder
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright 2024 Sematico
 * @license   GPL-3.0-or-later
 */

namespace Sematico\Baselibs\QueryBuilder\Statements;

use Sematico\Baselibs\QueryBuilder\Exceptions\InvalidQueryException;

/**
 * Insert statement class.
 *
 * This class represents an SQL INSERT statement and provides methods to build and execute it.
 */
class Insert extends Statement {

	/**
	 * The data to insert.
	 *
	 * @var array<string, mixed>
	 */
	private $data = [];

	/**
	 * Specify the table to insert into and execute the insert.
	 *
	 * This method is used to specify the table to insert into and execute the insert
	 * via the wpdb->insert method without the need to call the execute method.
	 *
	 * @param string $table The name of the table.
	 * @return int|false The number of rows inserted, or false on error.
	 */
	public function into( string $table ): int|false {
		$this->table( $table );
		$this->validate();

		global $wpdb;
		return $wpdb->insert( $this->table, $this->data );
	}

	/**
	 * Specify the data to insert.
	 *
	 * @param array<string, mixed> $data The data to insert (column => value pairs).
	 * @return self
	 */
	public function data( array $data ): self {
		$this->data = $data;
		return $this;
	}

	/**
	 * Validate the query.
	 *
	 * Ensures that a table is specified and data is provided for insertion.
	 *
	 * @throws InvalidQueryException If the query is invalid or incomplete.
	 */
	protected function validate(): void {
		if ( ! $this->table ) {
			throw new InvalidQueryException( 'No table specified' );
		}
		if ( empty( $this->data ) ) {
			throw new InvalidQueryException( 'No data specified for insertion' );
		}
	}

	/**
	 * Convert the query into an SQL string.
	 *
	 * Builds the complete SQL INSERT statement based on the specified table and data.
	 *
	 * @return string The complete SQL INSERT statement.
	 */
	public function to_sql(): string {
		$this->validate();

		$columns      = array_keys( $this->data );
		$column_list  = implode( ', ', $columns );
		$placeholders = [];

		foreach ( $this->data as $key => $value ) {
			if ( $value === null ) {
				$placeholders[] = 'NULL';
				// Remove the null value from $this->data to avoid preparing it
				unset( $this->data[ $key ] );
			} else {
				$placeholders[] = '%s';
				$this->set_param( $key, $value );
			}
		}

		$placeholders_string = implode( ', ', $placeholders );

		$sql = "INSERT INTO $this->table ($column_list) VALUES ($placeholders_string)";

		if ( ! $this->raw->is_empty() ) {
			$sql .= ' ' . $this->raw->to_sql();
		}

		return $this->prepare_sql( $sql );
	}
}
