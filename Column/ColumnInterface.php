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

namespace StingerSoft\AggridBundle\Column;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use ReflectionException;
use StingerSoft\AggridBundle\Exception\InvalidArgumentTypeException;
use StingerSoft\AggridBundle\Filter\Filter;
use StingerSoft\AggridBundle\Transformer\DataTransformerInterface;
use StingerSoft\AggridBundle\View\ColumnView;

interface ColumnInterface {

	public function setParent(?ColumnInterface $parent): self;

	public function getParent(): ?ColumnInterface;

	/**
	 * @return ColumnInterface[]|Collection
	 */
	public function getChildren(): array;

	public function addChild(ColumnInterface $child): self;

	/**
	 * Get the path to access the property on the bound object
	 *
	 * @return string the path to access the property on the bound object
	 */
	public function getPath(): string;

	/**
	 * Get the path to access the property on the bound object
	 *
	 * @param string $path the path to access the property on the bound object
	 * @return self
	 */
	public function setPath(string $path): self;

	/**
	 * Get the data source of the column.
	 *
	 * @return QueryBuilder|array the data source of the column.
	 */
	public function getDataSource();

	/**
	 * Get the query builder used by a filter for the column (if any).
	 *
	 * @return QueryBuilder the query builder used by a filter for the column (if any).
	 */
	public function getQueryBuilder(): ?QueryBuilder;

	/**
	 * Get the path to be used by a query builder for sorting and ordering etc.
	 * This may differ from the <code>ColumnInterface::getPath()</code> especially for joined paths.
	 *
	 * @return string the path to be used by a query builder for sorting and ordering etc.
	 */
	public function getQueryPath(): string;

	/**
	 * Set the path to be used by a query builder for sorting and ordering etc.
	 *
	 * @param string $queryPath the path to be used by a query builder for sorting and ordering etc.
	 * @return ColumnInterface
	 */
	public function setQueryPath(string $queryPath): self;

	/**
	 * Get the path to be used by a query builder for filtering.
	 * This may differ from the <code>ColumnInterface::getPath()</code> especially for joined paths.
	 *
	 * @return string the path to be used by a query builder for filtering.
	 */
	public function getFilterQueryPath(): string;

	/**
	 * Adds the given data transformer to the column.
	 * Any data transformers are executed whenever the value of the object / cell the column is mapped to shall be
	 * retrieved in order to be rendered. Data transformers are applied in the order they were added. The order may be
	 * influenced by forcing a data transformer to be appended rather than to be prepended, which is the default behaviour.
	 *
	 * @param DataTransformerInterface $dataTransformer the data transformer to be added
	 * @param bool                     $forceAppend     true, in case the data transformer shall be added to the end of the
	 *                                                  list of transformers (i.e. will be inserted after the already existing ones) or
	 *                                                  false (default), in case the data transformer shall be prepended to the list of
	 *                                                  transformers (i.e. it will be inserted before the already existing ones).
	 * @return ColumnInterface
	 */
	public function addDataTransformer(DataTransformerInterface $dataTransformer, $forceAppend = false): self;

	/**
	 * Get all attached data transformers for the column.
	 *
	 * @return DataTransformerInterface[] all attached data transformers for the column.
	 */
	public function getDataTransformers(): array;

	/**
	 * Clears the data transformers.
	 *
	 * @return self
	 */
	public function resetDataTransformers(): self;

	/**
	 * Get the delegate (if any) to be used when ordering the column in enterprise or server side mode.
	 *
	 * @return callable|null the delegate (if any) to be used when ordering the column in enterprise or server side mode.
	 */
	public function getServerSideOrderDelegate(): ?callable;

	/**
	 * Set the delegate (if any) to be used when ordering the column in enterprise or server side mode.
	 *
	 * @param callable|null $serverSideOrderDelegate
	 * @return $this
	 */
	public function setServerSideOrderDelegate(?callable $serverSideOrderDelegate = null): self;

	/**
	 * Get the delegate (if any) to be used when searching in the column in enterprise or server side mode.
	 *
	 * @return callable|null the delegate (if any) to be used when searching in the column in enterprise or server side mode.
	 */
	public function getServerSideSearchDelegate(): ?callable;

	/**
	 * Set the delegate (if any) to be used when searching in the column in enterprise or server side mode.
	 *
	 * @param callable|null $serverSideSearchDelegate the delegate (if any) to be used when searching in the column in enterprise or server side mode.
	 * @return $this
	 */
	public function setServerSideSearchDelegate(?callable $serverSideSearchDelegate = null): self;

	/**
	 * Get the options of the grid the column belongs to
	 *
	 * @return array the options of the grid the column belongs to
	 */
	public function getGridOptions(): array;

	/**
	 * Set the options of the grid the column belongs to
	 *
	 * @param array the options of the grid the column belongs to
	 * @return $this
	 */
	public function setGridOptions(array $gridOptions): self;

	/**
	 * Returns the column type
	 *
	 * @return ColumnTypeInterface the column type
	 */
	public function getColumnType(): ColumnTypeInterface;

	/**
	 * Returns the filter object of the column (if any) in case the column is filterable
	 *
	 * @return Filter|null the filter of the column or null, in case it is not filterable
	 */
	public function getFilter(): ?Filter;

	/**
	 * Sets the filter for the column (if any)
	 *
	 * @param Filter|null $filter
	 * @return ColumnInterface
	 */
	public function setFilter(?Filter $filter): ColumnInterface;

	/**
	 * Get the column options
	 *
	 * @return array the options of the column
	 */
	public function getColumnOptions(): array;

	/**
	 * Set the column options
	 *
	 * @param array $columnOptions the options of the column
	 * @return $this
	 */
	public function setColumnOptions(array $columnOptions): self;

	/**
	 * Returns whether the column can be ordered or sorted
	 *
	 * @return bool true in case the column can be ordered, false otherwise
	 */
	public function isOrderable(): bool;

	/**
	 * Returns whether the column is providing identity details
	 *
	 * @return bool true in case the column provides identity details, false otherwise
	 */
	public function isIdentityProvider(): bool;

	/**
	 * Get the hash code of the column.
	 *
	 * @return int the hash code of the column.
	 * @throws ReflectionException
	 */
	public function getHashCode(): int;

	/**
	 * @param mixed  $item
	 * @param string $rootAlias
	 * @return mixed
	 * @throws InvalidArgumentTypeException
	 * @throws ReflectionException
	 */
	public function createData($item, string $rootAlias);

	/**
	 * Returns whether the column is filterable or not.
	 *
	 * @return bool true in case the column is filterable, false otherwise
	 */
	public function isFilterable(): bool;

	/**
	 * Creates the view for the column
	 *
	 * @param ColumnView|null $parent the parent view (if any)
	 * @return ColumnView
	 * @throws InvalidArgumentTypeException
	 * @throws ReflectionException
	 */
	public function createView(ColumnView $parent = null): ColumnView;

	/**
	 * Creates the json configuration in the given view
	 * @param ColumnView $view
	 */
	public function createJsonConfiguration(ColumnView $view, ColumnView $parent = null): void;
	
}