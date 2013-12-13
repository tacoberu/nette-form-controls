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
 * @author Martin Takáč <taco@taco-beru.name>
 */
class FileUploaded extends Nette\Object
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
	 * @var boolean
	 */
	private $commited = False;


	/**
	 * @var boolean
	 */
	private $remove = False;


	/**
	 * @param string $path
	 */
	public function __construct($path, $type = 'unknow', $name = Null)
	{
		$this->path = $path;
		$this->type = $type;
		if (empty($this->name)) {
			$this->name = basename($path);
		}
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
	public function getTemporaryFile()
	{
		return $this->path;
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
	public function getContentType()
	{
		return $this->type;
	}



	/**
	 * @return boolean
	 */
	public function isCommited()
	{
		return $this->commited;
	}



	/**
	 * @return boolean
	 */
	public function isRemove()
	{
		return $this->remove;
	}



	/**
	 * @param boolean
	 */
	public function setCommited($m = True)
	{
		$this->commited = $m;
		return $this;
	}



	/**
	 * @param boolean
	 */
	public function setRemove($m = True)
	{
		$this->remove = $m;
		return $this;
	}



	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}


}
