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

namespace StingerSoft\AggridBundle\Components;

use StingerSoft\AggridBundle\View\ComponentView;

interface ComponentInterface {

	public const CATEGORY_STATUS_BAR = 'statusBar';
	public const CATEGORY_SIDE_BAR = 'sideBar';

	public const CATEGORIES = [
		self::CATEGORY_SIDE_BAR,
		self::CATEGORY_STATUS_BAR,
	];

	public function getId(): string;

	public function getComponentCategory(): string;

	public function getComponentType(): ComponentTypeInterface;

	public function getComponentTypeOptions(): array;

	public function setComponentTypeOptions(array $componentTypeOptions): self;

	public function createView(ComponentView $parent = null): ComponentView;

	public function getGridOptions(): array;

	public function setGridOptions(array $gridOptions): self;

	public function setParent(?ComponentInterface $parent): self;

	public function getParent(): ?ComponentInterface;

	/**
	 * @return array|ComponentInterface[]
	 */
	public function getChildren(): array;

	public function addChild(ComponentInterface $child): ComponentInterface;
}