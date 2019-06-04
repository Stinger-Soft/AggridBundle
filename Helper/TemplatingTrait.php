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

namespace StingerSoft\AggridBundle\Helper;

use Symfony\Component\Templating\EngineInterface;
use Twig\Environment;

trait TemplatingTrait {

	/**
	 * @var null|EngineInterface
	 */
	protected $templating;

	/**
	 * @var null|Environment
	 */
	protected $twig;

	/**
	 * Returns a rendered view.
	 *
	 * @param string $view
	 *            The name of the view to be rendered, must be in a valid format handable by twig.
	 * @param array $parameters
	 *            An array of parameters to be passed to the view
	 * @return string The rendered view
	 * @throws \Twig_Error_Loader
	 * @throws \Twig_Error_Runtime
	 * @throws \Twig_Error_Syntax
	 */
	public function renderView($view, array $parameters = array()): string {
		if($this->templating) {
			return $this->templating->render($view, $parameters);
		}

		if(!$this->twig) {
			throw new \LogicException('You can not use the "renderView" method if the Templating Component or the Twig Bundle are not available.');
		}

		return $this->twig->render($view, $parameters);
	}

}