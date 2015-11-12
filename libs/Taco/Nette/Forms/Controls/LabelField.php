<?php
/**
 * This file is part of the Taco Projects.
 *
 * Copyright (c) 2004, 2013 Martin Takáč (http://martin.takac.name)
 *
 * For the full copyright and license information, please view
 * the file LICENCE that was distributed with this source code.
 */

namespace Taco\Nette\Forms\Controls;


use Nette\Utils\Html,
	Nette\Forms\Form,
	Nette\Forms\Controls\BaseControl;
use Taco\Utils\Formaters;


/**
 *  Fake formulářový prvek, který bere hodnotu a jen ji vykreslí.
 *
 *	@author		Martin Takáč
 */
class LabelField extends BaseControl
{

	/** @var string */
	private $field;

	/** @var mixed unfiltered submitted value */
	private $rawValue = '';

	/** @var Formaters\Formater */
	private $formater = Null;


	/**
	 * Form container extension method. Do not call directly.
	 *
	 * @param FormContainer $form
	 * @param string $name
	 * @param string $caption
	 * @return LabelField
	 * /
	static function addLabelField(\Nette\Forms\FormContainer $form, $name, $caption)
	{
		return $form[$name] = new self($caption);
	}



	/**
	 * @param  string  caption
	 */
	function __construct($caption = NULL)
	{
		parent::__construct($caption);
		$this->control->type = 'hidden';
		$this->field = Html::el('strong');
		//~ $this->disabled = True;
		$this->omitted = TRUE;
	}



	/**
	 * Returns container HTML element template.
	 *
	 * @return Nette\Web\Html
	 */
	function getFieldPrototype()
	{
		return $this->field;
	}



	/**
	 * Returns raw control's value.
	 * @return mixed
	 */
	function getRawValue()
	{
		return $this->rawValue;
	}



	/**
	 * Returns traditional control's value.
	 * @return mixed
	 */
	function getValue()
	{
		return NULL;
	}



	/**
	 * Sets control's value.
	 * @param  string
	 * @return self
	 */
	function setValue($value)
	{
		$this->rawValue = (string) $value;
		return $this;
	}



	function setFieldFormater(Formaters\Formater $formater)
	{
		$this->formater = $formater;
		return $this;
	}



	/**
	 * Loads HTTP data.
	 * @return void
	 */
	function loadHttpData()
	{
		$this->rawValue = $this->getHttpData(Form::DATA_TEXT);
	}



	/**
	 * Generates control's HTML element.
	 * @param  string
	 * @return Nette\Web\Html
	 */
	function getControl($caption = Null)
	{
		$container = Html::el();

		$control = clone parent::getControl();
		$control->value = (string) $this->getRawValue();
		unset($control->disabled);
		unset($control->id);
		$container->add($control);

		$field = clone $this->field;
		$field->id = $this->getHtmlId();
		$field->setText($this->format($this->getRawValue()));
		$container->add($field);

		return $container;
	}



	private function format($m)
	{
		if (empty($this->formater)) {
			return $m;
		}

		return $this->formater->format($m);
	}


}
