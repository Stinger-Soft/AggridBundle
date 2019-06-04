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

use Doctrine\ORM\QueryBuilder;
use StingerSoft\AggridBundle\Filter\Filter;
use StingerSoft\AggridBundle\Service\DependencyInjectionExtensionInterface;
use StingerSoft\AggridBundle\Transformer\DataTransformerInterface;
use StingerSoft\AggridBundle\View\ColumnView;
use StingerSoft\PhpCommons\String\Utils;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Column implements ColumnInterface {

	/**
	 * @var string The path / property / key under which the column is registered. This will be used for accessing an
	 * objects value with a property accessor.
	 */
	protected $path;

	/**
	 * @var string the path to be used by a query builder for sorting and ordering etc. This may differ from the
	 * $this->path especially for joined paths
	 */
	protected $queryPath;

	/**
	 * @var ColumnTypeInterface the column type used for this column instance
	 */
	protected $columnType;

	/**
	 * @var array the options defined for the column type.
	 */
	protected $columnOptions;

	/**
	 * @var \Pec\Bundle\DatatableBundle\Transformer\DataTransformerInterface[]
	 */
	protected $dataTransformers = array();

	/**
	 * @var array the options defined for the original table this column belongs to
	 */
	protected $gridOptions;

	/**
	 * @var callable Callable to return an array of queryPath => direction mappings, allowing to order by joined fields.
	 * By default the query_path or path will be used to perform order by.
	 */
	protected $serverSideOrderDelegate;

	/**
	 * @var string the path to be used for querying the potential filter values for select or autocomplete filters
	 */
	protected $filterQueryPath;

	/**
	 * @var ColumnInterface the parent column (if any)
	 */
	protected $parent;
	/**
	 * @var OptionsResolver
	 */
	protected $resolver;
	/**
	 * @var DependencyInjectionExtensionInterface
	 */
	protected $dependencyInjectionExtension;
	/**
	 * @var array|QueryBuilder|null
	 */
	protected $dataSource;
	/**
	 * @var array|QueryBuilder|null
	 */
	protected $queryBuilder;
	/**
	 * @var boolean whether the column is orderable
	 */
	protected $orderable = false;

	/**
	 * @var boolean whether the column is filterable
	 */
	protected $filterable = false;

	/**
	 * @var Filter the filter object, resolved from filter type option and filter options option.
	 * The object is only created for the view and as such not available before the view was created.
	 */
	protected $filter;

	/**
	 * @var callable Callable to fetch the value of the bound object.
	 *      By default a property accessor will be used to fetch the value based on the configured path
	 */
	protected $valueDelegate;

	/**
	 * @var bool flag indicating whether the buildData method was executed already, as data transformers may not be added
	 * multiple times.
	 */
	protected $dataConfigured = false;

	/**
	 * Column constructor.
	 *
	 * @param string $path The path / property / key under which the column is registered. This will be used for accessing an
	 *                                                   objects value with a property accessor.
	 * @param ColumnTypeInterface $columnType the column type for this column
	 * @param array $columnTypeOptions the options for the column type
	 * @param array $gridOption the options of the table the column belongs to
	 * @param QueryBuilder|array $dataSource the data source of the table the column will be attached to. In case it is a query builder, it will be cloned in order to
	 *                                                   allow Filter instances to modify it if necessary.
	 * @param ColumnInterface|null $parent the parent column (if any) or null.
	 * @throws InvalidOptionsException in case the column type options contain invalid entries
	 */
	public function __construct($path, ColumnTypeInterface $columnType, DependencyInjectionExtensionInterface $dependencyInjectionExtension, array $columnTypeOptions = array(), array $gridOption = array(), $dataSource = null, ColumnInterface $parent = null) {
		$this->columnType = $columnType;
		$this->gridOptions = $gridOption;
		$this->dependencyInjectionExtension = $dependencyInjectionExtension;
		$this->path = $path;
		$this->resolver = new OptionsResolver();
		$this->columnOptions = $this->setupFilterOptionsResolver($columnType, $columnTypeOptions);
		$this->parent = $parent;
		$this->dataSource = $dataSource;
		if($dataSource instanceof QueryBuilder) {
			$this->queryBuilder = clone $dataSource;
		}

		if(!isset($this->columnOptions['path'])) {
			$this->columnOptions['path'] = $this->path;
		}

		$this->configureColumn();
	}

	/**
	 * @inheritdoc
	 */
	public function isOrderable(): bool {
		return $this->orderable;
	}

	/**
	 * @inheritdoc
	 */
	public function isFilterable(): bool {
		return $this->filterable;
	}

	/**
	 * @inheritdoc
	 */
	public function getFilter(): ?Filter {
		return $this->filter;
	}

	/**
	 * @inheritdoc
	 */
	public function setFilter(?Filter $filter): ColumnInterface {
		$this->filter = $filter;
		return $this;
	}

	public function getFilterQueryPath() {
		return $this->filterQueryPath === null ? $this->getQueryPath() : $this->filterQueryPath;
	}

	/**
	 * @inheritdoc
	 */
	public function createView(ColumnView $parent = null) {
		if(null === $parent && $this->parent) {
			$parent = $this->parent->createView();
		}

		$view = new ColumnView($parent);
		$this->buildView($view, $this->columnType, $this->columnOptions);

		if($view->vars['translation_domain'] === null) {
			$view->vars['translation_domain'] = $this->gridOptions['translation_domain'];
		}
//		if($view->vars['abbreviation_translation_domain'] === null) {
//			$view->vars['abbreviation_translation_domain'] = $this->gridOption['translation_domain'];
//		}
//		if($view->vars['tooltip_translation_domain'] === null) {
//			$view->vars['tooltip_translation_domain'] = $this->gridOption['translation_domain'];
//		}

		if($this->filter) {
			$view->filter = $this->filter->createView();
		}

		return $view;
	}

	/**
	 * @inheritdoc
	 */
	public function createData($item, $rootAlias) {
		$this->buildData($this->columnType, $this->columnOptions);
		return $this->generateData($item, $rootAlias, $this->columnOptions, $this->gridOptions);
	}

	/**
	 * Get the path to access the property on the bound object
	 *
	 * @return string the path to access the property on the bound object
	 */
	public function getPath(): string {
		return $this->path;
	}

	/**
	 * Get the path to access the property on the bound object
	 *
	 * @param string $path the path to access the property on the bound object
	 * @return ColumnInterface
	 */
	public function setPath(string $path): ColumnInterface {
		$this->path = $path;
		return $this;
	}

	/**
	 * Get the data source of the column.
	 *
	 * @return QueryBuilder|array the data source of the column.
	 */
	public function getDataSource() {
		return $this->dataSource;
	}

	/**
	 * @inheritdoc
	 */
	public function getGridOptions(): array {
		return $this->gridOptions;
	}

	/**
	 * @inheritdoc
	 */
	public function setGridOptions(array $gridOptions): self {
		$this->gridOptions = $gridOptions;
		return $this;
	}

	/**
	 * Get the query builder used by a filter for the column (if any).
	 *
	 * @return QueryBuilder the query builder used by a filter for the column (if any).
	 */
	public function getQueryBuilder(): ?QueryBuilder {
		return $this->queryBuilder;
	}

	/**
	 * @inheritdoc
	 */
	public function getServerSideOrderDelegate(): ?callable {
		return $this->serverSideOrderDelegate;
	}

	/**
	 * @inheritdoc
	 */
	public function setServerSideOrderDelegate(?callable $serverSideOrderDelegate = null) {
		$this->serverSideOrderDelegate = $serverSideOrderDelegate;
		return $this;
	}

	/**
	 * Get the path to be used by a query builder for sorting and ordering etc.
	 * This may differ from the <code>ColumnSettings::getPath()</code> especially for joined paths.
	 *
	 * @return string the path to be used by a query builder for sorting and ordering etc.
	 */
	public function getQueryPath(): string {
		return $this->queryPath ?? $this->getPath();
	}

	/**
	 * Set the path to be used by a query builder for sorting and ordering etc.
	 *
	 * @param string $queryPath the path to be used by a query builder for sorting and ordering etc.
	 * @return ColumnInterface
	 */
	public function setQueryPath(string $queryPath): void {
		$this->queryPath = $queryPath;
	}

	/**
	 * @inheritdoc
	 */
	public function getColumnOptions() {
		return $this->columnOptions;
	}

	/**
	 * @inheritdoc
	 */
	public function setColumnOptions(array $columnOptions) {
		$this->columnOptions = $columnOptions;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function addDataTransformer(DataTransformerInterface $dataTransformer, $forceAppend = false) {
		if($forceAppend) {
			$this->dataTransformers[] = $dataTransformer;
		} else {
			array_unshift($this->dataTransformers, $dataTransformer);
		}
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function resetDataTransformers() {
		$this->dataTransformers = array();
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getDataTransformers() {
		return $this->dataTransformers;
	}

	/**
	 * Configures any fields of the column according to the internal column options, such as filter delegate etc.
	 */
	protected function configureColumn() {
		$dataMode = $this->getGridOptions()['dataMode'];
		$this->valueDelegate = $this->columnOptions['value_delegate'];
		$this->queryPath = $this->columnOptions['query_path'];
		$this->filterQueryPath = $this->columnOptions['filter_query_path'];
		$this->orderable = AbstractColumnType::getBooleanValueDependingOnClientOrServer($this->columnOptions['orderable'], $dataMode);
		$this->filterable = AbstractColumnType::getBooleanValueDependingOnClientOrServer($this->columnOptions['filterable'], $dataMode);

		if($this->filterable && $this->columnOptions['filter_type'] !== null) {
			$this->filter = new Filter(
				$this->dependencyInjectionExtension->resolveFilterType($this->columnOptions['filter_type']),
				$this->dependencyInjectionExtension,
				$this->columnOptions['filter_options'],
				$this->columnOptions,
				$this->gridOptions,
				$this->dataSource
			);
		}
	}

	/**
	 * Fetches the configured value from the given item.
	 *
	 * @param object $item
	 *                              Bound object
	 * @param string $rootAlias
	 *                              the root alias is only necessary if no sub-objects (i.e. no joins) are used for this table.
	 * @param array $columnOptions the options of the column type
	 * @param array $gridOptions the options of the table type the column belongs to
	 * @return mixed The value
	 */
	protected function generateData($item, $rootAlias, $columnOptions, $gridOptions) {
		$path = Utils::startsWith($this->getPath(), $rootAlias . '.') ? substr($this->getPath(), strlen($rootAlias) + 1) : $this->getPath();
		$displayValue = call_user_func($this->valueDelegate, $item, $path, $columnOptions);
//		foreach($this->dataTransformers as $transformer) {
//			$displayValue = $transformer->transform($this, $item, $displayValue);
//		}
//		$data = array('display' => $displayValue);
//		if($tableOptions['serverSide'] === false) {
//			$this->appendSortData($data, $item, $path, $rootAlias, $columnOptions);
//			$this->appendFilterData($data, $item, $path, $rootAlias, $columnOptions);
//		}
		return $displayValue;
	}

	/**
	 * Sets up the options resolver for the given column type and the initial options.
	 * Setting up means that the column type options and all applicable parent options will be validated and
	 * resolved according to the hierarchy grandparent, then parent, then instance etc.
	 *
	 * @param ColumnTypeInterface $columnType the type to resolve the options for, also used for determining any parents
	 *                                        whose options are to be resolved as well
	 * @param array $options the initial options to also be resolved (if any).
	 * @return array the resolved options for the given column type.
	 */
	protected function setupFilterOptionsResolver(ColumnTypeInterface $columnType, array $options = array()): array {
		$this->resolveOptions($columnType, $this->resolver);
		return $this->resolver->resolve($options);
	}

	/**
	 * Merges the configurations of each type in the hierarchy starting from the top most type.
	 *
	 * @param ColumnTypeInterface $columnType the column type to resolve the options from
	 * @param OptionsResolver $resolver the resolver used for checking option values and defaults etc.
	 */
	protected function resolveOptions(ColumnTypeInterface $columnType, OptionsResolver $resolver): void {
		if($columnType->getParent()) {
			$parentType = $this->dependencyInjectionExtension->resolveColumnType($columnType->getParent());
			$this->resolveOptions($parentType, $resolver);
		}
		$columnType->configureOptions($resolver, $this->gridOptions);
	}

	/**
	 * Updates the given view.
	 *
	 * @param ColumnView $columnView the view to be updated
	 * @param ColumnTypeInterface $columnType the column type containing the information that may be relevant for the view
	 * @param array $columnOptions the options defined for the column type, containing information
	 *                                           such as the translation_domain etc.
	 */
	protected function buildView(ColumnView $columnView, ColumnTypeInterface $columnType, array $columnOptions = array()): void {
		if($columnType->getParent()) {
			$parentType = $this->dependencyInjectionExtension->resolveColumnType($columnType->getParent());
			$this->buildView($columnView, $parentType, $columnOptions);
		}
		$columnType->buildView($columnView, $this, $columnOptions);

		if($columnView->vars['translation_domain'] === null) {
			$columnView->vars['translation_domain'] = $this->columnOptions['translation_domain'];
		}
	}

	/**
	 * Calls the buildData method on the column type and all parent column types (if any), causing
	 * any data transformers along the hierarchy to be triggered.
	 * In case this method was already called once, it will immediately return.
	 *
	 * @param ColumnTypeInterface $columnType the column type to call the buildData method on, and all its parents.
	 * @param array $options the options for the column type.
	 */
	protected function buildData(ColumnTypeInterface $columnType, array $options = array()): void {
		if($this->dataConfigured) {
			return;
		}
		if($columnType->getParent()) {
			$parentType = $this->dependencyInjectionExtension->resolveColumnType($columnType->getParent());
			$this->buildData($parentType, $options);
		}
		$columnType->buildData($this, $options);

		$this->dataConfigured = true;
	}
}