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

namespace StingerSoft\AggridBundle\View;

class ComponentView extends AbstractBaseView {

	/**
	 * @var string The id of the side bar component
	 */
	public $id;

	/**
	 * @var string The template which should be used to create the JS configuration for this side bar component
	 */
	public $template;

	/**
	 * @var null|ComponentView the parent of this view (if any).
	 */
	public $parent;

	/** @var string */
	public $componentAlias;

	/** @var string|null */
	public $component;

	/**
	 * SideBarComponentView constructor.
	 *
	 * @param ComponentView|null $parent the parent of this view (if any).
	 */
	public function __construct(ComponentView $parent = null) {
		$this->parent = $parent;
	}

}