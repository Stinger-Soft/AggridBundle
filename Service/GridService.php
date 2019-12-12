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

namespace StingerSoft\AggridBundle\Service;

use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\PaginatorInterface;
use StingerSoft\AggridBundle\Grid\Grid;
use Twig\Environment;

class GridService implements GridServiceInterface {
	/**
	 *
	 * @var DependencyInjectionExtensionInterface
	 */
	protected $dependencyInjectionExtension;

	/**
	 * @var PaginatorInterface
	 */
	protected $paginator;

	/**
	 * @var null|Environment
	 */
	protected $twig;

	/**
	 * GridService constructor.
	 *
	 * @param DependencyInjectionExtensionInterface $dependencyInjectionExtension
	 * @param PaginatorInterface $paginator
	 * @param Environment|null $twig
	 */
	public function __construct(DependencyInjectionExtensionInterface $dependencyInjectionExtension, PaginatorInterface $paginator, ?Environment $twig) {
		$this->dependencyInjectionExtension = $dependencyInjectionExtension;
		$this->paginator = $paginator;
		$this->twig = $twig;
	}

	/**
	 * Creates a new grid instance for the given type and query builder.
	 *
	 * @param string $type the type of grid to be created
	 * @param QueryBuilder|array $dataSource the data source to be used for retrieving grid rows and column values
	 * @param array $options the options for the grid type
	 * @return Grid the grid instance
	 */
	public function createGrid($type, $dataSource, array $options = array()): Grid {
		return new Grid($type, $dataSource, $this->dependencyInjectionExtension, $this->paginator, $this->twig, $options);
	}
}