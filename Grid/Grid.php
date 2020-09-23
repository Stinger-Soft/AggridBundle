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

namespace StingerSoft\AggridBundle\Grid;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Knp\Component\Pager\Pagination\AbstractPagination;
use Knp\Component\Pager\PaginatorInterface;
use ReflectionException;
use StingerSoft\AggridBundle\Column\Column;
use StingerSoft\AggridBundle\Column\ColumnInterface;
use StingerSoft\AggridBundle\Column\ColumnTypeInterface;
use StingerSoft\AggridBundle\Components\ComponentInterface;
use StingerSoft\AggridBundle\Exception\InvalidArgumentTypeException;
use StingerSoft\AggridBundle\Filter\FilterTypeInterface;
use StingerSoft\AggridBundle\Helper\GridBuilder;
use StingerSoft\AggridBundle\Helper\GridBuilderInterface;
use StingerSoft\AggridBundle\Service\DependencyInjectionExtensionInterface;
use StingerSoft\AggridBundle\Service\GridOrderer;
use StingerSoft\AggridBundle\View\GridView;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;
use function in_array;

class Grid implements GridInterface {

	/**  @var Environment|null */
	protected $twig;

	/**  @var PaginatorInterface */
	protected $paginator;

	/** @var DependencyInjectionExtensionInterface */
	protected $dependencyInjectionExtension;

	/** @var array|QueryBuilder|null */
	protected $dataSource;

	/**  @var QueryBuilder|null */
	protected $queryBuilder;

	/** @var QueryBuilder|null */
	protected $originalQueryBuilder;

	/**  @var array */
	protected $options;

	/** @var GridTypeInterface */
	protected $gridType;

	/**  @var string */
	protected $rootAlias;

	/**  @var ColumnInterface[] */
	protected $columns;

	/** @var ComponentInterface[] */
	protected $components;

	/** @var GridTypeExtensionInterface[] */
	protected $typeExtensions = [];

	/** @var GridBuilderInterface */
	protected $builder;

	/** @var GridOrderer */
	protected $orderer;

	/** @var string[] */
	protected $orderExpressions = [];

	/** @var Query\Expr[] */
	protected $searchExpressions = [];

	/** @var Query\Expr[] */
	protected $filterExpressions = [];

	/** @var Query\Expr[] */
	protected $idExpressions = [];

	/** @var bool */
	protected $isSubmitted = false;

	/**
	 * @var int|null
	 * Paging first record indicator. This is the start point in the current data set (0 index based - i.e. 0 is the first record).
	 */
	protected $requestOffset;

	/**
	 * @var int|null Number of records that the table can display in the current draw.
	 */
	protected $requestCount;

	/**  @var int|null */
	protected $totalResults;

	/** @var array|null */
	protected $requestOrder;

	/** @var string|null */
	protected $requestSearch;

	/** @var array|null */
	protected $requestFilter;

	/** @var array|null */
	protected $requestGroupCols;

	/** @var array|null */
	protected $requestGroupColsKey;

	/** @var array|null */
	protected $requestIds;

	/**
	 * Constructs a new grid.
	 *
	 * @param string $gridTypeClass FQCN of the grid type to be used
	 * @param QueryBuilder|array $dataSource
	 *                                                             data source the grid will use for retrieving entries,
	 *                                                             applying filters, searches and ordering (if a query builder is given)
	 * @param DependencyInjectionExtensionInterface $dependencyInjectionExtension
	 * @param PaginatorInterface $paginator
	 * @param Environment|null $twig
	 * @param array $options
	 *                                                             an array of options to be passed to the grid type
	 * @throws ReflectionException
	 * @throws InvalidArgumentTypeException
	 */
	public function __construct(
		string $gridTypeClass,
		$dataSource,
		DependencyInjectionExtensionInterface $dependencyInjectionExtension,
		PaginatorInterface $paginator,
		?Environment $twig,
		array $options = []
	) {
		$this->twig = $twig;
		$this->paginator = $paginator;
		$this->dependencyInjectionExtension = $dependencyInjectionExtension;
		$this->dataSource = $dataSource;
		if($dataSource instanceof QueryBuilder) {
			$this->queryBuilder = clone $dataSource;
			$this->originalQueryBuilder = clone $dataSource;
		}
		$this->options = $options;

		$gridType = $this->dependencyInjectionExtension->resolveGridType($gridTypeClass);
		$this->typeExtensions = $this->dependencyInjectionExtension->resolveGridTypeExtensions($gridTypeClass);
		foreach($this->typeExtensions as $extension) {
			if(!$extension instanceof GridTypeExtensionInterface) {
				throw new UnexpectedTypeException($extension, GridTypeExtensionInterface::class);
			}
		}
		$this->options = $this->setupOptionsResolver($gridType, $options);
		$this->gridType = $gridType;
		$this->orderer = new GridOrderer();
		$this->rootAlias = '';
		if($this->queryBuilder) {
			$rootAliases = $this->queryBuilder instanceof QueryBuilder ? $this->queryBuilder->getRootAliases() : [];
			$this->rootAlias = current($rootAliases);
		}
		$this->builder = new GridBuilder($this, $dependencyInjectionExtension, $this->options);
		$this->buildGrid($gridType, $this->builder);
		$this->columns = $this->builder->all();
		$this->components = $this->builder->components();
	}

