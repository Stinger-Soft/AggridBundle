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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use ReflectionException;
use StingerSoft\AggridBundle\Exception\InvalidArgumentTypeException;
use StingerSoft\AggridBundle\Filter\Filter;
use StingerSoft\AggridBundle\Service\DependencyInjectionExtensionInterface;
use StingerSoft\AggridBundle\Transformer\DataTransformerInterface;
use StingerSoft\AggridBundle\View\ColumnView;
use StingerSoft\PhpCommons\Builder\HashCodeBuilder;
use StingerSoft\PhpCommons\String\Utils;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
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
	 * @var DataTransformerInterface[]
	 */
	protected $dataTransformers = [];

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
	 * @var callable Callable to update the query builder of the bound object in order to add any required
	 * where clauses explicitly required for a global search.
	 * By default the query_path or path property will be used to perform a like query for a global search term.
	 */
	protected $serverSideSearchDelegate;

	/**
	 * @var string the path to be used for querying the potential filter values for select or autocomplete filters
	 */
	protected $filterQueryPath;

	/**
	 * @var ColumnInterface|null the parent column (if any)
	 */
	protected $parent;

	/**
	 * @var ArrayCollection|ColumnInterface[] the children of the column (if any)
	 */
	protected $children;

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
	 * @var boolean whether the column is searchable globally
	 */
	protected $searchable = false;

	/**
	 * @var bool whether the column provides identity
	 */
	protected $identityProvider = false;

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

	/** @var ColumnTypeExtensionInterface[] */
	protected $typeExtensions = [];

	/**
	 * Column constructor.
	 *
	 * @param string                                $path              The path / property / key under which the column is registered. This will be used for accessing an
	 *                                                                 objects value with a property accessor.
	 * @param ColumnTypeInterface                   $columnType        the column type for this column
	 * @param DependencyInjectionExtensionInterface $dependencyInjectionExtension
	 * @param array                                 $columnTypeOptions the options for the column type
	 * @param array                                 $gridOption        the options of the table the column belongs to
	 * @param QueryBuilder|array|null               $dataSource        the data source of the table the column will be attached to. In case it is a query builder, it will be cloned in order to
	 *                                                                 allow Filter instances to modify it if necessary.
	 * @param ColumnInterface|null                  $parent            the parent column (if any) or null.
	 * @throws InvalidArgumentTypeException
	 * @throws ReflectionException
	 */
	public function __construct(
		string $path,
		ColumnTypeInterface $columnType,
		DependencyInjectionExtensionInterface $dependencyInjectionExtension,
		array $columnTypeOptions = [],
		array $gridOption = [],
		$dataSource = null,
		ColumnInterface $parent = null
	) {
		$this->children = new ArrayCollection();
		$this->columnType = $columnType;
		$this->gridOptions = $gridOption;
		$this->dependencyInjectionExtension = $dependencyInjectionExtension;
		$this->path = $path;
		$this->typeExtensions = $this->dependencyInjectionExtension->resolveColumnTypeExtensions(get_class($columnType));
		foreach($this->typeExtensions as $extension) {
			if(!$extension instanceof ColumnTypeExtensionInterface) {
				throw new UnexpectedTypeException($extension, ColumnTypeExtensionInterface::class);
			}
		}
		$this->resolver = new OptionsResolver();
		$this->columnOptions = $this->setupFilterOptionsResolver($columnType, $columnTypeOptions);
		$this->setParent($parent);
		$this->dataSource = $dataSource;
		if($dataSource instanceof QueryBuilder) {
			$this->queryBuilder = clone $dataSource;
		}

		if(!isset($this->columnOptions['path'])) {
			$this->columnOptions['path'] = $this->path;
		}

		$this->configureColumn();
	}

	public function setParent(?ColumnInterface $parent): ColumnInterface {
		$this->parent = $parent;
		if($parent !== null) {
			$parent->addChild($this);
		}
		return $this;
	}

	public function getParent(): ?ColumnInterface {
		return $this->parent;
	}

	public function getChildren(): array {
		return $this->children->toArray();
	}

	public function addChild(ColumnInterface $child): ColumnInterface {
		if(!$this->children->contains($child)) {
			$this->children[] = $child;
		}
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getColumnType(): ColumnTypeInterface {
		return $this->columnType;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isOrderable(): bool {
		return $this->orderable;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isIdentityProvider(): bool {
		return $this->identityProvider;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isFilterable(): bool {
		return $this->filterable;
	}

	/**
	 * @return bool
	 */
	public function isSearchable(): bool {
		return $this->searchable;
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

	public function getFilterQueryPath(): string {
		return $this->filterQueryPath ?? $this->getQueryPath();
	}

	/**
	 * @inheritdoc
	 */
	public function createView(ColumnView $parent = null): ColumnView {
		if(null === $parent && $this->parent) {
			$parent = $this->parent->createView();
		}

		$view = new ColumnView($parent);
		$this->buildView($view, $this->columnType, $this->columnOptions, $this->typeExtensions);

		if($view->vars['translation_domain'] === null) {
			$view->vars['translation_domain'] = $this->gridOptions['translation_domain'];
		}
		if($view->vars['headerTooltip_translation_domain'] === null) {
			$view->vars['headerTooltip_translation_domain'] = $this->gridOptions['translation_domain'];
		}

		if($this->filter) {
			$view->filter = $this->filter->createView($view);
		}

		return $view;
	}

	/**
	 * @inheritdoc
	 */
	public function createData($item, string $rootAlias) {
		$this->buildData($this->columnType, $this->columnOptions, $this->typeExtensions);
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
	 * @return self
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
	public function setGridOptions(array $gridOptions): ColumnInterface {
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
	public function setServerSideOrderDelegate(?callable $serverSideOrderDelegate = null): ColumnInterface {
		$this->serverSideOrderDelegate = $serverSideOrderDelegate;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getServerSideSearchDelegate(): ?callable {
		return $this->serverSideSearchDelegate;
	}

	/**
	 * @inheritdoc
	 */
	public function setServerSideSearchDelegate(?callable $serverSideSearchDelegate = null): ColumnInterface {
		$this->serverSideSearchDelegate = $serverSideSearchDelegate;
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
	 * @return self
	 */
	public function setQueryPath(string $queryPath): ColumnInterface {
		$this->queryPath = $queryPath;
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
	public function setColumnOptions(array $columnOptions): ColumnInterface {
		$this->columnOptions = $columnOptions;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function addDataTransformer(DataTransformerInterface $dataTransformer, $forceAppend = false): ColumnInterface {
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
	public function resetDataTransformers(): ColumnInterface {
		$this->dataTransformers = [];
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getDataTransformers(): array {
		return $this->dataTransformers;
	}

	/**
	 * Configures any fields of the column according to the internal column options, such as filter delegate etc.
	 *
	 * @throws InvalidArgumentTypeException
	 * @throws ReflectionException
	 */
	protected function configureColumn(): void {
		$dataMode = $this->getGridOptions()['dataMode'];
		$this->valueDelegate = $this->columnOptions['value_delegate'];
		$this->queryPath = $this->columnOptions['query_path'];
		$this->filterQueryPath = $this->columnOptions['filter_query_path'];
		$this->orderable = AbstractColumnType::getBooleanValueDependingOnClientOrServer($this->columnOptions['orderable'], $dataMode);
		$this->filterable = AbstractColumnType::getBooleanValueDependingOnClientOrServer($this->columnOptions['filterable'], $dataMode);
		$this->searchable = AbstractColumnType::getBooleanValueDependingOnClientOrServer($this->columnOptions['searchable'], $dataMode);
		$this->identityProvider = $this->columnOptions['providesIdentity'];
		$this->serverSideOrderDelegate = $this->columnOptions['order_server_delegate'];
		$this->serverSideSearchDelegate = $this->columnOptions['search_server_delegate'];

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
	 * Get the hash code of the column.
	 *
	 * @return int the hash code of the column.
	 * @throws ReflectionException
	 */
	public function getHashCode(): int {
		$builder = new HashCodeBuilder();
		return $builder
			->append($this->getPath())
			->append($this->isFilterable())
			->append($this->columnOptions['visible'])
			->toHashCode();
	}

	/**
	 * Fetches the configured value from the given item.
	 *
	 * @param mixed  $item
	 *                              Bound object
	 * @param string $rootAlias
	 *                              the root alias is only necessary if no sub-objects (i.e. no joins) are used for this table.
	 * @param array  $columnOptions the options of the column type
	 * @param array  $gridOptions   the options of the table type the column belongs to
	 * @return mixed The value
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected function generateData($item, string $rootAlias, array $columnOptions, array $gridOptions) {
		$path = Utils::startsWith($this->getPath(), $rootAlias . '.') ? substr($this->getPath(), strlen($rootAlias) + 1) : $this->getPath();
		$originalValue = $displayValue = call_user_func($this->valueDelegate, $item, $path, $columnOptions);
		foreach($this->dataTransformers as $transformer) {
			$displayValue = $transformer->transform($this, $item, $displayValue);
		}
		return ['value' => $originalValue, 'displayValue' => $displayValue];
	}

	/**
	 * Sets up the options resolver for the given column type and the initial options.
	 * Setting up means that the column type options and all applicable parent options will be validated and
	 * resolved according to the hierarchy grandparent, then parent, then instance etc.
	 *
	 * @param ColumnTypeInterface $columnType the type to resolve the options for, also used for determining any parents
	 *                                        whose options are to be resolved as well
	 * @param array               $options    the initial options to also be resolved (if any).
	 * @return array the resolved options for the given column type.
	 * @throws InvalidArgumentTypeException
	 * @throws ReflectionException
	 */
	protected function setupFilterOptionsResolver(ColumnTypeInterface $columnType, array $options = []): array {
		$this->resolveOptions($columnType, $this->resolver);
		foreach($this->typeExtensions as $extension) {
			$extension->configureOptions($this->resolver, $this->gridOptions);
		}
		return $this->resolver->resolve($options);
	}

	/**
	 * Merges the configurations of each type in the hierarchy starting from the top most type.
	 *
	 * @param ColumnTypeInterface $columnType the column type to resolve the options from
	 * @param OptionsResolver     $resolver   the resolver used for checking option values and defaults etc.
	 * @throws InvalidArgumentTypeException
	 * @throws ReflectionException
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
	 * @param ColumnView                     $columnView    the view to be updated
	 * @param ColumnTypeInterface            $columnType    the column type containing the information that may be relevant for the view
	 * @param array                          $columnOptions the options defined for the column type, containing information
	 *                                                      such as the translation_domain etc.
	 * @param ColumnTypeExtensionInterface[] $extensions    the extensions to be applied to the view
	 * @throws InvalidArgumentTypeException
	 * @throws ReflectionException
	 */
	protected function buildView(ColumnView $columnView, ColumnTypeInterface $columnType, array $columnOptions = [], array $extensions = []): void {
		if($columnType->getParent()) {
			$parentType = $this->dependencyInjectionExtension->resolveColumnType($columnType->getParent());
			$this->buildView($columnView, $parentType, $columnOptions);
		}
		$columnType->buildView($columnView, $this, $columnOptions);

		foreach($extensions as $extension) {
			$extension->buildView($columnView, $this, $columnOptions);
		}

		if($columnView->vars['translation_domain'] === null) {
			$columnView->vars['translation_domain'] = $this->columnOptions['translation_domain'];
		}
	}

	/**
	 * Calls the buildData method on the column type and all parent column types (if any), causing
	 * any data transformers along the hierarchy to be triggered.
	 * In case this method was already called once, it will immediately return.
	 *
	 * @param ColumnTypeInterface            $columnType the column type to call the buildData method on, and all its parents.
	 * @param array                          $options    the options for the column type.
	 * @param ColumnTypeExtensionInterface[] $extensions the extensions to be applied
	 * @throws InvalidArgumentTypeException
	 * @throws ReflectionException
	 */
	protected function buildData(ColumnTypeInterface $columnType, array $options = [], array $extensions = []): void {
		if($this->dataConfigured) {
			return;
		}
		if($columnType->getParent()) {
			$parentType = $this->dependencyInjectionExtension->resolveColumnType($columnType->getParent());
			$this->buildData($parentType, $options);
		}
		$columnType->buildData($this, $options);

		foreach($extensions as $extension) {
			$extension->buildData($this, $options);
		}

		$this->dataConfigured = true;
	}
}