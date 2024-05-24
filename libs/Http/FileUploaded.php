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
	 * Zda byl soubor uložen do systému (True), nebo je jen v transakci (False).
	 * @var boolean
	 */
	private $commited = False;


	/**
	 * V případě $commited == True && $remove == True - Soubor nahraný do systému, který má být smazán.
	 * V případě $commited == False && $remove == True - Soubor nahraný do transakce, který má být z transakce odstraněn.
	 * @var boolean
	 */
	private $remove = False;


	/**
	 * @param string $path Cesta k reálnému souboru. Slouží to jednak jako identifikátor, a druhak se to pokusíme zobrazit.
	 * @param string $type Mimetype as: "image/jpeg"
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
	 * @return string
	 */
	function getId()
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



	/**
	 * Has been any file uploaded?
	 */
	function isFilled(): bool
	{
		return ! $this->remove;
	}



	function getSize(): int
	{
		return 1;
	}



	function getError(): int
	{
		return 0;
	}

}
