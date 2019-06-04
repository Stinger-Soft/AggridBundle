<?php

namespace StingerSoft\AggridBundle\Filter;

use Doctrine\ORM\QueryBuilder;
use StingerSoft\AggridBundle\View\FilterView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateFilterType extends AbstractFilterType {

	public function configureOptions(OptionsResolver $resolver, array $columnOptions = array(), array $gridOptions = array()): void {
		$resolver->setDefault('filter_type', 'agDateColumnFilter');
		$resolver->setDefault('date_format', $columnOptions['date_format'] ?? null);
		$resolver->setDefault('jsTemplate', 'StingerSoftAggridBundle:Filter:date_filter.js.twig');
	}

	/**
	 * @inheritdoc
	 * @see AbstractFilterType::buildView()
	 */
	public function buildView(FilterView $view, FilterInterface $filter, array $options, $dataSource, $queryPath, $rootAlias): void {
		$view->vars['date_format'] = $options['date_format'];
	}

	public function applyFilter(QueryBuilder $queryBuilder, array $filterRequest, string $parameterBindingName, string $queryPath, array $filterTypeOptions, string $rootAlias) {
		$expr = null;

		$filterValue = $filterRequest['dateFrom'] ?? $filterRequest['dateTo'] ?? null;
		$filterValueTo = $filterRequest['dateTo'] ?? null;
		$filterType = $filterRequest['type'] ?? null;

		if($this->filterIsValid($filterValue, $filterTypeOptions)) {
			return $this->createExpression($filterType, $parameterBindingName, $queryPath, $queryBuilder, $filterValue, $filterValueTo);
		}
		return null;
	}
}