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

namespace StingerSoft\AggridBundle\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use StingerSoft\AggridBundle\View\FilterView;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface FilterTypeInterface {

	/**
	 * @var string String constant for specifying that conditional filtering should be applied using an AND expression
	 */
	public const FILTER_OPERATOR_AND = 'AND';

	/**
	 * @var string String constant for specifying that conditional filtering should be applied using an OR expression
	 */
	public const FILTER_OPERATOR_OR = 'OR';

	/**
	 * @var string String constant for specifying that filtering should be applied using an in expression
	 */
	public const FILTER_MATCH_MODE_SET = 'set';

	/**
	 * @var string String constant for specifying that filtering should be applied using a contains or in-string mechanism
	 */
	public const FILTER_MATCH_MODE_CONTAINS = 'contains';

	/**
	 * @var string String constant for specifying that filtering should be applied using a not contains or in-string mechanism
	 */
	public const FILTER_MATCH_MODE_NOT_CONTAINS = 'notContains';

	/**
	 * @var string String constant for specifying that filtering should be applied using an equals comparison
	 */
	public const FILTER_MATCH_MODE_EQUALS = 'equals';

	/**
	 * @var string String constant for specifying that filtering should be applied using an not equals comparison
	 */
	public const FILTER_MATCH_MODE_NOT_EQUALS = 'notEqual';

	/**
	 * @var string String constant for specifying that filtering should be applied using a starts-with mechanism
	 */
	public const FILTER_MATCH_MODE_STARTS_WITH = 'startsWith';

	/**
	 * @var string String constant for specifying that filtering should be applied using a ends-with mechanism
	 */
	public const FILTER_MATCH_MODE_ENDS_WITH = 'endsWith';

	/**
	 * @var string String constant for specifying that filtering should be applied using a in range mechanism
	 */
	public const FILTER_MATCH_MODE_RANGE = 'inRange';

	/**
	 * @var string String constant for specifying that filtering should be applied using a greater than comparison
	 */
	public const FILTER_MATCH_MODE_GREATER_THAN = 'greaterThan';

	/**
	 * @var string String constant for specifying that filtering should be applied using a greater than or equals comparison
	 */
	public const FILTER_MATCH_MODE_GREATER_THAN_OR_EQUALS = 'greaterThanOrEqual';

	/**
	 * @var string String constant for specifying that filtering should be applied using a lesser than comparison
	 */
	public const FILTER_MATCH_MODE_LESS_THAN = 'lessThan';

	/**
	 * @var string String constant for specifying that filtering should be applied using a lesser than or equals comparison
	 */
	public const FILTER_MATCH_MODE_LESS_THAN_OR_EQUALS = 'lessThanOrEqual';

	/**
	 * Builds the filter options using the given options resolver.
	 *
	 * This method is called for each type in the hierarchy starting from the
	 * top most type. It allows to define additional options for this type of filter.
	 *
	 * @param OptionsResolver $resolver      the options resolver used for checking validity of the filter options,
	 *                                       defining default values etc.
	 * @param array           $columnOptions the configured and resolved options of the column type the filter belongs to.
	 * @param array           $gridOptions   the configured and resolved options of the grid type the filter belongs to.
	 */
	public function configureOptions(OptionsResolver $resolver, array $columnOptions = [], array $gridOptions = []);

	/**
	 * Builds the filter view used for rendering of the filter.
	 *
	 * This method is called for each type in the hierarchy starting from the
	 * top most type. It allows adding more variables to the given view which may be used during rendering of the filter.
	 *
	 * @param FilterView         $view       the filter view to add any additional information to
	 * @param FilterInterface    $filter     the filter instance the view belongs to
	 * @param array              $options    the options of the column, previously configured by the #configureOptions method
	 * @param QueryBuilder|array $dataSource the data source of the underlying grid and column
	 * @param string             $queryPath  the query path under which the column is accessible in the query builder,
	 *                                       allowing for actual filtering by adding comparison expression on the query path
	 * @param string             $rootAlias  the root alias of the type contained in the grid whose column is to be filtered
	 * @return void
	 */
	public function buildView(FilterView $view, FilterInterface $filter, array $options, $dataSource, string $queryPath, string $rootAlias): void;

	/**
	 * Returns the name of the parent type.
	 *
	 * @return string|null The name of the parent type if any, null otherwise
	 */
	public function getParent(): ?string;

