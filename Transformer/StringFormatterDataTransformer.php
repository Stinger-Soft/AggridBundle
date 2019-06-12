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
 * The StringFormatterDataTransformer is automatically appended to all columns of type FormattedStringColumnType
 * and it ensures that the value to be rendered in the column is a string formatted according to the columns pattern
 * and parameters.
 *
 * The format of the string is defined by the columns 'string_format' option and any additional parameters, besides
 * the actual columns value, can be added and defined via the columns 'string_format_parameters' option.
 *
 * @see FormattedStringColumnType the column type that uses this formatter.
 */
class StringFormatterDataTransformer implements DataTransformerInterface {

	/**
	 * @param ColumnInterface $column
	 * @param                 $item
	 * @param mixed           $value
	 *            The value in the original representation
	 * @return mixed The value in the transformed representation
	 */
	public function transform(ColumnInterface $column, $item, $value) {
		$options = $column->getColumnOptions();
		$itemFormat = $options['string_format'];
		$additionalParameters = $options['string_format_parameters'];
		if(is_callable($itemFormat)) {
			$itemFormat = call_user_func($itemFormat, $item, $value, $column->getPath());
		}
		$values = array($value);
		if(is_callable($additionalParameters)) {
			// ah the parameters itself are callable and should return an array of key => values, so call the delegate
			$additionalParameters = call_user_func($additionalParameters, $item, $value, $column->getPath());
		}
		if(count($additionalParameters)) {
			// there are additional parameters!
			foreach($additionalParameters as $additionalParameterKey => $additionalParameterValue) {
				if(is_callable($additionalParameterValue)) {
					// the value is a delegate, so call it for retrieving the value
					$values[] = call_user_func($additionalParameterValue, $item, $value, $column->getPath());
				} else {
					$values[] = $additionalParameterValue;
				}
			}
		}
		return vsprintf($itemFormat, $values);
	}
}