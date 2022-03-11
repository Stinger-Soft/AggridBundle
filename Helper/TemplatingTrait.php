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

use LogicException;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

trait TemplatingTrait {

	/**
	 * @var null|Environment
	 */
	protected $twig;

	/**
	 * Returns a rendered view.
	 *
	 * @param string $view
	 *            The name of the view to be rendered, must be in a valid format handleable by twig.
	 * @param array  $parameters
	 *            An array of parameters to be passed to the view
	 * @return string The rendered view
	 * @throws LoaderError
	 * @throws RuntimeError
	 * @throws SyntaxError
	 */
	public function renderView(string $view, array $parameters = []): string {
		if(!$this->twig) {
			throw new LogicException('You can not use the "renderView" method if the Templating Component or the Twig Bundle are not available.');
		}

		return $this->twig->render($view, $parameters);
	}

}