<?php

namespace StingerSoft\AggridBundle\Filter;

use StingerSoft\AggridBundle\View\FilterView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NumberFilterType extends AbstractFilterType {

	public function configureOptions(OptionsResolver $resolver, array $columnOptions = array(), array $tableOptions = array()): void {
		$resolver->setDefault('filter_type', 'agNumberColumnFilter');
		$resolver->setDefault('includeBlanksInEquals', false);
		$resolver->setAllowedTypes('includeBlanksInEquals', 'bool');
		$resolver->setDefault('includeBlanksInLessThan', false);
		$resolver->setAllowedTypes('includeBlanksInLessThan', 'bool');
		$resolver->setDefault('includeBlanksInGreaterThan', false);
		$resolver->setAllowedTypes('includeBlanksInGreaterThan', 'bool');
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildView(FilterView $view, FilterInterface $filter, array $options, $dataSource, string $queryPath, string $rootAlias): void {
		$view->vars['includeBlanksInEquals'] = $options['includeBlanksInEquals'];
		$view->vars['includeBlanksInLessThan'] = $options['includeBlanksInLessThan'];
		$view->vars['includeBlanksInGreaterThan'] = $options['includeBlanksInGreaterThan'];
	}

}