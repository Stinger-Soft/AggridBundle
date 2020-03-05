<?php
/*
 * This file is part of the Stinger Soft AgGrid package.
 *
 * (c) Oliver Kotte <oliver.kotte@stinger-soft.net>
 * (c) Florian Meyer <florian.meyer@stinger-soft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace StingerSoft\AggridBundle\Components\StatusBar;

use Doctrine\Common\Collections\ArrayCollection;
use StingerSoft\AggridBundle\Service\DependencyInjectionExtensionInterface;
use StingerSoft\AggridBundle\View\StatusBarComponentView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StatusBarComponent implements StatusBarComponentInterface {

	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var array
	 */
	protected $gridOptions;

	/**
	 * @var StatusBarComponentInterface|null
	 */
	protected $parent;

	/**
	 * @var StatusBarComponentTypeInterface
	 */
	protected $componentType;

	/**
	 * @var array
	 */
	protected $componentTypeOptions;

	/**
	 * @var ArrayCollection|StatusBarComponentInterface[] the children of the status bar component (if any)
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

	public function __construct($id, StatusBarComponentTypeInterface $componentType, DependencyInjectionExtensionInterface $dependencyInjectionExtension, array $componentTypeOptions = [], array $gridOptions = [], StatusBarComponentInterface $parent = null) {
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

	public function createView(StatusBarComponentView $parent = null): StatusBarComponentView {
		if(null === $parent && $this->parent) {
			$parent = $this->parent->createView();
		}

		$view = new StatusBarComponentView($parent);
		$this->buildView($view, $this->componentType, $this->componentTypeOptions);

		return $view;
	}

	public function getId(): string {
		return $this->id;
	}

	public function getComponentTypeOptions(): array {
		return $this->componentTypeOptions;
	}

	public function setComponentTypeOptions(array $componentTypeOptions): StatusBarComponentInterface {
		$this->componentTypeOptions = $componentTypeOptions;

		return $this;
	}

	public function setParent(?StatusBarComponentInterface $parent): StatusBarComponentInterface {
		$this->parent = $parent;

		return $this;
	}

	public function getParent(): ?StatusBarComponentInterface {
		return $this->parent;
	}

	public function getGridOptions(): array {
		return $this->gridOptions;
	}

	public function setGridOptions(array $gridOptions): StatusBarComponentInterface {
		$this->gridOptions = $gridOptions;

		return $this;
	}

	public function getComponentType(): StatusBarComponentTypeInterface {
		return $this->componentType;
	}

	/**
	 * @inheritDoc
	 */
	public function getChildren(): array {
		return $this->children->toArray();
	}

	public function addChild(StatusBarComponentInterface $child): StatusBarComponentInterface {
		if(!$this->children->contains($child)) {
			$this->children[] = $child;
		}
		return $this;
	}

	/**
	 * Updates the given view.
	 *
	 * @param StatusBarComponentView          $componentView    the view to be updated
	 * @param StatusBarComponentTypeInterface $columnType       the status bar component type containing the information that may be relevant for the view
	 * @param array                           $componentOptions the options defined for the status bar component type type, containing information
	 *                                                          such as the translation_domain etc.
	 */
	protected function buildView(StatusBarComponentView $componentView, StatusBarComponentTypeInterface $columnType, array $componentOptions = []): void {
		if($columnType->getParent()) {
			$parentType = $this->dependencyInjectionExtension->resolveStatusBarComponentType($columnType->getParent());
			$this->buildView($componentView, $parentType, $componentOptions);
		}
		$columnType->buildView($componentView, $this, $componentOptions);
	}

	protected function setupFilterOptionsResolver(StatusBarComponentTypeInterface $componentType, array $options = []): array {
		$this->resolveOptions($componentType, $this->resolver);
		return $this->resolver->resolve($options);
	}

	/**
	 * Merges the configurations of each type in the hierarchy starting from the top most type.
	 *
	 * @param StatusBarComponentTypeInterface $componentType the status bar component type to resolve the options from
	 * @param OptionsResolver                 $resolver      the resolver used for checking option values and defaults etc.
	 */
	protected function resolveOptions(StatusBarComponentTypeInterface $componentType, OptionsResolver $resolver): void {
		if($componentType->getParent()) {
			$parentType = $this->dependencyInjectionExtension->resolveStatusBarComponentType($componentType->getParent());
			$this->resolveOptions($parentType, $resolver);
		}
		$componentType->configureOptions($resolver, $this->gridOptions);
	}

}