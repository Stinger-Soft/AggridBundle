<?php

namespace StingerSoft\AggridBundle\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use StingerSoft\AggridBundle\Exception\InvalidArgumentTypeException;
use StingerSoft\AggridBundle\Service\DependencyInjectionExtensionInterface;
use StingerSoft\AggridBundle\View\ColumnView;
use StingerSoft\AggridBundle\View\FilterView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The Filter class encapsulates all information required for handling filter types, creating views for filters and processing
 * filters items in order to retrieve the matching values to be used for cells bound to the column the filter is bound to.
 */
class Filter implements FilterInterface {

	/**
	 *
	 * @var FilterTypeInterface the filter type used for this filter instance
	 */
	protected $filterType;

	/**
	 *
	 * @var array the options defined for the filter type.
	 */
	protected $filterOptions;

	/**
	 *
	 * @var array the options defined for the original column this filter belongs to
	 */
	protected $columnOptions;

	/**
	 * @var array the view options of the column this filter belongs to
	 */
	protected $columnViewVars;

	/**
	 *
	 * @var array the options defined for the original table this filter belongs to
	 */
	protected $gridOptions;

	/**
	 *
	 * @var FilterInterface the parent filter (if any)
	 */
	protected $parent;

	/**
	 *
	 * @var null|callable the delegate to be used for filtering (if any), allowing to update query builder
	 */
	protected $filterDelegate = null;

	/**
	 *
	 * @var QueryBuilder the query builder of the table the column and this the filter will be attached to
	 */
	protected $queryBuilder;

	/**
	 * @var  QueryBuilder|array
	 */
	protected $dataSource;

	/**
	 * @var DependencyInjectionExtensionInterface
	 */
	protected $dependencyInjectionExtension;

	/**
	 * Filter constructor.
	 *
	 * @param FilterTypeInterface                   $filterType
	 *            the filter type for this filter
	 * @param DependencyInjectionExtensionInterface $dependencyInjectionExtension
	 * @param array                                 $filterTypeOptions
	 *            the options for the filter type
	 * @param array                                 $columnOptions
	 *            the options of the column the filter belongs to
	 * @param array                                 $gridOptions
	 *            the options of the table the filter belongs to
	 * @param QueryBuilder|array                    $dataSource
	 *            the data source of the table the column and this the filter will be attached to.
	 *            In case it is a query builder, it will be cloned in order to allow the filter to
	 *            modify it if necessary.
	 * @param FilterInterface|null                  $parent
	 *            the parent filter (if any) or null.
	 * @throws InvalidArgumentTypeException
	 */
	public function __construct(FilterTypeInterface $filterType, DependencyInjectionExtensionInterface $dependencyInjectionExtension, array $filterTypeOptions = [], array $columnOptions = [], array $gridOptions = [], $dataSource = null, FilterInterface $parent = null) {
		$this->dependencyInjectionExtension = $dependencyInjectionExtension;
		$this->columnOptions = $columnOptions;
		$this->gridOptions = $gridOptions;
		$this->filterType = $filterType;
		$this->filterOptions = $this->setupFilterOptionsResolver($filterType, $filterTypeOptions);
		$this->parent = $parent;
		$this->dataSource = $dataSource;
		if($dataSource instanceof QueryBuilder) {
			$this->queryBuilder = clone $dataSource;
		}

		$this->configureFilter();
	}

	/**
	 * @inheritdoc
	 */
	public function getFilterType(): FilterTypeInterface {
		return $this->filterType;
	}

	/**
	 * @inheritdoc
	 */
	public function getFilterOptions(): array {
		return $this->filterOptions;
	}