	/**
	 * @return GridTypeExtensionInterface[]
	 */
	public function getTypeExtensions(): array {
		return $this->typeExtensions;
	}

	/**
	 * Get the columns of the table.
	 *
	 * @return Column[] the columns of the table.
	 */
	public function getColumns(): array {
		return $this->columns;
	}

	/**
	 * @inheritDoc
	 */
	public function getComponents(): array {
		return $this->components;
	}

	/**
	 * Get the columns of the table.
	 *
	 * @return Column[] the columns of the table.
	 */
	public function getDataColumns(): array {
		$groupSize = $this->requestGroupCols !== null ? count($this->requestGroupCols) : 0;
		$pathSize = $this->requestGroupColsKey !== null ? count($this->requestGroupColsKey) : 0;

		//Group request. So we just return the last requested column
		if($groupSize > 0 && $groupSize > $pathSize) {
			$groupKeys = array_map(static function($item) {
				return $item['id'];
			}, $this->requestGroupCols);
			$dataColumns = [];
			foreach($this->columns as $column) {
				if(in_array($column->getPath(), $groupKeys, true)) {
					$dataColumns[] = $column;
				}
			}
			return array_slice($dataColumns, count($this->requestGroupColsKey), 1);
		}
		return $this->columns;
	}

	/**
	 * @return array|QueryBuilder
	 */
	public function getDataSource() {
		return $this->dataSource ?: $this->queryBuilder;
	}

	/**
	 * Get the query builder this table operates on.
	 *
	 * @return QueryBuilder the query builder this table operates on.
	 */
	public function getQueryBuilder(): ?QueryBuilder {
		return $this->queryBuilder;
	}

	/**
	 * Creates a table view object for the table type and its options.
	 *
	 * @return GridView
	 * @throws InvalidArgumentTypeException
	 * @throws ReflectionException
	 */
	public function createView(): GridView {
		$this->orderColumns();
		$gridView = new GridView($this, $this->gridType, $this->options, $this->columns, $this->components);
		$this->buildView($gridView, $this->gridType, $this->typeExtensions);
		return $gridView;
	}

	/**
	 * Returns whether the grid is submitted.
	 *
	 * @return bool if the grid is submitted, false otherwise
	 */
	public function isSubmitted(): bool {
		return $this->isSubmitted;
	}

	/**
	 * Return an array with key value entries used for ordering the data in the grid.
	 *
	 * The keys map to columns or query paths whereas the values determine the order direction (either 'asc' or 'desc')
	 *
	 * @return string[]
	 */
	public function getOrderExpressions(): array {
		return $this->orderExpressions;
	}

	/**
	 * Return an array of expressions used for searching in the whole grid.
	 *
	 * @return Query\Expr[]
	 */
	public function getSearchExpressions(): array {
		return $this->searchExpressions;
	}

	/**
	 * Return an array of expressions used for filtering specific columns of the grid.
	 *
	 * @return Query\Expr[]
	 */
	public function getFilterExpressions(): array {
		return $this->filterExpressions;
	}

	protected function doCheckIsSubmitted(Request $request): void {
		if($this->options['dataMode'] === GridType::DATA_MODE_INLINE) {
			$this->isSubmitted = false;
			return;
		}
		$paramBag = $this->options['dataMode'] === GridType::DATA_MODE_ENTERPRISE ? $request->request : $request->query;

		$requestString = $paramBag->get('agGrid', null);
		if($requestString === null) {
			$this->isSubmitted = false;
			return;
		}
		$requestData = is_array($requestString) ? $requestString : json_decode($requestString, true);
		if(!isset($requestData['gridId'])) {
			$this->isSubmitted = false;
			return;
		}
		$gridId = $this->gridType->getId($this->options);
		if(mb_stripos($gridId, '#') !== 0) {
			$gridId = '#' . $gridId;
		}
		$this->isSubmitted = $requestData['gridId'] === $gridId;
	}

