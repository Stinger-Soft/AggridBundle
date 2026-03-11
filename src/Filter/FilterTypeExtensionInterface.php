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

namespace StingerSoft\AggridBundle\Filter;

use StingerSoft\AggridBundle\View\FilterView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @method static iterable<string> getExtendedTypes() Gets the extended types
 */
interface FilterTypeExtensionInterface
{
    /**
     * @param array<string, mixed> $columnOptions
     * @param array<string, mixed> $gridOptions
     */
    public function configureOptions(OptionsResolver $resolver, array $columnOptions = [], array $gridOptions = []): void;

    /**
     * @param array<string, mixed> $options
     * @param \Doctrine\ORM\QueryBuilder|array<string, mixed>|null $dataSource
     */
    public function buildView(FilterView $view, FilterInterface $filter, array $options, $dataSource, string $queryPath, string $rootAlias): void;

    /**
     * Adds the json compatible configuration of this filter to the view
     * @param FilterView      $view the filter view to add any additional information to
     * @param FilterInterface $filter the filter instance the view belongs to
     * @param array<string, mixed> $options the options of the column, previously configured by the #configureOptions method
     */
    public function buildJsonConfiguration(FilterView $view, FilterInterface $filter, array $options): void;
}
