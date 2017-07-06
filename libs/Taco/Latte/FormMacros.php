<?php
/**
 * Copyright (c) since 2004 Martin Takáč (http://martin.takac.name)
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace Taco\Latte;

use Nette;
use Latte,
	Latte\MacroNode,
	Latte\PhpWriter;


/**
 * Macros for Nette\Forms.
 *
 * - {errors name}
 *
 * @author  Martin Takáč <martin@takac.name>
 * @credits David Grudl
 */
class FormMacros extends Latte\Macros\MacroSet
{

	static function install(Latte\Compiler $compiler)
	{
		$set = new static($compiler);
		$set->addMacro('errors', array($set, 'macroErrors'));
	}



	/**
	 * {errors ...}
	 */
	function macroErrors(MacroNode $node, PhpWriter $writer)
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
