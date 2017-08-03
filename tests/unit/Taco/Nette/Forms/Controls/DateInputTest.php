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
		$this->assertInstanceOf('Nette\Utils\Html', $m->control);
		$this->assertEquals('<div class="input">'
				. '<input name="foo[day]" size="4" placeholder="day">'
				. '<input name="foo[month]" size="4" placeholder="month">'
				. '<input name="foo[year]" size="4" placeholder="year">'
				. '</div>', (string)$m->control);

		$this->assertTrue((bool)$m->getOption('rendered'));
	}



	function testInputsEdit()
	{
		$form = new Form();

		$m = new DateInput();
		$m->value = new DateTime('2011-04-30');
		$form['foo'] = $m;

		//~ $this->assertNull($m->getValue());
		$this->assertInstanceOf('Nette\Utils\Html', $m->control);
		$this->assertEquals('<div class="input">'
				. '<input name="foo[day]" value="30" size="4" placeholder="day">'
				. '<input name="foo[month]" value="4" size="4" placeholder="month">'
				. '<input name="foo[year]" value="2011" size="4" placeholder="year">'
				. '</div>', (string)$m->control);
	}



	function _testInputsLoadValue()
	{
		$form = new Form();

		$m = new DateInput();
		//~ $m->value = new DateTime('2011-04-30');
		$form['foo'] = $m;

		$m->loadHttpData();

		dump($m->getValue());
	}



	function _testSingleEdit()
	{
		$form = new Form();

		$m = new DateInput(Null, DateInput::STYLE_SINGLE);
		$m->value = new DateTime('2011-04-30');
		$form['foo'] = $m;

		$this->assertInstanceOf('Nette\Utils\Html', $m->control);
		$this->assertEquals('<input name="foo[day]" value="2011-04-30" size="12">', (string)$m->control);
	}

}
