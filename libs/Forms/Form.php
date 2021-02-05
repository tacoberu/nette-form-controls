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


/**
 * Vylepšení:
 * - vyžaduje callback pro získání inicializačních dat.
 * - umožňuje zanořování skupin
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
		$this->monitor('Nette\Application\IPresenter');
		$this->setRenderer(new MyDefaultFormRenderer);
	}



	function getFlowName()
	{
		return $this->name;
	}



	function addGroup($caption = null, $setAsCurrent = true)
	{
		$group = new MyControlGroup;
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
		$group = new MyControlGroup;
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
	function removeGroup($name)
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
	function getGroups()
	{
		return $this->groups;
	}



	/**
	 * Returns the specified group.
	 * @param  string|int
	 * @return ControlGroup|null
	 */
	function getGroup($name)
	{
		return isset($this->groups[$name]) ? $this->groups[$name] : null;
	}



	protected function attached($presenter)
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
 * Vlastní implementace která umožňuje zanořovat skupiny do sebe.
 */
class MyControlGroup extends Nette\Forms\ControlGroup
{

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

}



/**
 * Vlastní implementace která umožňuje vykreslovat v sobě zanořené skupiny.
 */
class MyDefaultFormRenderer extends Nette\Forms\Rendering\DefaultFormRenderer
{

	function __construct()
	{
		$this->wrappers['control']['container-single'] = 'td colspan="2"';
		$this->wrappers['group-empty']['container'] = Null;
	}



	/**
	 * Renders form body.
	 * @return string
	 */
	function renderBody()
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

		$s .= $remains . $this->renderControls($this->form, True);

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
					$container->addHtml($this->renderPairMulti($buttons));
					$buttons = null;
				}
				$container->addHtml($this->renderPair($control));
			}
		}

		if ($buttons) {
			$container->addHtml($this->renderPairMulti($buttons));
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
					$container->addHtml($this->renderPairMulti($buttons));
					$buttons = null;
				}
				$container->addHtml($this->renderPair($control));
			}
		}

		if ($buttons) {
			$container->addHtml($this->renderPairMulti($buttons));
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
	function renderPairMulti(array $controls)
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


}
