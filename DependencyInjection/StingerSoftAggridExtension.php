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

namespace StingerSoft\AggridBundle\DependencyInjection;

use StingerSoft\AggridBundle\StingerSoftAggridBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class StingerSoftAggridExtension extends Extension {

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @throws \Exception
	 */
	public function load(array $configs, ContainerBuilder $container): void {
		$configuration = new Configuration();
		$config = $this->processConfiguration($configuration, $configs);

		$loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
		$loader->load('services.yml');

		$container->setParameter(StingerSoftAggridBundle::PARAMETER_LICENSE_KEY, $config['licenseKey']);
	}
}
