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

trait NumberFormatterTrait {

	protected function getNumberFormatter($options) : \NumberFormatter {
		if($options['number_formatter_pattern'] === null) {
			$formatter = new \NumberFormatter($options['number_formatter_locale'], $options['number_formatter_style']);
		} else {
			$formatter = new \NumberFormatter($options['number_formatter_locale'], $options['number_formatter_style'], $options['number_formatter_pattern']);
		}
		if(isset($options['number_formatter_attributes']) && is_array($options['number_formatter_attributes'])) {
			foreach($options['number_formatter_attributes'] as $attributeName => $attributeValue) {
				$formatter->setAttribute($attributeName, $attributeValue);
			}
		}
		if(isset($options['number_formatter_text_attributes']) && is_array($options['number_formatter_text_attributes'])) {
			foreach($options['number_formatter_text_attributes'] as $attributeName => $attributeValue) {
				$formatter->setTextAttribute($attributeName, $attributeValue);
			}
		}
		return $formatter;
	}

}