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

namespace StingerSoft\AggridBundle\Column;

use StingerSoft\AggridBundle\Transformer\AbridgedStringDataTransformer;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * An abridged string column type.
 * Nothing more, nothing less.
 */
class AbridgedStringColumnType extends AbstractColumnType {

    /**
     * @var AbridgedStringDataTransformer
     */
    protected $abridgedStringDataTransformer;

    public function __construct(AbridgedStringDataTransformer $abridgedStringDataTransformer) {
        $this->abridgedStringDataTransformer = $abridgedStringDataTransformer;
    }

    /**
     * @inheritdoc
     *
     * @see AbstractColumnType::configureOptions()
     */
    public function configureOptions(OptionsResolver $resolver, array $tableOptions = array()) : void {
        $resolver->setDefault('cellRenderer', 'RawHtmlRenderer');
        $resolver->setNormalizer('filter_options', static function(Options $options, $value) {
            if($value === null) {
                $value = [];
            }
            if(!isset($value['cellRenderer'])) {
                $value['cellRenderer'] = 'RawHtmlRenderer';
            }
            return $value;
        });

        $resolver->setDefault('max', -1);
        $resolver->setAllowedTypes('max', array(
            'numeric',
            'callable'
        ));

        $resolver->setDefault('wrap', -1);
        $resolver->setAllowedTypes('wrap', array(
            'numeric',
            'boolean',
            'callable'
        ));

        $resolver->setDefault('tooltipMax', -1);
        $resolver->setAllowedTypes('tooltipMax', array(
            'numeric',
            'callable'
        ));

        $resolver->setDefault('tooltip_wrap', -1);
        $resolver->setAllowedTypes('tooltip_wrap', array(
            'numeric',
            'boolean',
            'callable'
        ));

        $resolver->setDefault('container', 'body');
        $resolver->setAllowedTypes('container', array(
            'string',
            'callable'
        ));

        $resolver->setDefault('fullscreen', false);
        $resolver->setAllowedTypes('fullscreen', array(
            'boolean',
            'callable'
        ));

        $resolver->setDefault('nl2space', false);
        $resolver->setAllowedTypes('nl2space', array(
            'boolean'
        ));

        $resolver->setDefault('nl2br', true);
        $resolver->setAllowedTypes('nl2br', array(
            'boolean'
        ));
    }

    /**
     * @param ColumnInterface $column
     * @param array           $options
     * @return mixed|void
     */
    public function buildData(ColumnInterface $column, array $options) {
        $column->addDataTransformer($this->abridgedStringDataTransformer);
    }

}