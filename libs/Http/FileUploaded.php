<?php
/**
 * Copyright (c) since 2004 Martin Takáč (http://martin.takac.name)
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace Taco\Nette\Http;

use Nette;


/**
 * Nahraný, nebo nahrávání soubor.
 *
 * @author Martin Takáč <martin@takac.name>
 */
class FileUploaded
{

	use Nette\SmartObject;


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
	function __construct($path, $type, $name = Null)
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
	function getName()
	{
		return $this->name;
	}



	/**
	 * @return string
	 */
	function getTemporaryFile()
	{
		return $this->path;
	}



	/**
	 * @return string
	 */
	function getPath()
	{
		return $this->path;
	}



	/**
	 * @return boolean
	 */
	function isCommited()
	{
		return $this->commited;
	}



	/**
	 * @return boolean
	 */
	function isRemove()
	{
		return $this->remove;
	}



	/**
	 * @param boolean
	 */
	function setCommited($m = True)
	{
		$this->commited = $m;
		return $this;
	}



	/**
	 * @param boolean
	 */
	function setRemove($m = True)
	{
		$this->remove = $m;
		return $this;
	}



	/**
	 * @return string
	 */
	function getContentType()
	{
		return $this->type;
	}

}
