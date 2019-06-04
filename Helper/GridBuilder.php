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
use StingerSoft\AggridBundle\Column\ColumnInterface;
use StingerSoft\AggridBundle\Column\ColumnTypeInterface;
use StingerSoft\AggridBundle\Grid\Grid;
use StingerSoft\AggridBundle\Service\DependencyInjectionExtensionInterface;
use StingerSoft\AggridBundle\View\ColumnView;

class GridBuilder implements \IteratorAggregate, GridBuilderInterface {

	/**
	 * @var Column[] Array of all column settings
	 */
	protected $columns;

	/**
	 * @var Grid the grid this builder is used for
	 */
	protected $grid;

	/**
	 * @var array the options for the grid
	 */
	protected $gridOptions;

	/**
	 * @var DependencyInjectionExtensionInterface
	 */
	protected $dependencyInjectionExtension;

	public function __construct(Grid $grid, DependencyInjectionExtensionInterface $dependencyInjectionExtension, array $gridOptions = array()) {
		$this->grid = $grid;
		$this->gridOptions = $gridOptions;
		$this->dependencyInjectionExtension = $dependencyInjectionExtension;
		$this->columns = array();
	}

	/**
	 * {@inheritdoc}
	 * @see \StingerSoft\AggridBundle\Service\GridBuilderInterface::add()
	 */
	public function add($column, ?string $type = null, array $options = array()): GridBuilderInterface {
		if(!$column instanceof ColumnInterface) {
			$typeInstance = null;
			try {
				$typeInstance = $this->getColumnTypeInstance($type);
			} catch(\ReflectionException $re) {
				throw new \InvalidArgumentException('If the column parameter is no instance of the interface ' . ColumnInterface::class . ' you must specify a valid classname for the type to be used! ' . $type . ' given', null, $re);
			}
			$column = new Column($column, $typeInstance, $this->dependencyInjectionExtension, $options, $this->gridOptions, $this->grid->getQueryBuilder());
		}
		$this->columns[$column->getPath()] = $column;
		return $this;
	}

	/**
	 * Returns whether a column with the given path exists (implements the \ArrayAccess interface).
	 *
	 * @param string $path The path of the column
	 * @return bool
	 */
	public function offsetExists($path): bool {
		return $this->has($path);
	}

	/**
	 * Returns the column with the given path (implements the \ArrayAccess interface).
	 *
	 * @param string $path The path of the column
	 * @return Column The column
	 * @throws \OutOfBoundsException If the named column does not exist.
	 */
	public function offsetGet($path): Column {
		return $this->get($path);
	}

	/**
	 * Adds a column to the grid (implements the \ArrayAccess interface).
	 *
	 * @param string $path Ignored. The path of the column is used
	 * @param ColumnView $settings The column to be added
	 * @see self::add()
	 */
	public function offsetSet($path, $settings): void {
		$this->add($settings);
	}

	/**
	 * Removes the column with the given path from the grid (implements the \ArrayAccess interface).
	 *
	 * @param string $path The path of the column to remove
	 */
	public function offsetUnset($path): void {
		$this->remove($path);
	}

	/**
	 * Returns the iterator for the columns.
	 *
	 * @return \Traversable|Column[]
	 */
	public function getIterator() {
		return $this->columns;
	}

	/**
	 * Returns the number of columns (implements the \Coungrid interface).
	 *
	 * @return int The number of columns
	 */
	public function count(): int {
		return count($this->columns);
	}

	/**
	 * @inheritdoc
	 */
	public function get(string $path): Column {
		if(isset($this->columns[$path])) {
			return $this->columns[$path];
		}

		throw new \OutOfBoundsException(sprintf('Column "%s" does not exist.', $path));
	}

	/**
	 * @inheritdoc
	 */
	public function has(string $path): bool {
		return isset($this->columns[$path]);
	}

	/**
	 * Removes a column from the grid.
	 *
	 * @param string $path The path of the column to remove
	 * @return $this
	 */
	public function remove(string $path): GridBuilderInterface {
		if(isset($this->columns[$path])) {
			unset($this->columns[$path]);
		}
		return $this;
	}

	/**
	 * Returns all columns in this grid.
	 *
	 * @return Column[]
	 */
	public function all(): array {
		return $this->columns;
	}

	/**
	 * Creates an instance of the given column type class.
	 *
	 * @param string $class
	 *            Classname of the column type
	 * @return ColumnTypeInterface
	 * @throws \InvalidArgumentException
	 */
	private function getColumnTypeInstance($class): ColumnTypeInterface {
		if($class === null) {
			throw new \InvalidArgumentException('Paramater class may not be null!');
		}
		return $this->dependencyInjectionExtension->resolveColumnType($class);
	}
}