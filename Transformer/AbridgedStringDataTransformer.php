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
use StingerSoft\AggridBundle\Column\ColumnTrait;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class AbridgedStringDataTransformer implements DataTransformerInterface {

	use ColumnTrait;

	/**
	 * @var Environment the twig environment used for rendering views
	 */
	protected $environment;

	/**
	 * LinkDataTransformer constructor.
	 *
	 * @param Environment $environment the twig environment used for rendering views
	 */
	public function __construct(Environment $environment) {
		$this->environment = $environment;
	}

	/**
	 * @inheritDoc
	 * @throws LoaderError
	 * @throws RuntimeError
	 * @throws SyntaxError
	 */
	public function transform(ColumnInterface $column, $item, $value) {
		$options = $column->getColumnOptions();
		$path = $column->getPath();

		if(is_array($value)) {
			$value = implode(', ', $value);
		}
		if($value === null && isset($options['nl2space'])) {
			$value = '';
		}

		//$value = $value ?: $this->getDelegateValueOrScalar('empty_value', $item, $path, $options);
		return $this->environment->render('@StingerSoftAggrid/Column/abridged_string.html.twig', [
			'item'          => $item,
			'path'          => $path,
			'max'           => $this->getDelegateValueOrScalar('max', $item, $path, $options),
			'wrap'          => $this->getDelegateValueOrScalar('wrap', $item, $path, $options),
			'tooltipMax'    => $this->getDelegateValueOrScalar('tooltipMax', $item, $path, $options),
			'value'         => $value,
			'container'     => $this->getDelegateValueOrScalar('container', $item, $path, $options),
			'tooltipWrap'   => $this->getDelegateValueOrScalar('tooltip_wrap', $item, $path, $options),
			'fullscreen'    => $this->getDelegateValueOrScalar('fullscreen', $item, $path, $options),
			'nl2space'      => $options['nl2space'],
			'nl2br'         => $options['nl2br'],
			//If newline should be replaced by spaces, abridge
			'valueCleansed' => isset($options['nl2space']) ? trim(preg_replace('/(\r\n|\r|\n|\t|\s+)+/', ' ', $value)) : $value,
		]);
	}
}
