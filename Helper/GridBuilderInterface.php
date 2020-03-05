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

use ArrayAccess;
use Countable;
use OutOfBoundsException;
use StingerSoft\AggridBundle\Column\Column;
use StingerSoft\AggridBundle\Column\ColumnInterface;
use StingerSoft\AggridBundle\Components\StatusBar\StatusBarComponentInterface;
use StingerSoft\AggridBundle\Exception\InvalidArgumentTypeException;
use Traversable;

interface GridBuilderInterface extends ArrayAccess, Traversable, Countable {

	/**
	 * Adds a column to the grid
	 *
	 * @param ColumnInterface|string $column
	 *            Property path to bind to this column or ColumnView instance
	 * @param string                 $type
	 *            The type (i.e. class name) of this column
	 * @param array                  $options
	 *            Options to pass the column type
	 * @return $this The grid builder, allowing for chaining
	 * @throws InvalidArgumentTypeException
	 */
	public function add($column, ?string $type = null, array $options = []): self;

	/**
	 * Adds a column to the grid
	 *
	 * @param ColumnInterface|string $column
	 *            Property path to bind to this column or ColumnView instance
	 * @param string                 $type
	 *            The type (i.e. class name) of this column
	 * @param array                  $options
	 *            Options to pass the column type
	 * @return ColumnInterface
	 * @throws InvalidArgumentTypeException
	 */
	public function addColumn($column, ?string $type = null, array $options = []): ColumnInterface;

	/**
	 * Adds a column to the grid
	 *
	 * @param ColumnInterface|string $column
	 *            Property path to bind to this column or ColumnView instance
	 * @param string                 $type
	 *            The type (i.e. class name) of this column
	 * @param array                  $options
	 *            Options to pass the column type
	 * @return $this The grid builder, allowing for chaining
	 * @throws InvalidArgumentTypeException
	 */
	public function addGroup($column, ?string $type = null, array $options = []): self;

	/**
	 * Adds a column to the grid
	 *
	 * @param ColumnInterface|string $column
	 *            Property path to bind to this column or ColumnView instance
	 * @param string                 $type
	 *            The type (i.e. class name) of this column
	 * @param array                  $options
	 *            Options to pass the column type
	 * @return ColumnInterface
	 * @throws InvalidArgumentTypeException
	 */
	public function addGroupColumn($column, ?string $type = null, array $options = []): ColumnInterface;

	/**
	 * Returns the column with the given path.
	 *
	 * @param string $path The path of the column
	 * @return ColumnInterface
	 * @throws OutOfBoundsException If the named column does not exist.
	 */
	public function get(string $path): ColumnInterface;

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

	/**
	 * Adds a status bar component to the grid
	 *
	 * @param string      $id      the id of the status bar component
	 * @param string|null $type    the type (i.e. class) of status bar component
	 * @param array       $options Options to pass the status bar component type
	 * @return $this the grid builder, allowing for chaining
	 * @throws InvalidArgumentTypeException
	 */
	public function addStatusBarComponent(string $id, ?string $type = null, array $options = []): self;

	/**
	 * Removes a status bar component from the grid
	 *
	 * @param string $id the id of the status bar component to remove
	 * @return $this
	 */
	public function removeStatusBarComponent(string $id): self;

	/**
	 * Returns whether a status bar component with the given id exists
	 *
	 * @param string $id the id of the status bar component to check its existence
	 * @return bool
	 */
	public function hasStatusBarComponent(string $id): bool;

	/**
	 * @param string $id the id of the status bar component
	 * @return StatusBarComponentInterface
	 * @throws OutOfBoundsException If the status bar component does not exist.
	 */
	public function getStatusBarComponent(string $id): StatusBarComponentInterface;

	/**
	 * Returns all status bar components in this grid.
	 *
	 * @return StatusBarComponentInterface[]
	 */
	public function allStatusBarComponents(): array;

}