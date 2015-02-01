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


use Taco\Data\DateTime;
use Nette\Utils\Html,
	Nette\Forms\Helpers,
	Nette\Forms\Form,
	Nette\Forms\Controls\BaseControl;


/**
 * Přebírá a vrací objekt DateTime jako reprezentaci Datumu.
 * Na straně formuláře se jedná o jeden element, volitelně odekorovaný javascriptem.
 *
 * @author Martin Takáč <taco@taco-beru.name>
 * @credits David Grudl
 */
class DateInputSingle extends BaseControl
{

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
		$this->addRule(array(__class__, 'validateDate'), 'Invalid format of date.');
	}



	/**
	 * Nastavení controlu by code
	 *
	 * @param string|Nette\DateTime $value
	 */
	public function setValue($value)
	{
		if ($value && $value instanceof \DateTime) {
			$value = $value->format($this->format);
		}

		return parent::setValue($value);
	}



	/**
	 * @return Nette\DateTime
	 */
	public function getValue()
	{
		$value = parent::getValue();
		if (empty($value)) {
			return Null;
		}

		if (self::validateDate($this)) {
			return DateTime::createFromFormat($this->format, $value);
		}
	}



	/**
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		$input = parent::getControl();
		$input->value = $this->value;
		$input->data['date-format'] = self::formatAsBootstrapLike($this->format);
		$input->data['widget'] = "datepicker";
		return $input;
	}



	/**
	 * Is control filled?
	 * @return bool
	 */
	public function isFilled()
	{
		$value = $this->value;
		return $value !== NULL && $value !== array() && $value !== '';
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
		try {
			DateTime::createFromFormat($control->format, $control->value);
			return True;
		}
		catch (\Exception $e) {
			return False;
		}
	}


	/**
	 * Format aceptance only the date format, combination of d, dd, m, mm, yy, yyy.
	 * @param string
	 * @return string
	 */
	private static function formatAsBootstrapLike($s)
	{
		return strtr($s, array(
				// Day
				'j' => 'd',  // Day of the month without leading zeros 1 to 31
				'd' => 'dd', // Day of the month, 2 digits with leading zeros 01 to 31
				//~ 'D A textual representation of a day, three letters Mon through Sun
				//~ l (lowercase 'L') A full textual representation of the day of the week  Sunday through Saturday
				//~ N ISO-8601 numeric representation of the day of the week (added in PHP 5.1.0) 1 (for Monday) through 7 (for Sunday)
				//~ S  English ordinal suffix for the day of the month, 2 characters  st, nd, rd or th. Works well with j
				//~ w  Numeric representation of the day of the week  0 (for Sunday) through 6 (for Saturday)
				//~ z  The day of the year (starting from 0) 0 through 365
				// Week
				//~ W  ISO-8601 week number of year, weeks starting on Monday (added in PHP 4.1.0) Example: 42 (the 42nd week in the year)
				// Month
				//~ F  A full textual representation of a month, such as January or March January through December
				'n' => 'm', // Numeric representation of a month, without leading zeros 1 through 12
				'm' => 'mm', // Numeric representation of a month, with leading zeros 01 through 12
				//~ M  A short textual representation of a month, three letters Jan through Dec
				//~ t  Number of days in the given month 28 through 31
				// Year
				//~ L  Whether it's a leap year 1 if it is a leap year, 0 otherwise.
				//~ o  ISO-8601 year number. This has the same value as Y, except that if the ISO week number (W) belongs to the previous or next year, that year is used instead. (added in PHP 5.1.0) Examples: 1999 or 2003
				'y' => 'yy',   // A two digit representation of a year Examples: 99 or 03
				'Y' => 'yyyy', // A full numeric representation of a year, 4 digits Examples: 1999 or 2003
				));
	}

}
