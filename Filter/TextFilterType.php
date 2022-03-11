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

namespace StingerSoft\AggridBundle\Filter;

use Symfony\Component\OptionsResolver\OptionsResolver;

class TextFilterType extends AbstractFilterType {

	public function configureOptions(OptionsResolver $resolver, array $columnOptions = [], array $tableOptions = []): void {
		$resolver->setDefault('filter_type', 'agTextColumnFilter');
	}
}