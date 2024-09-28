<?php // phpcs:ignore WordPress.Files.FileName
/**
 * QueryBuilder class.
 *
 * @package   Sematico\Baselibs\QueryBuilder
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright 2024 Sematico
 * @license   MIT
 */

namespace Sematico\Baselibs\QueryBuilder;

use Sematico\Baselibs\QueryBuilder\Statements\Select;
use Sematico\Baselibs\QueryBuilder\Statements\Insert;
use Sematico\Baselibs\QueryBuilder\Statements\Delete;
use Sematico\Baselibs\QueryBuilder\Statements\Update;

/**
 * This class is a wrapper for the wpdb class.
 * It provides a fluent interface for building SQL queries.
 */
class QueryBuilder {
	/**
	 * The wpdb instance.
	 *
	 * @var \wpdb
	 */
	private $wpdb;

	/**
	 * Initialize the QueryBuilder.
	 */
	public function __construct() {
		global $wpdb;

		$this->wpdb = $wpdb;
	}

	/**
	 * Create a new Select statement.
	 *
	 * @param string|array ...$columns The columns to select.
	 * @return Select
	 */
	public function select( ...$columns ): Select {
		return ( new Select( $this->wpdb ) )->set_columns( ...$columns );
	}

	/**
	 * Create a new Insert statement.
	 *
	 * @param array<string, mixed> $data The data to insert (column => value pairs).
	 * @return Insert
	 */
	public function insert( array $data ): Insert {
		return ( new Insert( $this->wpdb ) )->data( $data );
	}

	/**
	 * Create a new Update statement.
	 *
	 * @param array<string, mixed> $data The data to update (column => value pairs).
	 * @return Update
	 */
	public function update( array $data ): Update {
		return ( new Update( $this->wpdb ) )->data( $data );
	}

	/**
	 * Create a new Delete statement.
	 *
	 * @return Delete
	 */
	public function delete(): Delete {
		return new Delete( $this->wpdb );
	}
}
