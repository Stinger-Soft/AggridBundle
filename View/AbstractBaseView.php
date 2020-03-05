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

abstract class AbstractBaseView {

	/**
	 * @var array Array of data which can be used inside the template
	 */
	public $vars;

	/**
	 * Gets array of data which can be used inside the template
	 */
	public function getVars(): array {
		return $this->vars;
	}

	/**
	 * Sets array of data which can be used inside the template
	 *
	 * @param array $vars
	 *            Array of data which can be used inside the template
	 * @return $this
	 */
	public function setVars($vars): self {
		$this->vars = $vars;
		return $this;
	}
}