	/**
	 * @inheritdoc
	 */
	public function setFilterOptions(array $filterOptions): FilterInterface {
		$this->filterOptions = $filterOptions;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getColumnOptions(): array {
		return $this->columnOptions;
	}

	/**
	 * @inheritdoc
	 */
	public function setColumnOptions(array $columnOptions): FilterInterface {
		$this->columnOptions = $columnOptions;
		return $this;
	}

	/**
	 *
	 * {@inheritdoc}
	 */
	public function getColumnOption($key) {
		return $this->columnOptions[$key];
	}

	/**
	 *
	 * {@inheritdoc}
	 */
	public function setColumnOption($key, $value): FilterInterface {
		$this->columnOptions[$key] = $value;
		return $this;
	}

	/**
	 *
	 * {@inheritdoc}
	 */
	public function getColumnViewVar($key) {
		return $this->columnViewVars[$key];
	}

	/**
	 *
	 * {@inheritdoc}
	 */
	public function setColumnViewVar($key, $value): FilterInterface {
		$this->columnViewVars[$key] = $value;
		return $this;
	}

	/**
	 *
	 * {@inheritdoc}
	 */
	public function getGridOption($key) {
		return $this->gridOptions[$key];
	}

	/**
	 * @inheritdoc
	 */
	public function getFilterDelegate(): ?callable {
		return $this->filterDelegate;
	}

	/**
	 * @inheritdoc
	 */
	public function createView(ColumnView $columnView, FilterView $parent = null): FilterView {
		$this->columnViewVars = $columnView->vars;

		if(null === $parent && $this->parent) {
			$parent = $this->parent->createView($columnView);
		}

		$view = new FilterView($parent);
		$this->buildView($view, $this->filterType, $this->filterOptions);

		if($view->vars['translation_domain'] === null) {
			$view->vars['translation_domain'] = $this->columnOptions['translation_domain'];
		}

		return $view;
	}

	public function applyFilter(QueryBuilder $queryBuilder, $filterRequest, string $parameterBindingName, string $queryPath, array $filterTypeOptions, string $rootAlias) {
		$delegate = $this->filterDelegate;
		if($delegate && is_callable($delegate)) {
			return $delegate($queryBuilder, $filterRequest, $parameterBindingName, $queryPath, $filterTypeOptions, $rootAlias, $this->getFilterType());
		}

		return $this->getFilterType()->applyFilter($queryBuilder, $filterRequest, $parameterBindingName, $queryPath, $filterTypeOptions, $rootAlias);
	}

	/**
	 * Configures any fields of the filter according to the internal filter options, such as filter delegate defined
	 * on the column etc.
	 */
	protected function configureFilter(): void {
		if(isset($this->filterOptions['server_delegate'])) {
			$this->filterDelegate = $this->filterOptions['server_delegate'];
		}
	}

	/**
	 * Updates the given view.
	 *
	 * @param FilterView          $filterView
	 *            the view to be updated
	 * @param FilterTypeInterface $filterType
	 *            the filter type containing the information that may be relevant for the view
	 * @param array               $filterOptions
	 *            the options defined for the filter type, containing information
	 *            such as the filter_server_delegate etc.
	 */
	protected function buildView(FilterView $filterView, FilterTypeInterface $filterType, array $filterOptions = []): void {
		if($filterType->getParent()) {
			$parentType = $this->dependencyInjectionExtension->resolveFilterType($filterType->getParent());
			$this->buildView($filterView, $parentType, $filterOptions);
		}
		$rootAliases = $this->queryBuilder ? $this->queryBuilder->getRootAliases() : [];
		if($this->columnOptions['filter_query_path'] !== null) {
			$path = $this->columnOptions['filter_query_path'];
		} else if($this->columnOptions['query_path'] !== null) {
			$path = $this->columnOptions['query_path'];
		} else {
			$path = $this->columnOptions['path'];
			if($this->queryBuilder && false === strpos($path, '.')) {
				$path = current($rootAliases) . '.' . $path;
			}
		}
		$filterType->buildView($filterView, $this, $filterOptions, $this->queryBuilder ?: $this->dataSource, $path, current($rootAliases));
	}

	/**
	 * Sets up the options resolver for the given filter type and the initial options.
	 * Setting up means that the filter type options and all applicable parent options will be validated and
	 * resolved according to the hierarchy grandparent, then parent, then instance etc.
	 *
	 * @param FilterTypeInterface $filterType
	 *            the type to resolve the options for, also used for determining any parents
	 *            whose options are to be resolved as well
	 * @param array               $options
	 *            the initial options to also be resolved (if any).
	 * @return array the resolved options for the given filter type.
	 * @throws InvalidArgumentTypeException
	 */
	protected function setupFilterOptionsResolver(FilterTypeInterface $filterType, array $options = []): array {
		$resolver = new OptionsResolver();
		$this->resolveOptions($filterType, $resolver);
		return $resolver->resolve($options);
	}

	/**
	 * Merges the configurations of each type in the hierarchy starting from the top most type.
	 *
	 * @param FilterTypeInterface $filterType
	 *            the filter type to resolve the options from
	 * @param OptionsResolver     $resolver
	 *            the resolver used for checking option values and defaults etc.
	 * @throws InvalidArgumentTypeException
	 */
	private function resolveOptions(FilterTypeInterface $filterType, OptionsResolver $resolver) {
		if($filterType->getParent()) {
			$parentType = $this->getFilterTypeInstance($filterType->getParent());
			$this->resolveOptions($parentType, $resolver);
		}
		$filterType->configureOptions($resolver, $this->columnOptions, $this->gridOptions);
	}

	/**
	 * Creates an instance of the given filter type class.
	 *
	 * @param string $class
	 *            Class name of the filter type
	 * @return object|FilterTypeInterface an instance of the given filter type
	 */
	private function getFilterTypeInstance($class): FilterTypeInterface {
		return $this->dependencyInjectionExtension->resolveFilterType($class);
	}
}