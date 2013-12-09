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
use Taco;


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
	 * Seznam existujících nahraných obrázků.
	 * @var array
	 */
	private $items = array();


	private $multiple = False;
	
	
	/**
	 * @param string
	 * @throws \InvalidArgumentException
	 */
	public function __construct($label = Null, $multiple = FALSE)
	{
		parent::__construct($label);
		$this->multiple = (bool) $multiple;
		$this->addRule(array(__class__, 'validateDate'), 'Neplatný datum.');
		
		$this->transaction = (int) (microtime(True) * 10000) - self::EPOCH_START;
	}



	/**
	 * Nastavení controlu ....
	 * 
	 * @param array of Taco\Nette\Forms\Controls\File $values
	 */
	public function setValue($values)
	{
		$this->items = array();
		if ($values && is_array($values)) {
			foreach ($values as $value) {
				$this->items[] = self::assertUploadesFile($value);
			}
		}
		return $this;
	}



	/**
	 * ...
	 * @return array of Taco\Nette\Http\FileUploaded | Nette\Http\FileUpload
	 */
	public function getValue()
	{
		$ret = $this->items;
		if ($this->value) {
			$ret[] = $this->value;
		}
		return $ret;
	}



	/**
	 * Loads HTTP data. File moved to transaction.
	 * 
	 * @return void
	 */
	public function loadHttpData()
	{
		$file = $this->getHttpData(Form::DATA_FILE, '[new]');
		if ($file === NULL) {
			$file = new FileUpload(NULL);
		}
		$this->transaction = $this->getHttpData(Form::DATA_LINE, '[transaction]');

		//	Ty, co přišli v pořádku, tak uložit do transakce
		if ($file->isOk()) {
			$file = self::storeToTransaction($this->transaction, $file);
		}

		$this->value = $file;
	}



	/**
	 * Returns HTML name of control.
	 * @return string
	 */
	public function getHtmlName()
	{
		return parent::getHtmlName() . ($this->multiple ? '[]' : '');
	}


	
	/**
	 * Html representation of control.
	 * 
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		$name = $this->getHtmlName();
		
		$container = Html::el();
		foreach ($this->items as $item) {
			$container->add(Html::el('div', array(
					'class' => 'file',
					))->setText($item->path));
		}

		return $container
			->add(Html::el('input', array(
					'type' => 'file',
					'name' => $name . '[new]',
					'multiple' => $this->multiple,
					)))
			->add(Html::el('input', array(
					'type' => 'text',
					'name' => $name . '[transaction]',
					'value' => $this->transaction,
					)))
			//~ ->add(Html::el('input')->name($name . '[month]')->value($this->month))
			//~ ->add(Html::el('input')->name($name . '[year]')->value($this->year))
			;
	}



	/**
	 * Unused
	 * 
	 * @param self $control
	 * @return bool
	 */
	public static function validateDate(self $control)
	{
		return True;
	}



	/**
	 * Has been any file uploaded?
	 * @return bool
	 */
	public function isFilled()
	{
		return $this->value instanceof FileUpload ? $this->value->isOk() : (bool) $this->value; // ignore NULL object
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

		//	Vytvořit, pokud neexistuje
		$dir = dirname($path);
		if (! file_exists($dir)) {
			mkdir($dir, 0777, True);
		}

		return $file->move($path);
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
		
		//	Vytvořit, pokud neexistuje
		if (file_exists($dir)) {
			$fs = new Filesystem();
			$fs->remove($dir);
		}
	}


}
