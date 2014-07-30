<?php
/**
 * Copyright (c) since 2004 Martin Takáč (http://martin.takac.name)
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace Taco\Nette\Forms\Controls;

require_once __dir__ . '/../../../../../../vendor/autoload.php';

use PHPUnit_Framework_TestCase;
use Nette\Forms\Form;
use DateTime;


/**
 * @call phpunit --bootstrap ../../../../../bootstrap.php ValueTest.php
 * @author Martin Takáč <martin@takac.name>
 */
class DateInputTest extends PHPUnit_Framework_TestCase
{

	function testInputsAdd()
	{
		$form = new Form();

		$m = new DateInput();
		$form['foo'] = $m;

		$this->assertNull($m->getValue());
		$this->assertFalse((bool)$m->getOption('rendered'));
		$this->assertInstanceOf('Nette\Web\Html', $m->control);
		$this->assertEquals('<div class="input">'
				. '<input name="foo[day]" size="4" placeholder="day" />'
				. '<input name="foo[month]" size="4" placeholder="month" />'
				. '<input name="foo[year]" size="4" placeholder="year" />'
				. '</div>', (string)$m->control);

		$this->assertTrue((bool)$m->getOption('rendered'));
	}



	function testInputsEdit()
	{
		$form = new Form();

		$m = new DateInput();
		$m->value = $val = new DateTime('2011-04-30');
		$form['foo'] = $m;

		$this->assertEquals($val, $m->getValue());
		$this->assertInstanceOf('Nette\Web\Html', $m->control);
		$this->assertEquals('<div class="input">'
				. '<input name="foo[day]" value="30" size="4" placeholder="day" />'
				. '<input name="foo[month]" value="4" size="4" placeholder="month" />'
				. '<input name="foo[year]" value="2011" size="4" placeholder="year" />'
				. '</div>', (string)$m->control);
	}



	function testSetValueAsString()
	{
		$form = new Form();

		$input = new DateInput();
		$input->value = $val = '2011-04-30';
		$form['foo'] = $input;
		$this->assertEquals(DateTime::createFromFormat('Y-m-d H:i:s', "$val 00:00:00"), $input->getValue());
		$this->assertInstanceOf('Nette\Web\Html', $input->control);
		$this->assertEquals('<div class="input">'
				. '<input name="foo[day]" value="30" size="4" placeholder="day" />'
				. '<input name="foo[month]" value="4" size="4" placeholder="month" />'
				. '<input name="foo[year]" value="2011" size="4" placeholder="year" />'
				. '</div>', (string)$input->control);
	}

}
