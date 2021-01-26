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

namespace StingerSoft\AggridBundle\View;

use ReflectionException;
use StingerSoft\AggridBundle\Column\Column;
use StingerSoft\AggridBundle\Column\ColumnInterface;
use StingerSoft\AggridBundle\Components\ComponentInterface;
use StingerSoft\AggridBundle\Exception\InvalidArgumentTypeException;
use StingerSoft\AggridBundle\Grid\GridInterface;
use StingerSoft\AggridBundle\Grid\GridTypeInterface;

class GridView extends AbstractBaseView {

	/**
	 * @var array the options for the grid type, containing information such as the translation_domain etc.
	 */
	protected $gridOptions;

	/**
	 * @var ColumnView[] the views for all columns belonging to the grid
	 */
	protected $columnViews;

	/**
	 * @var Column[] the columns belonging to the grid
	 */
	protected $columns;

	/** @var ComponentInterface[] */
	protected $components;

	/**
	 * @var array|ComponentView[]
	 */
	protected $componentViews;

	/**
	 * @var GridTypeInterface the grid type instance
	 */
	protected $gridType;

	/**
	 * @var GridInterface the grid instance
	 */
	protected $grid;

	/**
	 * @var string the id of the grid
	 */
	protected $gridId;

	/**
	 * @var null|ColumnInterface[]
	 */
	protected $filterColumns;

	/**
	 * @var array
	 */
	protected $additionalComponents;

	/**
	 * @var null|ColumnInterface[]
	 */
	protected $identityColumns;

	/**
	 * GridView Constructor.
	 *
	 * @param GridInterface        $gridInterface                the grid instance
	 * @param GridTypeInterface    $gridType                     the grid type instance
	 * @param array                $gridOptions                  the options for the grid type, containing information such as the
	 *                                                           translation_domain etc.
	 * @param ColumnInterface[]    $columns                      the columns belonging to the grid, required for generating the column
	 *                                                           views
	 * @param ComponentInterface[] $components                   the status bar components belonging to the grid,
	 *                                                           required for generating the status bar component views
	 * @throws InvalidArgumentTypeException
	 * @throws ReflectionException
	 */
	public function __construct(GridInterface $gridInterface, GridTypeInterface $gridType, array $gridOptions, array $columns, array $components) {
		$this->gridOptions = $gridOptions;
		$this->gridType = $gridType;
		$this->grid = $gridInterface;
		$this->gridId = $this->gridType->getId($this->gridOptions);
		$this->columns = $columns;
		$this->components = $components;
		$this->vars = [];
		$this->additionalComponents = [];

		$this->configureColumnViews();
		$this->configureComponentViews();
	}

	/**
	 * @return string
	 */
	public function getGridId(): string {
		return $this->gridId;
	}

	/**
	 * Gets the column views for the grid.
	 *
	 * @return ColumnView[] an array containing the views for all the columns belonging to the grid
	 */
	public function getColumns(): array {
		return $this->columnViews;
	}

	/**
	 * Gets the status bar component views for the grid.
	 *
	 * @return ComponentView[] an array containing the views for all the status bar components belonging to the grid
	 */
	public function getComponents(): array {
		return $this->componentViews;
	}

	/**
	 * @return ComponentView[]
	 */
	public function getStatusBarComponents(): array {
		return $this->componentViews[ComponentInterface::CATEGORY_STATUS_BAR] ?? [];
	}

	/**
	 * @return ComponentView[]
	 */
	public function getSideBarComponents(): array {
		return $this->componentViews[ComponentInterface::CATEGORY_SIDE_BAR] ?? [];
	}

	public function getAdditionalComponents(): array {
		return array_merge($this->gridOptions['components'] ?? [], $this->additionalComponents);
	}

	public function getJsTemplate() : ?string {
		return $this->gridOptions['templateJs'] ?? null;
	}

	public function getHtmlTemplate() :?string {
		return $this->gridOptions['templateHtml'] ?? null;
	}

