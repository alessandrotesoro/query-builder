<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Base class for all clauses.
 *
 * @package   Sematico\Baselibs\QueryBuilder
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright 2024 Sematico
 * @license   GPL-3.0-or-later
 */

namespace Sematico\Baselibs\QueryBuilder\Clauses;

use Sematico\Baselibs\QueryBuilder\Statements\Statement;

/**
 * Base class for all clauses.
 */
abstract class Clause {

	/**
	 * @var array<string, mixed> The parameters for the clause.
	 */
	protected $params = [];

	/**
	 * @var Statement The parent Statement object.
	 */
	protected $statement;

	/**
	 * Constructor for the Set class.
	 *
	 * @param Statement $statement The parent Statement object.
	 */
	public function __construct( Statement $statement ) {
		$this->statement = $statement;
	}

	/**
	 * Convert the clause to an SQL string.
	 *
	 * @return string The SQL string.
	 */
	abstract public function to_sql(): string;

	/**
	 * Set a parameter for the clause.
	 *
	 * @param string $key The parameter key.
	 * @param mixed  $value The parameter value.
	 */
	public function set_param( $key, $value ): self {
		$this->params[ $key ] = $value;

		return $this;
	}

	/**
	 * Get the parameters for the clause.
	 *
	 * @return array The parameters.
	 */
	public function get_params(): array {
		return $this->params;
	}

	/**
	 * Check if the clause is empty by looking at the params.
	 *
	 * @return bool True if the clause is empty, false otherwise.
	 */
	public function is_empty(): bool {
		return empty( $this->params );
	}

	/**
	 * Prepare the SQL string.
	 *
	 * @param string $sql The SQL string.
	 * @return string The prepared SQL string.
	 */
	public function prepare_sql( string $sql ): string {
		global $wpdb;

		$params = array_values( $this->get_params() );
		return $wpdb->prepare( $sql, ...$params ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}
}
