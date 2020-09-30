<?php


namespace StingerSoft\AggridBundle\Column;


use ArrayAccess;
use StingerSoft\AggridBundle\Filter\SetFilterType;
use StingerSoft\AggridBundle\View\ColumnView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class StateColumnType extends AbstractColumnType {

	public const DISPLAY_ICON_ONLY = 'icon-only';
	public const DISPLAY_LABEL_ONLY = 'label-only';
	public const DISPLAY_ICON_WITH_LABEL = 'icon-with-label';
	public const DISPLAY_ICON_WITH_TOOLTIP = 'icon-with-tooltip';

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
		$resolver->setRequired('colorMapping');
		$resolver->setAllowedTypes('colorMapping', 'array');
		$resolver->setDefault('colorMappingDomain', false);
		$resolver->setAllowedTypes('colorMappingDomain', ['bool', 'string']);

		$resolver->setRequired('iconMapping');
		$resolver->setAllowedTypes('iconMapping', 'array');
		$resolver->setDefault('iconMappingDomain', false);
		$resolver->setAllowedTypes('iconMappingDomain', ['bool', 'string']);

		$resolver->setRequired('labelMapping');
		$resolver->setAllowedTypes('labelMapping', 'array');
		$resolver->setDefault('labelMappingDomain', false);
		$resolver->setAllowedTypes('labelMappingDomain', ['bool', 'string']);

		$resolver->setDefault('display_type', self::DISPLAY_ICON_WITH_TOOLTIP);
		$resolver->setAllowedValues('display_type', array(
			self::DISPLAY_ICON_WITH_TOOLTIP,
			self::DISPLAY_ICON_WITH_LABEL,
			self::DISPLAY_LABEL_ONLY,
			self::DISPLAY_ICON_ONLY
		));

		$resolver->setDefault('filter_display_type', self::DISPLAY_ICON_WITH_LABEL);
		$resolver->setAllowedValues('filter_display_type', array(
			self::DISPLAY_ICON_WITH_TOOLTIP,
			self::DISPLAY_ICON_WITH_LABEL,
			self::DISPLAY_LABEL_ONLY,
			self::DISPLAY_ICON_ONLY
		));

		$resolver->setDefault('cellRenderer', 'StateRenderer');
		$resolver->setDefault('filter_type', SetFilterType::class);

		$resolver->setNormalizer('filter_options', function(Options $options, $value) {

			if($value === null) {
				$value = [];
			}
			$value['cellRenderer'] = 'StateRenderer';
			$value['cellRendererParams'] = [
				'states'       => $this->getStateMappings($options),
				'display_type' => $options['filter_display_type'],
			];
			return $value;
		});
	}

	/**
	 * @param array|ArrayAccess $options
	 * @return array
	 */
	protected function getStateMappings($options): array {
		$states = [];
		$labelMapping = $options['labelMapping'];
		$labelMappingDomain = $options['labelMappingDomain'];
		$iconMapping = $options['iconMapping'];
		$iconMappingDomain = $options['iconMappingDomain'];
		$colorMapping = $options['colorMapping'];
		$colorMappingDomain = $options['colorMappingDomain'];

		foreach($labelMapping as $stateId => $stateLabel) {
			$states[$stateId] = [
				'label' => $labelMappingDomain === false ? $stateLabel : $this->translator->trans($stateLabel, [], $labelMappingDomain),
				'icon'  => $iconMappingDomain === false ? $iconMapping[$stateId] : $this->translator->trans($iconMapping[$stateId], [], $iconMappingDomain),
				'color' => $colorMappingDomain === false ? $colorMapping[$stateId] : $this->translator->trans($colorMapping[$stateId], [], $colorMappingDomain),
			];
		}
		return $states;
	}

	public function buildView(ColumnView $view, ColumnInterface $column, array $options): void {
		$view->vars['cellRendererParams'] = [
			'states'       => $this->getStateMappings($options),
			'display_type' => $options['display_type'],
		];
	}
}