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

namespace StingerSoft\AggridBundle\Filter;

use Doctrine\ORM\QueryBuilder;
use StingerSoft\AggridBundle\View\FilterView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateFilterType extends AbstractFilterType {

	public function configureOptions(OptionsResolver $resolver, array $columnOptions = [], array $gridOptions = []): void {
		$resolver->setDefault('filter_type', 'agDateColumnFilter');
		$resolver->setDefault('date_format', $columnOptions['date_format'] ?? null);
		$resolver->setDefault('jsTemplate', '@StingerSoftAggrid/Filter/date_filter.js.twig');
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
		$view->vars['date_format'] = $options['date_format'];
		$view->vars['includeBlanksInEquals'] = $options['includeBlanksInEquals'];
		$view->vars['includeBlanksInLessThan'] = $options['includeBlanksInLessThan'];
		$view->vars['includeBlanksInGreaterThan'] = $options['includeBlanksInGreaterThan'];
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