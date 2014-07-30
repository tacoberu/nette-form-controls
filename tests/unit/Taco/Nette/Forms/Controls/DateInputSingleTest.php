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
class DateInputSingleTest extends PHPUnit_Framework_TestCase
{

	function testWithoutDefaultValue()
	{
		$form = new Form();

		$input = new DateInputSingle();
		$form['foo'] = $input; // must be attached

		$this->assertEmpty($input->getValue());
		$this->assertFalse((bool) $input->getOption('rendered'));
		$this->assertInstanceOf('Nette\Web\Html', $input->control);
		$this->assertEquals('<input name="foo" id="frm-foo" data-date-format="yyyy-mm-dd" data-widget="datepicker" />', (string) $input->getControl());
		$this->assertTrue((bool) $input->getOption('rendered'));
	}



	function testSingleEdit()
	{
		$form = new Form();

		$input = new DateInputSingle();
		$input->value = new DateTime('2011-04-30');
		$form['foo'] = $input;

		$this->assertEquals('2011-04-30', $input->getValue()->format('Y-m-d'));
		$this->assertInstanceOf('Nette\Web\Html', $input->control);
		$this->assertEquals('<input name="foo" '
				. 'id="frm-foo" '
				. 'value="2011-04-30" '
				. 'data-date-format="yyyy-mm-dd" data-widget="datepicker" />', (string)$input->control);
	}

}