	public function handleRequest(Request $request): void {
		$this->doCheckIsSubmitted($request);
		//todo do nothing if grid was not submitted. BC break?
		$requestString = $request->request->get('agGrid', null);
		if($requestString === null) {
			return;
		}
		[$offset, $count, $order, $search, $filter, $groupCols, $groupColsKey, $ids] = $this->parseRequest($request);
		$this->requestOffset = $offset;
		$this->requestCount = $count;
		$this->requestOrder = $order;
		$this->requestSearch = $search;
		$this->requestFilter = $filter;
		$this->requestGroupCols = $groupCols;
		$this->requestGroupColsKey = $groupColsKey;
		$this->requestIds = $ids;
	}

	protected function parseRequest(?Request $request): array {
		$requestString = $request !== null ? $request->request->get('agGrid', null) : null;
		if($requestString !== null) {
			$requestData = is_string($requestString) ? json_decode($requestString, true) : $requestString;
		} else {
			$requestData = [];
		}
		$offset = $requestData['startRow'] ?? 0;
		$endRow = $requestData['endRow'] ?? 0;
		$count = $endRow - $offset;
		$order = $requestData['sortModel'] ?? [];
		$search = $requestData['search'] ?? '';
		$filter = $requestData['filterModel'] ?? [];
		$groupCols = $requestData['rowGroupCols'] ?? [];
		$groupColsKey = $requestData['groupKeys'] ?? [];
		$ids = $requestData['__ids'] ?? [];

		$groupPathSize = count($groupColsKey);
		if($groupPathSize > 0) {
			if(count($groupCols) > $groupPathSize) {
				//Reset filter for tree queries
				$filter = [];
			}
			foreach($groupCols as $index => $filterId) {
				$filter[$this->requestGroupCols[$index]['id']] = [
					'filter' => $filterId,
					'type'   => FilterTypeInterface::FILTER_MATCH_MODE_EQUALS,
				];
			}
		}
		return [
			$offset,
			$count,
			$order,
			$search,
			$filter,
			$groupCols,
			$groupColsKey,
			$ids,
		];
	}

	/**
	 * @return Response
	 * @throws InvalidArgumentTypeException
	 * @throws NoResultException
	 * @throws NonUniqueResultException
	 * @throws ReflectionException
	 */
	public function createJsonDataResponse(): Response {
		return new JsonResponse($this->getData());
	}

	/**
	 * @return string
	 * @throws InvalidArgumentTypeException
	 * @throws NoResultException
	 * @throws NonUniqueResultException
	 * @throws ReflectionException
	 */
	public function createJsonData(): string {
		$json = json_encode($this->getData());
		if($json) {
			return $json;
		}
		$json = json_encode($this->utf8ize($this->getData()));
		if($json) {
			return $json;
		}
		return json_last_error_msg();
	}

	/**s
	 * Returns the amount of total results of the query before applying any filter by performing a count query using
	 * the root alias of the underlying query builder.
	 *
	 * @return integer the amount of total results of the query before applying any filter.
	 * @throws NonUniqueResultException
	 * @throws NoResultException
	 */
	public function getTotalResults(): int {
		if($this->totalResults === null) {
			if($this->options['total_results_query_builder'] instanceof QueryBuilder) {
				$countQb = $this->options['total_results_query_builder'];
				$this->totalResults = (int)$countQb->getQuery()->getSingleScalarResult();
			} else {
				if($this->queryBuilder) {
					$countQb = clone $this->queryBuilder;
					$countQb->resetDQLPart('orderBy');
					if(!empty($countQb->getDQLPart('groupBy'))) {
						$paginator = new Paginator($countQb);
						$this->totalResults = $paginator->count();
					} else {
						$this->totalResults = (int)$countQb->select('COUNT(' . $this->rootAlias . ')')->getQuery()->getSingleScalarResult();
					}
				}
				if(is_array($this->dataSource)) {
					$this->totalResults = count($this->dataSource);
				}
			}
		}
		return $this->totalResults;
	}

	/**
	 * Add filter to columns based on path and array values, set as pre_filtered_values
	 * per column.
	 *
	 * @param array $filter
	 *            Expects an array of column.path => values[]
	 * @return Grid This grid
	 */
	public function addFilter(array $filter): Grid {
		foreach($this->columns as $column) {
			if($column->isFilterable() && $column->getFilter() && array_key_exists($column->getPath(), $filter)) {
				$filterOptions = $column->getFilter()->getFilterOptions();
				$filterOptions['pre_filtered_value'] = $filter[$column->getPath()];
				$column->getFilter()->setFilterOptions($filterOptions);
			}
		}
		return $this;
	}

	protected function utf8ize($mixed) {
		if(is_array($mixed)) {
			foreach($mixed as $key => $value) {
				$mixed[$key] = $this->utf8ize($value);
			}
		} elseif(is_string($mixed)) {
			return mb_convert_encoding($mixed, 'UTF-8', 'UTF-8');
		}
		return $mixed;
	}

