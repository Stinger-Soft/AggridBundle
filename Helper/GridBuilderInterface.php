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
use ReflectionException;
use StingerSoft\AggridBundle\Column\Column;
use StingerSoft\AggridBundle\Column\ColumnInterface;
use StingerSoft\AggridBundle\Components\ComponentInterface;
use StingerSoft\AggridBundle\Components\StatusBar\StatusBarComponentInterface;
use StingerSoft\AggridBundle\Exception\InvalidArgumentTypeException;
use Traversable;

/**
 * @implements Traversable<string, ColumnInterface>
 */
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
	public function add($column, string $type, array $options = []): self;

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
	public function addColumn($column, string $type, array $options = []): ColumnInterface;

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
	public function addGroup($column, string $type, array $options = []): self;

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
	public function addGroupColumn($column, string $type, array $options = []): ColumnInterface;

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
	 * @throws ReflectionException
	 */
	public function addComponent(string $id, ?string $type = null, array $options = []): self;

	/**
	 * Removes a component from the grid
	 *
	 * @param string $category the category of the components to remove
	 * @param string $id       the id of the status bar component to remove
	 * @return $this
	 * @see ComponentInterface::CATEGORIES for categories that can be used
	 */
	public function removeComponent(string $category, string $id): self;

	/**
	 * Returns whether a component with the given id exists in the given category.
	 *
	 * @param string $category the category of the component to check
	 * @param string $id       the id of the component to check
	 * @return bool
	 * @see ComponentInterface::CATEGORIES for categories that can be used
	 */
	public function hasComponent(string $category, string $id): bool;

	/**
	 * Retrieves the component from the given category with the given id.
	 *
	 * @param string $category the category of the components to retrieve
	 * @param string $id       the id of the status bar component
	 * @return StatusBarComponentInterface
	 * @throws OutOfBoundsException If the category or component within that category does not exist.
	 * @see ComponentInterface::CATEGORIES for categories that can be used
	 */
	public function getComponent(string $category, string $id): ComponentInterface;

	/**
	 * Returns components in this grid.
	 *
	 * @param string|null $category the category of components to retrieve or null to retrieve all
	 * @return ComponentInterface[]
	 * @see ComponentInterface::CATEGORIES for categories that can be used
	 */
	public function components(?string $category = null): array;

}
