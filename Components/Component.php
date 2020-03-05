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

namespace StingerSoft\AggridBundle\Components;

use Doctrine\Common\Collections\ArrayCollection;
use StingerSoft\AggridBundle\Service\DependencyInjectionExtensionInterface;
use StingerSoft\AggridBundle\View\ComponentView;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class Component implements ComponentInterface {

	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var array
	 */
	protected $gridOptions;

	/**
	 * @var ComponentInterface|null
	 */
	protected $parent;

	/**
	 * @var ComponentTypeInterface
	 */
	protected $componentType;

	/**
	 * @var array
	 */
	protected $componentTypeOptions;

	/**
	 * @var ArrayCollection|ComponentInterface[] the children of the status bar component (if any)
	 */
	protected $children;

	/**
	 * @var OptionsResolver
	 */
	protected $resolver;

	/**
	 * @var DependencyInjectionExtensionInterface
	 */
	protected $dependencyInjectionExtension;

	public function __construct($id, ComponentTypeInterface $componentType, DependencyInjectionExtensionInterface $dependencyInjectionExtension, array $componentTypeOptions = [], array $gridOptions = [], ComponentInterface $parent = null) {
		$this->children = new ArrayCollection();
		$this->componentType = $componentType;
		$this->gridOptions = $gridOptions;
		$this->dependencyInjectionExtension = $dependencyInjectionExtension;
		$this->id = $id;
		$this->resolver = new OptionsResolver();
		$this->componentTypeOptions = $this->setupFilterOptionsResolver($componentType, $componentTypeOptions);
		$this->setParent($parent);

		if(!isset($this->componentTypeOptions['id'])) {
			$this->componentTypeOptions['id'] = $this->id;
		}
	}

	abstract protected function createConcreteView(ComponentView $parent = null): ComponentView;

	public function createView(ComponentView $parent = null): ComponentView {
		if(null === $parent && $this->parent) {
			$parent = $this->parent->createView();
		}

		$view = $this->createConcreteView($parent);
		$this->buildView($view, $this->componentType, $this->componentTypeOptions);

		return $view;
	}

	public function getId(): string {
		return $this->id;
	}

	public function getComponentTypeOptions(): array {
		return $this->componentTypeOptions;
	}

	public function setComponentTypeOptions(array $componentTypeOptions): ComponentInterface {
		$this->componentTypeOptions = $componentTypeOptions;

		return $this;
	}

	public function setParent(?ComponentInterface $parent): ComponentInterface {
		$this->parent = $parent;

		return $this;
	}

	public function getParent(): ?ComponentInterface {
		return $this->parent;
	}

	public function getGridOptions(): array {
		return $this->gridOptions;
	}

	public function setGridOptions(array $gridOptions): ComponentInterface {
		$this->gridOptions = $gridOptions;

		return $this;
	}

	public function getComponentType(): ComponentTypeInterface {
		return $this->componentType;
	}

	/**
	 * @inheritDoc
	 */
	public function getChildren(): array {
		return $this->children->toArray();
	}

	public function addChild(ComponentInterface $child): ComponentInterface {
		if(!$this->children->contains($child)) {
			$this->children[] = $child;
		}
		return $this;
	}

	/**
	 * Updates the given view.
	 *
	 * @param ComponentView          $componentView             the view to be updated
	 * @param ComponentTypeInterface $columnType                the status bar component type containing the information that may be relevant for the view
	 * @param array                  $componentOptions          the options defined for the status bar component type type, containing information
	 *                                                          such as the translation_domain etc.
	 */
	protected function buildView(ComponentView $componentView, ComponentTypeInterface $columnType, array $componentOptions = []): void {
		if($columnType->getParent()) {
			$parentType = $this->dependencyInjectionExtension->resolveComponentType($columnType->getParent());
			$this->buildView($componentView, $parentType, $componentOptions);
		}
		$columnType->buildView($componentView, $this, $componentOptions);
	}

	protected function setupFilterOptionsResolver(ComponentTypeInterface $componentType, array $options = []): array {
		$this->resolveOptions($componentType, $this->resolver);
		return $this->resolver->resolve($options);
	}

	/**
	 * Merges the configurations of each type in the hierarchy starting from the top most type.
	 *
	 * @param ComponentTypeInterface $componentType the status bar component type to resolve the options from
	 * @param OptionsResolver        $resolver      the resolver used for checking option values and defaults etc.
	 */
	protected function resolveOptions(ComponentTypeInterface $componentType, OptionsResolver $resolver): void {
		if($componentType->getParent()) {
			$parentType = $this->dependencyInjectionExtension->resolveComponentType($componentType->getParent());
			$this->resolveOptions($parentType, $resolver);
		}
		$componentType->configureOptions($resolver, $this->gridOptions);
	}
}