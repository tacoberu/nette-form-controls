<?php
/**
 * Copyright (c) since 2004 Martin Takáč (http://martin.takac.name)
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace Taco\Nette\Application\UI;

use Nette;
use Nette\Utils\Html;
use Nette\Utils\IHtmlString;
use Nette\Forms\ControlGroup;
use Nette\ComponentModel\IComponent;


/**
 * Improvements:
 * - requires a callback to get the initialization data.
 * - allows submerging groups
 */
class Form extends Nette\Application\UI\Form
{

	/**
	 * @var callback
	 */
	private $initialize;

	/**
	 * @var string
	 */
	private $name;


	/** @var ControlGroup[] */
	private $groups = [];


	function __construct($initialize, $parent = null, $name = null)
	{
		parent::__construct($parent, $name);
		$this->name = $name;
		$this->initialize = $initialize;
		$this->monitor(Nette\Application\IPresenter::class);
		$this->setRenderer(new MyDefaultFormRenderer);
	}



	function getFlowName()
	{
		return $this->name;
	}



	function addGroup($caption = null, bool $setAsCurrent = true) : ControlGroup
	{
		$group = new MyControlGroup($this);
		$group->setOption('label', $caption);
		$group->setOption('visual', true);

		if ($setAsCurrent) {
			$this->setCurrentGroup($group);
		}

		if (!is_scalar($caption) || isset($this->groups[$caption])) {
			return $this->groups[] = $group;
		} else {
			return $this->groups[$caption] = $group;
		}
	}



	function addGroupTo(ControlGroup $parent, $caption)
	{
		$group = new MyControlGroup($this);
		$group->setOption('label', $caption);
		$group->setOption('visual', true);

		$this->setCurrentGroup($group);
		$parent->add($group);
		return $group;
	}



	/**
	 * Removes fieldset group from form.
	 * @param  string|int|ControlGroup
	 * @return void
	 */
	function removeGroup($name) : void
	{
		if (is_string($name) && isset($this->groups[$name])) {
			$group = $this->groups[$name];

		} elseif ($name instanceof ControlGroup && in_array($name, $this->groups, true)) {
			$group = $name;
			$name = array_search($group, $this->groups, true);

		} else {
			throw new Nette\InvalidArgumentException("Group not found in form '$this->name'");
		}

		foreach ($group->getControls() as $control) {
			$control->getParent()->removeComponent($control);
		}

		unset($this->groups[$name]);
	}



	/**
	 * Returns all defined groups.
	 * @return ControlGroup[]
	 */
	function getGroups() : array
	{
		return $this->groups;
	}



	/**
	 * Returns the specified group.
	 * @param  string|int
	 * @return ControlGroup|null
	 */
	function getGroup($name) : ?ControlGroup
	{
		return isset($this->groups[$name]) ? $this->groups[$name] : null;
	}



	protected function attached(IComponent $presenter) : void
	{
		parent::attached($presenter);
		if ($presenter instanceof Nette\Application\IPresenter) {
			if ( ! $this->isAnchored() || ! $this->isSubmitted()) {
				if ($cb = $this->initialize) {
					$this->setDefaults((array) $cb($this));
				}
			}
		}
	}

}



/**
 * Custom implementation that allows groups to be nested within each other.
 */
class MyControlGroup extends Nette\Forms\ControlGroup
{
	private $form;
	private $attrs = [];


	function __construct(Nette\Forms\Form $form)
	{
		$this->form = $form;
		parent::__construct();
	}



	/**
	 * @return static
	 */
	function add(...$items)
	{
		foreach ($items as $item) {
			if ($item instanceof Nette\Forms\IControl) {
				$this->controls->attach($item);

			} elseif ($item instanceof Nette\Forms\Container) {
				foreach ($item->getComponents() as $component) {
					$this->add($component);
				}
			} elseif ($item instanceof \Traversable || is_array($item)) {
				$this->add(...$item);

			} elseif ($item instanceof Nette\Forms\ControlGroup) {
				$this->controls->attach($item);

			} else {
				$type = is_object($item) ? get_class($item) : gettype($item);
				throw new Nette\InvalidArgumentException("IControl or Container items expected, $type given.");
			}
		}
		return $this;
	}



	/**
	 * Returns form.
	 */
	function getForm(bool $_throw = true): ?Nette\Forms\Form
	{
		return $this->form;
	}



	/**
	 * Changes control's HTML attribute.
	 * @return static
	 */
	function setHtmlAttribute(string $name, $value = true)
	{
		$this->attrs[$name] = $value;
		return $this;
	}



	function getHtmlAttributes(): array
	{
		return $this->attrs;
	}
}



