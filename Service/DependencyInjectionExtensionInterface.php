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

use ReflectionException;
use StingerSoft\AggridBundle\Column\ColumnTypeInterface;
use StingerSoft\AggridBundle\Components\ComponentTypeInterface;
use StingerSoft\AggridBundle\Exception\InvalidArgumentTypeException;
use StingerSoft\AggridBundle\Filter\FilterTypeInterface;
use StingerSoft\AggridBundle\Grid\GridTypeInterface;

interface DependencyInjectionExtensionInterface {

	/**
	 * @param string $type
	 * @return GridTypeInterface
	 * @throws InvalidArgumentTypeException
	 * @throws ReflectionException
	 */
	public function resolveGridType(string $type): GridTypeInterface;

	/**
	 * @param string $type
	 * @return ColumnTypeInterface
	 * @throws InvalidArgumentTypeException
	 * @throws ReflectionException
	 */
	public function resolveColumnType(string $type): ColumnTypeInterface;

	/**
	 * @param string $type
	 * @return ComponentTypeInterface
	 * @throws InvalidArgumentTypeException
	 * @throws ReflectionException
	 */
	public function resolveComponentType(string $type): ComponentTypeInterface;

	/**
	 * @param string $type
	 * @return FilterTypeInterface
	 * @throws InvalidArgumentTypeException
	 * @throws ReflectionException
	 */
	public function resolveFilterType(string $type): FilterTypeInterface;

	public function setParameter(string $key, $value): void;

	public function getParameter(string $key, $default = null);
}