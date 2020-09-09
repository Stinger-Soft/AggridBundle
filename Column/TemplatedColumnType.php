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

use StingerSoft\AggridBundle\Transformer\TwigDataTransformer;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TemplatedColumnType extends AbstractColumnType {

	protected $transformer;

	public function __construct(TwigDataTransformer $transformer) {
		$this->transformer = $transformer;
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver, array $gridOptions = []): void {
		$resolver->setRequired('template');
		$resolver->setAllowedTypes('template', 'string');

		$resolver->setDefault('mapped', false);
		$resolver->setAllowedTypes('mapped', 'boolean');

		$resolver->setDefault('render_html', true);
		$resolver->setAllowedTypes('render_html', 'boolean');

		$resolver->setDefault('additionalContext', []);
		$resolver->setAllowedTypes('additionalContext', 'array');

		$resolver->setDefault('searchable', AbstractColumnType::CLIENT_SIDE_ONLY);
		$resolver->setDefault('cellRenderer', 'RawHtmlRenderer');

		$resolver->setNormalizer('filter_options', static function (Options $options, $value) {
			if($value === null) {
				$value = [];
			}
			if(!isset($value['cellRenderer'])) {
				$value['cellRenderer'] = 'RawHtmlRenderer';
			}
			return $value;
		});

		$resolver->setDefault('exportValueFormatter', function (Options $options, $previousValue) {
			if($previousValue !== null) {
				return $previousValue;
			}
			if($options['mapped']) {
				return 'ValueFormatter';
			}
			return 'StripHtmlDisplayValueFormatter';
		});

		$resolver->setDefault('value_delegate', function ($item, $path, $options) {
			return $options['mapped'] ? $this->generateItemValue($item, $path, $options) : null;
		});
	}

	/**
	 * @inheritdoc
	 */
	public function buildData(ColumnInterface $column, array $options) {
		$column->addDataTransformer($this->transformer);
	}
}