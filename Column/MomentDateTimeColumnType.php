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

class MomentDateTimeColumnType extends AbstractColumnType {

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver, array $gridOptions = []): void {
		$resolver->setDefault('date_format', 'L LTS');
		$resolver->setAllowedTypes('date_format', [
			'string',
		]);
		$resolver->setDefault('valueFormatter', 'DateTimeObjectFormatter');
		$resolver->setDefault('exportValueFormatter', 'DateTimeObjectFormatter');
		$resolver->setDefault('comparator', 'DateComparator');
		$resolver->setDefault('filter', 'agDateColumnFilter');
		$resolver->setDefault('js_column_template', '@StingerSoftAggrid/Column/datetime_moment.js.twig');
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildView(ColumnView $view, ColumnInterface $column, array $options): void {
		$view->vars['valueFormatterParams'] = [
			'dateFormat' => $options['date_format'],
		];
	}

}