<?php
/**
 * Copyright (c) since 2004 Martin Takáč (http://martin.takac.name)
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace Taco\Nette\Forms;


/**
 * @author Martin Takáč <martin@takac.name>
 */
interface QueryModel
{

	/**
	 * @param string $term
	 * @param numeric $page
	 * @param numeric $pageSize
	 * @return {total:numeric, items:array of {id:string, label:string}}
	 */
	function range($term, $page, $pageSize);


	/**
	 * @param string $id
	 * @return {id:string, label:string}
	 */
	function read($id);

}
