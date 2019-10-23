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
use Symfony\Component\Templating\EngineInterface;
use Twig\Environment;

class TwigDataTransformer implements DataTransformerInterface {

	use TemplatingTrait;

	public function __construct(?EngineInterface $templating, ?Environment $twig) {
		$this->templating = $templating;
		$this->twig = $twig;
	}

	/**
	 * @param ColumnInterface $column
	 * @param                 $item
	 * @param mixed $value
	 *            The value in the original representation
	 * @return mixed The value in the transformed representation
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