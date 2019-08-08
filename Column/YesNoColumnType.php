<?php
/*
 * This file is part of the Stinger Soft AgGrid package.
 *
 * (c) Oliver Kotte <oliver.kotte@stinger-soft.net>
 * (c) Florian Meyer <florian.meyer@stinger-soft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace StingerSoft\AggridBundle\Column;

use StingerSoft\AggridBundle\Filter\FilterInterface;
use StingerSoft\AggridBundle\Filter\SetFilterType;
use StingerSoft\AggridBundle\Grid\GridType;
use StingerSoft\AggridBundle\View\ColumnView;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class YesNoColumnType extends AbstractColumnType {
	const DISPLAY_ICON_ONLY = 'icon-only';
	const DISPLAY_LABEL_ONLY = 'label-only';
	const DISPLAY_ICON_WITH_LABEL = 'icon-with-label';
	const DISPLAY_ICON_WITH_TOOLTIP = 'icon-with-tooltip';

	/**
	 * @var TranslatorInterface
	 */
	protected $translator;

	/**
	 * YesNoColumnType constructor.
	 * @param TranslatorInterface $translator
	 */
	public function __construct(TranslatorInterface $translator) {
		$this->translator = $translator;
	}

	public function configureOptions(OptionsResolver $resolver, array $gridOptions = array()): void {
		$resolver->setDefault('yes_label', 'stingersoft_aggrid.column_types.yes_no.yes');
		$resolver->setAllowedTypes('yes_label', array('null', 'string'));

		$resolver->setDefault('no_label', 'stingersoft_aggrid.column_types.yes_no.no');
		$resolver->setAllowedTypes('no_label', array('null', 'string'));

		$resolver->setDefault('yes_icon', 'fa fa-fw fa-check');
		$resolver->setAllowedTypes('yes_icon', array('null', 'string'));

		$resolver->setDefault('no_icon', 'fa fa-fw fa-times');
		$resolver->setAllowedTypes('no_icon', array('null', 'string'));

		$resolver->setDefault('display_type', YesNoColumnType::DISPLAY_ICON_ONLY);
		$resolver->setAllowedValues('display_type', array(
			YesNoColumnType::DISPLAY_ICON_WITH_TOOLTIP, YesNoColumnType::DISPLAY_ICON_WITH_LABEL,
			YesNoColumnType::DISPLAY_LABEL_ONLY, YesNoColumnType::DISPLAY_ICON_ONLY
		));

		$resolver->setDefault('filter_display_type', YesNoColumnType::DISPLAY_LABEL_ONLY);
		$resolver->setAllowedValues('filter_display_type', array(
			YesNoColumnType::DISPLAY_ICON_WITH_TOOLTIP, YesNoColumnType::DISPLAY_ICON_WITH_LABEL,
			YesNoColumnType::DISPLAY_LABEL_ONLY, YesNoColumnType::DISPLAY_ICON_ONLY
		));

		$resolver->setNormalizer('display_type', static function(Options $options, $value) {
			if($value === YesNoColumnType::DISPLAY_ICON_ONLY || $value === YesNoColumnType::DISPLAY_ICON_WITH_LABEL || $value === YesNoColumnType::DISPLAY_ICON_WITH_TOOLTIP) {
				if($options['yes_icon'] === null || $options['no_icon'] === null) {
					throw new InvalidOptionsException(sprintf('When using "display_type" with a value of "%s" you must set "yes_icon" and "no_icon"!', $value));
				}
			}
			if($value === YesNoColumnType::DISPLAY_LABEL_ONLY || $value === YesNoColumnType::DISPLAY_ICON_WITH_LABEL || $value === YesNoColumnType::DISPLAY_ICON_WITH_TOOLTIP) {
				if($options['yes_label'] === null || $options['no_label'] === null) {
					throw new InvalidOptionsException(sprintf('When using "display_type" with a value of "%s" you must set "yes_label" and "no_label"!', $value));
				}
			}
			return $value;
		});

		$resolver->setDefault('label_translation_domain', 'StingerSoftAggridBundle');
		$resolver->setAllowedValues('label_translation_domain', static function($value) {
			if(is_string($value)) return true;
			if($value === null) return true;
			if($value === false) return true;
			return false;
		});
		$resolver->setDefault('cellRenderer', 'YesNoRenderer');
		$resolver->setDefault('filter_type', SetFilterType::class);

		$translator = $this->translator;
		$resolver->setNormalizer('filter_options', static function(Options $options, $value) use ($translator) {
			if($value === null) {
				$value = [];
			}
			$value['cellRenderer'] = 'YesNoRenderer';
			if($options['label_translation_domain'] === null) {
				$transDomain = $options['translation_domain'];
			} else {
				$transDomain = $options['label_translation_domain'];
			}
			$value['cellRendererParams'] = [
				'yes_label'    => $transDomain === false ? $options['yes_label'] : $translator->trans($options['yes_label'], [], $transDomain),
				'no_label'     => $transDomain === false ? $options['no_label'] : $translator->trans($options['no_label'], [], $transDomain),
				'yes_icon'     => $options['yes_icon'],
				'no_icon'      => $options['no_icon'],
				'display_type' => $options['filter_display_type'],
			];
			$value['data'] = static function(FilterInterface $filter, array $options, $dataSource, string $queryPath, string $rootAlias) {
				if($filter->getGridOption('dataMode') === GridType::DATA_MODE_ENTERPRISE) {
					return [0, 1];
				}
				return null;
			};
			return $value;

		});
//		$resolver->setDefault('js_column_template', '@StingerSoftAggrid/Column/yesno.js.twig');
	}

	public function buildView(ColumnView $view, ColumnInterface $column, array $options): void {
		if($options['label_translation_domain'] === null) {
			$view->vars['label_translation_domain'] = $options['translation_domain'];
		} else {
			$view->vars['label_translation_domain'] = $options['label_translation_domain'];
		}
		$view->vars['yes_label'] = $options['yes_label'];
		$view->vars['no_label'] = $options['no_label'];
		$view->vars['yes_icon'] = $options['yes_icon'];
		$view->vars['no_icon'] = $options['no_icon'];
		$view->vars['display_type'] = $options['display_type'];
		$view->vars['filter_display_type'] = $options['filter_display_type'];
		$view->vars['cellRendererParams'] = [
			'yes_label'    => $view->vars['label_translation_domain'] === false ? $options['yes_label'] : $this->translator->trans($options['yes_label'], [], $view->vars['label_translation_domain']),
			'no_label'     => $view->vars['label_translation_domain'] === false ? $options['no_label'] : $this->translator->trans($options['no_label'], [], $view->vars['label_translation_domain']),
			'yes_icon'     => $options['yes_icon'],
			'no_icon'      => $options['no_icon'],
			'display_type' => $options['display_type'],
		];
//		if( $options['display_type'] !== self::DISPLAY_LABEL_ONLY) {
//			$view->vars['render_html'] = true;
//		}
	}

}