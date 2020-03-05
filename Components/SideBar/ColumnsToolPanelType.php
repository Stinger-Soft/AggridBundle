<?php
/*
 * This file is part of the Stinger Soft AgGrid package.
 *
 * (c) Oliver Kotte <oliver.kotte@stinger-soft.net>
 * (c) Florian Meyer <florian.meyer@stinger-soft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace StingerSoft\AggridBundle\Components\SideBar;

use StingerSoft\AggridBundle\Components\ComponentInterface;
use StingerSoft\AggridBundle\View\ComponentView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ColumnsToolPanelType extends AbstractSideBarComponentType {

	protected const SUPPRESS_OPTIONS = [
		'suppressRowGroups'          => false,
		'suppressValues'             => false,
		'suppressPivots'             => false,
		'suppressPivotMode'          => false,
		'suppressColumnFilter'       => false,
		'suppressColumnSelectAll'    => false,
		'suppressColumnExpandAll'    => false,
		'contractColumnSelection'    => false,
		'suppressSyncLayoutWithGrid' => false,
	];

	public function configureOptions(OptionsResolver $resolver, array $gridOptions = []): void {
		$resolver->setDefault('iconKey', 'columns');
		$resolver->setDefault('labelDefault', 'Columns');
		$resolver->setDefault('labelKey', 'columns');
		$resolver->setDefault('componentAlias', 'agColumnsToolPanel');
		$resolver->setDefault('builtIn', true);

		foreach(self::SUPPRESS_OPTIONS as $option => $default) {
			$resolver->setDefault($option, $default);
		}
	}

	public function buildView(ComponentView $view, ComponentInterface $component, array $options): void {
		$params = $view->vars['toolPanelParams'];
		if($params === null) {
			$params = [];
		}
		$panelParams = [];
		foreach(array_keys(self::SUPPRESS_OPTIONS) as $option) {
			if(isset($options[$option]) && $options[$option] === true) {
				$panelParams[$option] = true;
			}
		}
		if(count($panelParams) > 0) {
			$view->vars['toolPanelParams'] = array_merge($params, $panelParams);
		}
	}

}