	/**
	 * @return array
	 * @throws InvalidArgumentTypeException
	 * @throws NoResultException
	 * @throws NonUniqueResultException
	 * @throws ReflectionException
	 */
	protected function getData(): array {
		$result = [];
		$items = $this->getItems();
		foreach($items as $item) {
			$result[] = $this->generateItemData($item, $this->getDataColumns());
		}
		if($this->options['dataMode'] !== GridType::DATA_MODE_INLINE) {
			$result = [
				'items' => $result,
				'total' => $this->getTotalResults(),
			];
		}
		if($this->options['dataMode'] === GridType::DATA_MODE_ENTERPRISE && $items instanceof AbstractPagination) {
			$result['total'] = $items->getTotalItemCount();
		}
		return $result;
	}

	protected function getItems() {
		if($this->queryBuilder === null) {
			return $this->dataSource;
		}
		$this->queryBuilder = clone $this->originalQueryBuilder;
		if($this->options['dataMode'] === GridType::DATA_MODE_ENTERPRISE) {
			$paginationOptions = $this->getPaginationOptions();
			$this->applyQueryBuilderExpressions($this->requestOrder, $this->requestSearch, $this->requestFilter, $this->requestGroupCols, $this->requestGroupColsKey, $this->requestIds);
			$query = $this->queryBuilder->getQuery();
			if(!$this->options['hydrateAsObject']) {
				$query->setHydrationMode(Query::HYDRATE_ARRAY);
			}
			$this->applyQueryHints($query);

			return $this->paginator->paginate($query, $this->requestOffset / $this->requestCount + 1, $this->requestCount, $paginationOptions);
		}
		if($this->options['hydrateAsObject']) {
			return $this->applyQueryHints($this->queryBuilder->getQuery())->getResult();
		}
		return $this->applyQueryHints($this->queryBuilder->getQuery())->getArrayResult();
	}

	/** @noinspection PhpUnusedLocalVariableInspection */
	public function getQueryBuilderMatchingRequest(Request $request): QueryBuilder {
		$this->queryBuilder = clone $this->originalQueryBuilder;
		[
			$offset,
			$count,
			$order,
			$search,
			$filter,
			$groupColumns,
			$groupColumnKeys,
			$ids,
		] = $this->parseRequest($request);
		return $this->applyQueryBuilderExpressions($order, $search, $filter, $groupColumns, $groupColumnKeys, $ids);
	}

	/** @noinspection PhpUnusedLocalVariableInspection */
	public function getQueryBuilderMatchingIds(array $ids): QueryBuilder {
		$this->queryBuilder = clone $this->originalQueryBuilder;
		[
			$offset,
			$count,
			$order,
			$search,
			$filter,
			$groupColumns,
			$groupColumnKeys,
			$ignorableIds,
		] = $this->parseRequest(null);
		return $this->applyQueryBuilderExpressions($order, $search, $filter, $groupColumns, $groupColumnKeys, $ids);
	}

	protected function applyQueryBuilderExpressions(array $orderBy, ?string $search, array $filter, array $groupColumns, array $groupColumnKeys, array $ids): QueryBuilder {
		if(count($ids)) {
			$this->applyIds($ids);
		} else {
			$this->applySearch($search);
			$this->applyFilter($filter);
			$this->applyGrouping($groupColumns, $groupColumnKeys);
		}
		$this->applyOrderBy($orderBy);
		return $this->queryBuilder;
	}

	protected function applyQueryHints(Query $query): Query {
		if(!isset($this->options['queryHints'])) {
			return $query;
		}
		foreach($this->options['queryHints'] as $hintKey => $hintValue) {
			$query->setHint($hintKey, $hintValue);
		}
		return $query;
	}

	/**
	 * @param mixed $item
	 * @param Column[] $columns
	 * @return array
	 * @throws InvalidArgumentTypeException
	 * @throws ReflectionException
	 */
	protected function generateItemData($item, array $columns): array {
		$itemArray = [];
		foreach($columns as $column) {
			$this->setNestedArrayValue($itemArray, $column->getPath(), $column->createData($item, $this->rootAlias));
		}
		return $itemArray;
	}

