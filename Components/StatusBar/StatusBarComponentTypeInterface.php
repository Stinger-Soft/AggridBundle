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

use StingerSoft\AggridBundle\Components\ComponentTypeInterface;

interface StatusBarComponentTypeInterface extends ComponentTypeInterface {

	public const ALIGN_LEFT = 'left';

	public const ALIGN_CENTER = 'center';

	public const ALIGN_RIGHT = 'right';

}