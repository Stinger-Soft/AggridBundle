<?php

namespace StingerSoft\AggridBundle\Filter;

use StingerSoft\AggridBundle\View\FilterView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NumberFilterType extends AbstractFilterType {

	public function configureOptions(OptionsResolver $resolver, array $columnOptions = array(), array $tableOptions = array()): void {
		$resolver->setDefault('filter_type', 'agNumberColumnFilter');
	}
}