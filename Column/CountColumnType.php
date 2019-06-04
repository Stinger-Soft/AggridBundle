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

/**
 * Displays the count of an array or collections field instead of displaying the data itself
 */
class CountColumnType extends AbstractColumnType {

	/**
	 * {@inheritdoc}
	 * @see \Pec\Bundle\DatatableBundle\Column\AbstractColumnType::configureOptions()
	 */
	public function configureOptions(OptionsResolver $resolver, array $tableOptions = array()): void {
		$resolver->setDefault('orderable', false);
		$resolver->setDefault('searchable', false);

		$propAccessor = $this->getPropertyAccessor();
		$resolver->setDefault('value_delegate', function($item, $path) use ($propAccessor) {
			return count($propAccessor->getValue($item, $path));
		});
	}

	/**
	 * {@inheritDoc}
	 * @see \Pec\Bundle\DatatableBundle\Column\AbstractColumnType::getParent()
	 */
	public function getParent(): string {
		return IntegerColumnType::class;
	}
}