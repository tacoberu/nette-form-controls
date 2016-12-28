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
 * @author     Martin Takáč (martin@takac.name)
 */

namespace Taco\Nette\Forms\Controls;


use Nette;


/**
 * ???
 */
class OptionItem extends Nette\Object
{

	private $value;
	private $name;
	private $attribs = array();


	function __construct($value, $name)
	{
		$this->value = $value;
		$this->name = $name;
	}


	function setAttrib($name, $value)
	{
		$this->attribs[$name] = (string)$value;
		return $this;
	}


	function getValue()
	{
		return $this->value;
	}


	function getName()
	{
		return $this->name;
	}


	function getAttribs()
	{
		return $this->attribs;
	}

}
