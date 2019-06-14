<?php
/*
 * This file is part of the PEC Platform StreetScooterApp.
 *
 * (c) PEC project engineers &amp; consultants
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace StingerSoft\AggridBundle\Column;

use function PHPSTORM_META\type;
use StingerSoft\AggridBundle\Exception\InvalidArgumentTypeException;
use StingerSoft\AggridBundle\Helper\GridBuilder;
use StingerSoft\AggridBundle\View\ColumnView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ColumnGroupType extends AbstractColumnType {

	public function configureOptions(OptionsResolver $resolver, array $gridOptions = []): void {
		$resolver->setRequired(['label', 'children']);
		$resolver->setAllowedTypes('children', ['array']);
		$resolver->setNormalizer('children', static function (Options $options, $valueToNormalize) {
			if(!is_array($valueToNormalize)) {
				throw new InvalidArgumentTypeException('You must provide an array for the "children" option!');
			}
			foreach($valueToNormalize as $key => $item) {
				if(!$item instanceof ColumnInterface) {
					$type = is_object($item) ? get_class($item) : gettype($item);
					$message = sprintf('Entry %s of option "children" must be of type %s but is "%s"!%sDid you use %s::add() instead of %s::addColumn()?', $key, ColumnInterface::class, $type, PHP_EOL . PHP_EOL, GridBuilder::class, GridBuilder::class);
					throw new InvalidArgumentTypeException($message);
				}
			}
			return $valueToNormalize;
		});

		$resolver->setDefault('marryChildren', true);
		$resolver->setAllowedTypes('marryChildren', ['boolean']);

		$resolver->setDefault('orderable', false);
		$resolver->setDefault('searchable', false);
		$resolver->setDefault('filterable', false);
	}

	public function buildView(ColumnView $view, ColumnInterface $column, array $options): void {
		$view->vars['marryChildren'] = $options['marryChildren'];
	}

}