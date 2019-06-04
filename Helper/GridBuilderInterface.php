<?php
declare(strict_types=1);
/*
 * This file is part of the Stinger Soft AgGrid package.
 *
 * (c) Oliver Kotte <oliver.kotte@stinger-soft.net>
 * (c) Florian Meyer <florian.meyer@stinger-soft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace StingerSoft\AggridBundle\Helper;

use StingerSoft\AggridBundle\Column\Column;

interface GridBuilderInterface extends \ArrayAccess, \Traversable, \Countable {

	/**
	 * Adds a column to the grid
	 *
	 * @param Column|string $column
	 *            Property path to bind to this column or ColumnView instance
	 * @param string $type
	 *            The type (i.e. class name) of this column
	 * @param array $options
	 *            Options to pass the column type
	 * @return $this The grid builder, allowing for chaining
	 */
	public function add($column, ?string $type = null, array $options = array()): self;

	/**
	 * Returns the column with the given path.
	 *
	 * @param string $path The path of the column
	 * @return Column
	 * @throws \OutOfBoundsException If the named column does not exist.
	 */
	public function get(string $path): Column;

	/**
	 * Returns whether a column with the given path exists..
	 *
	 * @param string $path The path of the column
	 * @return bool
	 */
	public function has(string $path): bool;

	/**
	 * Removes a column from the grid.
	 *
	 * @param string $path The path of the column to remove
	 * @return $this
	 */
	public function remove(string $path): self;

	/**
	 * Returns all columns in this grid.
	 *
	 * @return Column[]
	 */
	public function all(): array;

}