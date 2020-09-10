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
use StingerSoft\PhpCommons\String\Utils;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractFilterType implements FilterTypeInterface {

	/**
	 * {@inheritdoc}
	 * @see \StingerSoft\AggridBundle\Filter\FilterTypeInterface::configureOptions()
	 */
	public function configureOptions(OptionsResolver $resolver, array $columnOptions = [], array $tableOptions = []): void {

	}

	/**
	 * {@inheritdoc}
	 * @see \StingerSoft\AggridBundle\Filter\FilterTypeInterface::getParent()
	 */
	public function getParent(): ?string {
		return FilterType::class;
	}

	/**
	 * {@inheritdoc}
	 * @see \StingerSoft\AggridBundle\Filter\FilterTypeInterface::buildView()
	 */
	public function buildView(FilterView $view, FilterInterface $filter, array $options, $dataSource, string $queryPath, string $rootAlias): void {
	}

	public function applyFilter(QueryBuilder $queryBuilder, array $filterRequest, string $parameterBindingName, string $queryPath, array $filterTypeOptions, string $rootAlias) {
		$isConditionalFilter = isset($filterRequest['operator']);
		if($isConditionalFilter) {
			$expressions = [];
			$i = 0;
			foreach($filterRequest as $key => $value) {
				if(Utils::startsWith($key, 'condition')) {
					$expression = $this->handleFilterRequest($queryBuilder, $value, $parameterBindingName . ++$i, $queryPath, $filterTypeOptions, $rootAlias);
					if($expression !== null) {
						$expressions[] = $expression;
					}
				}
			}
			if(count($expressions) > 0) {
				if($filterRequest['operator'] === FilterTypeInterface::FILTER_OPERATOR_AND) {
					return $queryBuilder->expr()->andX()->addMultiple($expressions);
				}
				return $queryBuilder->expr()->orX()->addMultiple($expressions);
			}
		} else {
			return $this->handleFilterRequest($queryBuilder, $filterRequest, $parameterBindingName, $queryPath, $filterTypeOptions, $rootAlias);
		}
		return null;
	}

	public function handleFilterRequest(QueryBuilder $queryBuilder, array $filterRequest, string $parameterBindingName, string $queryPath, array $filterTypeOptions, string $rootAlias) {
		$filterValue = $filterRequest['filter'] ?? $filterRequest['values'];
		$filterValueTo = $filterRequest['filterTo'] ?? null;
		$filterType = $filterRequest['type'] ?? null;
		if(is_array($filterValue)) {
			$filterType = FilterTypeInterface::FILTER_MATCH_MODE_SET;
		}
		if($this->filterIsValid($filterValue, $filterTypeOptions)) {
			return $this->createExpression($filterType, $parameterBindingName, $queryPath, $queryBuilder, $rootAlias, $filterTypeOptions, $filterValue, $filterValueTo);
		}
		return null;
	}

	protected function createExpression(string $comparisonType, string $parameterBindingName, string $queryPath, QueryBuilder $queryBuilder, string $rootAlias, array $filterTypeOptions, $value, $toValue) {
		$expr = null;
		switch($comparisonType) {
			case FilterTypeInterface::FILTER_MATCH_MODE_EQUALS:
				$expr = $queryBuilder->expr()->eq($queryPath, $parameterBindingName);
				if(isset($filterTypeOptions['includeBlanksInEquals']) && $filterTypeOptions['includeBlanksInEquals']) {
					$expr = $queryBuilder->expr()->orX($expr, $queryBuilder->expr()->isNull($queryPath));
				}
				$queryBuilder->setParameter($parameterBindingName, $value);
				break;
			case FilterTypeInterface::FILTER_MATCH_MODE_NOT_EQUALS:
				$expr = $queryBuilder->expr()->neq($queryPath, $parameterBindingName);
				$queryBuilder->setParameter($parameterBindingName, $value);
				break;
			case FilterTypeInterface::FILTER_MATCH_MODE_GREATER_THAN:
				$expr = $queryBuilder->expr()->gt($queryPath, $parameterBindingName);
				if(isset($filterTypeOptions['includeBlanksInGreaterThan']) && $filterTypeOptions['includeBlanksInGreaterThan']) {
					$expr = $queryBuilder->expr()->orX($expr, $queryBuilder->expr()->isNull($queryPath));
				}
				$queryBuilder->setParameter($parameterBindingName, $value);
				break;
			case FilterTypeInterface::FILTER_MATCH_MODE_GREATER_THAN_OR_EQUALS:
				$expr = $queryBuilder->expr()->gte($queryPath, $parameterBindingName);
				if(isset($filterTypeOptions['includeBlanksInGreaterThan']) && $filterTypeOptions['includeBlanksInGreaterThan']) {
					$expr = $queryBuilder->expr()->orX($expr, $queryBuilder->expr()->isNull($queryPath));
				}
				$queryBuilder->setParameter($parameterBindingName, $value);
				break;
			case FilterTypeInterface::FILTER_MATCH_MODE_LESS_THAN:
				$expr = $queryBuilder->expr()->lt($queryPath, $parameterBindingName);
				if(isset($filterTypeOptions['includeBlanksInLessThan']) && $filterTypeOptions['includeBlanksInLessThan']) {
					$expr = $queryBuilder->expr()->orX($expr, $queryBuilder->expr()->isNull($queryPath));
				}
				$queryBuilder->setParameter($parameterBindingName, $value);
				break;
			case FilterTypeInterface::FILTER_MATCH_MODE_LESS_THAN_OR_EQUALS:
				$expr = $queryBuilder->expr()->lte($queryPath, $parameterBindingName);
				if(isset($filterTypeOptions['includeBlanksInLessThan']) && $filterTypeOptions['includeBlanksInLessThan']) {
					$expr = $queryBuilder->expr()->orX($expr, $queryBuilder->expr()->isNull($queryPath));
				}
				$queryBuilder->setParameter($parameterBindingName, $value);
				break;
			case FilterTypeInterface::FILTER_MATCH_MODE_RANGE:
				$expr = $queryBuilder->expr()->between($queryPath, $parameterBindingName, $parameterBindingName . '_to');
				$queryBuilder->setParameter($parameterBindingName, $value);
				$queryBuilder->setParameter($parameterBindingName . '_to', $toValue);
				break;
			case FilterTypeInterface::FILTER_MATCH_MODE_STARTS_WITH:
				$expr = $queryBuilder->expr()->like($queryPath, $parameterBindingName);
				$queryBuilder->setParameter($parameterBindingName, $value . '%');
				break;
			case FilterTypeInterface::FILTER_MATCH_MODE_ENDS_WITH:
				$expr = $queryBuilder->expr()->like($queryPath, $parameterBindingName);
				$queryBuilder->setParameter($parameterBindingName, '%' . $value);
				break;
			case FilterTypeInterface::FILTER_MATCH_MODE_CONTAINS:
				$expr = $queryBuilder->expr()->like($queryPath, $parameterBindingName);
				$queryBuilder->setParameter($parameterBindingName, '%' . $value . '%');
				break;
			case FilterTypeInterface::FILTER_MATCH_MODE_NOT_CONTAINS:
				$expr = $queryBuilder->expr()->notLike($queryPath, $parameterBindingName);
				$queryBuilder->setParameter($parameterBindingName, '%' . $value . '%');
				break;
			case FilterTypeInterface::FILTER_MATCH_MODE_SET:
				$expr = $queryBuilder->expr()->in($queryPath, $parameterBindingName);
				$queryBuilder->setParameter($parameterBindingName, $value);
				break;
		}
		return $expr;
	}

	/**
	 * Checks if the given filter is valid.
	 *
	 * A filter is considered valid when there is a filter value (i.e. it is not empty) and it is not a regular expression.
	 *
	 * @param string|string[] $filterValue       the filter value
	 * @param array           $filterTypeOptions the options of the filter type.
	 * @return bool true in case the filter value is not empty and the filter is no regular expression, false otherwise.
	 */
	protected function filterIsValid($filterValue, array $filterTypeOptions): bool {
		// Regular Expressions are ignored on server side, as there is no built-in regexp handling in doctrine
		if($filterTypeOptions['validation_delegate'] !== null && is_callable($filterTypeOptions['validation_delegate'])) {
			return $filterTypeOptions['validation_delegate']($filterValue, $filterTypeOptions);
		}
		if($filterTypeOptions['validate_empty'] === false) {
			return true;
		}
		return !empty($filterValue);
	}
}