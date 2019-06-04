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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * <p>The LinkDataTransformer automatically generates URLs for columns that defined a non-empty <code>route</code> option and
 * wraps the value of the column in a hyperlink using the generated URL.
 *
 * <p>There are different ways for generating the URL for a columns value:
 *
 * <p>In case the <code>route</code> option is a string value, it is used as the absolute URL to be used for hyperlinking.
 *
 * <p>In case the <code>route</code> option is a delegate or callable, it is expected to return either a string value or an array value.
 * <p>The parameters passed to the delegate are
 *    1. the object or item that is to be rendered in a table the column belongs to
 *    2. the value of the object or item to be rendered in the column
 *    3. the router interface, used for generating URLs
 *
 * <p>In case the <code>route</code> option is an array value or the value returned by the delegate is an array value, it is expected
 * to contain a <code>route</code> key, pointing to the route to be used for generating the URL.
 *
 * <p>Additionally, a key <code>route_params</code> may be existent, containing additional <code>key => value</code> pairs
 * which will be used as parameters for the route in order to generate the final URL.
 *
 * <p>Additionally, a key <code>attr</code> may be existent containing additional <code>key => value</code> pairs which
 * will be used as attributes for the generate a HTML tag, such as <code>title</code>, <code>data-target<code> etc.
 */
class LinkDataTransformer implements DataTransformerInterface {

	/**
	 * @var RouterInterface the router used for generating URLs from routes
	 */
	protected $router;

	/**
	 * LinkDataTransformer constructor.
	 *
	 * @param RouterInterface $router the router used for generating URLs from routes, injected
	 */
	public function __construct(RouterInterface $router) {
		$this->router = $router;
	}

	/**
	 * {@inheritDoc}
	 * @see \Pec\Bundle\DatatableBundle\Transformer\DataTransformerInterface::transform()
	 */
	public function transform(ColumnInterface $column, $item, $value) {
		$url = null;
		$options = $column->getColumnOptions();
		if($options['route']) {
			if(is_callable($options['route'])) {
				$url = call_user_func($options['route'], $item, $value, $this->router, $options);
			} else {
				$url = $options['route'];
			}
		}
		if($url) {
			$tmpAttributes = array();
			if(is_array($url)) {
				$tmpAttributes = array_key_exists('attr', $url) ? $url['attr'] : $tmpAttributes;
				$url = $this->router->generate($url['route'], $url['route_params'], UrlGeneratorInterface::ABSOLUTE_PATH);
			}
			$attributes = array_map(function ($value, $key) {
				return $key . '="' . $value . '"';
			}, array_values($tmpAttributes), array_keys($tmpAttributes));

			$attributes = implode(' ', $attributes);
			$value = '<a href="' . $url . '" ' . $attributes . '>' . $value . '</a>';
		}
		return $value;
	}

}