	/**
	 * Applies a column specific search on the tables query builder, filtering out all non-matching elements.
	 *
	 * <p>The columns to be taken into account are only those, who are marked as being filterable and those who actually
	 * provide a filter type and filter options as well as filter instance.
	 * For every filterable column, it is checked if it provides a delegate or callback to be used when filtering.
	 *
	 * <p>If so, the delegate will be invoked and the parameters required will be passed in order to modify the query
	 * builder of the table accordingly. As such, the delegate should create any additionally required comparison
	 * expressions for the column to be filtered and return those expression in order for them to be added to the final
	 * query.
	 *
	 * <p>In case no specific filter delegate is defined for a filterable column, the filter type defined for the column
	 * is used for applying the filter. The actual implementation of the filtering is then specific to the filter type
	 * and may result in addition of LIKE or equality expressions.
	 *
	 * <p>Finally, all comparison expressions, regardless of their type (like, eq or whatever) are added to the WHERE
	 * part of the underlying query builder by combining them with the AND disjunction.
	 *
	 * @param array $columns
	 *            an array containing the columns that are to be filtered, where every entry in the array is
	 *            identified by the columns name / id (array key) and the value is supposed to be another array
	 *            with two keys: 'value' containing the value to be searched for and 'regex' containing a
	 *            boolean value, indicating whether the filter is a regular expression (in case of true value)
	 *            or not (in case of false value).
	 *
	 * @see Table::getColumnsWithFilterValueOnly() to get any filterable columns in the correct structure to be passed as
	 *      the first argument
	 * @see Column::isFilterable() for determining whether a column is filterable
	 * @see Column::getFilter() for determining whether a column has a filter defined
	 * @see Column::getServerSideFilterDelegate() for determining whether a column has defined a specialised function to be used
	 *      for filtering
	 * @see FilterTypeInterface::applyFilter() and the overwritten implemenations, specific to the filter type are used
	 *      for applying a filter in case no filter delegate is defined for the column to be filtered.
	 */
	protected function applyFilter(array $columns): void {
		$this->filterExpressions = [];
		if(is_array($columns) && count($columns) > 0) {
			$filterQuery = [];
			$filterableColumnIds = $this->getFilterableColumnIds();
			$bindingCounter = 0;
			foreach($columns as $columnId => $filterSettings) {
				if(in_array($columnId, $filterableColumnIds, true)) {

					$column = $this->getColumn($columnId);
					if(!$column) {
						continue;
					}
					$filterParameterBinding = ':filter_' . $bindingCounter;
					$queryPath = $column->getFilterQueryPath();
					if(false === strpos($queryPath, '.')) {
						$queryPath = $this->rootAlias . '.' . $queryPath;
					}
					$returnValue = null;
					$filterObject = $column->getFilter();
					if($filterObject !== null) {
						$returnValue = $filterObject->applyFilter($this->queryBuilder, $filterSettings, $filterParameterBinding, $queryPath, $filterObject->getFilterOptions(), $this->rootAlias);
					}
					if($returnValue !== null) {
						$filterQuery[] = $returnValue;
						$this->filterExpressions[] = $returnValue;
						$bindingCounter++;
					}
				}
			}
			if(count($filterQuery) > 0) {
				$this->queryBuilder->andWhere($this->queryBuilder->expr()->andX()->addMultiple($filterQuery));
			}
		}
	}

	protected function applyGrouping(array $grouping, array $groupPath): void {
		$pathSize = count($groupPath);
		$groupSize = count($grouping);

		if(($groupSize > 0) && $groupSize > $pathSize) {
			foreach($grouping as $index => $group) {
				if($index === $pathSize) {
					$column = $this->getColumn($group['id']);
					if($column !== null) {
						$filterPath = $column->getFilterQueryPath();
						$this->queryBuilder->select($filterPath);
						$this->queryBuilder->groupBy($filterPath);
					}
				}
			}
		}
	}

	/**
	 * Gets the column instance for the given columns path
	 *
	 * @param string $columnId
	 *            The path of the column.
	 * @return Column the column instance for the given columns path
	 */
	protected function getColumn(string $columnId): ?Column {
		foreach($this->columns as $column) {
			if($column->getPath() === $columnId) {
				return $column;
			}
		}
		return null;
	}

	/**
	 * Get the names / ids of columns which are filterable.
	 *
	 * A column is filterable, if column->isFilterable() returns true and column->getFilter() is not null.
	 *
	 * @return string[] the names / ids of columns which are filterable.
	 */
	protected function getFilterableColumnIds(): array {
		$result = [];
		foreach($this->getColumns() as $column) {
			if($column->isFilterable() && $column->getFilter() !== null) {
				$result[] = $column->getPath();
			}
		}
		return $result;
	}

	/**
	 * Sets a value in a nested array based on path
	 * See http://stackoverflow.com/a/9628276/419887
	 *
	 * @param array $array
	 *            The array to modify
	 * @param string $path
	 *            The path in the array
	 * @param mixed $value
	 *            The value to set
	 * @param string $delimiter
	 *            The separator for the path
	 * @return mixed The previous value
	 */
	protected function setNestedArrayValue(array &$array, string $path, $value, $delimiter = '.') {
		$pathParts = explode($delimiter, $path);

		$current = &$array;
		foreach($pathParts as $key) {
			$current = &$current[$key];
		}

		$backup = $current;
		$current = $value;

		return $backup;
	}

