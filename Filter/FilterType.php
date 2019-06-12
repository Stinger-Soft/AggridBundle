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
		$cellRendererParams = $options['cellRendererParams'];
		if($cellRendererParams === null) {
			$cellRendererParams = [];
		}
		$view->vars = array_replace($view->vars, [
			'newRowsAction'      => $options['newRowsAction'] !== null && $options['newRowsAction'] !== self::NEW_ROWS_ACTION_DEFAULT ? $options['newRowsAction'] : null,
			'cellRenderer'       => $options['cellRenderer'],
			'cellRendererParams' => $cellRendererParams,
			'translation_domain' => $options['translation_domain'],
			'debounceMs'         => $options['debounceMs'],
			'applyButton'        => $options['applyButton'],
			'clearButton'        => $options['clearButton'],
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

		$resolver->setDefault('cellRenderer', null);
		$resolver->setAllowedTypes('cellRenderer', ['null', 'string']);

		$resolver->setDefault('cellRendererParams', []);
		$resolver->setAllowedTypes('cellRendererParams', ['null', 'array']);

		$resolver->setDefault('translation_domain', 'messages');
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

		$resolver->setDefault('debounceMs', 100);
		$resolver->setAllowedTypes('debounceMs', ['null', 'int']);

		$resolver->setDefault('applyButton', true);
		$resolver->setAllowedTypes('applyButton', ['bool', 'null']);

		$resolver->setDefault('clearButton', true);
		$resolver->setAllowedTypes('clearButton', ['bool', 'null']);

		$resolver->setDefault('validate_empty', true);
		$resolver->setAllowedTypes('validate_empty', ['bool']);

		$resolver->setDefault('validation_delegate', null);
		$resolver->setAllowedTypes('validation_delegate', ['null', 'callable', \Closure::class]);

		$resolver->setDefault('server_delegate', null);
		$resolver->setAllowedTypes('server_delegate', ['null', 'callable', \Closure::class]);
	}

	public function getParent(): ?string {
		return null;
	}
}