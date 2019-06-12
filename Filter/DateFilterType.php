<?php

namespace StingerSoft\AggridBundle\Filter;

use Doctrine\ORM\QueryBuilder;
use StingerSoft\AggridBundle\View\FilterView;
use StingerSoft\PhpCommons\String\Utils;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateFilterType extends AbstractFilterType {

	public function configureOptions(OptionsResolver $resolver, array $columnOptions = [], array $gridOptions = []): void {
		$resolver->setDefault('filter_type', 'agDateColumnFilter');
		$resolver->setDefault('date_format', $columnOptions['date_format'] ?? null);
		$resolver->setDefault('jsTemplate', '@StingerSoftAggrid/Filter/date_filter.js.twig');
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildView(FilterView $view, FilterInterface $filter, array $options, $dataSource, string $queryPath, string $rootAlias): void {
		$view->vars['date_format'] = $options['date_format'];
	}

	public function handleFilterRequest(QueryBuilder $queryBuilder, array $filterRequest, string $parameterBindingName, string $queryPath, array $filterTypeOptions, string $rootAlias) {
		$filterValue = $filterRequest['dateFrom'] ?? $filterRequest['dateTo'] ?? null;
		$filterValueTo = $filterRequest['dateTo'] ?? null;
		$filterType = $filterRequest['type'] ?? null;
		if($this->filterIsValid($filterValue, $filterTypeOptions)) {
			return $this->createExpression($filterType, $parameterBindingName, $queryPath, $queryBuilder, $rootAlias, $filterTypeOptions, $filterValue, $filterValueTo);
		}
		return null;
	}
}