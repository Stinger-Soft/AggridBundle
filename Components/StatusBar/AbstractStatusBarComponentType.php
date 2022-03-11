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

use StingerSoft\AggridBundle\Components\ComponentInterface;
use StingerSoft\AggridBundle\View\ComponentView;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractStatusBarComponentType implements StatusBarComponentTypeInterface {

	public function configureOptions(OptionsResolver $resolver, array $gridOptions = []): void {
	}

	public function buildView(ComponentView $view, ComponentInterface $component, array $options): void {
	}

	public function getParent(): ?string {
		return StatusBarComponentType::class;
	}
}