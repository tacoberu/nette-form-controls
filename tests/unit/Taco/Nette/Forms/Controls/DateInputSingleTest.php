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



	function _testInputsLoadValue()
	{
		$form = new Form();

		$m = new DateInput();
		//~ $m->value = new DateTime('2011-04-30');
		$form['foo'] = $m;

		$m->loadHttpData();

		dump($m->getValue());
	}



	function testSingleEdit()
	{
		$form = new Form();

		$m = new DateInputSingle();
		$m->value = new DateTime('2011-04-30');
		$form['foo'] = $m;

		$this->assertInstanceOf('Nette\Utils\Html', $m->control);
		$this->assertEquals('<input name="foo" value="2011-04-30" size="12">', (string)$m->control);
	}



}
