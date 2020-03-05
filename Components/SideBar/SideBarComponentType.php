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

namespace StingerSoft\AggridBundle\Components\SideBar;

use StingerSoft\AggridBundle\Components\ComponentInterface;
use StingerSoft\AggridBundle\Components\ComponentType;
use StingerSoft\AggridBundle\View\ComponentView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SideBarComponentType extends AbstractSideBarComponentType {

	public function configureOptions(OptionsResolver $resolver, array $gridOptions = []): void {
		$resolver->setDefined('labelKey');
		$resolver->setAllowedTypes('labelKey', 'string');

		$resolver->setRequired('labelDefault');
		$resolver->setAllowedTypes('labelDefault', 'string');

		$resolver->setRequired('iconKey');
		$resolver->setAllowedTypes('iconKey', 'string');

		$resolver->setDefault('toolPanelParams', null);
		$resolver->setAllowedTypes('toolPanelParams', ['null', 'array']);

		$resolver->setDefault('js_component_template', '@StingerSoftAggrid/Component/SideBar/panel.js.twig');
	}

	public function buildView(ComponentView $view, ComponentInterface $component, array $options): void {
		$view->vars['toolPanel'] = $view->componentAlias;
		$view->vars['toolPanelParams'] = $options['toolPanelParams'];
		$view->vars['labelKey'] = $options['labelKey'] ?? null;
		$view->vars['labelDefault'] = $options['labelDefault'];
		$view->vars['iconKey'] = $options['iconKey'];
	}

	public function getParent(): ?string {
		return ComponentType::class;
	}

}