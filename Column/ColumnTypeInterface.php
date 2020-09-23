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

use StingerSoft\AggridBundle\View\ColumnView;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface ColumnTypeInterface {

	/** @var string constant for search expression conjunction using AND */
	public const SEARCH_OPERATOR_AND = 'AND';

	/** @var string constant for search expression conjunction using OR */
	public const SEARCH_OPERATOR_OR = 'OR';

	/** @var string constant for menu tab containing general information */
	public const MENU_TAB_GENERAL = 'generalMenuTab';

	/** @var string constant for menu tab containing filter information */
	public const MENU_TAB_FILTER = 'filterMenuTab';

	/** @var string constant for menu tab containing column information */
	public const MENU_TAB_COLUMNS = 'columnsMenuTab';

	/** @var string[] valid constants to be used for menu tabs on columns */
	public const MENU_TABS = [self::MENU_TAB_GENERAL, self::MENU_TAB_FILTER, self::MENU_TAB_COLUMNS];

	/**
	 * Builds the column options using the given options resolver.
	 *
	 * This method is called for each type in the hierarchy starting from the
	 * top most type. It allows to define additional options for this type of column.
	 *
	 * @param OptionsResolver $resolver     the options resolver used for checking validity of the column options,
	 *                                      defining default values etc.
	 * @param array           $gridOptions  the options of the grid the column belongs to, containing options such as
	 *                                      the grids translation domain etc.
	 * @return void
	 */
	public function configureOptions(OptionsResolver $resolver, array $gridOptions = []): void;

	/**
	 * Builds the column view used for rendering of the column.
	 *
	 * This method is called for each type in the hierarchy starting from the
	 * top most type. It allows adding more variables to the given view which may be used during rendering of the column.
	 *
	 * @param ColumnView      $view    the column view to add any additional information to
	 * @param ColumnInterface $column  the column instance the view belongs to
	 * @param array           $options the options of the column, previously configured by the #configureOptions method
	 * @return void
	 */
	public function buildView(ColumnView $view, ColumnInterface $column, array $options): void;

	/**
	 * Builds the data for the given column.
	 *
	 * This method is called for each type in the hierarchy starting from the top most type.
	 * It allows adding any additional data transformers which my be required for generating the correct value or to
	 * manipulate any options or column settings, filters etc.
	 *
	 * This options is called immediately before the value for an object bound to this column is returned, and the method
	 * is only called once in order to ensure no data transformers are added multiple times.
	 *
	 * @param ColumnInterface $column  the column to build the data for
	 * @param array           $options the options of the column
	 * @return mixed
	 */
	public function buildData(ColumnInterface $column, array $options);

	/**
	 * Returns the name of the parent type or null.
	 *
	 * @return string|null The name of the parent type if any, null otherwise
	 */
	public function getParent(): ?string;
}