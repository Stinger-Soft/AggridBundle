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

namespace StingerSoft\AggridBundle\Components\SideBar;

use StingerSoft\AggridBundle\Components\ComponentInterface;
use StingerSoft\AggridBundle\View\ComponentView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FiltersToolPanelType extends AbstractSideBarComponentType {

	public function configureOptions(OptionsResolver $resolver, array $gridOptions = []): void {
		$resolver->setDefault('iconKey', 'filter');
		$resolver->setDefault('labelDefault', 'Filters');
		$resolver->setDefault('labelKey', 'filters');
		$resolver->setDefault('componentAlias', 'agFiltersToolPanel');
		$resolver->setDefault('builtIn', true);

		$resolver->setDefault('suppressExpandAll', false);
		$resolver->setAllowedTypes('suppressExpandAll', 'bool');

		$resolver->setDefault('suppressFilterSearch', false);
		$resolver->setAllowedTypes('suppressFilterSearch', 'bool');
	}

	public function buildView(ComponentView $view, ComponentInterface $component, array $options): void {
		$params = $view->vars['toolPanelParams'];
		if($params === null) {
			$params = [];
		}
		$panelParams = [];
		if($options['suppressFilterSearch']) {
			$panelParams['suppressFilterSearch'] = true;
		}
		if($options['suppressExpandAll']) {
			$panelParams['suppressExpandAll'] = true;
		}
		if(count($panelParams) > 0) {
			$view->vars['toolPanelParams'] = array_merge($params, $panelParams);
		}
	}
}