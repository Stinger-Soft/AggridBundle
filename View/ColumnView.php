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

class ColumnView extends AbstractBaseView {

	/**
	 * @var string The path to access the property on the bound object
	 */
	public $path;

	/**
	 * @var string The template which should be used to create the JS configuration for this column
	 */
	public $template;

	/**
	 * @var FilterView|null the view for the filter of the column (if any).
	 */
	public $filter;

	/**
	 * @var null|ColumnView the parent of this view (if any).
	 */
	public $parent;

	/**
	 * ColumnView constructor.
	 *
	 * @param ColumnView|null $parent the parent of this view (if any).
	 */
	public function __construct(ColumnView $parent = null) {
		$this->parent = $parent;
	}

	public function getChildView(string $path): ?ColumnView {
		if(!isset($this->vars['children']) && !is_array($this->vars['children'])) {
			return null;
		}
		/**
		 * @var ColumnView $child
		 */
		foreach($this->vars['children'] as $child) {
			if($child->path === $path) {
				return $child;
			}
		}
		return null;
	}

}