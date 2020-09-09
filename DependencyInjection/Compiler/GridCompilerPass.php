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

use StingerSoft\AggridBundle\StingerSoftAggridBundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class GridCompilerPass implements CompilerPassInterface {

	/** @var string */
	protected $gridExtensionService;

	/** @var string */
	protected $gridTypeTag;

	/** @var string */
	protected $columnTypeTag;

	/** @var string */
	protected $filterTypeTag;

	public function __construct(
		string $gridExtensionService = 'stingersoft_aggrid.extension',
		string $gridTypeTag = 'stingersoft_aggrid.grid',
		string $columnTypeTag = 'stingersoft_aggrid.column',
		string $filterTypeTag = StingerSoftAggridBundle::FILTER_TYPE_SERVICE_TAG
	) {
		$this->gridExtensionService = $gridExtensionService;
		$this->gridTypeTag = $gridTypeTag;
		$this->columnTypeTag = $columnTypeTag;
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

	}

	private function processTypes(ContainerBuilder $container, string $tagType, array &$servicesMap): array {
		// Builds an array with fully-qualified type class names as keys and service IDs as values
		foreach($container->findTaggedServiceIds($tagType, true) as $serviceId => $tag) {
			// Add form type service to the service locator
			$serviceDefinition = $container->getDefinition($serviceId);
			$servicesMap[$serviceDefinition->getClass()] = new Reference($serviceId);
		}
		return $servicesMap;
	}
}