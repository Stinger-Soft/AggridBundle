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

use NumberFormatter;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Renders a percentage formatted value in a column using PHPs number format capabilities.
 *
 * @see NumberFormatterColumnType for formatting numbers in a more generic way
 */
class PercentageColumnType extends AbstractColumnType {

	/**
	 * @inheritdoc
	 *
	 * @see AbstractColumnType::configureOptions()
	 */
	public function configureOptions(OptionsResolver $resolver, array $tableOptions = []): void {
		$resolver->setDefault('number_formatter_style', NumberFormatter::PERCENT);
		$resolver->setDefault('filterValueGetter', 'PercentageValueGetter');

		$resolver->setDefault('format_null', true);
		$resolver->setAllowedTypes('format_null', 'boolean');
	}

	/**
	 * @inheritdoc
	 */
	public function getParent(): ?string {
		return NumberFormatterColumnType::class;
	}

}