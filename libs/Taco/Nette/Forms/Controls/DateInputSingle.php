<?php
/**
 * Copyright (c) since 2004 Martin Takáč (http://martin.takac.name)
 * @license   https://opensource.org/licenses/MIT MIT
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
 * @author Martin Takáč <martin@takac.name>
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



}
