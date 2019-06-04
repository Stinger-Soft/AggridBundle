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

namespace StingerSoft\AggridBundle\Exception;

use Symfony\Component\Form\Exception\InvalidConfigurationException;

class OrderedConfigurationException extends InvalidConfigurationException {
	/**
	 * Creates a "CIRCULAR DIFFERED" exception.
	 *
	 * @param array $stack The circular stack.
	 * @param string $position The position (before|after).
	 *
	 * @return self The "CIRCULAR DIFFERED" exception.
	 */
	public static function createCircularDiffered(array $stack, $position): self {
		$stack[] = $stack[0];

		return new self(sprintf(
			'The form ordering cannot be resolved due to conflict in %s positions (%s).',
			$position,
			implode(' => ', self::decorateValues($stack))
		));
	}

	/**
	 * Creates an "INVALID DIFFERED" exception.
	 *
	 * @param string $name The form name.
	 * @param string $position The position (before|after).
	 * @param string $differed The differed form name.
	 *
	 * @return self The "INVALID DIFFERED" exception.
	 */
	public static function createInvalidDiffered($name, $position, $differed): self {
		$decoratedDiffered = self::decorateValue($differed);

		return new self(sprintf(
			'The %s form is configured to be placed just %s the form %s but the form %s does not exist.',
			self::decorateValue($name),
			$position,
			$decoratedDiffered,
			$decoratedDiffered
		));
	}

	/**
	 * Creates an "INVALID STRING POSITION" exception.
	 *
	 * @param string $name The form name.
	 * @param string $position The invalid string position.
	 *
	 *
	 * @return self The "INVALID STRING POSITION" exception.
	 */
	public static function createInvalidStringPosition($name, $position): self {
		return new self(sprintf(
			'The %s form uses position as string which can only be "first" or "last" (current: %s).',
			self::decorateValue($name),
			self::decorateValue($position)
		));
	}

	/**
	 * Creates an "INVALID ARRAY CONFIGURATION" exception.
	 *
	 * @param string $name The form name.
	 * @param array $position The invalid array position.
	 *
	 * @return self The "INVALID ARRAY CONFIGURATION" exception.
	 */
	public static function createInvalidArrayPosition($name, array $position): self {
		return new self(sprintf(
			'The %s form uses position as array or you must define the "before" or "after" option (current: %s).',
			self::decorateValue($name),
			implode(', ', self::decorateValues(array_keys($position)))
		));
	}

	/**
	 * Creates a "SYMETRIC DIFFERED" exception.
	 *
	 * @param string $name The form name.
	 * @param string $symetric The symectric form name.
	 *
	 * @return self The "SYMETRIC DIFFERED" exception.
	 */
	public static function createSymetricDiffered($name, $symetric): self {
		return new self(sprintf(
			'The form ordering does not support symetrical before/after option (%s <=> %s).',
			self::decorateValue($name),
			self::decorateValue($symetric)
		));
	}

	/**
	 * Decorates values with the decorator.
	 *
	 * @param array $values The values.
	 * @param string $decorator The decorator.
	 *
	 * @return array The decorated values.
	 */
	private static function decorateValues(array $values, string $decorator = '"'): array {
		$result = array();

		foreach($values as $key => $value) {
			$result[$key] = self::decorateValue($value, $decorator);
		}

		return $result;
	}

	/**
	 * Decorates a value with the decorator.
	 *
	 * @param string $value The value.
	 * @param string $decorator The decorator.
	 *
	 * @return string The decorated value.
	 */
	private static function decorateValue(string $value, string $decorator = '"'): string {
		return $decorator . $value . $decorator;
	}
}
