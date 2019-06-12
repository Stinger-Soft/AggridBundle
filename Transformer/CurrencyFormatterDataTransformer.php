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

namespace StingerSoft\AggridBundle\Transformer;

use StingerSoft\AggridBundle\Column\ColumnInterface;

/**
 * The CurrencyFormatterDataTransformer automatically formats a columns' value as a currency according to a defined locale,
 * style and pattern using PHPs number formatter capabilities for all CurrencyColumnType column types.
 *
 */
class CurrencyFormatterDataTransformer implements DataTransformerInterface {

	/**
	 * @param ColumnInterface $column
	 * @param                 $item
	 * @param mixed           $value
	 *            The value in the original representation
	 * @return mixed The value in the transformed representation
	 */
	public function transform(ColumnInterface $column, $item, $value) {
		$options = $column->getColumnOptions();
		$formatNullValue = $options['format_null'];
		if($value === null && !$formatNullValue) {
			return $value;
		}
		$formatter = new \NumberFormatter($options['number_formatter_locale'], $options['number_formatter_style'], $options['number_formatter_pattern']);
		$currency = $options['currency'];
		if(is_callable($currency)) {
			$currency = call_user_func($currency, $item, $column->getPath(), $options);
		}
		return $formatter->formatCurrency($value, $currency);
	}
}