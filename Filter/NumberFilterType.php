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

class NumberFilterType extends AbstractFilterType {

	public function configureOptions(OptionsResolver $resolver, array $columnOptions = [], array $tableOptions = []): void {
		$resolver->setDefault('filter_type', 'agNumberColumnFilter');
		$resolver->setDefault('includeBlanksInEquals', false);
		$resolver->setAllowedTypes('includeBlanksInEquals', 'bool');
		$resolver->setDefault('includeBlanksInLessThan', false);
		$resolver->setAllowedTypes('includeBlanksInLessThan', 'bool');
		$resolver->setDefault('includeBlanksInGreaterThan', false);
		$resolver->setAllowedTypes('includeBlanksInGreaterThan', 'bool');
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildView(FilterView $view, FilterInterface $filter, array $options, $dataSource, string $queryPath, string $rootAlias): void {
		$view->vars['includeBlanksInEquals'] = $options['includeBlanksInEquals'];
		$view->vars['includeBlanksInLessThan'] = $options['includeBlanksInLessThan'];
		$view->vars['includeBlanksInGreaterThan'] = $options['includeBlanksInGreaterThan'];
	}

}