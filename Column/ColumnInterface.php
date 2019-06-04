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

use Doctrine\ORM\QueryBuilder;
use StingerSoft\AggridBundle\Transformer\DataTransformerInterface;

interface ColumnInterface {
	/**
	 * Get the path to access the property on the bound object
	 *
	 * @return string the path to access the property on the bound object
	 */
	public function getPath(): string;

	/**
	 * Get the path to access the property on the bound object
	 *
	 * @param string $path the path to access the property on the bound object
	 * @return ColumnInterface
	 */
	public function setPath(string $path): self;

	/**
	 * Get the data source of the column.
	 *
	 * @return QueryBuilder|array the data source of the column.
	 */
	public function getDataSource();

	/**
	 * Get the query builder used by a filter for the column (if any).
	 *
	 * @return QueryBuilder the query builder used by a filter for the column (if any).
	 */
	public function getQueryBuilder(): ?QueryBuilder;

	/**
	 * Get the path to be used by a query builder for sorting and ordering etc.
	 * This may differ from the <code>ColumnSettings::getPath()</code> especially for joined paths.
	 *
	 * @return string the path to be used by a query builder for sorting and ordering etc.
	 */
	public function getQueryPath(): string;

	/**
	 * Set the path to be used by a query builder for sorting and ordering etc.
	 *
	 * @param string $queryPath the path to be used by a query builder for sorting and ordering etc.
	 * @return ColumnInterface
	 */
	public function setQueryPath(string $queryPath): void;

	/**
	 * Adds the given data transformer to the column.
	 * Any data transformers are executed whenever the value of the object / cell the column is mapped to shall be
	 * retrieved in order to be rendered. Data transformers are applied in the order they were added. The order may be
	 * influenced by forcing a data transformer to be appended rather than to be prepended, which is the default behaviour.
	 *
	 * @param DataTransformerInterface $dataTransformer the data transformer to be added
	 * @param bool                     $forceAppend     true, in case the data transformer shall be added to the end of the
	 *                                                  list of transformers (i.e. will be inserted after the already existing ones) or
	 *                                                  false (default), in case the data transformer shall be prepended to the list of
	 *                                                  transformers (i.e. it will be inserted before the already existing ones).
	 * @return ColumnInterface
	 */
	public function addDataTransformer(DataTransformerInterface $dataTransformer, $forceAppend = false);

	/**
	 * Get all attached data transformers for the column.
	 *
	 * @return DataTransformerInterface[] all attached data transformers for the column.
	 */
	public function getDataTransformers();

	/**
	 * Clears the data transformers.
	 *
	 * @return ColumnInterface
	 */
	public function resetDataTransformers();

}