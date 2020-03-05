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
use Symfony\Component\OptionsResolver\OptionsResolver;

interface StatusBarComponentTypeInterface {

	public const ALIGN_LEFT = 'left';

	public const ALIGN_CENTER = 'center';

	public const ALIGN_RIGHT = 'right';

	/**
	 * Builds the status bar component options using the given options resolver.
	 *
	 * This method is called for each type in the hierarchy starting from the
	 * top most type. It allows to define additional options for this type of status bar component.
	 *
	 * @param OptionsResolver $resolver     the options resolver used for checking validity of the status bar component options,
	 *                                      defining default values etc.
	 * @param array           $gridOptions  the options of the grid the status bar component belongs to, containing options such as
	 *                                      the grids translation domain etc.
	 * @return void
	 */
	public function configureOptions(OptionsResolver $resolver, array $gridOptions = []): void;

	/**
	 * Builds the status bar view used for rendering of the status bar component.
	 *
	 * This method is called for each type in the hierarchy starting from the
	 * top most type. It allows adding more variables to the given view which may be used during rendering of the status
	 * bar component.
	 *
	 * @param StatusBarComponentView      $view      the column view to add any additional information to
	 * @param StatusBarComponentInterface $component the status bar component instance the view belongs to
	 * @param array                       $options   the options of the status bar component, previously configured by the #configureOptions method
	 * @return void
	 */
	public function buildView(StatusBarComponentView $view, StatusBarComponentInterface $component, array $options): void;

	/**
	 * Returns the name of the parent type or null.
	 *
	 * @return string|null The name of the parent type if any, null otherwise
	 */
	public function getParent(): ?string;
}