	/**
	 * Handles any incoming filter request and takes care of conditional filtering as well.
	 *
	 * Any filterable value is passed to the #handleFilterRequest() method individually.
	 *
	 * @param QueryBuilder $queryBuilder            the query builder to create filter expressions for.
	 * @param string[]     $filterRequest
	 *                                              an array containing the requested filter settings, may including the following keys:
	 *                                              <code>type</code>: The matching type (see FilterTypeInterface::FILTER_MATCH_MODE_*)
	 *                                              <code>filter</code>: The value to filter for
	 *                                              <code>filterTo</code>: Second value to filter for (e.g. for range filters)
	 *                                              <code>filterType</code>: The value type of the filter
	 *                                              or
	 *                                              <code>condition1</code>: The condition1 of the filter
	 *                                              <code>condition2</code>: The condition2 of the filter
	 *                                              <code>operator</code>: The operator of the conditions
	 * @param string       $parameterBindingName    the initial name of the parameter to be used for binding the filter
	 *                                              value to any query builder expression, the binding name is suffixed
	 *                                              with a counter value. The value can and should be used to bind
	 *                                              parameters on the query builder, like this:
	 *                                              <code>$queryBuilder->setParameter($parameterBindingName, $filterValue)</code>
	 * @param string       $queryPath               the path determining the field to filter on. If you for instance performed
	 *                                              a <code>$queryBuilder->leftJoin('user.address', 'address')</code> and
	 *                                              the column to be filtered shall display the addresses city, the query path
	 *                                              would be something like <code>address.city</code>. Use <code>$rootAlias</code>
	 *                                              in order to be able to query on <code>user.address.city</code> (if required).
	 * @param array        $filterTypeOptions       an array containing all resolved and configured options of the filter type.
	 *                                              These options may contain additional information useful for filtering, such as
	 *                                              performing case insensitive filtering, matching information (exact matches only,
	 *                                              substring matches, etc.)
	 * @param string       $rootAlias               the root alias under which the underlying objects are queried
	 * @return Expr|Expr\Comparison|null an expression (and set parameters!) to be added to the filter query or <code>null</code> in
	 *                                              case no filtering will be applied for the given values. If this method
	 *                                              returns any expression, its parameters MUST be bound in here!.
	 *                                              Any expression returned will be added to an <code>andWhere</code> clause
	 *                                              to the underlying query builder.
	 */
	public function applyFilter(QueryBuilder $queryBuilder, array $filterRequest, string $parameterBindingName, string $queryPath, array $filterTypeOptions, string $rootAlias);

	/**
	 * Apply any filtering on the given QueryBuilder using the given value to filter by.
	 *
	 * @param QueryBuilder $queryBuilder            the query builder to create filter expressions for.
	 * @param string[]     $filterRequest
	 *                                              an array containing the requested filter settings, may including the following keys:
	 *                                              <code>type</code>: The matching type (see FilterTypeInterface::FILTER_MATCH_MODE_*)
	 *                                              <code>filter</code>: The value to filter for
	 *                                              <code>filterTo</code>: Second value to filter for (e.g. for range filters)
	 *                                              <code>filterType</code>: The value type of the filter
	 * @param string       $parameterBindingName    the initial name of the parameter to be used for binding the filter
	 *                                              value to any query builder expression, the binding name is suffixed
	 *                                              with a counter value. The value can and should be used to bind
	 *                                              parameters on the query builder, like this:
	 *                                              <code>$queryBuilder->setParameter($parameterBindingName, $filterValue)</code>
	 * @param string       $queryPath               the path determining the field to filter on. If you for instance performed
	 *                                              a <code>$queryBuilder->leftJoin('user.address', 'address')</code> and
	 *                                              the column to be filtered shall display the addresses city, the query path
	 *                                              would be something like <code>address.city</code>. Use <code>$rootAlias</code>
	 *                                              in order to be able to query on <code>user.address.city</code> (if required).
	 * @param array        $filterTypeOptions       an array containing all resolved and configured options of the filter type.
	 *                                              These options may contain additional information useful for filtering, such as
	 *                                              performing case insensitive filtering, matching information (exact matches only,
	 *                                              substring matches, etc.)
	 * @param string       $rootAlias               the root alias under which the underlying objects are queried
	 * @return Expr|Expr\Comparison|null an expression (and set parameters!) to be added to the filter query or <code>null</code> in
	 *                                              case no filtering will be applied for the given values. If this method
	 *                                              returns any expression, its parameters MUST be bound in here!.
	 *                                              Any expression returned will be added to an <code>andWhere</code> clause
	 *                                              to the underlying query builder.
	 */
	public function handleFilterRequest(QueryBuilder $queryBuilder, array $filterRequest, string $parameterBindingName, string $queryPath, array $filterTypeOptions, string $rootAlias);
}