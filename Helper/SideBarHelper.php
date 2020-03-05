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

abstract class SideBarHelper {

	public static function getColumnToolPanelConfiguration(bool $suppressRowGroups = false, bool $suppressValues = false, bool $suppressPivots = true,
														   bool $suppressPivotMode = true, bool $suppressSideButtons = false, bool $suppressColumnFilter = false,
														   bool $suppressColumnSelectAll = false, bool $suppressColumnExpandAll = false): array {
		$toolPanelParams = [
			'suppressRowGroups'       => $suppressRowGroups,
			'suppressValues'          => $suppressValues,
			'suppressPivots'          => $suppressPivots,
			'suppressPivotMode'       => $suppressPivotMode,
			'suppressSideButtons'     => $suppressSideButtons,
			'suppressColumnFilter'    => $suppressColumnFilter,
			'suppressColumnSelectAll' => $suppressColumnSelectAll,
			'suppressColumnExpandAll' => $suppressColumnExpandAll,
		];
		return self::getToolPanelConfiguration('columns', 'Columns', 'columns', 'columns', 'agColumnsToolPanel', $toolPanelParams);

	}

	public static function getFilterToolPanelConfiguration(bool $suppressExpandAll = false, bool $suppressFilterSearch = false): array {
		$toolPanelParams = [
			'suppressExpandAll'    => $suppressExpandAll,
			'suppressFilterSearch' => $suppressFilterSearch,
		];
		return self::getToolPanelConfiguration('filters', 'Filters', 'filters', 'filter', 'agFiltersToolPanel', $toolPanelParams);
	}

	public static function getToolPanelConfiguration(string $id, string $labelDefault, string $labelKey, string $iconKey, string $toolPanel, array $toolPanelParams): array {
		return [
			'id'              => $id,
			'labelDefault'    => $labelDefault,
			'labelKey'        => $labelKey,
			'iconKey'         => $iconKey,
			'toolPanel'       => $toolPanel,
			'toolPanelParams' => $toolPanelParams,
		];
	}
}