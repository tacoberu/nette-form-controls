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
