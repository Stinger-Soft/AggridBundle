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

use StingerSoft\AggridBundle\View\ColumnView;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractColumnTypeExtension implements ColumnTypeExtensionInterface {

	public function configureOptions(OptionsResolver $resolver, array $gridOptions = []): void {
	}

	public function buildView(ColumnView $view, ColumnInterface $column, array $options): void {
	}

	public function buildData(ColumnInterface $column, array $options) {
	}

	public function buildJsonConfiguration(ColumnView $view, ColumnInterface $column, array $options): void {
		
	}
}