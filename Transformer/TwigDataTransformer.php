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

namespace StingerSoft\AggridBundle\Transformer;

use StingerSoft\AggridBundle\Column\ColumnInterface;
use StingerSoft\AggridBundle\Helper\TemplatingTrait;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class TwigDataTransformer implements DataTransformerInterface {

	use TemplatingTrait;

	public function __construct(?Environment $twig) {
		$this->twig = $twig;
	}

	/**
	 * @inheritDoc
	 *
	 * @throws LoaderError
	 * @throws RuntimeError
	 * @throws SyntaxError
	 */
	public function transform(ColumnInterface $column, $item, $value) {
		$options = $column->getColumnOptions();
		$originalContext = [
			'item'         => $item,
			'path'         => $column->getPath(),
			'value'        => $value,
			'options'      => $options,
			'tableOptions' => $column->getGridOptions(),
		];
		$additionalContext = $options['additionalContext'];
		$context = array_merge($additionalContext, $originalContext);
		return trim($this->renderView($options['template'], $context));
	}
}