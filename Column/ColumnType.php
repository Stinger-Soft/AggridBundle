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

use StingerSoft\AggridBundle\Transformer\LinkDataTransformer;
use StingerSoft\AggridBundle\View\ColumnView;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ColumnType extends AbstractColumnType {

	/**
	 * @var LinkDataTransformer
	 */
	protected $linkTransformer;

	public function __construct(LinkDataTransformer $linkTransformer) {
		$this->linkTransformer = $linkTransformer;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \StingerSoft\AggridBundle\Column\ColumnTypeInterface::getParent()
	 */
	public function getParent(): ?string {
		return null;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \StingerSoft\AggridBundle\Column\ColumnTypeInterface::configureOptions()
	 */
	public function configureOptions(OptionsResolver $resolver, array $gridOptions = array()): void {
		$resolver->setDefault('path', null);
		$resolver->setAllowedTypes('path', array('null', 'string'));

		$resolver->setDefault('label', '');
		$resolver->setAllowedTypes('label', array(
			'string',
			'null'
		));

		$resolver->setDefault('translation_domain', null);
		$resolver->setAllowedTypes('translation_domain', array(
			'string',
			'null',
			'boolean'
		));

		$resolver->setDefault('value_delegate', null);
		$resolver->setAllowedTypes('value_delegate', array(
			'null',
			'callable'
		));
		$that = $this;
		$resolver->setNormalizer('value_delegate', static function(Options $options, $value) use ($that) {
			if($value === null) {
				$value = static function($item, $path, $options) use ($that) {
					return $that->generateItemValue($item, $path, $options);
				};
			}
			return $value;
		});

		$resolver->setDefault('query_path', null);
		$resolver->setAllowedTypes('query_path', array(
			'null',
			'string'
		));

		$resolver->setDefault('js_column_template', 'StingerSoftAggridBundle:Column:column.js.twig');
		$resolver->setAllowedTypes('js_column_template', 'string');

		$resolver->setDefault('orderable', true);
		$resolver->setAllowedValues('orderable', array(
			true,
			false,
		));

		$resolver->setDefault('filterable', true);
		$resolver->setAllowedValues('filterable', array(
			true,
			false,
		));

		$resolver->setDefault('filter_type', function(Options $options) {
			return null;
		});
		$resolver->setAllowedTypes('filter_type', array(
			'null',
			'string'
		));
		$resolver->setNormalizer('filter_type', function(Options $options, $value) {
			if($value !== null && !$options['filterable']) {
				throw new InvalidOptionsException(sprintf('When using "filter_type" with a value of "%s" you must set "filterable" to true!', $value));
			}
			return $value;
		});

		$resolver->setDefault('filter_options', array());
		$resolver->setAllowedTypes('filter_options', array(
			'array'
		));

		$resolver->setDefault('filter_query_path', null);
		$resolver->setAllowedTypes('filter_query_path', array(
			'null',
			'string'
		));

		$resolver->setDefault('resizable', true);
		$resolver->setAllowedValues('resizable', array(
			true,
			false,
		));

		$resolver->setDefault('rowGroup', false);
		$resolver->setAllowedValues('rowGroup', array(
			true,
			false,
		));
		$resolver->setNormalizer('rowGroup', static function(Options $options, $value) use ($gridOptions) {
			if($value !== false && !isset($gridOptions['enterpriseLicense'])) {
				throw new InvalidArgumentException('rowGroup is only available in the enterprise edition. Please set a license key!');
			}
			return $value;
		});

		$resolver->setDefault('enableRowGroup', false);
		$resolver->setAllowedValues('enableRowGroup', array(
			true,
			false,
		));
		$resolver->setNormalizer('enableRowGroup', static function(Options $options, $value) use ($gridOptions) {
			if($value !== false && !isset($gridOptions['enterpriseLicense'])) {
				throw new InvalidArgumentException('enableRowGroup is only available in the enterprise edition. Please set a license key!');
			}
			return $value;
		});

		$resolver->setDefault('pivot', false);
		$resolver->setAllowedValues('pivot', array(
			true,
			false,
		));
		$resolver->setNormalizer('pivot', static function(Options $options, $value) use ($gridOptions) {
			if($value !== false && !isset($gridOptions['enterpriseLicense'])) {
				throw new InvalidArgumentException('pivot is only available in the enterprise edition. Please set a license key!');
			}
			return $value;
		});

		$resolver->setDefault('enablePivot', false);
		$resolver->setAllowedValues('enablePivot', array(
			true,
			false,
		));
		$resolver->setNormalizer('enablePivot', static function(Options $options, $value) use ($gridOptions) {
			if($value !== false && !isset($gridOptions['enterpriseLicense'])) {
				throw new InvalidArgumentException('enablePivot is only available in the enterprise edition. Please set a license key!');
			}
			return $value;
		});

		$resolver->setDefault('aggFunc', false);
		$resolver->setAllowedTypes('aggFunc', ['bool', 'string']);
		$resolver->setNormalizer('aggFunc', static function(Options $options, $value) use ($gridOptions) {
			if($value !== false && !isset($gridOptions['enterpriseLicense'])) {
				throw new InvalidArgumentException('aggFunc is only available in the enterprise edition. Please set a license key!');
			}
			return $value;
		});



		$resolver->setDefault('visible', true);
		$resolver->setAllowedTypes('visible', array(
			'boolean'
		));

		$resolver->setDefault('editable', false);
		$resolver->setAllowedTypes('editable', array(
			'boolean'
		));

		$resolver->setDefault('includeDefinition', true);
		$resolver->setAllowedTypes('includeDefinition', array(
			'boolean'
		));

		$resolver->setNormalizer('rowGroup', static function(Options $options, $value) use ($gridOptions) {
			if($value === true && !isset($gridOptions['enterpriseLicense'])) {
				throw new InvalidArgumentException('rowGroup is only available in the enterprise edition. Please set a license key!');
			}
			return $value;
		});

		$resolver->setDefault('position', null);
		$resolver->setAllowedTypes('position', array(
			'null',
			'string',
			'array'
		));
		$resolver->setAllowedValues('position', static function ($valueToCheck) {
			if(is_string($valueToCheck)) {
				return !($valueToCheck !== 'last' && $valueToCheck !== 'first');
			}
			if(is_array($valueToCheck)) {
				return isset($valueToCheck['before']) || isset($valueToCheck['after']);
			}
			if($valueToCheck === null)
				return true;
			return false;
		});

	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \StingerSoft\AggridBundle\Column\ColumnTypeInterface::buildView()
	 */
	public function buildView(ColumnView $view, ColumnInterface $column, array $options): void {
		$view->path = $column->getPath();
		$view->template = $options['js_column_template'];

		$view->vars['label'] = $options['label'];
		$view->vars['translation_domain'] = $options['translation_domain'];
		$view->vars['filterable'] = $options['filterable'];
		$view->vars['orderable'] = $options['orderable'];
		$view->vars['rowGroup'] = $options['rowGroup'];
		$view->vars['enableRowGroup'] = $options['enableRowGroup'];
		$view->vars['pivot'] = $options['pivot'];
		$view->vars['enablePivot'] = $options['enablePivot'];
		$view->vars['aggFunc'] = $options['aggFunc'];
		$view->vars['resizable'] = $options['resizable'];
		$view->vars['visible'] = $options['visible'] && !$options['rowGroup'];
		$view->vars['editable'] = $options['editable'];
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \StingerSoft\AggridBundle\Column\ColumnTypeInterface::buildData()
	 */
	public function buildData(ColumnInterface $column, array $options) {
	}
}