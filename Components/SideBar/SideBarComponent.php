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

namespace StingerSoft\AggridBundle\Components\SideBar;

use StingerSoft\AggridBundle\Components\Component;
use StingerSoft\AggridBundle\Components\ComponentInterface;
use StingerSoft\AggridBundle\View\ComponentView;
use StingerSoft\AggridBundle\View\SideBarComponentView;

class SideBarComponent extends Component implements SideBarComponentInterface {

	protected function createConcreteView(ComponentView $parent = null): ComponentView {
		return new SideBarComponentView($parent);
	}

	public function getComponentCategory(): string {
		return ComponentInterface::CATEGORY_SIDE_BAR;
	}

}