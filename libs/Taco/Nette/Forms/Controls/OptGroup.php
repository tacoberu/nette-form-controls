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
class OptGroupItem extends Nette\Object
{

	private $label;
	private $items;
	private $attribs = array();

	function __construct($label, array $items = array())
	{
		$this->label = $label;
		$this->items = $items;
	}


	function setAttrib($name, $value)
	{
		$this->attribs[$name] = (string)$value;
		return $this;
	}


	function addOption(OptionItem $m)
	{
		$this->items[$m->value] = $m;
		return $this;
	}


	function getLabel()
	{
		return $this->label;
	}


	function getAttribs()
	{
		return $this->attribs;
	}


	function getItems()
	{
		return $this->items;
	}



}
