<?php
/**
 * This file is part of the Taco Projects.
 *
 * Copyright (c) 2004, 2013 Martin Takáč (http://martin.takac.name)
 *
 * For the full copyright and license information, please view
 * the file LICENCE that was distributed with this source code.
 *
 * PHP version 5.3
 *
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Nette\Forms\Controls;


use Nette\Forms\Controls\TextBase;
use Taco\Data;


/**
 * @author Martin Takáč <martin@takac.name>
 */
class RichTextControl extends TextBase
{


	/**
	 * @param  string  label
	 */
	function __construct($label = Null)
	{
		parent::__construct($label);
		$this->control->setName('textarea');
	}



	/**
	 * Nastavení controlu by code
	 *
	 * @param string|Taco\Data\RichText $value
	 */
	function setValue($value)
	{
		if (empty($value)) {
			return parent::setValue(Null);
		}
		elseif ($value instanceof Data\RichText) {
			return parent::setValue($value->content);
		}
		elseif (is_string($value)) {
			return parent::setValue($value);
		}
		else {
			throw new InvalidArgumentException('Value must be Taco\Data\RichText or NULL');
		}
	}



	/**
	 * @return Nette\DateTime
	 */
	function getValue()
	{
		return new Data\RichText($this->getRawValue());
	}



	/**
	 * @return string
	 */
	function getRawValue()
	{
		return parent::getValue();
	}



	/**
	 * Generates control's HTML element.
	 * @return Nette\Utils\Html
	 */
	function getControl()
	{
		$value = $this->getRawValue();
		if ($value === '') {
			$value = $this->translate($this->emptyValue);
		}
		return parent::getControl()
			->setText($value);
	}


}
