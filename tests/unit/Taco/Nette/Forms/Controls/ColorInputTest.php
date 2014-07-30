<?php
/**
 * Copyright (c) since 2004 Martin Takáč (http://martin.takac.name)
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace Taco\Nette\Forms\Controls;

use PHPUnit_Framework_TestCase;
use Nette\Forms\Form;


/**
 * @author Martin Takáč <martin@takac.name>
 */
class ColorInputTest extends PHPUnit_Framework_TestCase
{

	function testWithoutDefaultValue()
	{
		$form = new Form();

		$input = new ColorInput();
		$form['foo'] = $input; // must be attached

		$this->assertEmpty($input->getValue());
		$this->assertFalse((bool) $input->getOption('rendered'));
		$this->assertInstanceOf('Nette\Web\Html', $input->control);
		$this->assertEquals('<input type="text" data-type="color" name="foo" id="frm-foo" value="" />', (string) $input->getControl());
		$this->assertTrue((bool) $input->getOption('rendered'));

		$form->validate();
		$this->assertEquals([], $form->getErrors());
	}



	function testWithDefaultValue()
	{
		$form = new Form();

		$control = new ColorInput();
		$form['foo'] = $control; // must be attached
		$form->setDefaults(['foo' => '#aaaaaa']);

		$this->assertEquals('#aaaaaa', $control->getValue());
		$this->assertFalse((bool) $control->getOption('rendered'));
		$this->assertInstanceOf('Nette\Web\Html', $control->control);
		$this->assertEquals('<input type="text" data-type="color" name="foo" id="frm-foo" value="#aaaaaa" />', (string) $control->control);
		$this->assertTrue((bool) $control->getOption('rendered'));

		$form->validate();
		//~ $this->assertEquals([], $form->getErrors());
	}



	function testWithIllegalDefaultValue()
	{
		$form = new Form();

		$control = new ColorInput();
		$form['foo'] = $control; // must be attached
		$form->setDefaults(['foo' => 'aaaaaa']);

		$this->assertEquals('aaaaaa', $control->getValue());
		$this->assertFalse((bool) $control->getOption('rendered'));
		$this->assertInstanceOf('Nette\Web\Html', $control->control);
		$this->assertEquals('<input type="text" data-type="color" name="foo" id="frm-foo" value="aaaaaa" />', (string) $control->control);
		$this->assertTrue((bool) $control->getOption('rendered'));

		$form->validate();
		$this->assertEquals(['Color must be in hex format.'], $form->getErrors());
	}

}
