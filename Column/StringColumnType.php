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

use StingerSoft\AggridBundle\Transformer\Nl2BrStringDataTransformer;
use StingerSoft\AggridBundle\Transformer\TranslateStringDataTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StringColumnType extends AbstractColumnType {
	/**
	 * @var Nl2BrStringDataTransformer
	 */
	protected $nl2BrStringDataTransformer;

	/**
	 * @var TranslateStringDataTransformer
	 */
	protected $translateStringDataTransformer;

	public function __construct(Nl2BrStringDataTransformer $nl2BrStringDataTransformer, TranslateStringDataTransformer $translateStringDataTransformer) {
		$this->nl2BrStringDataTransformer = $nl2BrStringDataTransformer;
		$this->translateStringDataTransformer = $translateStringDataTransformer;
	}

	/**
	 * @inheritdoc
	 *
	 * @see AbstractColumnType::configureOptions()
	 */
	public function configureOptions(OptionsResolver $resolver, array $tableOptions = array()) : void {
		$resolver->setDefault('nl2br', false);
		$resolver->setAllowedTypes('nl2br', 'boolean');

		$resolver->setDefault('value_translation_domain', false);
		$resolver->setAllowedTypes('value_translation_domain', array('string', 'boolean', 'null'));
	}

	/**
	 * @inheritdoc
	 */
	public function buildData(ColumnInterface $column, array $options) {
		if($options['value_translation_domain'] !== false) {
			$column->addDataTransformer($this->translateStringDataTransformer);
		}
		if(isset($options['nl2br']) && $options['nl2br'] === true) {
			$column->addDataTransformer($this->nl2BrStringDataTransformer);
		}

	}
}