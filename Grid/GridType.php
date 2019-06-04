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
		$gridOptions['attr']['style'] = 'height: '.$gridOptions['height'];
		$gridOptions['attr']['class'] =  'ag-theme-balham';
		$view->vars['attr'] = $gridOptions['attr'];

	}

	private function configureAggridViewValues(GridView $view, array $gridOptions): void {
		$view->vars['enterpriseLicense'] = $gridOptions['enterpriseLicense'];
		$view->vars['treeData'] = $gridOptions['treeData'];
		$view->vars['sideBar'] = $gridOptions['sideBar'];

	}

	private function configureStingerViewValues(GridView $view, array $gridOptions, array $columns): void {
		$view->vars['translation_domain'] = $gridOptions['translation_domain'];
		$view->vars['total_results_query_builder'] = $gridOptions['total_results_query_builder'];
		$view->vars['default_order_property'] = $gridOptions['default_order_property'];
		$view->vars['default_order_direction'] = $gridOptions['default_order_direction'];

	}

	private function configureStingerOptions(OptionsResolver $resolver): void {
		$resolver->setDefault('translation_domain', 'messages');
		$resolver->setAllowedTypes('translation_domain', array(
			'string',
			'null',
			'boolean'
		));
		$resolver->setDefault('total_results_query_builder', null);
		$resolver->setAllowedTypes('total_results_query_builder', array('null', QueryBuilder::class));

		$resolver->setDefault('default_order_property', 'id');
		$resolver->setAllowedTypes('default_order_property', array('string', 'null'));
		$resolver->setDefault('default_order_direction', 'asc');
		$resolver->setAllowedValues('default_order_direction', array('asc', 'desc'));

		$resolver->setDefault('height', '500px');

		$resolver->setDefault('hydrateAsObject', true);
		$resolver->setAllowedTypes('hydrateAsObject', array(
			'boolean'
		));
	}

	private function configureAggridOptions(OptionsResolver $resolver): void {
		$resolver->setDefault('dataMode', self::DATA_MODE_INLINE);
		$resolver->setAllowedValues('dataMode', array(
			self::DATA_MODE_INLINE,
			self::DATA_MODE_AJAX,
			self::DATA_MODE_ENTERPRISE,
		));

		$resolver->setDefault('ajax_url', null);
		$resolver->setAllowedTypes('ajax_url', array(
			'string',
			'null'
		));

		$resolver->setNormalizer('ajax_url', function(Options $options, $valueToNormalize) {
			if($valueToNormalize === null && $options['dataMode'] !== self::DATA_MODE_INLINE) {
				throw new InvalidOptionsException('When using "dataMode"  with a value of ajax or enterprise you must set "ajax_url"!');
			}
			return $valueToNormalize;
		});

		$resolver->setDefault('ajax_method', 'POST');
		$resolver->setAllowedValues('ajax_method', array(
			'GET',
			'POST'
		));

		$resolver->setDefault('enterpriseLicense', null);
		$resolver->setAllowedTypes('enterpriseLicense', array(
			'string',
			'null'
		));

		$resolver->setDefault('treeData', false);
		$resolver->setAllowedValues('treeData', array(
			true,
			false,
		));

		$resolver->setDefault('sideBar', false);
		$resolver->setAllowedValues('sideBar', array(
			true,
			false,
			'columns',
			'filters'
		));

	}
}