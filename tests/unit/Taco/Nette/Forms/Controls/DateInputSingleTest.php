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


require_once __dir__ . '/../../../../../../vendor/autoload.php';


use PHPUnit_Framework_TestCase;
use Nette\Forms\Form;
use DateTime;


/**
 * @call phpunit --bootstrap ../../../../../bootstrap.php ValueTest.php
 */
class DateInputSingleTest extends PHPUnit_Framework_TestCase
{



	function testInputsLoadValue()
	{
		$form = $this->getMock("Nette\Forms\Form", array('getHttpData'));

		$form->expects($this->at(0))
			->method("getHttpData")
			->with(Null, Null);
		$form->expects($this->at(1))
			->method("getHttpData")
			->with(Form::DATA_TEXT, '')
			->will($this->returnValue('2012-11-05'))
			;

		$m = new DateInputSingle();
		$m->parent = $form;
		$m->loadHttpData();
		$m->validate();

		$this->assertFalse($m->hasErrors());
		$this->assertEquals('2012-11-05', $m->getValue()->format('Y-m-d'));
		$this->assertEquals('<input name="" id="frm-" '
				. 'data-nette-rules=\'[{"op":"Taco\\\\Nette\\\\Forms\\\\Controls\\\\DateInputSingle::validateDate","msg":"Invalid format of date."}]\' '
				. 'value="2012-11-05" data-date-format="yyyy-mm-dd" data-widget="datepicker">', (string)$m->control);
	}



	function testInputsLoadValueInvalid()
	{
		$form = $this->getMock("Nette\Forms\Form", array('getHttpData'));

		$form->expects($this->at(0))
			->method("getHttpData")
			->with(Null, Null);
		$form->expects($this->at(1))
			->method("getHttpData")
			->with(Form::DATA_TEXT, '')
			->will($this->returnValue('abc'));

		$m = new DateInputSingle();
		$m->parent = $form;
		$m->loadHttpData();
		$m->validate();

		$this->assertTrue($m->hasErrors());
		$this->assertNull($m->getValue());
		$this->assertEquals('<input name="" id="frm-" '
				. 'data-nette-rules=\'[{"op":"Taco\\\\Nette\\\\Forms\\\\Controls\\\\DateInputSingle::validateDate","msg":"Invalid format of date."}]\' '
				. 'value="abc" data-date-format="yyyy-mm-dd" data-widget="datepicker">', (string)$m->control);
		$this->assertEquals(array('Invalid format of date.'), $m->getErrors());
	}



	function testSingleEdit()
	{
		$form = new Form();

		$m = new DateInputSingle();
		$m->value = new DateTime('2011-04-30');
		$form['foo'] = $m;

		$this->assertEquals('2011-04-30', $m->getValue()->format('Y-m-d'));
		$this->assertInstanceOf('Nette\Utils\Html', $m->control);
		$this->assertEquals('<input name="foo" id="frm-foo" data-nette-rules=\'[{"op":"Taco\\\\Nette\\\\Forms\\\\Controls\\\\DateInputSingle::validateDate","msg":"Invalid format of date."}]\' value="2011-04-30" data-date-format="yyyy-mm-dd" data-widget="datepicker">', (string)$m->control);
	}



}