	/**
	 * Merges the grid columns of each type in the hierarchy starting from the top most type.
	 *
	 * @param GridTypeInterface $gridType
	 *            the grid type to build the columns from
	 * @param GridBuilderInterface $builder
	 *            the grid builder
	 * @throws InvalidArgumentTypeException
	 * @throws ReflectionException
	 */
	protected function buildGrid(GridTypeInterface $gridType, GridBuilderInterface $builder): void {
		if($gridType->getParent()) {
			$parentType = $this->dependencyInjectionExtension->resolveGridType($gridType->getParent());
			$this->buildGrid($parentType, $builder);
		}
		$gridType->buildGrid($this->builder, $this->options);

		foreach($this->typeExtensions as $extension) {
			$extension->buildGrid($this->builder, $this->options);
		}
	}

	/**
	 * Sets up the options resolver for the given grid type and the initial options.
	 * Setting up means that the column type options and all applicable parent options will be validated and
	 * resolved according to the hierarchy grandparent, then parent, then instance etc.
	 *
	 * @param GridTypeInterface $gridType
	 *            the type to resolve the options for, also used for determining any parents
	 *            whose options are to be resolved as well
	 * @param array $options
	 *            the initial options to also be resolved (if any).
	 * @return array the resolved options for the given grid type.
	 * @throws InvalidArgumentTypeException
	 * @throws ReflectionException
	 */
	protected function setupOptionsResolver(GridTypeInterface $gridType, array $options): array {
		$resolver = new OptionsResolver();
		$this->resolveOptions($gridType, $resolver);
		foreach($this->typeExtensions as $extension) {
			$extension->configureOptions($resolver);
		}
		$options = $resolver->resolve($options);
		return $options;
	}

	/**
	 * @param GridView $view
	 * @param GridTypeInterface $gridType
	 * @param GridTypeExtensionInterface[] $extensions the extensions to be applied
	 * @throws InvalidArgumentTypeException
	 * @throws ReflectionException
	 */
	protected function buildView(GridView $view, GridTypeInterface $gridType, array $extensions = []): void {
		if($gridType->getParent()) {
			$parentType = $this->dependencyInjectionExtension->resolveGridType($gridType->getParent());
			$this->buildView($view, $parentType);
		}
		$gridType->buildView($view, $this, $this->options, $this->columns);

		foreach($extensions as $extension) {
			$extension->buildView($view, $this, $this->options, $this->columns);
		}
	}

	protected function orderColumns(): void {
		// order columns according to position!
		$tmpColumns = $this->columns;
		$newColumnKeys = $this->orderer->order($this);
		$this->columns = [];

		foreach($newColumnKeys as $name) {
			if(!isset($tmpColumns[$name])) {
				continue;
			}

			$this->columns[$name] = $tmpColumns[$name];
			unset($tmpColumns[$name]);
		}

		foreach($tmpColumns as $name => $child) {
			$this->columns[$name] = $child;
		}
	}

	protected function getPaginationOptions(): array {
		return $this->options['paginationOptions'] ?? [];
	}

