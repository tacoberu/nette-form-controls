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
	private $items = array();


	/**
	 * Seznam existujících nahraných souborů, které se mají smazat.
	 * @var array
	 */
	private $remove = array();


	/**
	 * Možnost nahrávat více souborů najednou.
	 * @var boolean
	 */
	private $multiple = False;
	
	
	/**
	 * @param string
	 * @throws \InvalidArgumentException
	 */
	public function __construct($label = Null, $multiple = True)
	{
		parent::__construct($label);
		$this->multiple = True; //(bool) $multiple;
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
		return array_merge($this->items, $this->remove, $this->value);
	}



	/**
	 * Loads HTTP data. File moved to transaction.
	 * 
	 * @return void
	 */
	public function loadHttpData()
	{
		$this->transaction = $this->getHttpData(Form::DATA_LINE, '[transaction]');
		$files = $this->getHttpData(Form::DATA_FILE, '[new][]');
		$items = $this->getHttpData(Form::DATA_LINE, '[exists][]');
		$remove = $this->getHttpData(Form::DATA_LINE, '[remove][]');

		//	Ty, co přišli v pořádku, tak uložit do transakce
		$this->value = array();
		foreach ($files as $file) {
			if ($file->isOk()) {
				$this->value[] = self::storeToTransaction($this->transaction, $file);
			}
		}
		
		//	Promazávání existujících.
		$this->items = array();
		foreach ($items as $item) {
			if (in_array($item, $remove)) {
				$this->remove[] = new FileRemove($item);
			}
			else {
				$this->items[] = new FileUploaded($item);
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
		
		$container = Html::el();
		
		foreach ($this->items as $item) {
			$container->add(Html::el('input', array(
					'type' => 'hidden',
					'value' => $item->path,
					'name' => $name . '[exists][]',
					)));
			$container->add(Html::el('input', array(
					'type' => 'checkbox',
					'value' => $item->path,
					'name' => $name . '[remove][]',
					'title' => strtr('Remove file: %{name}', array(
							'%{name}' => $item->name
							)),
					)));
			$container->add(Html::el('span', array(
					'class' => array('file', $item->type),
					))->setText($item->name));
		}

		return $container
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
