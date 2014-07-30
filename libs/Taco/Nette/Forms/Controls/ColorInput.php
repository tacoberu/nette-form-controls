<?php
/**
 * Copyright (c) since 2004 Martin Takáč (http://martin.takac.name)
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace Taco\Nette\Forms\Controls;

use Nette\Forms\Form,
	Nette\Forms\TextInput;


/**
 * @author Martin Takáč <martin@takac.name>
 */
class ColorInput extends TextInput
{

	const PATTERN = '/^(#[0-9a-fA-F]{6})|transparent|inherit$/';


	/**
	 * @param string
	 * @throws InvalidArgumentException
	 */
	function __construct($label = Null)
	{
		parent::__construct($label);
		$this->addCondition(Form::FILLED)
				->addRule(Form::REGEXP, 'Color must be in hex format.', self::PATTERN);
		$this->control->{'data-type'} = 'color';
	}

}
