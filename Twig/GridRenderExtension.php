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

namespace StingerSoft\AggridBundle\Twig;

use StingerSoft\AggridBundle\View\GridView;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class GridRenderExtension extends AbstractExtension {

	/** @var Environment */
	protected $environment;

	/**  @var string */
	protected $twigHtmlTemplate;

	/** @var string */
	protected $twigJsTemplate;

	/**
	 * @param Environment $environment
	 * @param string      $twigHtmlTemplate
	 * @param string      $twigJsTemplate
	 */
	public function __construct(Environment $environment, string $twigHtmlTemplate, string $twigJsTemplate) {
		$this->environment = $environment;
		$this->twigHtmlTemplate = $twigHtmlTemplate;
		$this->twigJsTemplate = $twigJsTemplate;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFunctions(): array {
		return [
			new TwigFunction('aggrid_grid_render', [
				$this,
				'render',
			], [
				'is_safe' => [
					'html',
				],
			]),
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFilters(): array {
		return [
			new TwigFilter('aggrid_array_is_associative', [
				self::class,
				'isAssociativeArray',
			]),
			new TwigFilter('aggrid_array_is_indexed', [
				self::class,
				'isIndexedArray',
			]),
		];
	}

	public static function isIndexedArray($array): bool {
		return !self::isAssociativeArray($array);
	}

	public static function isAssociativeArray($array): bool {
		if(!is_array($array)) {
			return false;
		}
		$keys = array_keys($array);
		foreach($keys as $key) {
			if(is_string($key)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Renders a grid.
	 *
	 * @param GridView $grid
	 * @param array    $options
	 *
	 * @return string
	 * @throws LoaderError
	 * @throws RuntimeError
	 * @throws SyntaxError
	 */
	public function render(GridView $grid, array $options = []): string {
		$options = array_merge([
			'html_template' => $this->twigHtmlTemplate,
			'js_template'   => $this->twigJsTemplate,
			'grid'          => $grid,
		], $options);
		return $this->environment->render($options['html_template'], $options) . "\n" . $this->environment->render($options['js_template'], $options);
	}
}