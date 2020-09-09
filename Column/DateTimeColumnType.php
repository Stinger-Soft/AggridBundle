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

use Locale;
use StingerSoft\AggridBundle\Transformer\DateTimeFormatterDataTransformer;
use StingerSoft\AggridBundle\View\ColumnView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Basic implementation to render a column representing a php DateTime object with support for Symfony based date formatting.
 */
class DateTimeColumnType extends AbstractColumnType {

	protected $transformer;

	public function __construct(DateTimeFormatterDataTransformer $transformer) {
		$this->transformer = $transformer;
	}

	/**
	 * {@inheritdoc}
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
	 */
	public function configureOptions(OptionsResolver $resolver, array $tableOptions = []): void {
		$resolver->setDefault('locale', Locale::getDefault());

		$dateTimeFormatValidator = function ($valueToCheck) {
			return array_key_exists($valueToCheck, DateTimeFormatterDataTransformer::getValidFormats());
		};
		$resolver->setDefault('time_format', 'medium');
		$resolver->setAllowedValues('time_format', $dateTimeFormatValidator);
		$resolver->setDefault('date_format', 'medium');
		$resolver->setAllowedValues('date_format', $dateTimeFormatValidator);

		$resolver->setDefault('format', null);
		$resolver->setDefault('calendar', 'gregorian');
		$resolver->setAllowedValues('calendar', ['gregorian', 'traditional']);

		$resolver->setDefault('comparator', 'DateComparator');
		$resolver->setDefault('filterValueGetter', 'ValueGetter');
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildView(ColumnView $view, ColumnInterface $column, array $options): void {
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildData(ColumnInterface $column, array $options) {
		$column->addDataTransformer($this->transformer);
	}
}