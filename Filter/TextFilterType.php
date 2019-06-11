<?php

namespace StingerSoft\AggridBundle\Filter;

use StingerSoft\AggridBundle\View\FilterView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TextFilterType extends AbstractFilterType {

	/**
	 * {@inheritdoc}
	 */
	public function buildView(FilterView $view, FilterInterface $filter, array $options, $dataSource, string $queryPath, string $rootAlias): void {

	}

	public function configureOptions(OptionsResolver $resolver, array $columnOptions = array(), array $tableOptions = array()): void {
		$resolver->setDefault('filter_type', 'agTextColumnFilter');
	}
}