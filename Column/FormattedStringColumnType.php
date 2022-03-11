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

use StingerSoft\AggridBundle\Transformer\StringFormatterDataTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Renders the data value of a cell using a formatted string.
 *
 * The parameters and string format can be defined via the options according to the PHP <code>printf</code> / <code>sprintf</code> / <code>vsprintf</code> syntax.
 */
class FormattedStringColumnType extends AbstractColumnType {

	/**
	 * @var StringFormatterDataTransformer
	 */
	protected $transformer;

	public function __construct(StringFormatterDataTransformer $transformer) {
		$this->transformer = $transformer;
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver, array $tableOptions = []): void {
		$resolver->setDefault('string_format', '%s');
		$resolver->setAllowedTypes('string_format', ['string', 'callable']);

		$resolver->setDefault('string_format_parameters', null);
		$resolver->setAllowedTypes('string_format_parameters', ['null', 'array', 'callable']);

		$resolver->setDefault('format_null', true);
		$resolver->setAllowedTypes('format_null', 'boolean');
	}

	/**
	 * @inheritdoc
	 */
	public function buildData(ColumnInterface $column, array $options) {
		$column->addDataTransformer($this->transformer);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getParent(): ?string {
		return StringColumnType::class;
	}

}