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

namespace Taco\Nette\Forms\Controls;


use Nette,
	Nette\DateTime,
	Nette\Utils\Html,
	Nette\Utils\Validators,
	Nette\Forms\Form,
	Nette\Forms\Controls\BaseControl;
use Taco,
	Taco\Nette\Http\FileUploaded,
	Taco\Nette\Http\FileRemove;


/**
 * @author Martin Takáč <taco@taco-beru.name>
 */
class MultipleUploadControl extends BaseControl
{

	const EPOCH_START = 13866047000000;


	/**
	 * Identifikátor, pod kterým je evidována transakce.
	 * @var int
	 */
	private $transaction;


	/**
	 * Seznam existujících nahraných souborů.
	 * @var array
	 */
	private $uploaded = array();


	/**
	 * Seznam existujících nahraných souborů, které se mají smazat.
	 * @var array
	 */
	private $remove = array();


	/**
	 * Seznam existujících nahraných souborů, které se ještě nezapsaly do modelu.
	 * @var array
	 */
	private $uploading = array();


	/**
	 * Možnost nahrávat více souborů najednou.
	 * @var boolean
	 */
	private $multiple = False;


	/**
	 * Funkce pro zpracování mimetype na class
	 * @var function
	 */
	private $parseType;


	/**
	 * @param string
	 * @throws \InvalidArgumentException
	 */
	public function __construct($label = Null, $multiple = True)
	{
		parent::__construct($label);
		$this->multiple = True; //(bool) $multiple;
		$this->control = Html::el('ul', array(
				'class' => 'file-uploader',
				));
		$this->transaction = (int) (microtime(True) * 10000) - self::EPOCH_START;
		$this->parseType = function ($s)
		{
			if (empty($s)) {
				return $s;
			}

			$p = explode('/', $s, 2);
			return $p[0];
		};
	}



	/**
	 * Set function for formating mime type class
	 *
	 * @param function
	 */
	public function setMimeTypeClassFunction($fce)
	{
		$this->parseType = $fce;
	}



	/**
	 * Set control's values.
	 *
	 * @param array of Taco\Nette\Forms\Controls\File $values
	 */
	public function setValue($values)
	{
		$this->value = array();
		if ($values && is_array($values)) {
			foreach ($values as $value) {
				$this->value[] = self::assertUploadesFile($value)->setCommited(True);
			}
		}
		return $this;
	}



	/**
	 * Returning values.
	 * @return array of Taco\Nette\Http\FileUploaded | Nette\Http\FileUpload
	 */
	public function getValue()
	{
		return array_merge($this->uploaded, $this->remove, (array)$this->value);
	}



	/**
	 * Loads HTTP data. File moved to transaction.
	 *
	 * @return void
	 */
	public function loadHttpData()
	{
		$this->value = array();

		$this->transaction = $this->getHttpData(Form::DATA_LINE, '[transaction]');

		$newfiles = $this->getHttpData(Form::DATA_FILE, '[new][]');

		$uploadedFiles = $this->getHttpData(Form::DATA_LINE, '[uploaded][files][]');
		$uploadedRemove = $this->getHttpData(Form::DATA_LINE, '[uploaded][remove][]');

		$uploadingFiles = $this->getHttpData(Form::DATA_LINE, '[uploading][files][]');
		$uploadingRemove = $this->getHttpData(Form::DATA_LINE, '[uploading][remove][]');

		// Promazávání existujících.
		$this->uploaded = array();
		foreach ($uploadedFiles as $item) {
			$file = self::createFileUploadedFromValue($item);
			$file->setCommited(True);
			if (in_array($item, $uploadedRemove)) {
				$file->setRemove(True);
			}
			$this->value[] = $file;
		}

		// Promazávání transakce.
		foreach ($uploadingFiles as $item) {
			if (! in_array($item, $uploadingRemove)) {
				$file = self::createFileUploadedFromValue($item);
				$file->setCommited(False);
				$this->value[] = $file;
			}
		}

		// Ty, co přišli v pořádku, tak uložit do transakce, co nejsou v pořádku zahodit a oznámit neuspěch.
		foreach ($newfiles as $file) {
			if ($file->isOk()) {
				$this->value[] = self::storeToTransaction($this->transaction, $file);
			}
			else {
				$this->addError(self::formatError($file));
			}
		}
	}



