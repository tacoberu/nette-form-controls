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
class DateInput extends BaseControl
{

	const STYLE_INPUTS = 1;
	const STYLE_SELECTS = 2;


	/**
	 * @var int
	 */
	private $day, $month, $year;


	private $style;


	/**
	 * @param string
	 * @param int from self::STYLE_*
	 * @throws \InvalidArgumentException
	 */
	public function __construct($label = Null, $style = self::STYLE_INPUTS)
	{
		parent::__construct($label);
		$this->style = $style;
		$this->addRule(array(__class__, 'validateDate'), 'Invalid format of date.');
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
	 * @return void
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
		$this->setOption('rendered', True);
		switch ($this->style) {
			case self::STYLE_SELECTS:
				return $this->makeControlWithSelects();
			case self::STYLE_INPUTS:
			default:
				return $this->makeControlWithInputs();
		}
	}



	/**
	 * @return Nette\Utils\Html
	 */
	private function makeControlWithInputs()
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
	 * @return Nette\Utils\Html
	 */
	private function makeControlWithSelects()
	{
		$name = $this->getHtmlName();
		$days = range(0,31);
		unset($days[0]);
		$months = range(0,12);
		unset($months[0]);
		return Html::el('div', array(
				'class' => array('input'),
				))
			->add(Helpers::createSelectBox(
					$days,
					array('selected?' => $this->day)
					)
					->name($name . '[day]'))
			->add(Helpers::createSelectBox(
					$months,
					array('selected?' => $this->month)
					)
					->name($name . '[month]'))
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
		// Value is correct
		if ($control->getValue()) {
			return True;
		}

		// Value is empty, this is correct
		if (empty($control->day) && empty($control->month) && empty($control->year)) {
			return True;
		}

		return False;;
	}



}
