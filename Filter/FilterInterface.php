<?php

namespace StingerSoft\AggridBundle\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use StingerSoft\AggridBundle\View\FilterView;

interface FilterInterface {
	/**
	 * Creates a new view to be used for rendering column filters.
	 *
	 * @param FilterView|null $parent an already pre-populated parent view to extend, if any
	 * @return FilterView the new filter view.
	 */
	public function createView(?FilterView $parent = null): ?FilterView;

	/**
	 * Get the filter type used for this filter instance
	 *
	 * @return FilterTypeInterface the filter type used for this filter instance
	 */
	public function getFilterType(): FilterTypeInterface;

	/**
	 * Get the options defined for the filter type.
	 *
	 * @return array the options defined for the filter type.
	 */
	public function getFilterOptions(): array;

	/**
	 * Set the options defined for the filter type.
	 *
	 * @param array $filterOptions the options defined for the filter type.
	 * @return FilterInterface .
	 */
	public function setFilterOptions(array $filterOptions): self;

	/**
	 * Get the callable to update the query builder of the column the filter is bound to in order to add any required
	 * where clauses explicitly for that column to be used for filtering that column.
	 *
	 * By default the query_path or path property will be used to perform a like query for a column specific filter term.
	 *
	 * @return callable|null the callable to update the query builder of the column the filter is bound to in order to add any required
	 * where clauses explicitly for that column to be used for filtering that column.
	 */
	public function getFilterDelegate(): ?callable;

	/**
	 * Get the options defined for the original column this filter belongs to
	 *
	 * @return array the options defined for the original column this filter belongs to
	 */
	public function getColumnOptions(): array;

	/**
	 * Set the options defined for the original column this filter belongs to
	 *
	 * @param array $columnOptions the options defined for the original column this filter belongs to
	 * @return FilterInterface
	 */
	public function setColumnOptions(array $columnOptions): self;

	/**
	 * Get the option by key defined for the original column this filter belongs to
	 *
	 * @param string $key The array key to fetch
	 * @return array the options defined for the original column this filter belongs to
	 */
	public function getColumnOption($key);

	/**
	 * Set the option by key defined for the original column this filter belongs to
	 *
	 * @param string $key The array key to set
	 * @param mixed $value the options defined for the original column this filter belongs to
	 * @return FilterInterface
	 */
	public function setColumnOption(string $key, $value): self;

	/**
	 * Apply any filtering on the given QueryBuilder using the given value to filter by.
	 *
	 * In case the column the filter belongs to has a filter_server_delegate defined, the delegate is called in order to perform
	 * any filtering. In case no filter_server_delegate is defined for the column the filter belongs to, the applyFilter method
	 * of the underlying filter type is used.
	 *
	 * @param QueryBuilder $queryBuilder
	 *            the query builder to create filter expressions for.
	 * @param string[] $filterRequest
	 *            an array containing the requested filter settings, including the following keys:
	 *            <code>type</code>: The matching type (see FilterTypeInterface::FILTER_MATCH_MODE_*)
	 *            <code>filter</code>: The value to filter for
	 *            <code>filterTo</code>: Second value to filter for (e.g. for range filters)
	 *            <code>filterType</code>: The value type of the filter
	 *
	 * @param string $parameterBindingName
	 *            the initial name of the parameter to be used for binding the filter
	 *            value to any query builder expression, the binding name is suffixed
	 *            with a counter value. The value can and should be used to bind
	 *            parameters on the query builder, like this:
	 *            <code>$queryBuilder->setParameter($parameterBindingName, $filterValue)</code>
	 * @param string $queryPath
	 *            the path determining the field to filter on. If you for instance performed
	 *            a <code>$queryBuilder->leftJoin('user.address', 'address')</code> and
	 *            the column to be filtered shall display the addresses city, the query path
	 *            would be something like <code>address.city</code>. Use <code>$rootAlias</code>
	 *            in order to be able to query on <code>user.address.city</code> (if required).
	 * @param array $filterTypeOptions
	 *            an array containing all resolved and configured options of the filter type.
	 *            These options may contain additional information useful for filtering, such as
	 *            performing case insensitive filtering, matching information (exact matches only,
	 *            substring matches, etc.)
	 * @param string $rootAlias
	 * @return Expr|Expr\Comparison|null an expression (and set parameters!) to be added to the filter query or <code>null</code> in
	 *         case no filtering will be applied for the given values. If this method
	 *         returns any expression, its parameters MUST be bound in here!.
	 *         Any expression returned will be added to an <code>andWhere</code> clause
	 *         to the underlying query builder.
	 *
	 * @see FilterTypeInterface::applyFilter()
	 */
	public function applyFilter(QueryBuilder $queryBuilder, array $filterRequest, string $parameterBindingName, string $queryPath, array $filterTypeOptions, string $rootAlias);
}