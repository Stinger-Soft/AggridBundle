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

use Psr\Container\ContainerInterface;
use StingerSoft\AggridBundle\Column\ColumnTypeInterface;
use StingerSoft\AggridBundle\Filter\FilterTypeInterface;
use StingerSoft\AggridBundle\Grid\GridTypeInterface;
use StingerSoft\AggridBundle\Helper\InstanceHelperTrait;

class DependencyInjectionExtension implements DependencyInjectionExtensionInterface {

	use InstanceHelperTrait;

	protected $typeContainer;

	protected $parameters = array();

	/**
	 * @param ContainerInterface $typeContainer
	 */
	public function __construct(ContainerInterface $typeContainer) {
		$this->typeContainer = $typeContainer;
	}

	public function resolveGridType(string $type): GridTypeInterface {
		return $this->resolveType($type, GridTypeInterface::class);
	}

	public function resolveColumnType(string $type): ColumnTypeInterface {
		return $this->resolveType($type, ColumnTypeInterface::class);
	}


	public function resolveFilterType(string $type): FilterTypeInterface {
		return $this->resolveType($type, FilterTypeInterface::class);
	}

	public function setParameter(string $key, $value): void {
		$this->parameters[$key] = $value;
	}

	public function getParameter(string $key, $default = null) {
		return $this->parameters[$key] ?? $default;
	}

	/**
	 * @param string $type
	 * @param string $typeInterfaceClassName
	 * @return mixed|object
	 * @throws \ReflectionException
	 * @throws \StingerSoft\AggridBundle\Exception\InvalidArgumentTypeException
	 */
	protected function resolveType(string $type, string $typeInterfaceClassName) {
		if($this->typeContainer->has($type)) {
			return $this->typeContainer->get($type);
		}
		return $this->createTypeInstance($type, $typeInterfaceClassName);
	}

}