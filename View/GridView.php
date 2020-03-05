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

use StingerSoft\AggridBundle\Column\Column;
use StingerSoft\AggridBundle\Column\ColumnInterface;
use StingerSoft\AggridBundle\Components\StatusBar\StatusBarComponentInterface;
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

	/** @var StatusBarComponentInterface[]  */
	protected $statusBarComponents;

	/**
	 * @var StatusBarComponentView[]
	 */
	protected $statusBarComponentsViews;

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
	 * GridView Constructor.
	 *
	 * @param GridInterface                 $gridInterface       the grid instance
	 * @param GridTypeInterface             $gridType            the grid type instance
	 * @param array                         $gridOptions         the options for the grid type, containing information such as the
	 *                                                           translation_domain etc.
	 * @param ColumnInterface[]             $columns             the columns belonging to the grid, required for generating the column
	 *                                                           views
	 * @param StatusBarComponentInterface[] $statusBarComponents the status bar components belonging to the grid,
	 *                                                           required for generating the status bar component views
	 */
	public function __construct(GridInterface $gridInterface, GridTypeInterface $gridType, array $gridOptions, array $columns, array $statusBarComponents) {
		$this->gridOptions = $gridOptions;
		$this->gridType = $gridType;
		$this->grid = $gridInterface;
		$this->gridId = $this->gridType->getId($this->gridOptions);
		$this->columns = $columns;
		$this->statusBarComponents = $statusBarComponents;
		$this->vars = [];

		$this->configureColumnViews();
		$this->configureStatusBarComponentViews();
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
	 * @return StatusBarComponentView[] an array containing the views for all the status bar components belonging to the grid
	 */
	public function getStatusBarComponents() : array {
		return $this->statusBarComponentsViews;
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

	protected function configureColumnViews(): void {
		$this->columnViews = [];
		$rootViews = [];
		foreach($this->columns as $column) {
			$options = $column->getColumnOptions();
			if($column->getParent() === null && !isset($rootViews[$column->getPath()]) && $options['renderable']) {
				$view = $column->createView();
				$rootViews[$column->getPath()] = $view;
				$this->addChildViews($view, $column);
			}
		}
		$this->columnViews = $rootViews;
	}

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

	protected function configureStatusBarComponentViews() : void {
		$this->statusBarComponentsViews = [];
		$rootViews = [];
		foreach($this->statusBarComponents as $statusBarComponent) {
			if($statusBarComponent->getParent() === null && !isset($rootViews[$statusBarComponent->getId()])) {
				$view = $statusBarComponent->createView();
				$rootViews[$statusBarComponent->getId()] = $view;
				$this->addStatusBarComponentChildViews($view, $statusBarComponent);
			}
		}
		$this->statusBarComponentsViews = $rootViews;
	}

	protected function addStatusBarComponentChildViews(StatusBarComponentView $parentView, StatusBarComponentInterface $statusBarComponent): void {
		if(count($statusBarComponent->getChildren())) {
			$childViews = [];
			foreach($statusBarComponent->getChildren() as $child) {
				$childView = $child->createView($parentView);
				$this->addStatusBarComponentChildViews($childView, $child);
				$childViews[] = $childView;
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

	public function getInlineData(): ?string {
		return $this->grid->createJsonData();
	}

}