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

namespace StingerSoft\AggridBundle\View;

use StingerSoft\AggridBundle\Twig\GridRenderExtension;
use StingerSoft\PhpCommons\String\Utils;

abstract class AbstractBaseView
{
    /**
     * @var array<string, mixed> Array of data which can be used inside the template
     */
    public $vars;

    /**
     * @var array<string, mixed>|null
     */
    public $jsonConfiguration;

    /**
     * @param array<string, mixed> $source
     * @param array<string, mixed> $target
     * @param string $key
     * @param mixed $ignoreOn
     */
    public static function addFieldIfSet(array $source, array &$target, $key, $ignoreOn = null, bool $filterJsFunction = false): void
    {
        if (isset($source[$key]) && $source[$key] !== $ignoreOn && (!$filterJsFunction || !Utils::startsWith($source[$key], 'function'))) {
            $target[$key] = $source[$key];
        }
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public static function functionalize($value)
    {
        if (Utils::startsWith($value, 'function')) {
            //not supported in json mode!
            return null;
        }
        return $value;
    }

    /**
     * @param mixed $options
     * @return mixed
     */
    public static function deserializeOptionArray($options)
    {
        $result = null;
        if (!is_iterable($options)) {
            $result = self::functionalize($options);
        } else {
            $result = [];
            $isIndexed = GridRenderExtension::isIndexedArray($options);
            foreach ($options as $key => $value) {
                if ($isIndexed) {
                    $result[] = self::functionalize($value);
                } else {
                    $result[$key] = self::functionalize($value);
                }
            }
        }
        return $result;

    }

    /**
     * Gets array of data which can be used inside the template
     *
     * @return array<string, mixed>
     */
    public function getVars(): array
    {
        return $this->vars;
    }

    /**
     * Sets array of data which can be used inside the template
     *
     * @param array<string, mixed> $vars
     *            Array of data which can be used inside the template
     * @return $this
     */
    public function setVars($vars): self
    {
        $this->vars = $vars;
        return $this;
    }
}
