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

namespace StingerSoft\AggridBundle\Components\StatusBar;

use StingerSoft\AggridBundle\Components\ComponentInterface;
use StingerSoft\AggridBundle\View\ComponentView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AggregationComponentType extends AbstractStatusBarComponentType {

	protected const AGGREGATION_FUNCTIONS = [
		'count' => true,
		'sum'   => true,
		'max'   => true,
		'min'   => true,
		'avg'   => true,
	];

	public function configureOptions(OptionsResolver $resolver, array $gridOptions = []): void {
		$resolver->setDefault('componentIdentifier', 'agAggregationComponent');
		$resolver->setDefault('builtIn', true);

		foreach(self::AGGREGATION_FUNCTIONS as $option => $default) {
			$resolver->setDefault($option, $default);
		}
	}

	public function buildView(ComponentView $view, ComponentInterface $component, array $options): void {
		$params = $view->vars['statusPanelParams'];
		if($params === null) {
			$params = [];
		}
		$aggregationFunctions = [];
		foreach(array_keys(self::AGGREGATION_FUNCTIONS) as $function) {
			if(isset($options[$function]) && $options[$function] === true) {
				$aggregationFunctions[] = $function;
			}
		}
		if(count($aggregationFunctions) > 0) {
			$view->vars['statusPanelParams'] = array_merge($params, ['aggFuncs' => $aggregationFunctions]);
		}
	}

}