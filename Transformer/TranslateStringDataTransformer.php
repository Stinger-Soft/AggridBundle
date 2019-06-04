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
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslateStringDataTransformer implements DataTransformerInterface {

	/**
	 * @var TranslatorInterface the translator to translate translatable strings.
	 */
	protected $translator;

	/**
	 * LinkDataTransformer constructor.
	 *
	 * @param TranslatorInterface $translator the translator to translate translatable strings, injected
	 */
	public function __construct(TranslatorInterface $translator) {
		$this->translator = $translator;
	}


	/**
	 * @param ColumnInterface $column
	 * @param                 $item
	 * @param mixed           $value
	 *            The value in the original representation
	 * @return mixed The value in the transformed representation
	 */
	public function transform(ColumnInterface $column, $item, $value) {
		$options = $column->getColumnOptions();
		if($options['value_translation_domain'] === true || $options['value_translation_domain'] === null) {
			if($options['translation_domain'] === null) {
				$tableOption = $column->getTableOptions();
				$value = $this->translator->trans($value, array(), $tableOption['translation_domain']);
			} else {
				$value = $this->translator->trans($value, array(), $options['translation_domain']);
			}
		} else if($options['value_translation_domain'] !== false) {
			$value = $this->translator->trans($value, array(), $options['value_translation_domain']);
		}
		return $value;
	}

}