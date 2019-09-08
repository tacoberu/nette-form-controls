<?php
/**
 * Copyright (c) since 2004 Martin Takáč (http://martin.takac.name)
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace Taco\Nette\Forms;

use Nette\Utils\Validators;


/**
 * Simple QueryModel implementation by pair of callbacks.
 * @author Martin Takáč <martin@takac.name>
 */
class CallbackQueryModel implements QueryModel
{

	/**
	 * @var calback(term:string, page:numeric, pageSize:numeric) -> {total:numeric, items:array of {id:string, label:string}}
	 */
	private $dataquery;


	/**
	 * @var calback(id:string) -> {id:string, label:string}
	 */
	private $dataread;


	/**
	 * @param calback(term:string, page:numeric, pageSize:numeric) -> {total:numeric, items:array of {id:string, label:string}}
	 * @param calback(id:string) -> {id:string, label:string}
	 */
	function __construct($dataquery, $dataread)
	{
		$this->dataquery = $dataquery;
		$this->dataread = $dataread;
	}



	/**
	 * @param string $term
	 * @param numeric $page
	 * @param numeric $pageSize
	 * @return {total:numeric, items:array of {id:string, label:string}}
	 */
	function range($term, $page, $pageSize)
	{
		Validators::assert($term, 'string');
		Validators::assert($page, 'numeric');
		Validators::assert($pageSize, 'numeric');
		$fn = $this->dataquery;
		return $fn($term, $page, $pageSize);
	}



	/**
	 * @param string $id
	 * @return {id:string, label:string}
	 */
	function read($id)
	{
		Validators::assert($id, 'string:1..');
		$fn = $this->dataread;
		return $fn($id);
	}

}
