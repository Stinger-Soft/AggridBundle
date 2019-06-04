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

namespace StingerSoft\AggridBundle\Grid;

use StingerSoft\AggridBundle\Column\Column;
use StingerSoft\AggridBundle\View\GridView;

interface GridInterface {
	/**
	 * Get all columns belonging to the grid.
	 *
	 * @return Column[] all columns belonging to the grid.
	 */
	public function getColumns(): array;

	public function createJsonData(): string;
}