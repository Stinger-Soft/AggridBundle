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
use InvalidArgumentException;
use StingerSoft\AggridBundle\Grid\GridType;
use StingerSoft\AggridBundle\View\FilterView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SetFilterType extends AbstractFilterType {

	public function configureOptions(OptionsResolver $resolver, array $columnOptions = [], array $gridOptions = []): void {
		$resolver->setDefault('filter_type', 'agSetColumnFilter');
		$resolver->setDefault('jsTemplate', '@StingerSoftAggrid/Filter/setfilter.js.twig');

		$resolver->setDefault('data', $gridOptions['dataMode'] === GridType::DATA_MODE_ENTERPRISE);
		$resolver->setAllowedTypes('data', ['null', 'array', 'boolean', 'callable']);

		$resolver->setDefault('keyValueMapping', null);
		$resolver->setAllowedTypes('keyValueMapping', ['null', 'array', 'callable']);

		$resolver->setDefault('cellRenderer', static function (Options $options, $previousValue) {
			if($options['keyValueMapping'] !== null) {
				return $previousValue ?? 'KeyValueMappingRenderer';
			}
			return $previousValue;
		});
		$resolver->setDefault('translation_domain', true);
		$resolver->setAllowedTypes('translation_domain', ['bool', 'string']);

		$resolver->setDefault('allow_null_value', false);
		$resolver->setAllowedTypes('allow_null_value', 'boolean');

		$resolver->setDefault('null_value', null);
		$resolver->setNormalizer('null_value', static function (Options $options, $valueToNormalize) {
			if($valueToNormalize === null && $options['allow_null_value'] === true) {
				throw new InvalidArgumentException('When setting "allow_null_value" to true, you must provide a non-null value for the "null_value" option!');
			}
			return $valueToNormalize;
		});
		$resolver->setDefault('strict_null_check', false);
		$resolver->setAllowedTypes('strict_null_check', 'bool');
	}

	protected function createExpression(string $comparisonType, string $parameterBindingName, string $queryPath, QueryBuilder $queryBuilder, string $rootAlias, array $filterTypeOptions, $value, $toValue) {
		if($comparisonType === FilterTypeInterface::FILTER_MATCH_MODE_SET && $filterTypeOptions['allow_null_value']) {
			$hasNullValue = false;
			$nonNullValues = [];
			$nullValue = $filterTypeOptions['null_value'];
			$strictNullCheck = $filterTypeOptions['strict_null_check'];
			foreach($value as $entry) {
				/** @noinspection TypeUnsafeComparisonInspection */
				$isNull = $strictNullCheck ? $entry === $nullValue : $entry == $nullValue;
				if($isNull) {
					$hasNullValue = true;
				} else {
					$nonNullValues[] = $entry;
				}
			}
			if($hasNullValue) {
				if(count($nonNullValues) > 0) {
					$expr = $queryBuilder->expr()->orX(
						$queryBuilder->expr()->in($queryPath, $parameterBindingName),
						$queryBuilder->expr()->isNull($queryPath)
					);
					$queryBuilder->setParameter($parameterBindingName, $nonNullValues);
				} else {
					$expr = $queryBuilder->expr()->isNull($queryPath);
				}
				return $expr;
			}
		}
		return parent::createExpression($comparisonType, $parameterBindingName, $queryPath, $queryBuilder, $rootAlias, $filterTypeOptions, $value, $toValue);
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildView(FilterView $view, FilterInterface $filter, array $options, $dataSource, string $queryPath, string $rootAlias): void {
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
			$rawData = call_user_func($options['data'], $filter, $options, $dataSource, $queryPath, $rootAlias);
		}

		$translationDomain = $options['translation_domain'];
		if($translationDomain === true) {
			$translationDomain = $filter->getColumnViewVar('translation_domain');
		}

		$cellRendererParams = $view->vars['cellRendererParams'];
		if($cellRendererParams === null) {
			$cellRendererParams = [];
		}
		$keyValueMapping = $options['keyValueMapping'];
		if(is_callable($keyValueMapping)) {
			$keyValueMapping = $keyValueMapping($filter, $rawData, $options, $dataSource, $queryPath, $rootAlias);
		}

		$cellRendererParams['keyValueMapping'] = $keyValueMapping;
		$cellRendererParams['translation_domain'] = $translationDomain;
		$view->vars = array_replace($view->vars, [
			'data'               => $rawData,
			'cellRendererParams' => $cellRendererParams,
		]);
	}
}