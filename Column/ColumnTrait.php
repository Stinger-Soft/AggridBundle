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

namespace StingerSoft\AggridBundle\Column;

use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use function explode;

/**
 * The default column trait provides several useful default methods which may be necessary for column types.
 */
trait ColumnTrait {

	/**
	 * @var PropertyAccessor Helper to access properties by its path
	 */
	private $propAccessor;

	/**
	 * @var LoggerInterface $logger
	 */
	private $logger;

	/**
	 * Get the value from the given item under the given path.
	 *
	 * @param mixed              $item    object the object to retrieve the value from
	 * @param string             $path    string the path to use for getting the value, usually this is the name of a property,
	 *                                    field or "get" method. The value is retrieved using a PropertyAccessor.
	 * @param Options|array|null $options any additional options which may be required
	 *                                    for accessing the value, not used in the default implementation, but maybe useful for
	 *                                    inheriting classes.
	 * @return mixed|null the value, which may be null
	 * @see          PropertyAccessor for details on how $path is used for retrieving a value from the given $item.
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function generateItemValue($item, string $path, $options = null) {
		$propAccessor = $this->getPropertyAccessor();
		try {
			//Grouping or hydrates as array
			if(is_array($item)) {
				$arrayPath = '[' . str_replace('.', '][', $path) . ']';
				if($propAccessor->isReadable($item, $arrayPath)) {
					return $propAccessor->getValue($item, $arrayPath);
				}
				$pathArray = explode('.', $path);
				return $item[array_pop($pathArray)];
			}
			//normal items
			return $propAccessor->getValue($item, $path);
		} catch(NoSuchPropertyException $noSuchPropertyException) {
		} catch(UnexpectedTypeException $unexpectedTypeException) {
		}
		return null;
	}

	/**
	 * Get the value configured for the given option from the options array if it is scalar, otherwise return the value
	 * of the callable if the option is a callable.
	 *
	 * In case the option is a delegate, it must have the following signature:
	 * <code>function(mixed $item, string $path, array $options)</code>
	 *
	 * Where $item is the $item passed to this function, $path is the $path passed to this function and $options are the
	 * $options passed to this function. The delegate is expected to return a value matching the option specific type
	 * definition.
	 *
	 * @param string $optionName the name of the option to get the value for
	 * @param mixed  $item       the item to be passed to the delegate if necessary
	 * @param string $path       the path to be passed to the delegate if necessary
	 * @param array  $options    the options to get the option value from and to be passed to the delegate if necessary
	 * @return mixed the scalar value of the option or the value returned by the delegate.
	 */
	public function getDelegateValueOrScalar(string $optionName, $item, string $path, array $options = []) {
		return is_callable($options[$optionName]) ? call_user_func($options[$optionName], $item, $path, $options) : $options[$optionName];
	}

	/**
	 * Get the logger.
	 *
	 * @return LoggerInterface the logger.
	 */
	protected function getLogger(): LoggerInterface {
		return $this->logger;
	}

	/**
	 * @required
	 * @param LoggerInterface $logger
	 */
	public function setLogger(LoggerInterface $logger): void {
		$this->logger = $logger;
	}

	/**
	 * Gets a helper to access properties by its path
	 *
	 * @return PropertyAccessor
	 */
	protected function getPropertyAccessor(): PropertyAccessor {
		if(!$this->propAccessor) {
			$this->propAccessor = PropertyAccess::createPropertyAccessorBuilder()->enableExceptionOnInvalidIndex()->getPropertyAccessor();
		}
		return $this->propAccessor;
	}

}