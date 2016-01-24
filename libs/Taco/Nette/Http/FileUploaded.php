<?php
/**
 * Copyright (c) 2004, 2016 Martin Takáč (http://martin.takac.name)
 *
 * For the full copyright and license information, please view
 * the file LICENCE that was distributed with this source code.
 */

namespace Taco\Nette\Http;


use Nette;


/**
 * Nahraný, nebo nahrávání soubor.
 *
 * @author Martin Takáč <taco@taco-beru.name>
 */
class FileUploaded extends Nette\Object
{


	/**
	 * @sample "mp16.jpg"
	 * @var string
	 */
	private $name;


	/**
	 * @sample "/tmp/upload-669965256695/mp16.jpg"
	 * @var string
	 */
	private $path;


	/**
	 * @sample "image/jpeg"
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
	public function __construct($path, $type, $name = Null)
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
	public function getContentType()
	{
		return $this->type;
	}


}
