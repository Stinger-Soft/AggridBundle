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

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;

class HierarchyColumnType extends AbstractColumnType {

	public function configureOptions(OptionsResolver $resolver, array $tableOptions = []): void {
		$resolver->setRequired('parent_field');
		$resolver->setDefault('id_field', 'id');
		$resolver->setDefault('path', 'orgHierarchy');
		$resolver->setDefault('visible', false);

		$propertyAccessor = PropertyAccess::createPropertyAccessor();

		$resolver->setDefault('value_delegate', static function ($item, string $path, array $options) use ($propertyAccessor) {
			$paths = [];
			$parent = $item;
			while($parent = $propertyAccessor->getValue($parent, $options['parent_field'])) {
				array_unshift($paths, $propertyAccessor->getValue($parent, $options['id_field']));
			}
			$paths[] = $propertyAccessor->getValue($item, $options['id_field']);
			return $paths;
		});
	}
}