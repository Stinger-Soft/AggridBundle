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

namespace StingerSoft\AggridBundle\Components\StatusBar;

use StingerSoft\AggridBundle\View\StatusBarComponentView;

interface StatusBarComponentInterface {

	public function getId(): string;

	public function getComponentType(): StatusBarComponentTypeInterface;

	public function getComponentTypeOptions(): array;

	public function setComponentTypeOptions(array $componentTypeOptions): self;

	public function createView(StatusBarComponentView $parent = null): StatusBarComponentView;

	public function getGridOptions(): array;

	public function setGridOptions(array $gridOptions): self;

	public function setParent(?StatusBarComponentInterface $parent): self;

	public function getParent(): ?StatusBarComponentInterface;

	/**
	 * @return array|StatusBarComponentInterface[]
	 */
	public function getChildren(): array;

	public function addChild(StatusBarComponentInterface $child): StatusBarComponentInterface;
}