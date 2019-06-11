<?php

namespace StingerSoft\AggridBundle\Filter;

use StingerSoft\AggridBundle\Grid\GridType;
use StingerSoft\AggridBundle\View\FilterView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FilterType extends AbstractFilterType {

	public const NEW_ROWS_ACTION_KEEP = 'keep';
	public const NEW_ROWS_ACTION_DEFAULT = 'default';

	/**
	 * {@inheritdoc}
	 */
	public function buildView(FilterView $view, FilterInterface $filter, array $options, $dataSource, string $queryPath, string $rootAlias): void {
		$view->vars['filter_type'] = $options['filter_type'];
		$view->jsTemplate = $options['jsTemplate'];
		$view->vars['newRowsAction'] = $options['newRowsAction'] !== null && $options['newRowsAction'] !== self::NEW_ROWS_ACTION_DEFAULT ? $options['newRowsAction'] : null;
		$view->vars = array_replace($view->vars, [
			'translation_domain' => $options['translation_domain'],
		]);
	}

	/**
	 * {@inheritdoc}
	 * @see \StingerSoft\AggridBundle\Filter\FilterTypeInterface::configureOptions()
	 */
	public function configureOptions(OptionsResolver $resolver, array $columnOptions = [], array $gridOptions = []): void {
		$resolver->setRequired('jsTemplate');
		$resolver->setDefault('jsTemplate', '@StingerSoftAggrid/Filter/filter.js.twig');
		$resolver->setAllowedTypes('jsTemplate', 'string');

		$resolver->setDefault('filter_type', null);

		$resolver->setDefault('translation_domain', 'StingerSoftAggridBundle');
		$resolver->setAllowedTypes('translation_domain', [
			'string',
			'null',
			'boolean',
		]);
		$resolver->setDefined('newRowsAction');
		$resolver->setAllowedValues('newRowsAction', [null, self::NEW_ROWS_ACTION_DEFAULT, self::NEW_ROWS_ACTION_KEEP]);
		$resolver->setDefault('newRowsAction', static function (Options $options, $previousValue) use ($gridOptions) {
			if($previousValue === null && $gridOptions['dataMode'] === GridType::DATA_MODE_ENTERPRISE) {
				return self::NEW_ROWS_ACTION_KEEP;
			}
			return $previousValue;
		});

	}

	public function getParent(): ?string {
		return null;
	}
}