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


use Nette\DateTime,
	Nette\Utils\Html,
	Nette\Forms\Helpers,
	Nette\Forms\Form,
	Nette\Forms\Controls\BaseControl;


/**
 * @author Martin Takáč <taco@taco-beru.name>
 * @credits David Grudl
 */
class DateInputSingle extends BaseControl
{

	/**
	 * Uložené hodnoty data.
	 * @var string
	 */
	private $blob;


	/**
	 * Formát data.
	 * @var string
	 */
	private $format = 'Y-m-d';


	/**
	 * @param string
	 * @param string
	 * @throws \InvalidArgumentException
	 */
	public function __construct($label = Null, $format = Null)
	{
		parent::__construct($label);
		if (isset($format)) {
			$this->format = $format;
		}
		$this->addRule(array(__class__, 'validateDate'), 'Neplatné datum.');
	}



	/**
	 * Nastavení controlu by code
	 *
	 * @param string|Nette\DateTime $value
	 */
	public function setValue($value)
	{
		if ($value) {
			$data = DateTime::from($value);
			$this->blob = $data->format($this->format);
		}
		else {
			$this->blob = Null;
		}
		return $this;
	}



	/**
	 * @return Nette\DateTime
	 */
	public function getValue()
	{
		return DateTime::createFromFormat($this->format, $this->blob);
	}



	/**
	 * Mapování hondot z requestu od uživatele na control.
	 * @return void
	 */
	public function loadHttpData()
	{
		$this->blob = $this->getHttpData(Form::DATA_LINE);
	}



	/**
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		$name = $this->getHtmlName();

		return Html::el('input', array(
					'name' => $name,
					'value' => $this->formatValue($this->blob),
					'size' => 12,
					));
	}


	/**
	 * Kontrolujeme příchozivší data od uživatele.
	 *
	 * @param self $control
	 *
	 * @return bool
	 */
	public static function validateDate(self $control)
	{
		//	Value is correct
		if ($control->getValue()) {
			return True;
		}

		//	Value is empty, this is correct
		if (empty($control->blob)) {
			return True;
		}

		return False;;
	}



	private function formatValue($blob)
	{
		if (isset($blob)) {
			$date = new DateTime($blob);
			return $date->format($this->format);
		}
	}

}
