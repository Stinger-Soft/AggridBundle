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

use ReflectionException;
use StingerSoft\AggridBundle\Exception\InvalidArgumentTypeException;
use StingerSoft\AggridBundle\View\ComponentView;

interface ComponentInterface {

	public const CATEGORY_STATUS_BAR = 'statusBar';
	public const CATEGORY_SIDE_BAR = 'sideBar';

	public const CATEGORIES = [
		self::CATEGORY_SIDE_BAR,
		self::CATEGORY_STATUS_BAR,
	];

	/**
	 * Get the id of the component.
	 *
	 * @return string the id of the component.
	 */
	public function getId(): string;

	/**
	 * Get the category of the component.
	 *
	 * Is either 'statusBar' or 'sideBar'
	 *
	 * @return string  either 'statusBar' or 'sideBar'
	 *
	 * @see ComponentInterface::CATEGORIES
	 * @see ComponentInterface::CATEGORY_SIDE_BAR
	 * @see ComponentInterface::CATEGORY_SIDE_BAR
	 */
	public function getComponentCategory(): string;

	/**
	 * Get the type of the component
	 *
	 * @return ComponentTypeInterface the type of the component
	 */
	public function getComponentType(): ComponentTypeInterface;

	/**
	 * Get the options for the component type.
	 *
	 * @return array the options for the component type.
	 */
	public function getComponentTypeOptions(): array;

	/**
	 * Set the options for the component type.
	 *
	 * @param array $componentTypeOptions the options for the component type.
	 * @return $this
	 */
	public function setComponentTypeOptions(array $componentTypeOptions): self;

	/**
	 * Create the view for the component
	 *
	 * @param ComponentView|null $parent the view of the parent component (if any)
	 * @return ComponentView
	 * @throws InvalidArgumentTypeException
	 * @throws ReflectionException
	 */
	public function createView(ComponentView $parent = null): ComponentView;

	/**
	 * Get the options of the grid the component belongs to
	 *
	 * @return array the options of the grid the component belongs to
	 */
	public function getGridOptions(): array;

	/**
	 * Set the options of the grid the component belongs to
	 *
	 * @param array $gridOptions the options of the grid the component belongs to
	 * @return $this
	 */
	public function setGridOptions(array $gridOptions): self;

	/**
	 * Set the parent of the component (if any).
	 *
	 * @param ComponentInterface|null $parent the parent of the component (if any).
	 * @return $this
	 */
	public function setParent(?ComponentInterface $parent): self;

	/**
	 * Get the parent of the component (if any).
	 *
	 * @return ComponentInterface|null the parent of the component (if any).
	 */
	public function getParent(): ?ComponentInterface;

	/**
	 * Get an array containing all children of the component.
	 *
	 * @return array|ComponentInterface[] an array containing all children of the component.
	 */
	public function getChildren(): array;

	/**
	 * Add the given child to the component.
	 *
	 * @param ComponentInterface $child the child to be added to the component.
	 * @return ComponentInterface
	 */
	public function addChild(ComponentInterface $child): ComponentInterface;

	/**
	 * Remove the given child from the component.
	 *
	 * @param ComponentInterface $child the child to be removed from the component.
	 * @return bool true in case the child was removed, false otherwise
	 */
	public function removeChild(ComponentInterface $child): bool;
}