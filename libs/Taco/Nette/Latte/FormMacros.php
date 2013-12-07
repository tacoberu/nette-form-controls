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


namespace Taco\Nette\Latte;


use Nette,
	Nette\Latte\MacroNode,
	Nette\Latte\PhpWriter;


/**
 * Macros for Nette\Forms.
 *
 * - {errors name}
 *
 * @author		Martin Takáč <taco@taco-beru.name>
 * @credits 	David Grudl
 */
class FormMacros extends Nette\Latte\Macros\MacroSet
{

	public static function install(Nette\Latte\Compiler $compiler)
	{
		$set = new static($compiler);
		$set->addMacro('errors', array($set, 'macroErrors'));
	}



	/**
	 * {errors ...}
	 */
	public function macroErrors(MacroNode $node, PhpWriter $writer)
	{
		$words = $node->tokenizer->fetchWords();
		if (!$words) {
			throw new CompileException("Missing name in {{$node->name}}.");
		}
		$name = array_shift($words);
		$res = $writer->write(
				($name[0] === '$' 
						? '$_input = is_object(%0.word) ? %0.word : $_form[%0.word]; echo implode(", ", $_input)' 
						: 'echo implode(", ", $_form[%0.word]->errors)'),
				$name
				);
		return $res;
	}

}
