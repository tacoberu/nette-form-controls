<?php
/**
 * Copyright (c) since 2004 Martin Takáč (http://martin.takac.name)
 * @license   https://opensource.org/licenses/MIT MIT
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
 * Správa souborů. Ve formuláři můžeme mět již předvyplněné soubory které můžeme chtít
 * odstraňovat/mazat. Soubory můžeme volně přidávat a odebírat. Nic se neukládá
 * (všechno ale se uchovává v transakci) dokud formulář neuložíme.
 *
 * @author Martin Takáč <martin@takac.name>
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
		$this->value = array();
		if ($values && is_array($values)) {
			foreach ($values as $value) {
				$this->value[] = self::assertUploadesFile($value)->setCommited(True);
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

		//	Promazávání existujících.
		$this->uploaded = array();
		foreach ($uploadedFiles as $item) {
			$file = new FileUploaded($item);
			$file->setCommited(True);
			if (in_array($item, $uploadedRemove)) {
				$file->setRemove(True);
			}
			$this->value[] = $file;
		}

		//	Promazávání transakce.
		foreach ($uploadingFiles as $item) {
			if (! in_array($item, $uploadingRemove)) {
				$file = new FileUploaded($item);
				$file->setCommited(False);
				$this->value[] = $file;
			}
		}

		//	Ty, co přišli v pořádku, tak uložit do transakce, co nejsou v pořádku zahodit a oznámit neuspěch.
		//	@TODO oznámit neuspěch
		foreach ($newfiles as $file) {
			if ($file->isOk()) {
				$this->value[] = self::storeToTransaction($this->transaction, $file);
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

		$container = Html::el('ul');

		//	Prvky nahrané už někde na druhé straně
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
							'value' => $item->path,
							'name' => "{$name}[{$section}][files][]",
							)))
					->add(Html::el('input', array(
							'type' => 'checkbox',
							'checked' => ($item->isRemove()),
							'value' => $item->path,
							'name' => "{$name}[{$section}][remove][]",
							'title' => strtr('Remove file: %{name}', array(
									'%{name}' => $item->name
									)),
							)))
					->add(Html::el('span', array(
							'class' => array('file', $item->type),
							))->setText($item->name))
					);
		}

		//	Nový prvek
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
				->add(Html::el('span', array(
						'class' => array('file', 'new-file'),
						))->setText('Nevybráno'))
						);
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

		//	Vytvořit, pokud neexistuje
		if (file_exists($dir)) {
			$fs = new Filesystem();
			$fs->remove($dir);
		}
	}


}
