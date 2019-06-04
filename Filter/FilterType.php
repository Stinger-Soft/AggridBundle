<?php

namespace StingerSoft\AggridBundle\Filter;

use StingerSoft\AggridBundle\View\FilterView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FilterType extends AbstractFilterType {

	/**
	 * {@inheritdoc}
	 * @see \StingerSoft\AggridBundle\Filter\FilterTypeInterface::buildView()
	 */
	public function buildView(FilterView $view, FilterInterface $filter, array $options, $dataSource, string $queryPath, string $rootAlias): void {
		$view->vars['filter_type'] = $options['filter_type'];
		$view->jsTemplate = $options['jsTemplate'];
		$view->vars = array_replace($view->vars, array(
//			'type'                      => $options['type'],
			'translation_domain'        => $options['translation_domain'],
//			'filter_default_label'      => $options['filter_default_label'],
//			'filter_reset_button_text'  => $options['filter_reset_button_text'],
//			'filter_container_selector' => $options['filter_container_selector'],
//			'filter_container_class'    => $options['filter_container_class'],
//			'filter_container_id'       => $options['filter_container_id'],
//			'filter_plugin_options'     => $options['filter_plugin_options'],
//			'column_data_type'          => $options['column_data_type'],
//			'text_data_delimiter'       => $options['text_data_delimiter'],
//			'html_data_type'            => $options['html_data_type'],
//			'html_data_selector'        => $options['html_data_selector'],
//			'html5_data'                => $options['html5_data'],
//			'sort_as'                   => $options['sort_as'],
//			'sort_as_custom_func'       => $options['sort_as_custom_func'],
//			'sort_order'                => $options['sort_order'],
//			'filter_match_mode'         => $options['filter_match_mode'],
//			'reset_button_style_class'  => $options['reset_button_style_class'],
//			'pre_filtered_value'        => $options['pre_filtered_value'],
//			'highlight_mode'            => $options['highlight_mode'],
//			'auto_focus'                => $options['auto_focus'],
		));
	}

	/**
	 * {@inheritdoc}
	 * @see \StingerSoft\AggridBundle\Filter\FilterTypeInterface::configureOptions()
	 */
	public function configureOptions(OptionsResolver $resolver, array $columnOptions = array(), array $gridOptions = array()): void {
		$resolver->setRequired('jsTemplate');
		$resolver->setDefault('jsTemplate', 'StingerSoftAggridBundle:Filter:filter.js.twig');
		$resolver->setAllowedTypes('jsTemplate', 'string');

		$resolver->setDefault('filter_type', null);

		$resolver->setDefault('translation_domain', 'PecDatatableBundle');
		$resolver->setAllowedTypes('translation_domain', array(
			'string',
			'null',
			'boolean'
		));
	}


	public function getParent(): ?string {
		return null;
	}
}