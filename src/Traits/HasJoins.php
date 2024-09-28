<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Join trait.
 *
 * This trait provides functionality to add JOIN clauses to SQL queries.
 *
 * @package   Sematico\Baselibs\QueryBuilder
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright 2024 Sematico
 * @license   MIT
 */

namespace Sematico\Baselibs\QueryBuilder\Traits;

use Sematico\Baselibs\QueryBuilder\Clauses\Join;

/**
 * Trait HasJoins
 *
 * Provides methods to add and manage JOIN clauses in SQL queries.
 */
trait HasJoins {

	/**
	 * The Join clause object.
	 *
	 * @var Join
	 */
	protected $join;

	/**
	 * Add an INNER JOIN clause to the query.
	 *
	 * @param string $table The table to join.
	 * @param string $alias The alias for the joined table.
	 * @param string $first The first column for the join condition.
	 * @param string $operator The operator for the join condition.
	 * @param string $second The second column for the join condition.
	 * @return $this Fluent interface.
	 */
	public function join( string $table, string $alias, string $first, string $operator, string $second ): self {
		$this->join->join( $table, $alias, $first, $operator, $second );
		return $this;
	}

	/**
	 * Add a LEFT JOIN clause to the query.
	 *
	 * @param string $table The table to join.
	 * @param string $alias The alias for the joined table.
	 * @param string $first The first column for the join condition.
	 * @param string $operator The operator for the join condition.
	 * @param string $second The second column for the join condition.
	 * @return $this Fluent interface.
	 */
	public function left_join( string $table, string $alias, string $first, string $operator, string $second ): self {
		$this->join->left_join( $table, $alias, $first, $operator, $second );
		return $this;
	}

	/**
	 * Add a RIGHT JOIN clause to the query.
	 *
	 * @param string $table The table to join.
	 * @param string $alias The alias for the joined table.
	 * @param string $first The first column for the join condition.
	 * @param string $operator The operator for the join condition.
	 * @param string $second The second column for the join condition.
	 * @return $this Fluent interface.
	 */
	public function right_join( string $table, string $alias, string $first, string $operator, string $second ): self {
		$this->join->right_join( $table, $alias, $first, $operator, $second );
		return $this;
	}
}
