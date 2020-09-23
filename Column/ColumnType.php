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

use Closure;
use StingerSoft\AggridBundle\Filter\SetFilterType;
use StingerSoft\AggridBundle\Filter\TextFilterType;
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
	 * {@inheritdoc}
	 */
	public function getParent(): ?string {
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver, array $gridOptions = []): void {
		$this->configureAggridOptions($resolver, $gridOptions);
		$this->configureStingerOptions($resolver, $gridOptions);
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildView(ColumnView $view, ColumnInterface $column, array $options): void {
		$view->path = $column->getPath();
		$view->template = $options['js_column_template'];
		$this->buildAgGridView($view, $column, $options);
		$this->buildStingerView($view, $column, $options);
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \StingerSoft\AggridBundle\Column\ColumnTypeInterface::buildData()
	 */
	public function buildData(ColumnInterface $column, array $options) {
		if($options['route']) {
			$column->addDataTransformer($this->linkTransformer, true);
		}
	}

	protected function configureStingerOptions(OptionsResolver $resolver, array $gridOptions = []): void {
		$resolver->setDefault('path', null);
		$resolver->setAllowedTypes('path', ['null', 'string']);

		$resolver->setDefault('label', '');
		$resolver->setAllowedTypes('label', [
			'string',
			'null',
		]);

		$resolver->setDefault('translation_domain', null);
		$resolver->setAllowedTypes('translation_domain', [
			'string',
			'null',
			'boolean',
		]);

		$resolver->setDefault('headerTooltip_translation_domain', null);
		$resolver->setAllowedTypes('headerTooltip_translation_domain', [
			'string',
			'null',
			'boolean',
		]);

		$resolver->setDefault('value_delegate', null);
		$resolver->setAllowedTypes('value_delegate', [
			'null',
			'callable',
		]);
		$that = $this;
		$resolver->setNormalizer('value_delegate', static function (Options $options, $value) use ($that) {
			if($value === null) {
				$value = static function ($item, $path, $options) use ($that) {
					return $that->generateItemValue($item, $path, $options);
				};
			}
			return $value;
		});

		$resolver->setDefault('query_path', null);
		$resolver->setAllowedTypes('query_path', [
			'null',
			'string',
		]);

		$resolver->setDefault('js_column_template', '@StingerSoftAggrid/Column/column.js.twig');
		$resolver->setAllowedTypes('js_column_template', 'string');

		$resolver->setDefault('orderable', true);
		$resolver->setAllowedValues('orderable', [
			true,
			false,
			AbstractColumnType::CLIENT_SIDE_ONLY,
			AbstractColumnType::SERVER_SIDE_ONLY,
		]);

		$resolver->setDefault('searchable', true);
		$resolver->setAllowedValues('searchable', [
			true,
			false,
			AbstractColumnType::CLIENT_SIDE_ONLY,
			AbstractColumnType::SERVER_SIDE_ONLY,
		]);

		$resolver->setDefault('filterable', true);
		$resolver->setAllowedValues('filterable', [
			true,
			false,
			AbstractColumnType::CLIENT_SIDE_ONLY,
			AbstractColumnType::SERVER_SIDE_ONLY,
		]);

		$resolver->setDefault('providesIdentity', false);
		$resolver->setAllowedTypes('providesIdentity', 'bool');

		$resolver->setDefault('identityValueGetter', 'ValueGetter');
		$resolver->setAllowedTypes('identityValueGetter', 'string');

		$resolver->setDefault('exportable', true);
		$resolver->setAllowedTypes('exportable', 'bool');

		$resolver->setDefault('exportValueFormatter', static function (Options $options, $previousValue) {
			if($previousValue !== null) {
				return $previousValue;
			}
			if($options['route']) {
				return 'StripHtmlDisplayValueFormatter';
			}
			return null;
		});
		$resolver->setAllowedTypes('exportValueFormatter', ['null', 'string']);

		$resolver->setDefault('filter_type', static function (Options $options, $previousValue) use ($gridOptions) {
			if($previousValue !== null) {
				return $previousValue;
			}
			if(isset($options['filterable']) && $options['filterable']) {
				if(isset($gridOptions['enterpriseLicense'])) {
					return SetFilterType::class;
				}
				return TextFilterType::class;
			}
			return null;
		});

		$resolver->setAllowedTypes('filter_type', [
			'null',
			'string',
		]);

		$resolver->setDefault('filter_options', []);
		$resolver->setAllowedTypes('filter_options', [
			'array',
		]);

		$resolver->setDefault('filter_query_path', null);
		$resolver->setAllowedTypes('filter_query_path', [
			'null',
			'string',
		]);

		$resolver->setDefault('order_server_delegate', null);
		$resolver->setAllowedTypes('order_server_delegate', ['null', 'callable', Closure::class]);

		$resolver->setDefault('search_server_delegate', null);
		$resolver->setAllowedTypes('search_server_delegate', ['null', 'callable', Closure::class]);

		$resolver->setDefault('tokenize_search_term', false);
		$resolver->setAllowedTypes('tokenize_search_term', ['bool', 'string']);
		$resolver->setNormalizer('tokenize_search_term',  static function (Options $options, $value) {
			if($value === '') {
				throw new InvalidOptionsException('An empty string cannot be used as a token!');
			}
			return $value;
		});

		$resolver->setDefault('tokenize_search_conjunction', ColumnTypeInterface::SEARCH_OPERATOR_AND);
		$resolver->setAllowedValues('tokenize_search_conjunction', [ColumnTypeInterface::SEARCH_OPERATOR_AND, ColumnTypeInterface::SEARCH_OPERATOR_OR]);

		$resolver->setDefault('position', null);
		$resolver->setAllowedTypes('position', [
			'null',
			'string',
			'array',
		]);
		$resolver->setAllowedValues('position', static function ($valueToCheck) {
			if(is_string($valueToCheck)) {
				return !($valueToCheck !== 'last' && $valueToCheck !== 'first');
			}
			if(is_array($valueToCheck)) {
				return isset($valueToCheck['before']) || isset($valueToCheck['after']);
			}
			if($valueToCheck === null) {
				return true;
			}
			return false;
		});
		$resolver->setDefault('route', null);
		$resolver->setAllowedTypes('route', [
			'string',
			'array',
			'callable',
			'null',
		]);
		$resolver->setNormalizer('route', static function (Options $options, $value) {
			if(is_array($value)) {
				if(!array_key_exists('route', $value)) {
					throw new InvalidOptionsException('When using "route" option with an array value, you must add a "route" key pointing to the route to be used!');
				}
				if(!array_key_exists('route_params', $value)) {
					$value['route_params'] = [];
				}
			}
			return $value;
		});

		$resolver->setDefault('comparator', 'ValueComparator');
		$resolver->setAllowedTypes('comparator', 'string');

		$resolver->setDefault('renderable', true);
		$resolver->setAllowedTypes('renderable', 'boolean');

		$resolver->setDefault('clipboardValueFormatter', 'DisplayValueFormatter');
		$resolver->setAllowedTypes('clipboardValueFormatter', 'string');
	}

	protected function configureAggridOptions(OptionsResolver $resolver, array $gridOptions = []): void {
		$resolver->setDefault('width', null);
		$resolver->setAllowedTypes('width', [
			'integer',
			'null',
		]);
		$resolver->setDefault('minWidth', null);
		$resolver->setAllowedTypes('minWidth', [
			'integer',
			'null',
		]);
		$resolver->setDefault('maxWidth', null);
		$resolver->setAllowedTypes('maxWidth', [
			'integer',
			'null',
		]);
		$resolver->setDefault('resizable', true);
		$resolver->setAllowedValues('resizable', [
			true,
			false,
		]);

		$resolver->setDefault('rowGroup', false);
		$resolver->setAllowedValues('rowGroup', [
			true,
			false,
		]);
		$resolver->setNormalizer('rowGroup', static function (Options $options, $value) use ($gridOptions) {
			if($value !== false && !isset($gridOptions['enterpriseLicense'])) {
				throw new InvalidArgumentException('rowGroup is only available in the enterprise edition. Please set a license key!');
			}
			return $value;
		});

		$resolver->setDefault('enableValue', false);
		$resolver->setAllowedTypes('enableValue', 'bool');

		$resolver->setDefault('enableRowGroup', false);
		$resolver->setAllowedValues('enableRowGroup', [
			true,
			false,
		]);
		$resolver->setNormalizer('enableRowGroup', static function (Options $options, $value) use ($gridOptions) {
			if($value !== false && !isset($gridOptions['enterpriseLicense'])) {
				throw new InvalidArgumentException('enableRowGroup is only available in the enterprise edition. Please set a license key!');
			}
			return $value;
		});

		$resolver->setDefault('pivot', false);
		$resolver->setAllowedValues('pivot', [
			true,
			false,
		]);
		$resolver->setNormalizer('pivot', static function (Options $options, $value) use ($gridOptions) {
			if($value !== false && !isset($gridOptions['enterpriseLicense'])) {
				throw new InvalidArgumentException('pivot is only available in the enterprise edition. Please set a license key!');
			}
			return $value;
		});

		$resolver->setDefault('enablePivot', false);
		$resolver->setAllowedValues('enablePivot', [
			true,
			false,
		]);
		$resolver->setNormalizer('enablePivot', static function (Options $options, $value) use ($gridOptions) {
			if($value !== false && !isset($gridOptions['enterpriseLicense'])) {
				throw new InvalidArgumentException('enablePivot is only available in the enterprise edition. Please set a license key!');
			}
			return $value;
		});

		$resolver->setDefault('aggFunc', false);
		$resolver->setAllowedTypes('aggFunc', ['bool', 'string']);
		$resolver->setNormalizer('aggFunc', static function (Options $options, $value) use ($gridOptions) {
			if($value !== false && !isset($gridOptions['enterpriseLicense'])) {
				throw new InvalidArgumentException('aggFunc is only available in the enterprise edition. Please set a license key!');
			}
			return $value;
		});

		$resolver->setDefault('visible', true);
		$resolver->setAllowedTypes('visible', [
			'boolean',
		]);

		$resolver->setDefault('editable', false);
		$resolver->setAllowedTypes('editable', [
			'boolean',
		]);

		$resolver->setDefault('includeDefinition', true);
		$resolver->setAllowedTypes('includeDefinition', [
			'boolean',
		]);

		$resolver->setNormalizer('rowGroup', static function (Options $options, $value) use ($gridOptions) {
			if($value === true && !isset($gridOptions['enterpriseLicense'])) {
				throw new InvalidArgumentException('rowGroup is only available in the enterprise edition. Please set a license key!');
			}
			return $value;
		});

		$resolver->setDefault('menuTabs', static function (Options $options, $previousValue) use ($gridOptions) {
			return $previousValue ?? $gridOptions['menuTabs'];
		});
		$resolver->setAllowedTypes('menuTabs', ['null', 'array']);
		$resolver->setNormalizer('menuTabs', static function (Options $options, $value) {
			if($value === null) {
				return null;
			}
			if(is_array($value)) {
				foreach($value as $item) {
					if(!in_array($item, ColumnTypeInterface::MENU_TABS, true)) {
						throw new InvalidArgumentException(sprintf('"%s" is not a valid option for menu tabs, use on or multiple of "%s" constants instead!', $item, ColumnTypeInterface::class . '::MENU_TAB*'));
					}
				}
				return $value;
			}
			throw new InvalidArgumentException('menuTabs may only be null or an array containing constants of ' . ColumnTypeInterface::class);
		});

		$resolver->setDefault('suppressMenu', false);
		$resolver->setAllowedTypes('suppressMenu', ['bool']);

		$resolver->setDefault('autoHeight', null);
		$resolver->setAllowedTypes('autoHeight', ['null', 'bool']);

		$resolver->setDefault('valueFormatter', null);
		$resolver->setAllowedTypes('valueFormatter', ['null', 'string']);

		$resolver->setDefault('valueGetter', null);
		$resolver->setAllowedTypes('valueGetter', ['null', 'string']);

		$resolver->setDefault('filterValueGetter', null);
		$resolver->setAllowedTypes('filterValueGetter', ['null', 'string']);

		$resolver->setDefault('quickFilter', null);
		$resolver->setAllowedTypes('quickFilter', ['null', 'string']);

		$resolver->setDefault('keyCreator', null);
		$resolver->setAllowedTypes('keyCreator', ['null', 'string']);

		$resolver->setDefault('tooltipField', null);
		$resolver->setAllowedTypes('tooltipField', ['null', 'string']);

		$resolver->setDefault('tooltip', null);
		$resolver->setAllowedTypes('tooltip', ['null', 'string']);

		$resolver->setDefault('headerTooltip', null);
		$resolver->setAllowedTypes('headerTooltip', ['null', 'string']);

		$resolver->setDefault('checkboxSelection', false);
		$resolver->setAllowedTypes('checkboxSelection', ['bool', 'string']);

		$resolver->setDefault('headerCheckboxSelection', false);
		$resolver->setAllowedTypes('headerCheckboxSelection', 'bool');

		$resolver->setDefault('headerCheckboxSelectionFilteredOnly', false);
		$resolver->setAllowedTypes('headerCheckboxSelectionFilteredOnly', 'bool');

		$resolver->setDefault('cellRenderer', null);
		$resolver->setDefault('cellRenderer', static function (Options $options, $previousValue) {
			if($previousValue === null && $options['route'] !== null) {
				return 'RawHtmlRenderer';
			}
			return $previousValue;
		});
		$resolver->setAllowedTypes('cellRenderer', ['null', 'string']);

		$resolver->setDefault('cellRendererParams', null);
		$resolver->setAllowedTypes('cellRendererParams', ['null', 'array']);

		$resolver->setDefault('columnGroupShow', null);
		$resolver->setAllowedValues('columnGroupShow', [null, 'closed', 'open', true]);

		$resolver->setDefault('cellStyle', null);
		$resolver->setAllowedTypes('cellStyle', ['null', 'string', 'array']);

		$resolver->setDefault('cellClass', null);
		$resolver->setAllowedTypes('cellClass', ['null', 'string', 'array']);

		$resolver->setDefault('cellClassRules', null);
		$resolver->setAllowedTypes('cellClassRules', ['null', 'string', 'array']);

		$resolver->setDefault('headerClass', null);
		$resolver->setAllowedTypes('headerClass', ['null', 'string', 'array']);

		$resolver->setDefault('toolPanelClass', null);
		$resolver->setAllowedTypes('toolPanelClass', ['null', 'string', 'array']);
	}

	/** @noinspection PhpUnusedParameterInspection */
	protected function buildStingerView(ColumnView $view, ColumnInterface $column, array $options): void {
		$view->vars['label'] = $options['label'];
		$view->vars['translation_domain'] = $options['translation_domain'];
		$view->vars['headerTooltip_translation_domain'] = $options['headerTooltip_translation_domain'] ?? $options['translation_domain'];
		$view->vars['exportable'] = $options['exportable'];
		$view->vars['exportValueFormatter'] = $options['exportValueFormatter'];
		$view->vars['route'] = $options['route'];
		$view->vars['renderable'] = $options['renderable'];
		$view->vars['clipboardValueFormatter'] = $options['clipboardValueFormatter'];
		$view->vars['providesIdentity'] = $options['providesIdentity'];
		$view->vars['identityValueGetter'] = $options['identityValueGetter'];
	}

	protected function buildAgGridView(ColumnView $view, ColumnInterface $column, array $options): void {
		$dataMode = $column->getGridOptions()['dataMode'];
		$view->vars['searchable'] = AbstractColumnType::getBooleanValueDependingOnClientOrServer($options['searchable'], $dataMode);
		$view->vars['filterable'] = AbstractColumnType::getBooleanValueDependingOnClientOrServer($options['filterable'], $dataMode);
		$view->vars['orderable'] = AbstractColumnType::getBooleanValueDependingOnClientOrServer($options['orderable'], $dataMode);

		$view->vars['pivot'] = $options['pivot'];
		$view->vars['enablePivot'] = $options['enablePivot'];
		$view->vars['aggFunc'] = $options['aggFunc'];
		$view->vars['resizable'] = $options['resizable'];
		$view->vars['width'] = $options['width'];
		$view->vars['minWidth'] = $options['minWidth'];
		$view->vars['maxWidth'] = $options['maxWidth'];
		$view->vars['visible'] = $options['visible'] && !$options['rowGroup'];
		$view->vars['rowGroup'] = $options['rowGroup'];
		$view->vars['menuTabs'] = $options['menuTabs'];
		$view->vars['enableRowGroup'] = $options['enableRowGroup'];
		$view->vars['enableValue'] = $options['enableValue'];
		$view->vars['editable'] = $options['editable'];
		$view->vars['valueFormatter'] = $options['valueFormatter'];
		$view->vars['filterValueGetter'] = $options['filterValueGetter'];
		$view->vars['keyCreator'] = $options['keyCreator'];
		$view->vars['quickFilter'] = $options['quickFilter'];
		$view->vars['valueGetter'] = $options['valueGetter'];
		$view->vars['autoHeight'] = $options['autoHeight'];
		$view->vars['suppressMenu'] = $options['suppressMenu'];
		$view->vars['tooltipField'] = $options['tooltipField'];
		$view->vars['tooltip'] = $options['tooltip'];
		$view->vars['headerTooltip'] = $options['headerTooltip'];
		$view->vars['checkboxSelection'] = $options['checkboxSelection'];
		$view->vars['headerCheckboxSelection'] = $options['headerCheckboxSelection'];
		$view->vars['headerCheckboxSelectionFilteredOnly'] = $options['headerCheckboxSelectionFilteredOnly'];
		$view->vars['cellRenderer'] = $options['cellRenderer'];
		$view->vars['cellRendererParams'] = $options['cellRendererParams'];
		$view->vars['columnGroupShow'] = $options['columnGroupShow'];
		$view->vars['cellStyle'] = $options['cellStyle'];
		$view->vars['cellClass'] = $options['cellClass'];
		$view->vars['cellClassRules'] = $options['cellClassRules'];
		$view->vars['headerClass'] = $options['headerClass'];
		$view->vars['toolPanelClass'] = $options['toolPanelClass'];
		$view->vars['comparator'] = $options['comparator'];
	}
}
