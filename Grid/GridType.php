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
use StingerSoft\AggridBundle\View\GridView;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GridType extends AbstractGridType {

	public const DATA_MODE_INLINE = 'inline';
	public const DATA_MODE_AJAX = 'ajax';
	public const DATA_MODE_ENTERPRISE = 'enterprise';

	/**
	 *
	 * {@inheritdoc}
	 * @see \StingerSoft\AggridBundle\Grid\GridTypeInterface::buildGrid()
	 */
	public function getParent(): ?string {
		return null;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \StingerSoft\AggridBundle\Grid\GridTypeInterface::buildGrid()
	 */
	public function buildView(GridView $view, GridInterface $grid, array $gridOptions, array $columns): void {
		$this->configureDefaultViewValues($view, $gridOptions, $columns);
		$this->configureAggridViewValues($view, $gridOptions);
		$this->configureStingerViewValues($view, $gridOptions, $columns);
	}

	/**
	 * {@inheritdoc}
	 * @see \StingerSoft\AggridBundle\Grid\GridTypeInterface::configureOptions()
	 */
	public function configureOptions(OptionsResolver $resolver): void {
		$this->configureStingerOptions($resolver);
		$this->configureAggridOptions($resolver);
	}

	private function configureDefaultViewValues(GridView $view, array $gridOptions, array $columns): void {
		$view->vars['id'] = $gridOptions['attr']['id'] = $view->getGridId();
		$view->vars['ajax_url'] = $gridOptions['ajax_url'];
		$view->vars['dataMode'] = $gridOptions['dataMode'];
		$gridOptions['attr']['style'] = 'height: ' . $gridOptions['height'];
		$gridOptions['attr']['class'] = 'ag-theme-balham';
		$view->vars['attr'] = $gridOptions['attr'];

	}

	private function configureAggridViewValues(GridView $view, array $gridOptions): void {
		$view->vars['enterpriseLicense'] = $gridOptions['enterpriseLicense'];
		$view->vars['treeData'] = $gridOptions['treeData'];
		$view->vars['sideBar'] = $gridOptions['sideBar'];
		$view->vars['cacheBlockSize'] = $gridOptions['cacheBlockSize'];
		$view->vars['pagination'] = $gridOptions['pagination'];
		$view->vars['paginationPageSize'] = $gridOptions['paginationPageSize'];
		$view->vars['paginationAutoPageSize'] = $gridOptions['paginationAutoPageSize'];
		$view->vars['suppressPaginationPanel'] = $gridOptions['suppressPaginationPanel'];
	}

	private function configureStingerViewValues(GridView $view, array $gridOptions, array $columns): void {
		$view->vars['translation_domain'] = $gridOptions['translation_domain'];
		$view->vars['total_results_query_builder'] = $gridOptions['total_results_query_builder'];
		$view->vars['default_order_property'] = $gridOptions['default_order_property'];
		$view->vars['default_order_direction'] = $gridOptions['default_order_direction'];

	}

	private function configureStingerOptions(OptionsResolver $resolver): void {
		$resolver->setDefault('translation_domain', 'messages');
		$resolver->setAllowedTypes('translation_domain', [
			'string',
			'null',
			'boolean',
		]);
		$resolver->setDefault('total_results_query_builder', null);
		$resolver->setAllowedTypes('total_results_query_builder', ['null', QueryBuilder::class]);

		$resolver->setDefault('default_order_property', 'id');
		$resolver->setAllowedTypes('default_order_property', ['string', 'null']);
		$resolver->setDefault('default_order_direction', 'asc');
		$resolver->setAllowedValues('default_order_direction', ['asc', 'desc']);

		$resolver->setDefault('height', '500px');

		$resolver->setDefault('hydrateAsObject', true);
		$resolver->setAllowedTypes('hydrateAsObject', [
			'boolean',
		]);
	}

	private function configureAggridOptions(OptionsResolver $resolver): void {
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

		$resolver->setDefault('enterpriseLicense', null);
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

		$resolver->setDefault('sideBar', false);
		$resolver->setAllowedValues('sideBar', [
			true,
			false,
			'columns',
			'filters',
		]);
		$resolver->setNormalizer('sideBar', static function (Options $options, $value) {
			if($value !== false && !isset($options['enterpriseLicense'])) {
				throw new InvalidArgumentException('sideBar is only available in the enterprise edition. Please set a license key!');
			}
			return $value;
		});

		$resolver->setDefault('cacheBlockSize', 100);
		$resolver->setAllowedTypes('cacheBlockSize', 'int');

		$resolver->setDefault('pagination', false);
		$resolver->setAllowedValues('pagination', [
			true,
			false,
		]);
		$resolver->setDefault('paginationPageSize', 100);
		$resolver->setAllowedTypes('paginationPageSize','int');
		$resolver->setDefault('paginationAutoPageSize', false);
		$resolver->setAllowedTypes('paginationAutoPageSize','bool');
		$resolver->setDefault('suppressPaginationPanel', false);
		$resolver->setAllowedTypes('suppressPaginationPanel','bool');
	}
}