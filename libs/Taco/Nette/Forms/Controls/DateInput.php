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
	Nette\Forms\Form,
	Nette\Forms\Controls\BaseControl;


/**
 * @author Martin Takáč <taco@taco-beru.name>
 * @credits David Grudl
 */
class DateInput extends BaseControl
{

	/** @var int */
	private $day, $month, $year;


	/**
	 * @param string
	 * @param string
	 * @throws \InvalidArgumentException
	 */
	public function __construct($label = Nsull)
	{
		parent::__construct($label);
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
			$this->day = $data->format('j');
			$this->month = $data->format('n');
			$this->year = $data->format('Y');
		}
		else {
			$this->day = $this->month = $this->year = Null;
		}
		return $this;
	}



	/**
	 * ...
	 * @return Nette\DateTime
	 */
	public function getValue()
	{
		return @ checkdate($this->month, $this->day, $this->year)
				? DateTime::from("{$this->year}-{$this->month}-{$this->day}")
				: Null;
	}



	/**
	 * Mapování hondot z requestu od uživatele na control.
	 */
	public function loadHttpData()
	{
		$this->year = $this->getHttpData(Form::DATA_LINE, '[year]');
		$this->month = $this->getHttpData(Form::DATA_LINE, '[month]');
		$this->day = $this->getHttpData(Form::DATA_LINE, '[day]');
	}


	
	/**
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		$name = $this->getHtmlName();
		
		return Html::el('div', array(
				'class' => array('input'),
				))
			->add(Html::el('input', array(
					'name' => $name . '[day]',
					'value' => $this->day,
					'size' => 4,
					'placeholder' => 'day',
					)))
			->add(Html::el('input', array(
					'name' => $name . '[month]',
					'value' => $this->month,
					'size' => 4,
					'placeholder' => 'month',
					)))
			->add(Html::el('input', array(
					'name' => $name . '[year]',
					'value' => $this->year,
					'size' => 4,
					'placeholder' => 'year',
					)));
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
		if (empty($control->day) && empty($control->month) && empty($control->year)) {
			return True;
		}

		return False;;
	}

}