	/**
	 * Html representation of control.
	 *
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		$name = $this->getHtmlName();

		$container = clone $this->control;
		$parseTypeFunction = $this->parseType;

		// Prvky nahrané už někde na druhé straně
		foreach ($this->value as $item) {
			if ($item->isCommited()) {
				$section = 'uploaded';
			}
			else {
				$section = 'uploading';
			}

			$container->add(Html::el('li', array('class' => "file {$section}-file"))
					->add(Html::el('input', array(
							'type' => 'hidden',
							'value' => self::formatValue($item),
							'name' => "{$name}[{$section}][files][]",
							)))
					->add(Html::el('input', array(
							'type' => 'checkbox',
							'checked' => ($item->isRemove()),
							'value' => self::formatValue($item),
							'name' => "{$name}[{$section}][remove][]",
							'title' => strtr('Remove file: %{name}', array(
									'%{name}' => $item->name
									)),
							)))
					->add(Html::el('span', array(
							'class' => array('file', $parseTypeFunction($item->contentType)),
							))->setText($item->name))
					);
		}

		// Nový prvek
		return $container->add(Html::el('li', array('class' => 'file new-file'))
				->add(Html::el('input', array(
						'type' => 'file',
						'name' => $name . '[new][]',
						'multiple' => True, //$this->multiple,
						)))
				->add(Html::el('input', array(
						'type' => 'hidden',
						'name' => $name . '[transaction]',
						'value' => $this->transaction,
						)))
						);
	}



	/**
	 * This method will be called when the component (or component's parent)
	 * becomes attached to a monitored object. Do not call this method yourself.
	 * @param  Nette\ComponentModel\IComponent
	 * @return void
	 */
	protected function attached($form)
	{
		if ($form instanceof Nette\Forms\Form) {
			if ($form->getMethod() !== Nette\Forms\Form::POST) {
				throw new Nette\InvalidStateException('File upload requires method POST.');
			}
			$form->getElementPrototype()->enctype = 'multipart/form-data';
		}
		parent::attached($form);
	}



	private static function assertUploadesFile(Taco\Nette\Http\FileUploaded $value)
	{
		return $value;
	}



	/**
	 * První přesunutí do adresáře který reprezentuje transakci.
	 *
	 * @param string id Identifikátor transakce.
	 * @param Nette\Http\FileUpload $file Soubor do transakce.
	 *
	 * @return Soubor v transakci
	 */
	private static function storeToTransaction($id, Nette\Http\FileUpload $file)
	{
		Validators::assert($id, 'string');

		$path = array(sys_get_temp_dir(), 'upload-' . $id, $file->sanitizedName);
		$path = implode(DIRECTORY_SEPARATOR, $path);

		// Vytvořit, pokud neexistuje
		$dir = dirname($path);
		if (! file_exists($dir)) {
			mkdir($dir, 0777, True);
		}

		$file->move($path);
		return new FileUploaded($file->temporaryFile, $file->contentType, $file->name);
	}



	/**
	 * Odstranění adresáře s transakcí.
	 *
	 * @param string id Identifikátor transakce.
	 */
	private static function removeToTransaction($id)
	{
		Validators::assert($id, 'string');

		$dir = array(sys_get_temp_dir(), 'upload-' . $id);
		$dir = implode(DIRECTORY_SEPARATOR, $dir);

		// Vytvořit, pokud neexistuje
		if (file_exists($dir)) {
			$fs = new Filesystem();
			$fs->remove($dir);
		}
	}



	/**
	 * @param string $s 'image/jpeg'
	 * @return string 'image'
	 */
	private static function parseType($s)
	{
		if (empty($s)) {
			return $s;
		}

		$p = explode('/', $s, 2);
		return $p[0];
	}



	/**
	 * @param Taco\Nette\Http\FileUploaded $s
	 * @return string 'image'
	 */
	private static function formatValue($s)
	{
		return $s->contentType . '#' . $s->path;
	}



	/**
	 * @param Taco\Nette\Http\FileUploaded $s
	 * @return string
	 */
	private static function formatError($file)
	{
		switch ($file->error) {
			case UPLOAD_ERR_OK:
				throw \LogicException('No error.');
			case UPLOAD_ERR_INI_SIZE:
				$message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
				break;
			case UPLOAD_ERR_FORM_SIZE:
				$message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
				break;
			case UPLOAD_ERR_PARTIAL:
				$message = "The uploaded file was only partially uploaded";
				break;
			case UPLOAD_ERR_NO_FILE:
				$message = "No file was uploaded";
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				$message = "Missing a temporary folder";
				break;
			case UPLOAD_ERR_CANT_WRITE:
				$message = "Failed to write file to disk";
				break;
			case UPLOAD_ERR_EXTENSION:
				$message = "File upload stopped by extension";
				break;
			default:
				$message = "Unknown upload error";
				break;
		}

		return "{$file->name}: {$message}";
	}



	/**
	 * @param string $s 'image/jpeg#tasks/6s3qva8l/4728-05.jpg'
	 * @return Taco\Nette\Http\FileUploaded $s
	 */
	private static function createFileUploadedFromValue($s)
	{
		$s = explode('#', $s, 2);
		$file = new FileUploaded($s[1], $s[0]);
		return $file;
	}




}
