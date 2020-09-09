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

namespace StingerSoft\AggridBundle\Components\StatusBar;

use StingerSoft\AggridBundle\Components\ComponentInterface;
use StingerSoft\AggridBundle\Components\ComponentType;
use StingerSoft\AggridBundle\View\ComponentView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StatusBarComponentType extends AbstractStatusBarComponentType {

	public function configureOptions(OptionsResolver $resolver, array $gridOptions = []): void {
		$resolver->setDefault('statusPanelParams', null);
		$resolver->setAllowedTypes('statusPanelParams', ['null', 'array']);

		$resolver->setDefault('align', null);
		$resolver->setAllowedValues('align', [
			null,
			StatusBarComponentTypeInterface::ALIGN_RIGHT,
			StatusBarComponentTypeInterface::ALIGN_LEFT,
			StatusBarComponentTypeInterface::ALIGN_CENTER,
		]);

		$resolver->setDefault('js_component_template', '@StingerSoftAggrid/Component/StatusBar/panel.js.twig');
	}

	public function buildView(ComponentView $view, ComponentInterface $component, array $options): void {
		$view->vars['key'] = $component->getId();
		$view->vars['align'] = $options['align'];
		$view->vars['statusPanelParams'] = $options['statusPanelParams'];
		$view->vars['statusPanel'] = $view->componentAlias;
	}

	public function getParent(): ?string {
		return ComponentType::class;
	}

}