	/**
	 * Sets the column views for the table.
	 *
	 * @param ColumnView[] $columns the column views array to set
	 * @return $this
	 */
	public function setColumns(array $columns): self {
		$this->columnViews = $columns;
		return $this;
	}

	/**
	 * @throws ReflectionException
	 * @throws InvalidArgumentTypeException
	 */
	protected function configureColumnViews(): void {
		$this->columnViews = [];
		$rootViews = [];
		foreach($this->columns as $column) {
			$options = $column->getColumnOptions();
			if($options['renderable'] && $column->getParent() === null && !isset($rootViews[$column->getPath()])) {
				$view = $column->createView();
				$rootViews[$column->getPath()] = $view;
				$this->addChildViews($view, $column);
			}
		}
		$this->columnViews = $rootViews;
	}

	/**
	 * @param ColumnView      $parentView
	 * @param ColumnInterface $column
	 * @throws InvalidArgumentTypeException
	 * @throws ReflectionException
	 */
	protected function addChildViews(ColumnView $parentView, ColumnInterface $column): void {
		if(count($column->getChildren())) {
			$childViews = [];
			foreach($column->getChildren() as $child) {
				$childView = $child->createView($parentView);
				$this->addChildViews($childView, $child);
				$childViews[] = $childView;
			}
			$parentView->vars['children'] = $childViews;
		}
	}

	/**
	 * @throws InvalidArgumentTypeException
	 * @throws ReflectionException
	 */
	protected function configureComponentViews(): void {
		$this->componentViews = [];
		$rootViews = [
			ComponentInterface::CATEGORY_STATUS_BAR => [],
			ComponentInterface::CATEGORY_SIDE_BAR   => [],
		];
		foreach($this->components as $category => $components) {
			/** @var ComponentInterface $component */
			foreach($components as $component) {
				if($component->getParent() === null && !isset($rootViews[$category][$component->getId()])) {
					$view = $component->createView();
					if($view->component !== null) {
						$this->additionalComponents[$view->componentAlias] = $view->component;
					}
					$rootViews[$category][$component->getId()] = $view;
					$this->addComponentChildViews($view, $component);
				}
			}
		}
		$this->componentViews = $rootViews;
	}

	/**
	 * @param ComponentView      $parentView
	 * @param ComponentInterface $statusBarComponent
	 * @throws InvalidArgumentTypeException
	 * @throws ReflectionException
	 */
	protected function addComponentChildViews(ComponentView $parentView, ComponentInterface $statusBarComponent): void {
		if(count($statusBarComponent->getChildren())) {
			$childViews = [
				ComponentInterface::CATEGORY_STATUS_BAR => [],
				ComponentInterface::CATEGORY_SIDE_BAR   => [],
			];
			foreach($statusBarComponent->getChildren() as $child) {
				$category = $child->getComponentCategory();
				$childView = $child->createView($parentView);
				if($childView->component !== null) {
					$this->additionalComponents[$childView->componentAlias] = $childView->component;
				}
				$this->addComponentChildViews($childView, $child);
				$childViews[$category][] = $childView;
			}
			$parentView->vars['children'] = $childViews;
		}
	}

	/**
	 * Get all columns that are filterable and provide a filter instance.
	 *
	 * @return ColumnInterface[] an array of filterable columns, may be empty
	 */
	public function getFilterableColumns(): array {
		if($this->filterColumns === null) {
			$this->filterColumns = [];
			foreach($this->columnViews as $index => $column) {
				if($column->filter !== null) {
					$this->filterColumns[$index] = $column;
				}
			}
		}
		return $this->filterColumns;
	}

	public function getIdentityColumns(): array {
		if($this->identityColumns === null) {
			$this->identityColumns = [];
			foreach($this->columns as $index => $column) {
				if($column->isIdentityProvider()) {
					$columnOptions = $column->getColumnOptions();
					$this->identityColumns[$index] = $columnOptions['identityValueGetter'];
				}
			}
		}
		return $this->identityColumns;
	}

	public function getInlineData(): ?string {
		return $this->grid->createJsonData();
	}

}