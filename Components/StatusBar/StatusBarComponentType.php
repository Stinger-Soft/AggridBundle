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

use StingerSoft\AggridBundle\View\StatusBarComponentView;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StatusBarComponentType extends AbstractStatusBarComponentType {

	public function configureOptions(OptionsResolver $resolver, array $gridOptions = []): void {
		$resolver->setRequired('componentIdentifier');
		$resolver->setAllowedTypes('componentIdentifier', 'string');

		$resolver->setDefault('params', null);
		$resolver->setAllowedTypes('params', ['null', 'array']);

		$resolver->setDefault('builtIn', false);
		$resolver->setAllowedTypes('builtIn', 'bool');

		$resolver->setDefault('componentName', null);
		$resolver->setAllowedTypes('componentName', ['null', 'string']);
		$resolver->setNormalizer('componentName', static function(Options $options, $valueToNormalize) {
			if($options['builtIn'] === false && $valueToNormalize === null) {
				throw new InvalidOptionsException('You must specify a component name, if "builtIn" option is false!');
			}
			return $valueToNormalize;
		});

		$resolver->setDefault('builtIn', false);
		$resolver->setAllowedTypes('builtIn', 'bool');

		$resolver->setDefault('align', null);
		$resolver->setAllowedValues('align', [
			null,
			StatusBarComponentTypeInterface::ALIGN_RIGHT,
			StatusBarComponentTypeInterface::ALIGN_LEFT,
			StatusBarComponentTypeInterface::ALIGN_CENTER,
		]);

		$resolver->setDefault('js_component_template', '@StingerSoftAggrid/Component/StatusBar/panel.js.twig');
		$resolver->setAllowedTypes('js_component_template', 'string');
	}

	public function buildView(StatusBarComponentView $view, StatusBarComponentInterface $column, array $options): void {
		$view->id = $column->getId();
		$view->componentName = $options['componentName'];
		$view->componentIdentifier = $options['componentIdentifier'];
		$view->template = $options['js_component_template'];

		$view->vars['builtIn'] = $options['builtIn'];
		$view->vars['key'] = $column->getId();
		$view->vars['align'] = $options['align'];
		$view->vars['params'] = $options['params'];
	}

	public function getParent(): ?string {
		return null;
	}

}