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

class ColumnView {

	/**
	 * @var string The path to access the property on the bound object
	 */
	public $path;

	/**
	 * @var string The template which should be used to create the JS configuration for this column
	 */
	public $template;

	/**
	 * @var array Array of data which can be used inside the template
	 */
	public $vars;

	/**
	 * @var FilterView|null the view for the filter of the column (if any).
	 */
	public $filter = null;

	/**
	 * @var null|ColumnView the parent of this view (if any).
	 */
	public $parent = null;

	/**
	 * ColumnView constructor.
	 *
	 * @param ColumnView|null $parent the parent of this view (if any).
	 */
	public function __construct(ColumnView $parent = null) {
		$this->parent = $parent;
	}

	/**
	 * Gets array of data which can be used inside the template
	 */
	public function getVars() {
		return $this->vars;
	}

	/**
	 * Sets array of data which can be used inside the template
	 *
	 * @param array $vars
	 *            Array of data which can be used inside the template
	 * @return ColumnView
	 */
	public function setVars($vars) : self {
		$this->vars = $vars;
		return $this;
	}

}