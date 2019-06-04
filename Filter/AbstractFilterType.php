<?php

namespace StingerSoft\AggridBundle\Filter;

use Doctrine\ORM\QueryBuilder;
use StingerSoft\AggridBundle\View\FilterView;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractFilterType implements FilterTypeInterface {

	/**
	 * {@inheritdoc}
	 * @see \StingerSoft\AggridBundle\Filter\FilterTypeInterface::configureOptions()
	 */
	public function configureOptions(OptionsResolver $resolver, array $columnOptions = array(), array $tableOptions = array()): void {

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

		$filterValue = $filterRequest['filter'] ?? $filterRequest['values'];
		$filterValueTo = $filterRequest['filterTo'] ?? null;
		$filterType = $filterRequest['type'] ?? null;
		if(is_array($filterValue)) {
			$filterType = FilterTypeInterface::FILTER_MATCH_MODE_SET;
		}
		if($this->filterIsValid($filterValue, $filterTypeOptions)) {
			return $this->createExpression($filterType, $parameterBindingName, $queryPath, $queryBuilder, $filterValue, $filterValueTo);
		}
		return null;
	}

	protected function createExpression(string $comparisonType, string $parameterBindingName, string $queryPath, QueryBuilder $queryBuilder, $value, $toValue) {
		$expr = null;
		switch($comparisonType) {
			case FilterTypeInterface::FILTER_MATCH_MODE_EQUALS:
				$expr = $queryBuilder->expr()->eq($queryPath, $parameterBindingName);
				$queryBuilder->setParameter($parameterBindingName, $value);
				break;
			case FilterTypeInterface::FILTER_MATCH_MODE_NOT_EQUALS:
				$expr = $queryBuilder->expr()->neq($queryPath, $parameterBindingName);
				$queryBuilder->setParameter($parameterBindingName, $value);
				break;
			case FilterTypeInterface::FILTER_MATCH_MODE_GREATER_THAN:
				$expr = $queryBuilder->expr()->gt($queryPath, $parameterBindingName);
				$queryBuilder->setParameter($parameterBindingName, $value);
				break;
			case FilterTypeInterface::FILTER_MATCH_MODE_GREATER_THAN_OR_EQUALS:
				$expr = $queryBuilder->expr()->gte($queryPath, $parameterBindingName);
				$queryBuilder->setParameter($parameterBindingName, $value);
				break;
			case FilterTypeInterface::FILTER_MATCH_MODE_LESS_THAN:
				$expr = $queryBuilder->expr()->lt($queryPath, $parameterBindingName);
				$queryBuilder->setParameter($parameterBindingName, $value);
				break;
			case FilterTypeInterface::FILTER_MATCH_MODE_LESS_THAN_OR_EQUALS:
				$expr = $queryBuilder->expr()->lte($queryPath, $parameterBindingName);
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
	 * @param string|string[] $filterValue the filter value
	 * @param array $filterTypeOptions the options of the filter type.
	 * @return bool true in case the filter value is not empty and the filter is no regular expression, false otherwise.
	 */
	protected function filterIsValid($filterValue, array $filterTypeOptions): bool {
		// Regular Expressions are ignored on server side, as there is no built-in regexp handling in doctrine
		return !empty($filterValue);
	}
}