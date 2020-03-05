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

use StingerSoft\AggridBundle\Column\ColumnTypeInterface;
use StingerSoft\AggridBundle\Components\StatusBar\StatusBarComponentTypeInterface;
use StingerSoft\AggridBundle\Filter\FilterTypeInterface;
use StingerSoft\AggridBundle\Grid\GridTypeInterface;

interface DependencyInjectionExtensionInterface {

	public function resolveGridType(string $type): GridTypeInterface;

	public function resolveColumnType(string $type): ColumnTypeInterface;

	public function resolveStatusBarComponentType(string $type): StatusBarComponentTypeInterface;

	public function resolveFilterType(string $type): FilterTypeInterface;

	public function setParameter(string $key, $value): void;

	public function getParameter(string $key, $default = null);
}