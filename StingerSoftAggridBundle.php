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

namespace StingerSoft\AggridBundle;

use StingerSoft\AggridBundle\Column\ColumnTypeExtensionInterface;
use StingerSoft\AggridBundle\Column\ColumnTypeInterface;
use StingerSoft\AggridBundle\DependencyInjection\Compiler\GridCompilerPass;
use StingerSoft\AggridBundle\Filter\FilterTypeExtensionInterface;
use StingerSoft\AggridBundle\Filter\FilterTypeInterface;
use StingerSoft\AggridBundle\Grid\GridTypeExtensionInterface;
use StingerSoft\AggridBundle\Grid\GridTypeInterface;
use StingerSoft\AggridBundle\Service\DependencyInjectionExtensionInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 */
class StingerSoftAggridBundle extends Bundle {

	public const GRID_TYPE_SERVICE_TAG = 'stingersoft_aggrid.grid';
	public const GRID_TYPE_EXTENSION_SERVICE_TAG = 'stingersoft_aggrid.grid_extension';

	public const COLUMN_TYPE_SERVICE_TAG = 'stingersoft_aggrid.column';
	public const COLUMN_TYPE_EXTENSION_SERVICE_TAG = 'stingersoft_aggrid.column_extension';

	public const FILTER_TYPE_SERVICE_TAG = 'stingersoft_aggrid.filter';
	public const FILTER_TYPE_EXTENSION_SERVICE_TAG = 'stingersoft_aggrid.filter_extension';

	public const GRID_EXTENSION_SERVICE_ID = DependencyInjectionExtensionInterface::class;
	public const PARAMETER_LICENSE_KEY = 'stingersoft_aggrid.licenseKey';

	/**
	 * @param $env
	 * @return array
	 */
	public static function getRequiredBundles($env): array {
		$bundles = [];
		$bundles['StingerSoftAggridBundle'] = '\\' . __CLASS__;
		return $bundles;
	}

	public function build(ContainerBuilder $container): void {
		$container->registerForAutoconfiguration(GridTypeInterface::class)->addTag(self::GRID_TYPE_SERVICE_TAG);
		$container->registerForAutoconfiguration(ColumnTypeInterface::class)->addTag(self::COLUMN_TYPE_SERVICE_TAG);
		$container->registerForAutoconfiguration(FilterTypeInterface::class)->addTag(self::FILTER_TYPE_SERVICE_TAG);
		$container->registerForAutoconfiguration(GridTypeExtensionInterface::class)->addTag(self::GRID_TYPE_EXTENSION_SERVICE_TAG);
		$container->registerForAutoconfiguration(ColumnTypeExtensionInterface::class)->addTag(self::COLUMN_TYPE_EXTENSION_SERVICE_TAG);
		$container->registerForAutoconfiguration(FilterTypeExtensionInterface::class)->addTag(self::FILTER_TYPE_EXTENSION_SERVICE_TAG);
		$container->addCompilerPass(
			new GridCompilerPass(
				self::GRID_EXTENSION_SERVICE_ID,
				self::GRID_TYPE_SERVICE_TAG,
				self::COLUMN_TYPE_SERVICE_TAG,
				self::FILTER_TYPE_SERVICE_TAG,
				self::GRID_TYPE_EXTENSION_SERVICE_TAG,
				self::COLUMN_TYPE_EXTENSION_SERVICE_TAG,
				self::FILTER_TYPE_EXTENSION_SERVICE_TAG
			));
	}
}