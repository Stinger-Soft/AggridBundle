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

namespace StingerSoft\AggridBundle\DependencyInjection\Compiler;

use StingerSoft\AggridBundle\Service\DependencyInjectionExtensionInterface;
use StingerSoft\AggridBundle\StingerSoftAggridBundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

class GridCompilerPass implements CompilerPassInterface {

	use PriorityTaggedServiceTrait;

	/** @var string */
	protected $gridExtensionService;

	/** @var string */
	protected $gridTypeTag;

	/** @var string */
	protected $columnTypeTag;

	/** @var string */
	protected $filterTypeTag;

	/** @var string */
	protected $gridTypeExtensionTag;

	/** @var string */
	protected $columnTypeExtensionTag;

	public function __construct(
		string $gridExtensionService = DependencyInjectionExtensionInterface::class,
		string $gridTypeTag = StingerSoftAggridBundle::GRID_TYPE_SERVICE_TAG,
		string $columnTypeTag = StingerSoftAggridBundle::COLUMN_TYPE_SERVICE_TAG,
		string $filterTypeTag = StingerSoftAggridBundle::FILTER_TYPE_SERVICE_TAG,
		string $gridTypeExtensionTag = StingerSoftAggridBundle::GRID_TYPE_EXTENSION_SERVICE_TAG,
		string $columnTypeExtensionTag = StingerSoftAggridBundle::COLUMN_TYPE_EXTENSION_SERVICE_TAG
	) {
		$this->gridExtensionService = $gridExtensionService;
		$this->gridTypeTag = $gridTypeTag;
		$this->gridTypeExtensionTag = $gridTypeExtensionTag;
		$this->columnTypeTag = $columnTypeTag;
		$this->columnTypeExtensionTag = $columnTypeExtensionTag;
		$this->filterTypeTag = $filterTypeTag;
	}

	public function process(ContainerBuilder $container): void {
		$definition = null;
		if($container->hasAlias($this->gridExtensionService)) {
			$this->gridExtensionService = $container->getAlias($this->gridExtensionService)->__toString();
		}

		if(!$container->hasDefinition($this->gridExtensionService)) {
			return;
		}

		$definition = $container->getDefinition($this->gridExtensionService);

		$servicesMap = [];
		$this->processTypes($container, $this->gridTypeTag, $servicesMap);
		$this->processTypes($container, $this->columnTypeTag, $servicesMap);
		$this->processTypes($container, $this->filterTypeTag, $servicesMap);
		$definition->addArgument(ServiceLocatorTagPass::register($container, $servicesMap));

		$definition->addArgument($this->processExtensionType($container, $this->gridTypeExtensionTag));
		$definition->addArgument($this->processExtensionType($container, $this->columnTypeExtensionTag));
	}

	protected function processExtensionType(ContainerBuilder $container, string $tagType): array {
		$typeExtensionsClasses = [];
		foreach($this->findAndSortTaggedServices($tagType, $container) as $reference) {
			$serviceId = (string) $reference;
			$serviceDefinition = $container->getDefinition($serviceId);
			$parameterBag = $container->getParameterBag();
			if($parameterBag === null) {
				return [];
			}
			$typeExtensionClass = $parameterBag->resolveValue($serviceDefinition->getClass());
			if(!method_exists($typeExtensionClass, 'getExtendedTypes')) {
				new InvalidArgumentException(sprintf('"%s" tagged services have to implement the static getExtendedTypes() method. Class "%s" for service "%s" does not implement it.', $tagType, $typeExtensionClass, $serviceId));
			}
			$extendsTypes = false;
			foreach($typeExtensionClass::getExtendedTypes() as $extendedType) {
				$typeExtensionsClasses[$extendedType][] = new Reference($serviceId);
				$extendsTypes = true;
			}
			if(!$extendsTypes) {
				throw new InvalidArgumentException(sprintf('The getExtendedTypes() method for service "%s" does not return any extended types.', $serviceId));
			}
		}
		return $typeExtensionsClasses;
	}

	private function processTypes(ContainerBuilder $container, string $tagType, array &$servicesMap): array {
		foreach($container->findTaggedServiceIds($tagType, true) as $serviceId => $tag) {
			$serviceDefinition = $container->getDefinition($serviceId);
			$servicesMap[$serviceDefinition->getClass()] = new Reference($serviceId);
		}
		return $servicesMap;
	}
}