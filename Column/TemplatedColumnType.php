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

namespace StingerSoft\AggridBundle\Column;

use StingerSoft\AggridBundle\Helper\TemplatingTrait;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Templating\EngineInterface;
use Twig\Environment;

class TemplatedColumnType extends AbstractColumnType {

	use ColumnTrait;
	use TemplatingTrait;

	public function __construct(?EngineInterface $templating, ?Environment $twig) {
		$this->templating = $templating;
		$this->twig = $twig;
	}

	/**
	 * {@inheritdoc}
	 * @see \Pec\Bundle\DatatableBundle\Column\AbstractColumnType::configureOptions()
	 */
	public function configureOptions(OptionsResolver $resolver, array $tableOptions = array()): void {
		$resolver->setRequired('template');
		$resolver->setAllowedTypes('template', 'string');

		$resolver->setDefault('mapped', false);
		$resolver->setAllowedTypes('mapped', 'boolean');

		$resolver->setDefault('additionalContext', array());
		$resolver->setAllowedTypes('additionalContext', 'array');

		$that = $this;
		$resolver->setDefault('value_delegate', function($item, $path, $options) use ($that, $tableOptions) {
			$value = $options['mapped'] ? $this->generateItemValue($item, $path, $options) : null;
			$originalContext = array(
				'item'         => $item,
				'path'         => $path,
				'value'        => $value,
				'options'      => $options,
				'tableOptions' => $tableOptions
			);
			$additionalContext = $options['additionalContext'];
			$context = array_merge($additionalContext, $originalContext);
			return trim($that->renderView($options['template'], $context));
		});
	}

}