	/**
	 * Adds orderBy statements to the query builder according to the given columns to be ordered.
	 *
	 * <p>The columns to be taken into account are only those, who are marked as being orderable.
	 * For every orderable column, it is checked if it provides a delegate or callback to be used when ordering.
	 *
	 * <p>If so, the delegate will be invoked and the parameters required will be passed in order to modify the query
	 * builder of the table accordingly. As such, the delegate should return an array containing key => value pairs for
	 * every orderBy statement to be added to the query builder, where each key is used as the selector or query path
	 * for the query builder and the value must be the order direction ('asc' or 'desc').
	 *
	 * <p>In case no specific order delegate is defined for an orderable column, the columns query path and the order
	 * direction given in the first parameter will be used.
	 *
	 * <p>Finally, all paths and directions are added to the orderBy part of the underlying query builder consecutively.
	 *
	 * <p>The given parameter for the columns to be ordered must follow a well-defined structure. Every entry in the
	 * given array must have two keys and corresponding values:
	 * 1. key 'column' with an array as its value containing a 'name' key whose value refers to the name or id of the
	 * column to be sorted.
	 * 2. key 'dir' whose value indicates the direction of ordering, 'asc' for ascending, 'desc' for descending ordering
	 *
	 * <p>Example:
	 * <code>
	 * array(
	 * 0 => array(
	 * 'column' => array(
	 * 'name' => 'column1'
	 * ),
	 * 'dir' => 'asc'
	 * ),
	 * 1 => array(
	 * 'column' => array(
	 * 'name' => 'column2'
	 * ),
	 * 'dir' => 'desc'
	 * ),
	 * ...
	 * )
	 * </code>
	 *
	 * @param array $orderByEntries
	 *            an array containing the columns to be ordered, with details on how the ordering shall
	 *            be applied.
	 *
	 * @see Table::getOrderableColumnIds() for retrieval of all columns which are actually orderable
	 * @see Column::isOrderable() for determining whether a column is orderable
	 * @see Column::getServerSideOrderDelegate() for determining whether a column has defined a specialised function to be used
	 *      for ordering
	 */
	protected function applyOrderBy(array $orderByEntries): void {
		$orderByEntries = array_filter($orderByEntries, static function($entry) {
			// we only want to have entry containing a column AND a direction
			return isset($entry['colId'], $entry['sort']) && $entry['sort'] !== '';
		});
		$this->orderExpressions = [];
		if(count($orderByEntries) > 0) {
			$orderQuery = [];
			$orderableColumnIds = $this->getOrderableColumnPaths();
			foreach($orderByEntries as $orderBy) {
				$columnName = $orderBy['colId'];
				$direction = $orderBy['sort'];
				$column = $this->columns[$columnName];
				$queryPath = $column->getQueryPath();
				if(false === strpos($queryPath, '.')) {
					$queryPath = $this->rootAlias . '.' . $queryPath;
				}
				if(in_array($columnName, $orderableColumnIds, false)) {
					$delegate = $column->getServerSideOrderDelegate();
					if($delegate && is_callable($delegate)) {
						/** @var array $delegatedOrderByEntries */
						$delegatedOrderByEntries = $delegate($direction, $this->queryBuilder, $column, $queryPath, $this->rootAlias);
						if($delegatedOrderByEntries && count($delegatedOrderByEntries) > 0) {
							foreach($delegatedOrderByEntries as $path => $direction) {
								$orderQuery[$path] = $direction;
							}
						}
					} else {
						$orderQuery[$queryPath] = $direction;
					}
				}
			}
			if(count($orderQuery) > 0) {
				foreach($orderQuery as $path => $direction) {
					$this->orderExpressions[$path] = $direction;
					$this->queryBuilder->addOrderBy($path, $direction);
				}
			}
		} else {
			$orders = [];
			// default order(s) !!
			$defaultOrders = $this->options['default_orders'];
			if(is_array($defaultOrders) && count($defaultOrders) > 0) {
				foreach($defaultOrders as $path => $direction) {
					if(false === strpos($path, '.')) {
						$path = $this->rootAlias . '.' . $path;
					}
					$orders[$path] = $direction;
				}
			} else {
				$path = $this->options['default_order_property'];
				if($path !== null) {
					$direction = $this->options['default_order_direction'];
					if(false === strpos($path, '.')) {
						$path = $this->rootAlias . '.' . $path;
					}
					$orders[$path] = $direction;
				}
			}
			foreach($orders as $path => $direction) {
				$this->orderExpressions[$path] = $direction;
				$this->queryBuilder->addOrderBy($path, $direction);
			}
		}
	}

	protected function applyIds(array $ids): void {
		$this->idExpressions = [];
		if(is_array($ids)) {
			$bindingCounter = 0;
			$ors = [];
			foreach($ids as $idEntry) {
				$ands = [];
				foreach($idEntry as $columnId => $value) {
					$column = $this->columns[$columnId];
					$idParameterBinding = ':_id_' . $bindingCounter;
					$queryPath = $column->getQueryPath();
					if(false === strpos($queryPath, '.')) {
						$queryPath = $this->rootAlias . '.' . $queryPath;
					}
					if(is_array($value)) {
						$expression = $this->queryBuilder->expr()->in($queryPath, $idParameterBinding);
					} else {
						$expression = $this->queryBuilder->expr()->eq($queryPath, $idParameterBinding);
					}
					$this->queryBuilder->setParameter($idParameterBinding, $value);
					$bindingCounter++;
					$ands[] = $expression;
					$this->idExpressions[] = $expression;
				}
				$ors[] = $this->queryBuilder->expr()->andX()->addMultiple($ands);
			}
			$this->queryBuilder->andWhere($this->queryBuilder->expr()->orX()->addMultiple($ors));
		}
	}

	/**
	 * Get the names / ids of columns which are providing identity.
	 *
	 * A column is providing identity, if column->isIdentityProvider() returns true
	 *
	 * @return string[] the names / ids of columns which are providing identity.
	 */
	protected function getIdentifyingPaths(): array {
		$result = [];
		foreach($this->columns as $columnId => $column) {
			if($column->isIdentityProvider()) {
				$result[] = $column->getPath();
			}
		}
		return $result;
	}

