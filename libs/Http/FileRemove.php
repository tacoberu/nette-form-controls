<?php
/**
 * Copyright (c) since 2004 Martin Takáč (http://martin.takac.name)
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace Taco\Nette\Http;

use Nette;


/**
 * Soubor ke smazání.
 *
 * @author Martin Takáč <martin@takac.name>
 */
class FileRemove
{

	use Nette\SmartObject;


	/**
	 * @var string
	 */
	private $name;


	/**
	 * @var string
	 */
	private $path;


	/**
	 * @var string
	 */
	private $type;


	/**
	 * @param string $path
	 */
	function __construct($path, $type = 'unknow')
	{
		$this->path = $path;
		$this->type = $type;
		$this->name = basename($path);
	}



	/**
	 * @return string
	 */
	function getName()
	{
		return $this->name;
	}



	/**
	 * @return string
	 */
	function getPath()
	{
		return $this->path;
	}



	/**
	 * @return string
	 */
	function getType()
	{
		return $this->type;
	}

}
