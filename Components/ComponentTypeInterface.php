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
use Symfony\Component\OptionsResolver\OptionsResolver;

interface ComponentTypeInterface {

	/**
	 * Builds the component options using the given options resolver.
	 *
	 * This method is called for each type in the hierarchy starting from the
	 * top most type. It allows to define additional options for this type of component.
	 *
	 * @param OptionsResolver $resolver     the options resolver used for checking validity of the component options,
	 *                                      defining default values etc.
	 * @param array           $gridOptions  the options of the grid the component belongs to, containing options such as
	 *                                      the grids translation domain etc.
	 * @return void
	 */
	public function configureOptions(OptionsResolver $resolver, array $gridOptions = []): void;

	/**
	 * Builds the component view used for rendering of the  component.
	 *
	 * This method is called for each type in the hierarchy starting from the
	 * top most type. It allows adding more variables to the given view which may be used during rendering of the component.
	 *
	 * @param ComponentView      $view      the column view to add any additional information to
	 * @param ComponentInterface $component the component instance the view belongs to
	 * @param array              $options   the options of the component, previously configured by the #configureOptions method
	 * @return void
	 */
	public function buildView(ComponentView $view, ComponentInterface $component, array $options): void;

	/**
	 * Returns the name of the parent type or null.
	 *
	 * @return string|null The name of the parent type if any, null otherwise
	 */
	public function getParent(): ?string;
}