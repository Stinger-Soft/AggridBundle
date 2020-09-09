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

namespace StingerSoft\AggridBundle\Grid;

use StingerSoft\AggridBundle\Helper\GridBuilderInterface;
use StingerSoft\AggridBundle\View\GridView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Interface GridTypeExtensionInterface
 *
 * @package StingerSoft\AggridBundle\Grid
 *
 * @method static iterable getExtendedTypes() Gets the extended types
 */
interface GridTypeExtensionInterface {

	public function buildGrid(GridBuilderInterface $builder, array $gridOptions): void;

	public function buildView(GridView $view, GridInterface $grid, array $gridOptions, array $columns): void;

	public function configureOptions(OptionsResolver $resolver): void;

}