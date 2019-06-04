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

namespace StingerSoft\AggridBundle\Helper;

use StingerSoft\AggridBundle\Exception\InvalidArgumentTypeException;

/**
 * The InstanceHelperTrait helps creating instances of a certain class type under certain conditions.
 *
 * In case an object instance of a certain type (column type, filter type, grid type) shall be used, some options for
 * those types may be configured and resolved along a hierarchy of types - similar to Symfony form components. As such,
 * any parent types must be constructed properly and their corresponding configuration methods must be called in
 * order to ensure correct initialisation of options etc.
 *
 * As it can happen that certain types to not have an empty constructor or implement the required interfaces, the helper
 * trait tries to invoke the types constructor with either no arguments at all (default behaviour) or by reflection
 * in case a constructor exists, but expects required parameters.
 */
trait InstanceHelperTrait {

	/**
	 * Tries to create an instance of the given type class, ensuring that it implements the given interface.
	 *
	 * @param $typeClassName          string the FQCN of the type to be created
	 * @param $typeInterfaceClassName string|null the FQCN of the interface the type to be created must implement or
	 *                                null if no interface must be implemented
	 * @return object a new instance of the given type class
	 * @throws InvalidArgumentTypeException in case the class identified by the given class name does not implement
	 * @throws \ReflectionException
	 *                                identified by the the given interface class name
	 */
	public function createTypeInstance($typeClassName, $typeInterfaceClassName = null) {
		$reflectionClass = new \ReflectionClass($typeClassName);
		if($typeInterfaceClassName !== null && !$reflectionClass->implementsInterface($typeInterfaceClassName)) {
			throw new InvalidArgumentTypeException(sprintf('The given class type "%s" does not implement the interface "%s"', $typeClassName, $typeInterfaceClassName));
		}

		if($reflectionClass->getConstructor() === null || $reflectionClass->getConstructor()->getNumberOfRequiredParameters() > 0) {
			return $reflectionClass->newInstanceWithoutConstructor();
		}

		return $reflectionClass->newInstance();
	}

}