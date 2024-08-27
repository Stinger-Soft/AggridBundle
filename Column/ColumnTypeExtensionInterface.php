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

/**
 * @method static iterable getExtendedTypes() Gets the extended types
 */
interface ColumnTypeExtensionInterface {

	public function configureOptions(OptionsResolver $resolver, array $gridOptions = []): void;

	public function buildView(ColumnView $view, ColumnInterface $column, array $options): void;

	public function buildData(ColumnInterface $column, array $options);

	/**
	 * Builds the column configuration as a json compatible array
	 *
	 * This method is called for each type in the hierarchy starting from the
	 * top most type. It allows adding more variables to the given view which may be used during rendering of the column.
	 *
	 * @param ColumnView      $view    the column view to add any additional information to
	 * @param ColumnInterface $column  the column instance the view belongs to
	 * @param array           $options the options of the column, previously configured by the #configureOptions method
	 * @return void
	 */
	public function buildJsonConfiguration(ColumnView $view, ColumnInterface $column, array $options): void;
}