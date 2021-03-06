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

use Symfony\Component\OptionsResolver\OptionsResolver;

class TotalAndFilteredRowCountComponentType extends AbstractStatusBarComponentType {

	public function configureOptions(OptionsResolver $resolver, array $gridOptions = []): void {
		$resolver->setDefault('componentAlias', 'agTotalAndFilteredRowCountComponent');
		$resolver->setDefault('builtIn', true);
	}
}