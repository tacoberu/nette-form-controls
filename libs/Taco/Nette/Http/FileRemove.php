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

namespace Taco\Nette\Http;


use Nette;


/**
 * Soubor ke smazání.
 * 
 * @author Martin Takáč <taco@taco-beru.name>
 */
class FileRemove extends Nette\Object
{


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
	public function __construct($path, $type = 'unknow')
	{
		$this->path = $path;
		$this->type = $type;
		$this->name = basename($path);
	}



	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}



	/**
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}


	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}


}