/**
 * Own implementation that allows drawing nested groups.
 */
class MyDefaultFormRenderer extends Nette\Forms\Rendering\DefaultFormRenderer
{

	function __construct()
	{
		$this->wrappers['control']['container-single'] = 'td colspan="2"';
		$this->wrappers['group-empty']['container'] = Null;
		$this->wrappers['group-empty']['container'] = Null;
		$this->wrappers['pair']['container-buttons'] = 'div class="field controls"';
	}



	/**
	 * Renders form body.
	 * @return string
	 */
	function renderBody() : string
	{
		$s = $remains = '';

		$translator = $this->form->getTranslator();

		foreach ($this->form->getGroups() as $group) {
			if ($group->getOption('rendered') || !$group->getControls() || !$group->getOption('visual')) {
				continue;
			}
			list($out, $remains) = $this->renderGroup($group, $remains, True);
			$s .= $out;
		}

		$s .= $remains . $this->renderControls2($this->form, True);

		$container = $this->getWrapper('form container');
		$container->setHtml($s);
		return $container->render(0);
	}



	/**
	 * Renders group of controls.
	 * @param  Nette\Forms\Container|Nette\Forms\ControlGroup
	 * @return string
	 */
	function renderControls($parent, $root = False) : string
	{
		/*
		Myslel jsem místo toho $root. Ale nějak to zlobí.
		if ($parent instanceof Nette\Forms\Form) {
			dump($parent);
		}*/

		if (!($parent instanceof Nette\Forms\Container || $parent instanceof Nette\Forms\ControlGroup)) {
			throw new Nette\InvalidArgumentException('Argument must be Nette\Forms\Container or Nette\Forms\ControlGroup instance.');
		}

		$container = $this->getWrapper('controls container');

		$buttons = null;
		foreach ($parent->getControls() as $control) {
			if ($control instanceof Nette\Forms\ControlGroup) {
				if ($control->getOption('rendered') || !$control->getControls() || !$control->getOption('visual')) {
					continue;
				}
				list($s, $_) = $this->renderGroup($control, '');
				$control->setOption('rendered', true);

				//~ if ($parent instanceof Nette\Forms\Form) {
				if ($root) {
					$container->addHtml($this->getWrapper('control container-single')->setHtml($s));
				}
				else {
					if ( ! $pair = $this->getWrapper('pair container-group')) {
						$pair = $this->getWrapper('pair container');
					}
					$pair->addHtml($this->getWrapper('control container-single')->setHtml($s));
					$container->addHtml($pair);
				}
			}
			elseif ($control->getOption('rendered') || $control->getOption('type') === 'hidden' || $control->getForm(false) !== $this->form) {
				// skip
			}
			elseif ($control->getOption('type') === 'button') {
				$buttons[] = $control;
			}
			else {
				if ($buttons) {
					$container->addHtml($this->renderButtons($buttons));
					$buttons = null;
				}
				$container->addHtml($this->renderPair($control));
			}
		}

		if ($buttons) {
			$container->addHtml($this->renderButtons($buttons));
		}

		$s = '';
		if (count($container)) {
			$s .= "\n" . $container . "\n";
		}

		return $s;
	}



	private function renderControls2($parent, $root = False) : string
	{
		/*
		Myslel jsem místo toho $root. Ale nějak to zlobí.
		if ($parent instanceof Nette\Forms\Form) {
			dump($parent);
		}*/

		if (!($parent instanceof Nette\Forms\Container || $parent instanceof Nette\Forms\ControlGroup)) {
			throw new Nette\InvalidArgumentException('Argument must be Nette\Forms\Container or Nette\Forms\ControlGroup instance.');
		}

		$container = $this->getWrapper('controls container');

		$buttons = null;
		foreach ($parent->getControls() as $control) {
			if ($control instanceof Nette\Forms\ControlGroup) {
				if ($control->getOption('rendered') || !$control->getControls() || !$control->getOption('visual')) {
					continue;
				}
				list($s, $_) = $this->renderGroup($control, '');
				$control->setOption('rendered', true);

				//~ if ($parent instanceof Nette\Forms\Form) {
				if ($root) {
					$container->addHtml($this->getWrapper('control container-single')->setHtml($s));
				}
				else {
					if ( ! $pair = $this->getWrapper('pair container-group')) {
						$pair = $this->getWrapper('pair container');
					}
					$pair->addHtml($this->getWrapper('control container-single')->setHtml($s));
					$container->addHtml($pair);
				}
			}
			elseif ($control->getOption('rendered') || $control->getOption('type') === 'hidden' || $control->getForm(false) !== $this->form) {
				// skip
			}
			elseif ($control->getOption('type') === 'button') {
				$buttons[] = $control;
			}
			else {
				if ($buttons) {
					$container->addHtml($this->renderButtons($buttons));
					$buttons = null;
				}
				$container->addHtml($this->renderPair($control));
			}
		}

		if ($buttons) {
			$container->addHtml($this->renderButtons($buttons));
		}

		$s = '';
		if (count($container)) {
			$s .= "\n" . $container . "\n";
		}

		return $s;
	}



