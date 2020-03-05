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

namespace StingerSoft\AggridBundle\View;

class StatusBarComponentView extends ComponentView {

	/**
	 * @var string The id of the status bar component
	 */
	public $id;

	/**
	 * @var string The template which should be used to create the JS configuration for this status bar component
	 */
	public $template;

	/**
	 * @var null|StatusBarComponentView the parent of this view (if any).
	 */
	public $parent;

	/**
	 * StatusBarComponentView constructor.
	 *
	 * @param StatusBarComponentView|null $parent the parent of this view (if any).
	 */
	public function __construct(StatusBarComponentView $parent = null) {
		$this->parent = $parent;
	}

}