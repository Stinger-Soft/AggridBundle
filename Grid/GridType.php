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

namespace StingerSoft\AggridBundle\Grid;

use Doctrine\ORM\QueryBuilder;
use StingerSoft\AggridBundle\Column\ColumnTypeInterface;
use StingerSoft\AggridBundle\Filter\FilterType;
use StingerSoft\AggridBundle\StingerSoftAggridBundle;
use StingerSoft\AggridBundle\View\GridView;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GridType extends AbstractGridType {

	public const DATA_MODE_INLINE = 'inline';
	public const DATA_MODE_AJAX = 'ajax';
	public const DATA_MODE_ENTERPRISE = 'enterprise';

	public const COLUMN_AUTO_SIZE_TO_FIT = 'sizeToFit';
	public const COLUMN_AUTO_SIZE_ALL = 'all';

	/** @var string|null */
	protected $licenseKey;

	public function __construct(ParameterBagInterface $parameterBag) {
		if($parameterBag->has(StingerSoftAggridBundle::PARAMETER_LICENSE_KEY)) {
			$this->licenseKey = $parameterBag->get(StingerSoftAggridBundle::PARAMETER_LICENSE_KEY);
		}
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
	public function buildView(GridView $view, GridInterface $grid, array $gridOptions, array $columns): void {
		$this->configureDefaultViewValues($view, $gridOptions, $columns);
		$this->configureAggridViewValues($view, $gridOptions);
		$this->configureStingerViewValues($view, $gridOptions, $columns);
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver): void {
		$this->configureStingerOptions($resolver);
		$this->configureAggridOptions($resolver);
	}

	/** @noinspection PhpUnusedParameterInspection */
	protected function configureDefaultViewValues(GridView $view, array $gridOptions, array $columns): void {
		$view->vars['id'] = $gridOptions['attr']['id'] = $view->getGridId();
		$view->vars['aggrid_id'] = str_replace('-', '_', $view->vars['id']);
		$view->vars['aggrid_js_id'] = str_replace([' ', '#'], ['_', ''], $view->vars['aggrid_id']);
		$view->vars['stingerSoftAggrid_js_var'] = 'stingerSoftAggrid' . $view->vars['aggrid_js_id'];
		$view->vars['ajax_url'] = $gridOptions['ajax_url'];
		$view->vars['dataMode'] = $gridOptions['dataMode'];
		if(!$gridOptions['autoHeight']) {
			$gridOptions['attr']['style'] = 'height: ' . $gridOptions['height'];
		} else {
			$view->vars['domLayout'] = 'autoHeight';
		}
		$gridOptions['attr']['class'] = $gridOptions['theme'];
		$view->vars['attr'] = $gridOptions['attr'];

	}

	protected function configureAggridViewValues(GridView $view, array $gridOptions): void {
		$view->vars['enableBrowserTooltips'] = $gridOptions['enableBrowserTooltips'];
		$view->vars['enableRangeSelection'] = $gridOptions['enableRangeSelection'];
		$view->vars['enterpriseLicense'] = $gridOptions['enterpriseLicense'];
		$view->vars['treeData'] = $gridOptions['treeData'];
		$view->vars['sideBar'] = $gridOptions['sideBar'];
		$view->vars['sideBarOptions'] = [
			'defaultToolPanel' => $gridOptions['sideBarOptions']['defaultToolPanel'] ?? null,
			'position'         => $gridOptions['sideBarOptions']['position'] ?? null,
			'hiddenByDefault'  => $gridOptions['sideBarOptions']['hiddenByDefault'] ?? null,
		];
		$view->vars['cacheBlockSize'] = $gridOptions['cacheBlockSize'];
		$view->vars['pagination'] = $gridOptions['pagination'];
		$view->vars['paginationPageSize'] = $gridOptions['paginationPageSize'];
		$view->vars['paginationAutoPageSize'] = $gridOptions['paginationAutoPageSize'];
		$view->vars['suppressPaginationPanel'] = $gridOptions['suppressPaginationPanel'];
		$view->vars['icons'] = $gridOptions['icons'];
		$view->vars['suppressCsvExport'] = $gridOptions['suppressCsvExport'];
		$view->vars['suppressExcelExport'] = $gridOptions['suppressExcelExport'];
		$view->vars['rowHeight'] = $gridOptions['rowHeight'];
		$view->vars['rowStyle'] = $gridOptions['rowStyle'];
		$view->vars['getRowStyle'] = $gridOptions['getRowStyle'];
		$view->vars['rowClass'] = $gridOptions['rowClass'];
		$view->vars['getRowClass'] = $gridOptions['getRowClass'];
		$view->vars['rowClassRules'] = $gridOptions['rowClassRules'];
		$view->vars['rowSelection'] = $gridOptions['rowSelection'];
		$view->vars['rowMultiSelectWithClick'] = $gridOptions['rowMultiSelectWithClick'];
		$view->vars['suppressRowClickSelection'] = $gridOptions['suppressRowClickSelection'];
		$view->vars['nativeOptions'] = $gridOptions['nativeOptions'];
		$view->vars['getRowNodeId'] = $gridOptions['getRowNodeId'];
		$view->vars['components'] = $gridOptions['components'];
	}

	protected function configureStingerViewValues(GridView $view, array $gridOptions, array $columns): void {
		$view->vars['translation_domain'] = $gridOptions['translation_domain'];
		$view->vars['total_results_query_builder'] = $gridOptions['total_results_query_builder'];
		$view->vars['default_order_property'] = $gridOptions['default_order_property'];
		$view->vars['default_order_direction'] = $gridOptions['default_order_direction'];
		$view->vars['default_orders'] = $gridOptions['default_orders'];
		$view->vars['persistState'] = $gridOptions['persistState'];
		$view->vars['searchEnabled'] = $gridOptions['searchEnabled'];
		$view->vars['paginationDropDown'] = $gridOptions['paginationDropDown'];
		$view->vars['reloadButton'] = $gridOptions['reloadButton'];
		$view->vars['clearFilterButton'] = $gridOptions['clearFilterButton'];
		$view->vars['autosizeColumnsButton'] = $gridOptions['autosizeColumnsButton'];
		$view->vars['autoResizeColumns'] = $gridOptions['autoResizeColumns'];
		$view->vars['autoResizeManuallyResizedColumns'] = $gridOptions['autoResizeManuallyResizedColumns'];
		$view->vars['autoResizeFixedWidthColumns'] = $gridOptions['autoResizeFixedWidthColumns'];
		$view->vars['form_id'] = $gridOptions['form_id'];
		$view->vars['templateTopBar'] = $gridOptions['templateTopBar'];

		if($gridOptions['versionHash'] === true) {
			$hashing = hash_init('sha256', HASH_HMAC, 'stingersoft-aggrid');
			foreach($columns as $column) {
				hash_update($hashing, (string)$column->getHashCode());
			}
			if($gridOptions['versionHashModifier'] !== null) {
				hash_update($hashing, $gridOptions['versionHashModifier']);
			}
			$gridOptions['versionHash'] = hash_final($hashing);
		}
		$view->vars['versionHash'] = $gridOptions['versionHash'];
		$view->vars['applyCellrendererOnPivotHeader'] = $gridOptions['applyCellrendererOnPivotHeader'];
	}

	protected function configureStingerOptions(OptionsResolver $resolver): void {
		$resolver->setDefault('translation_domain', 'messages');
		$resolver->setAllowedTypes('translation_domain', [
			'string',
			'null',
			'boolean',
		]);
		$resolver->setDefault('total_results_query_builder', null);
		$resolver->setAllowedTypes('total_results_query_builder', ['null', QueryBuilder::class]);

		$resolver->setDefault('default_order_property', 'id');
		$resolver->setAllowedTypes('default_order_property', ['null', 'string', 'null']);
		$resolver->setDefault('default_order_direction', 'asc');
		$resolver->setAllowedValues('default_order_direction', ['asc', 'desc']);

		$resolver->setDefault('default_orders', null);
		$resolver->setAllowedTypes('default_orders', ['array', 'null']);
		$resolver->setNormalizer('default_orders', static function (Options $options, $valueToNormalize) {
			if(is_array($valueToNormalize) && count($valueToNormalize) > 0) {
				foreach($valueToNormalize as $key => $direction) {
					$hasValidKey = is_string($key);
					$fixedDirection = strtolower(trim($direction));
					$hasValidDirection = $fixedDirection === 'asc' || $fixedDirection === 'desc';
					if(!$hasValidKey || !$hasValidDirection) {
						$message = '';
						if(!$hasValidKey) {
							$message = sprintf('The option "%s" with value [%s => %s] is invalid. Entry keys must be strings as they represent query paths and not %s! ', 'default_orders', $key, $direction, $key);
						}
						if(!$hasValidDirection) {
							$message .= sprintf('The option "%s" with value [%s => %s] is invalid. Entry values must either be "asc" or "desc" but not %s!', 'default_orders', $key, $direction, $direction);
						}
						throw new InvalidOptionsException($message);
					}
				}
			}
			return $valueToNormalize;
		});

		$resolver->setDefault('height', '50vh');

		$resolver->setDefault('hydrateAsObject', true);
		$resolver->setAllowedTypes('hydrateAsObject', [
			'boolean',
		]);

		$resolver->setDefault('queryHints', null);
		$resolver->setAllowedTypes('queryHints', [
			'null',
			'array',
		]);

		$resolver->setDefault('persistState', false);
		$resolver->setAllowedTypes('persistState', ['boolean']);

		$resolver->setDefault('searchEnabled', true);
		$resolver->setAllowedTypes('searchEnabled', ['boolean']);

		$resolver->setDefault('paginationDropDown', static function (Options $options, $previousValue) {
			if($previousValue === null && $options['pagination']) {
				return [10, 25, 50, 75, 100, 150, 200, 250, 500];
			}
			return $previousValue;
		});
		$resolver->setAllowedTypes('paginationDropDown', ['null', 'array']);

		$resolver->setDefault('reloadButton', false);
		$resolver->setAllowedTypes('reloadButton', ['boolean']);

		$resolver->setDefault('clearFilterButton', false);
		$resolver->setAllowedTypes('clearFilterButton', ['boolean']);

		$resolver->setDefault('autosizeColumnsButton', false);
		$resolver->setAllowedTypes('autosizeColumnsButton', ['boolean']);

		$resolver->setDefault('versionHash', static function (Options $options, $previousValue) {
			if($previousValue === null && $options['persistState'] === true) {
				return true;
			}
			return $previousValue;
		});
		$resolver->setAllowedTypes('versionHash', ['bool', 'null', 'string']);

		$resolver->setDefault('templateTopBar', '@StingerSoftAggrid/Grid/grid_search_pagination.html.twig');
		$resolver->setAllowedTypes('templateTopBar', 'string');

		$resolver->setDefault('versionHashModifier', null);
		$resolver->setAllowedTypes('versionHashModifier', ['null', 'string']);

		$resolver->setDefault('form_id', null);
		$resolver->setAllowedTypes('form_id', ['null', 'string']);

		$resolver->setDefault('filterNewRowsAction', null);
		$resolver->setAllowedValues('filterNewRowsAction', [
			null,
			FilterType::NEW_ROWS_ACTION_DEFAULT,
			FilterType::NEW_ROWS_ACTION_KEEP,
		]);

		$resolver->setDefault('autoResizeColumns', false);
		$resolver->setAllowedValues('autoResizeColumns', [
			false, self::COLUMN_AUTO_SIZE_ALL, self::COLUMN_AUTO_SIZE_TO_FIT,
		]);

		$resolver->setDefault('autoResizeManuallyResizedColumns', false);
		$resolver->setAllowedTypes('autoResizeManuallyResizedColumns', 'bool');
		$resolver->setDefault('autoResizeFixedWidthColumns', false);
		$resolver->setAllowedTypes('autoResizeFixedWidthColumns', 'bool');
	}

	protected function configureAggridOptions(OptionsResolver $resolver): void {
		$resolver->setDefault('components', null);
		$resolver->setAllowedTypes('components', ['null', 'array']);

		$resolver->setDefault('enableBrowserTooltips', true);
		$resolver->setAllowedTypes('enableBrowserTooltips', 'bool');

		$resolver->setDefault('enableRangeSelection', false);
		$resolver->setAllowedTypes('enableRangeSelection', 'bool');

		$resolver->setDefault('theme', 'ag-theme-balham');
		$resolver->setAllowedTypes('theme', 'string');

		$resolver->setDefault('dataMode', self::DATA_MODE_INLINE);
		$resolver->setAllowedValues('dataMode', [
			self::DATA_MODE_INLINE,
			self::DATA_MODE_AJAX,
			self::DATA_MODE_ENTERPRISE,
		]);

		$resolver->setDefault('ajax_url', null);
		$resolver->setAllowedTypes('ajax_url', [
			'string',
			'null',
		]);

		$resolver->setNormalizer('ajax_url', static function (Options $options, $valueToNormalize) {
			if($valueToNormalize === null && $options['dataMode'] !== self::DATA_MODE_INLINE) {
				throw new InvalidOptionsException('When using "dataMode"  with a value of ajax or enterprise you must set "ajax_url"!');
			}
			return $valueToNormalize;
		});

		$resolver->setDefault('ajax_method', 'POST');
		$resolver->setAllowedValues('ajax_method', [
			'GET',
			'POST',
		]);

		$resolver->setDefault('enterpriseLicense', function (Options $options, $previousValue) {
			return $previousValue ?? $this->licenseKey;
		});
		$resolver->setAllowedTypes('enterpriseLicense', [
			'string',
			'null',
		]);

		$resolver->setDefault('treeData', false);
		$resolver->setAllowedValues('treeData', [
			true,
			false,
		]);
		$resolver->setNormalizer('treeData', static function (Options $options, $value) {
			if($value !== false && !isset($options['enterpriseLicense'])) {
				throw new InvalidArgumentException('treeData is only available in the enterprise edition. Please set a license key!');
			}
			return $value;
		});

		$resolver->setDefault('autoHeight', false);
		$resolver->setAllowedTypes('autoHeight', 'bool');

		$addSideBarOptions = static function (OptionsResolver $sidebarResolver) {
			$sidebarResolver->setDefault('defaultToolPanel', null);
			$sidebarResolver->setAllowedTypes('defaultToolPanel', ['null', 'string']);

			$sidebarResolver->setDefault('position', null);
			$sidebarResolver->setAllowedValues('position', [null, 'right', 'left']);

			$sidebarResolver->setDefined('hiddenByDefault');
			$sidebarResolver->setAllowedValues('hiddenByDefault', [null, true]);
		};
		$resolver->setDefault('sideBarOptions', static function (OptionsResolver $sidebarResolver) use ($addSideBarOptions) {
			$addSideBarOptions($sidebarResolver);
		});
		$resolver->setAllowedTypes('sideBarOptions', 'array');
		$resolver->setNormalizer('sideBarOptions', static function (Options $options, $valueToNormalize) use ($addSideBarOptions) {
			$sidebarResolver = new OptionsResolver();
			$addSideBarOptions($sidebarResolver);
			$sidebarResolver->resolve($valueToNormalize);
			return $valueToNormalize;
		});

		$resolver->setDefault('sideBar', false);
		$resolver->setAllowedValues('sideBar', static function ($valueToCheck) {
			if($valueToCheck === 'columns' || $valueToCheck === 'filters') {
				return true;
			}
			if($valueToCheck === false || $valueToCheck === true) {
				return true;
			}
			if(is_array($valueToCheck)) {
				return true;
			}
			return false;
		});
		$resolver->setNormalizer('sideBar', function (Options $options, $valueToNormalize) {
			if($valueToNormalize !== false && !isset($options['enterpriseLicense'])) {
				throw new InvalidArgumentException('sideBar is only available in the enterprise edition. Please set a license key!');
			}
			if(is_array($valueToNormalize)) {
				return $this->validateSideBarOptions($options, $valueToNormalize);
			}
			return $valueToNormalize;
		});
		$resolver->setDeprecated('sideBar', 'Add components to the sidebar by using the GridBuilder::addComponent method!');

		$resolver->setDefault('menuTabs', null);
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

		$resolver->setDefault('cacheBlockSize', 100);
		$resolver->setAllowedTypes('cacheBlockSize', 'int');

		$resolver->setDefault('pagination', false);
		$resolver->setAllowedValues('pagination', [
			true,
			false,
		]);
		$resolver->setDefault('paginationPageSize', 100);
		$resolver->setAllowedTypes('paginationPageSize', 'int');
		$resolver->setDefault('paginationAutoPageSize', false);
		$resolver->setAllowedTypes('paginationAutoPageSize', 'bool');
		$resolver->setDefault('suppressPaginationPanel', false);
		$resolver->setAllowedTypes('suppressPaginationPanel', 'bool');

		$resolver->setDefault('suppressCsvExport', true);
		$resolver->setAllowedTypes('suppressCsvExport', 'bool');

		$resolver->setDefault('suppressExcelExport', true);
		$resolver->setAllowedTypes('suppressExcelExport', 'bool');
		$resolver->setNormalizer('suppressExcelExport', static function (Options $options, $value) {
			if($value === false && !isset($options['enterpriseLicense'])) {
				throw new InvalidArgumentException('suppressExcelExport is only available in the enterprise edition. Please set a license key!');
			}
			return $value;
		});

		$resolver->setDefault('rowStyle', null);
		$resolver->setAllowedTypes('rowStyle', ['null', 'string']);
		$resolver->setDefault('getRowStyle', null);
		$resolver->setAllowedTypes('getRowStyle', ['null', 'string']);
		$resolver->setDefault('rowClass', null);
		$resolver->setAllowedTypes('rowClass', ['null', 'string']);
		$resolver->setDefault('getRowClass', null);
		$resolver->setAllowedTypes('getRowClass', ['null', 'string']);
		$resolver->setDefault('rowClassRules', null);
		$resolver->setAllowedTypes('rowClassRules', ['null', 'string', 'array']);
		$resolver->setDefault('rowHeight', null);
		$resolver->setAllowedTypes('rowHeight', ['null', 'int']);

		$resolver->setDefault('rowSelection', null);
		$resolver->setAllowedValues('rowSelection', [null, 'single', 'multiple']);
		$resolver->setDefault('rowMultiSelectWithClick', false);
		$resolver->setAllowedTypes('rowMultiSelectWithClick', 'boolean');
		$resolver->setDefault('suppressRowClickSelection', false);
		$resolver->setAllowedTypes('suppressRowClickSelection', 'boolean');

		//Possible icons: https://www.ag-grid.com/javascript-grid-icons/
		$resolver->setDefault('icons', [
			'sortAscending' => '<i class="fas fa-sort-amount-up"></i>',
			'sortDescending' => '<i class="fas fa-sort-amount-down"></i>',
			'menu' => '<i class="far fa-bars" style="width: 12px;"></i>',
			'menuPin' => '<i class="far fa-thumbtack"></i>',
			'filter' => '<i class="far fa-filter"></i>',
			'columns' => '<i class="far fa-columns"></i>',
			'columnMoveMove' => '<i class="far fa-arrows-alt"></i>',
			'dropNotAllowed' => '<i class="far fa-ban"></i>',
			//			'checkboxChecked'       => '<i class="far fa-check-square" style="font-size: 1.3em;"></i>',
			//			'checkboxUnchecked'     => '<i class="far fa-square" style="font-size: 1.3em;"></i>',
			//			'checkboxIndeterminate' => '<i class="far fa-minus-square" style="font-size: 1.3em;"></i>',
		]);
		$resolver->setAllowedTypes('icons', ['array', 'null']);

		$resolver->setDefault('nativeOptions', false);
		$resolver->setDefault('getRowNodeId', null);
		$resolver->setAllowedTypes('getRowNodeId', ['string', 'null']);


		$resolver->setDefault('applyCellrendererOnPivotHeader', false);
		$resolver->setAllowedTypes('applyCellrendererOnPivotHeader', 'bool');
	}

	protected function validateSideBarOptions(Options $options, $sidebarOption) {
		if(is_array($sidebarOption)) {
			// empty arrays are allowed, is the same as false
			if(count($sidebarOption) === 0) {
				return $sidebarOption;
			}
			// in case there is a toolPanels key, check the contents
			if(array_key_exists('toolPanels', $sidebarOption)) {
				$toolPanels = $sidebarOption['toolPanels'];
				if(is_array($toolPanels)) {
					// not having any tool panel in the sidebar is fine
					if(count($toolPanels) === 0) {
						return $sidebarOption;
					}
					foreach($toolPanels as $toolPanel) {
						if(is_string($toolPanel)) {
							if($toolPanel !== 'columns' && $toolPanel !== 'filters') {
								throw new InvalidOptionsException(sprintf('"%s" is not a valid alias for a toolPanel of the sidebar!', $toolPanel));
							}
						} else if(is_array($toolPanel)) {
							$this->validateToolPanel($options, $toolPanel);
						}
					}
					return $sidebarOption;
				}
				throw new InvalidOptionsException(sprintf('"%s" is not a valid option for the toolPanels option of the sidebar!', (string)$toolPanels));
			}
			throw new InvalidOptionsException(sprintf('The key "toolPanels" is missing in the sidebar object!'));
		}
		throw new InvalidOptionsException(sprintf('"%s" is not a valid option for the sidebar as it must be an array!', (string)$sidebarOption));
	}

	/** @noinspection PhpUnusedParameterInspection */
	protected function validateToolPanel(Options $options, $valueToNormalize): void {
		$optionsResolver = new OptionsResolver();
		$optionsResolver->setRequired('id');
		$optionsResolver->setAllowedTypes('id', 'string');

		$optionsResolver->setRequired('labelKey');
		$optionsResolver->setAllowedTypes('labelKey', 'string');

		$optionsResolver->setRequired('labelDefault');
		$optionsResolver->setAllowedTypes('labelDefault', 'string');

		$optionsResolver->setDefault('iconKey', null);
		$optionsResolver->setAllowedTypes('iconKey', ['string', 'null']);

		$optionsResolver->setDefault('toolPanel', null);
		$optionsResolver->setAllowedTypes('toolPanel', ['string', 'null']);
		$optionsResolver->setNormalizer('toolPanel', static function (Options $options, $toolPanel) {
			if($toolPanel === null && $options['toolPanelFramework'] === null) {
				throw new InvalidOptionsException('You must specify a value for either "toolPanel" or "toolPanelFramework" !');
			}
			return $toolPanel;
		});

		$optionsResolver->setDefault('toolPanelFramework', null);
		$optionsResolver->setAllowedTypes('toolPanelFramework', ['string', 'null']);
		$optionsResolver->setNormalizer('toolPanelFramework', static function (Options $options, $toolPanel) {
			if($toolPanel === null && $options['toolPanel'] === null) {
				throw new InvalidOptionsException('You must specify a value for either "toolPanel" or "toolPanelFramework" !');
			}
			return $toolPanel;
		});

		$optionsResolver->setDefault('toolPanelParams', null);
		$optionsResolver->setAllowedTypes('toolPanelParams', ['null', 'array']);

		$optionsResolver->resolve($valueToNormalize);
	}
}
