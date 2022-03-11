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

namespace StingerSoft\AggridBundle\Column;

use StingerSoft\AggridBundle\Grid\GridType;
use StingerSoft\AggridBundle\View\ColumnView;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractColumnType implements ColumnTypeInterface {

	/**
	 * @var string constant defining that a boolean option is true only for server side tables
	 */
	public const SERVER_SIDE_ONLY = 'server';

	/**
	 * @var string constant defining that a boolean option is true only for client side tables
	 */
	public const CLIENT_SIDE_ONLY = 'client';



	use ColumnTrait;

	public static function getBooleanValueDependingOnClientOrServer($optionValue, string $dataMode): bool {
		if($optionValue === true) {
			return true;
		}
		if($optionValue === false) {
			return false;
		}
		return $dataMode === GridType::DATA_MODE_ENTERPRISE ? $optionValue === self::SERVER_SIDE_ONLY : $optionValue === self::CLIENT_SIDE_ONLY;
	}

	/**
	 * {@inheritdoc}
	 * @see \StingerSoft\AggridBundle\Column\ColumnTypeInterface::configureOptions()
	 */
	public function configureOptions(OptionsResolver $resolver, array $gridOptions = []): void {
	}

	/**
	 * {@inheritdoc}
	 * @see \StingerSoft\AggridBundle\Column\ColumnTypeInterface::buildView()
	 */
	public function buildView(ColumnView $view, ColumnInterface $column, array $options): void {
	}

	/**
	 * @inheritdoc
	 */
	public function buildData(ColumnInterface $column, array $options) {
	}

	/**
	 * {@inheritdoc}
	 * @see \StingerSoft\AggridBundle\Column\ColumnTypeInterface::getParent()
	 */
	public function getParent(): ?string {
		return ColumnType::class;
	}
}