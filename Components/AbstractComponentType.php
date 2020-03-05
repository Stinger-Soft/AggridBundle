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

namespace StingerSoft\AggridBundle\Components;

use StingerSoft\AggridBundle\View\ComponentView;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractComponentType implements ComponentTypeInterface {

	/**
	 * @inheritDoc
	 */
	public function configureOptions(OptionsResolver $resolver, array $gridOptions = []): void {
		$resolver->setRequired('componentAlias');
		$resolver->setAllowedTypes('componentAlias', 'string');

		$resolver->setDefault('builtIn', false);
		$resolver->setAllowedTypes('builtIn', 'bool');

		$resolver->setDefault('component', null);
		$resolver->setAllowedTypes('component', ['null', 'string']);
		$resolver->setNormalizer('component', static function (Options $options, $valueToNormalize) {
			if($options['builtIn'] === false && $valueToNormalize === null) {
				throw new InvalidOptionsException('You must specify a component, if "builtIn" option is false!');
			}
			return $valueToNormalize;
		});

		$resolver->setRequired('js_component_template');
		$resolver->setAllowedTypes('js_component_template', 'string');
	}

	/**
	 * @inheritDoc
	 */
	public function buildView(ComponentView $view, ComponentInterface $component, array $options): void {
		$view->id = $component->getId();
		$view->component = $options['component'];
		$view->componentAlias = $options['componentAlias'];
		$view->template = $options['js_component_template'];
		$view->vars['builtIn'] = $options['builtIn'];
	}

	/**
	 * @inheritDoc
	 */
	public function getParent(): ?string {
		return null;
	}
}