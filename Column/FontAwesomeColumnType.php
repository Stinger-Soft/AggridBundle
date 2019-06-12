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
 * Renders a font awesome icon.
 */
class FontAwesomeColumnType extends AbstractColumnType {

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver, array $tableOptions = array()): void {
		$resolver->setDefault('mapped', true);
		$resolver->setDefault('no_value_icon', null);
		$resolver->setAllowedTypes('no_value_icon', array(
			'null',
			'string'
		));
		$resolver->setDefault('template', '@StingerSoftAggrid/Column/fontawesome_icon.html.twig');
	}

	/**
	 *
	 * {@inheritdoc}
	 */
	public function getParent(): ?string {
		return TemplatedColumnType::class;
	}
}