	private function renderGroup($group, $remains, $root = False)
	{
		if (empty($group->getOption('label'))) {
			$container = $group->getOption('container', $this->getWrapper('group-empty container'));
		}
		else {
			$container = $group->getOption('container', $this->getWrapper('group container'));
		}
		$container = $container instanceof Html ? clone $container : Html::el($container);

		foreach ($group->getHtmlAttributes() as $key => $value) {
			$container->{$key} = $value;
		}

		$id = $group->getOption('id');
		if ($id) {
			$container->id = $id;
		}

		$s = "\n" . $container->startTag();
		$translator = $this->form->getTranslator();

		$text = $group->getOption('label');
		if ($text instanceof IHtmlString) {
			$s .= $this->getWrapper('group label')->addHtml($text);

		} elseif ($text != null) { // intentionally ==
			if ($translator !== null) {
				$text = $translator->translate($text);
			}
			$s .= "\n" . $this->getWrapper('group label')->setText($text) . "\n";
		}

		$text = $group->getOption('description');
		if ($text instanceof IHtmlString) {
			$s .= $text;

		} elseif ($text != null) { // intentionally ==
			if ($translator !== null) {
				$text = $translator->translate($text);
			}
			$s .= $this->getWrapper('group description')->setText($text) . "\n";
		}

		$s .= $ss = $this->renderControls($group, $root);
		if (empty($ss)) {
			return [$ss, $remains];
		}

		$remains = $container->endTag() . "\n" . $remains;
		if (!$group->getOption('embedNext')) {
			$s .= $remains;
			$remains = '';
		}

		return [$s, $remains];
	}



	/**
	 * Renders single visual row of multiple controls.
	 * @param  Nette\Forms\IControl[]
	 * @return string
	 */
	function renderPairMulti(array $controls) : string
	{
		$s = [];
		foreach ($controls as $control) {
			if (!$control instanceof Nette\Forms\IControl) {
				throw new Nette\InvalidArgumentException('Argument must be array of Nette\Forms\IControl instances.');
			}
			$description = $control->getOption('description');
			if ($description instanceof IHtmlString) {
				$description = ' ' . $description;

			} elseif ($description != null) { // intentionally ==
				if ($control instanceof Nette\Forms\Controls\BaseControl) {
					$description = $control->translate($description);
				}
				$description = ' ' . $this->getWrapper('control description')->setText($description);

			} else {
				$description = '';
			}

			$control->setOption('rendered', true);
			$el = $control->getControl();
			if ($el instanceof Html && $el->getName() === 'input') {
				$el->class($this->getValue("control .$el->type"), true);
			}
			$s[] = $el . $description;
		}
		$pair = $this->getWrapper('pair container');
		$pair->addHtml($this->getWrapper('control container-single')->setHtml(implode(' ', $s)));
		return $pair->render(0);
	}



	/**
	 * @param  Nette\Forms\IControl[]
	 * @return string
	 */
	function renderButtons(array $controls) : string
	{
		$s = [];
		foreach ($controls as $control) {
			if (!$control instanceof Nette\Forms\IControl) {
				throw new Nette\InvalidArgumentException('Argument must be array of Nette\Forms\IControl instances.');
			}
			$description = $control->getOption('description');
			if ($description instanceof IHtmlString) {
				$description = ' ' . $description;

			} elseif ($description != null) { // intentionally ==
				if ($control instanceof Nette\Forms\Controls\BaseControl) {
					$description = $control->translate($description);
				}
				$description = ' ' . $this->getWrapper('control description')->setText($description);

			} else {
				$description = '';
			}

			$control->setOption('rendered', true);
			$el = $control->getControl();
			if ($el instanceof Html && $el->getName() === 'input') {
				$el->class($this->getValue("control .$el->type"), true);
			}
			$s[] = $el . $description;
		}

		if ($this->hasWrapper('pair container-buttons')) {
			$pair = $this->getWrapper('pair container-buttons');
		}
		else {
			$pair = $this->getWrapper('pair container');
		}
		$pair->addHtml($this->getWrapper('control container-single')->setHtml(implode(' ', $s)));

		return $pair->render(0);
	}



	function hasWrapper(string $name): bool
	{
		return (bool) $this->getValue($name);
	}

}