	/**
	 * Get the names / ids of columns which are orderable.
	 *
	 * A column is orderable, if column->isOrderable() returns true
	 *
	 * @return string[] the names / ids of columns which are orderable.
	 */
	protected function getOrderableColumnPaths(): array {
		$result = [];
		foreach($this->columns as $columnId => $column) {
			if($column->isOrderable()) {
				$result[] = $column->getPath();
			}
		}
		return $result;
	}

	protected function applySearch(?string $searchTerm): void {
		if(empty($searchTerm)) {
			return;
		}
		$this->searchExpressions = [];
		$searchQuery = [];
		$searchableColumns = $this->getSearchableColumnPaths();
		$bindingCounter = 0;
		foreach($searchableColumns as $columnId) {
			$column = $this->columns[$columnId];
			$searchParameterBinding = ':search_' . $bindingCounter;
			$queryPath = $column->getQueryPath();
			if(false === strpos($queryPath, '.')) {
				$queryPath = $this->rootAlias . '.' . $queryPath;
			}
			$options = $column->getColumnOptions();
			$conjunction = $options['tokenize_search_conjunction'] ?? ColumnTypeInterface::SEARCH_OPERATOR_AND;
			$token = null;
			if(isset($options['tokenize_search_term'])) {
				$token = $options['tokenize_search_term'] === true ? ' ' : $options['tokenize_search_term'];
			}
			if($token !== null) {
				$searchTerms = explode($token, $searchTerm);
			} else {
				$searchTerms = [$searchTerm];
			}
			$delegate = $column->getServerSideSearchDelegate();

			if($delegate && is_callable($delegate)) {
				$searchTermAnds = [];
				foreach($searchTerms as $index => $term) {
					$searchExpression = $delegate($this->queryBuilder, $searchParameterBinding . '_' . $index, $term, $column, $queryPath);
					if($searchExpression !== null) {
						if(is_array($searchExpression)) {
							foreach($searchExpression as $expression) {
								$searchQuery[] = $expression;
							}
						} else {
							$searchQuery[] = $searchExpression;
						}
						$bindingCounter++;
						$searchTermAnds[] = $searchExpression;
					}
				}
				if($conjunction === ColumnTypeInterface::SEARCH_OPERATOR_AND) {
					$searchQuery[] = $this->queryBuilder->expr()->andX(...$searchTermAnds);
				} else {
					$searchQuery[] = $this->queryBuilder->expr()->orX(...$searchTermAnds);
				}
			} else {
				$searchTermAnds = [];
				foreach($searchTerms as $index => $term) {
					$searchTermAnds[] = $this->queryBuilder->expr()->like($queryPath, $searchParameterBinding . '_' . $index);
					$this->queryBuilder->setParameter($searchParameterBinding . '_' . $index, '%' . $term . '%');
				}
				if($conjunction === ColumnTypeInterface::SEARCH_OPERATOR_AND) {
					$searchQuery[] = $this->queryBuilder->expr()->andX(...$searchTermAnds);
				} else {
					$searchQuery[] = $this->queryBuilder->expr()->orX(...$searchTermAnds);
				}
				$bindingCounter++;
			}
		}
		$this->searchExpressions = $searchQuery;
		if(count($searchQuery) > 0) {
			$this->queryBuilder->andWhere($this->queryBuilder->expr()->orX()->addMultiple($searchQuery));
		}
	}

	/**
	 * Get the names / ids of columns which are searchable.
	 *
	 * A column is searchable, if column->isSearchable() returns true
	 *
	 * @return string[] the names / ids of columns which are searchable.
	 */
	protected function getSearchableColumnPaths(): array {
		$result = [];
		foreach($this->columns as $columnId => $column) {
			if($column->isSearchable()) {
				$result[] = $column->getPath();
			}
		}
		return $result;
	}

	/**
	 * Merges the configurations of each type in the hierarchy starting from the top most type.
	 *
	 * @param GridTypeInterface $gridType
	 *            the grid type to resolve the options from
	 * @param OptionsResolver $resolver
	 *            the resolver used for checking option values and defaults etc.
	 * @throws InvalidArgumentTypeException
	 * @throws ReflectionException
	 */
	protected function resolveOptions(GridTypeInterface $gridType, OptionsResolver $resolver): void {
		if($gridType->getParent()) {
			$parentType = $this->dependencyInjectionExtension->resolveGridType($gridType->getParent());
			$this->resolveOptions($parentType, $resolver);
		}
		$gridType->configureOptions($resolver);
	}

}