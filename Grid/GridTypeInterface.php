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

use StingerSoft\AggridBundle\Column\Column;
use StingerSoft\AggridBundle\Helper\GridBuilderInterface;
use StingerSoft\AggridBundle\View\GridView;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface GridTypeInterface {
	/**
	 * Builds the grid
	 *
	 * This method is called for each type in the hierarchy starting from the
	 * top most type.
	 *
	 * @param GridBuilderInterface $builder
	 * @param array $gridOptions
	 */
	public function buildGrid(GridBuilderInterface $builder, array $gridOptions): void;

	/**
	 * Builds the grid view used for rendering of the grid.
	 *
	 * This method is called for each type in the hierarchy starting from the
	 * top most type. It allows adding more variables to the given view which may be used during rendering of the grid.
	 *
	 * @param GridView $view the grid view to add any additional information to
	 * @param GridInterface $grid the grid instance the view belongs to
	 * @param array $gridOptions the options of the grid, previously configured via the #configureOptions method
	 * @param Column[] $columns the columns of the grid
	 * @return void
	 */
	public function buildView(GridView $view, GridInterface $grid, array $gridOptions, array $columns): void;

	/**
	 * Returns the HTML id to identify this grid.
	 *
	 * @param array $gridOptions the resolved options of the grid type, maybe containing an id prefix or suffix
	 * @return string the HTML id of this grid
	 */
	public function getId(array $gridOptions): string;

	/**
	 * Builds the grid settings.
	 *
	 * This method is called for each type in the hierarchy starting from the
	 * top most type.
	 *
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver): void;

	/**
	 * Returns the name of the parent type.
	 *
	 * @return string|null The name of the parent type if any, null otherwise
	 */
	public function getParent(): ?string;
}