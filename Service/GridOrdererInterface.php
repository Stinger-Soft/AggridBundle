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

namespace StingerSoft\AggridBundle\Service;

use StingerSoft\AggridBundle\Grid\GridInterface;

interface GridOrdererInterface {

	/**
	 * Orders the columns of the given grid.
	 *
	 * @param GridInterface $grid The grid to order the columns of.
	 *
	 * @return array The ordered column child names.
	 */
	public function order(GridInterface $grid) : array;
}