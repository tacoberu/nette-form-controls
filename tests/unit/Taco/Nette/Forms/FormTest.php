<?php
/**
 * Copyright (c) since 2004 Martin Takáč (http://martin.takac.name)
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace Taco\Nette\Application\UI;

use PHPUnit\Framework\TestCase;


class FormTest extends TestCase
{

	function testWithoutGroups()
	{
		$form = new Form(Null);
		$form->addText('foo', 'Foo');
		$rec = $form->addContainer('rec');
		$rec->addText('name', 'Name');
		$form->addButton('send', 'Send');
		$this->assertSame(['foo', 'rec', 'send'], array_keys(iterator_to_array($form->getComponents())));
		$this->assertSame([], $form->getGroups());
		$this->assertSame(Null, $form->getCurrentGroup());
	}



	function testWithGroups()
	{
		$form = new Form(Null);
		$form->addText('foo', 'Foo');
		$form->addGroup('G');
		$form->addText('doo', 'Name');
		$form->addButton('send', 'Send');
		$this->assertSame(['foo', 'doo', 'send'], array_keys(iterator_to_array($form->getComponents())));
		$this->assertSame(['G'], array_keys($form->getGroups()));
		//~ dump(array_keys($form->getGroups()));
		//~ $this->assertSame([], $form->getGroups());
		//~ $this->assertSame(Null, $form->getCurrentGroup());
	}

}
