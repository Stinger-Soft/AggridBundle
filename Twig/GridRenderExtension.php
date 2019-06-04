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

class GridRenderExtension extends \Twig_Extension {

	/**
	 * @var \Twig_Environment
	 */
	protected $environment;

	/**
	 * @var string
	 */
	protected $twigHtmlTemplate;

	/**
	 * @var string
	 */
	protected $twigJsTemplate;

	/**
	 * @param \Twig_Environment $environment
	 * @param string $twigHtmlTemplate
	 * @param string $twigJsTemplate
	 */
	public function __construct(\Twig_Environment $environment, $twigHtmlTemplate, $twigJsTemplate) {
		$this->environment = $environment;
		$this->twigHtmlTemplate = $twigHtmlTemplate;
		$this->twigJsTemplate = $twigJsTemplate;
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \Twig_Extension::getFunctions()
	 */
	public function getFunctions(): array {
		return array(
			new \Twig_SimpleFunction('aggrid_grid_render', array(
				$this,
				'render'
			), array(
				'is_safe' => array(
					'html'
				)
			))
		);
	}

	/**
	 * Renders a grid with the specified renderer.
	 *
	 * @param GridView $grid
	 * @param array $options
	 * @param string $renderer
	 *
	 * @return string
	 * @throws \Twig_Error_Loader
	 * @throws \Twig_Error_Runtime
	 * @throws \Twig_Error_Syntax
	 */
	public function render(GridView $grid, array $options = array(), $renderer = null): string {
		$options = array_merge(array(
			'html_template' => $this->twigHtmlTemplate,
			'js_template'   => $this->twigJsTemplate,
			'grid'          => $grid
		), $options);
		return $this->environment->render($options['html_template'], $options) . "\n" . $this->environment->render($options['js_template'], $options);
	}
}