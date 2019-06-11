<?php

namespace StingerSoft\AggridBundle\Filter;

use Doctrine\ORM\QueryBuilder;
use StingerSoft\AggridBundle\Grid\GridType;
use StingerSoft\AggridBundle\Grid\GridTypeInterface;
use StingerSoft\AggridBundle\View\FilterView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SetFilterType extends AbstractFilterType {

	public function configureOptions(OptionsResolver $resolver, array $columnOptions = array(), array $gridOptions = array()): void {
		$resolver->setDefault('filter_type', 'agSetColumnFilter');
		$resolver->setDefault('jsTemplate', '@StingerSoftAggrid/Filter/setfilter.js.twig');

		$resolver->setDefault('data', $gridOptions['dataMode'] === GridType::DATA_MODE_ENTERPRISE);
		$resolver->setAllowedTypes('data', array('null', 'array', 'boolean', 'callable'));
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildView(FilterView $view, FilterInterface $filter, array $options, $dataSource, string $queryPath, string $rootAlias) : void {
		$rawData = $options['data'];

		if($options['data'] === true) {
			if($dataSource instanceof QueryBuilder) {
				$queryBuilder = $dataSource;
				$queryBuilder->select($queryPath);
				$queryBuilder->distinct(true);
				$queryBuilder->groupBy($queryPath);
				$queryBuilder->orderBy($queryPath);
				$result = $queryBuilder->getQuery()->getScalarResult();
				$rawData = array_map('current', $result);
				$rawData = array_values($rawData);
			}
		} else if(is_callable($options['data'])) {
			if($dataSource instanceof QueryBuilder) {
				$rawData = call_user_func($options['data'], $filter, $options, $dataSource, $queryPath, $rootAlias);
			}
		}


		$view->vars = array_replace($view->vars, array(
			'data' => $rawData
		));
	}
}