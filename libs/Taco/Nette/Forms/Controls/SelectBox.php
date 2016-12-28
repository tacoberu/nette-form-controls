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
	Nette\Utils\Html,
	Nette\Forms\Controls as NC;


/**
 * Selectbox umějící i atributy u 2D selectboxů.
 * Select box control that allows single item selection.
 */
class SelectBox extends NC\ChoiceControl
{

	/** validation rule */
	const VALID = ':selectBoxValid';


	/** @var array of option / optgroup */
	private $options = array();


	/** @var mixed */
	private $prompt = FALSE;



	/**
	 * Sets first prompt item in select box.
	 * @param  string
	 * @return self
	 */
	public function setPrompt($prompt)
	{
		$this->prompt = $prompt;
		return $this;
	}



	/**
	 * Returns first prompt item?
	 * @return mixed
	 */
	public function getPrompt()
	{
		return $this->prompt;
	}



	/**
	 * Sets options and option groups from which to choose.
	 * @return self
	 */
	public function setItems(array $items, $useKeys = TRUE)
	{
		if (!$useKeys) {
			$res = array();
			foreach ($items as $key => $value) {
				unset($items[$key]);
				if (is_array($value)) {
					foreach ($value as $val) {
						$res[$key][(string) $val] = $val;
					}
				} else {
					$res[(string) $value] = $value;
				}
			}
			$items = $res;
		}
		$this->options = $items;
		return parent::setItems(Nette\Utils\Arrays::flatten(self::flatten($items), TRUE));
	}



	/**
	 * Zahodíme doplňující informace uložených v objektech.
	 *
	 * @param array
	 * @return array
	 */
	private static function flatten($xs)
	{
		$ret = array();
		foreach ($xs as $k => $val) {
			if ($val instanceof OptGroupItem) {
				$ret[$k] = self::flatten($val->items);
			}
			elseif ($val instanceof OptionItem) {
				$ret[$val->value] = $val->name;
			}
			else {
				$ret[$k] = $val;
			}
		}
		return $ret;
	}



	/**
	 * Generates control's HTML element.
	 * @return Nette\Utils\Html
	 */
	function getControl()
	{
		$items = $this->prompt === FALSE ? array() : array('' => $this->translate($this->prompt));
		foreach ($this->options as $key => $value) {
			$items[is_array($value) ? $this->translate($key) : $key] = $this->translate($value);
		}

		return Helpers::createSelectBox(
			$items,
			array(
				'selected?' => $this->value,
				'disabled:' => is_array($this->disabled) ? $this->disabled : NULL
			)
		)->addAttributes(parent::getControl()->attrs);
	}



	/**
	 * Performs the server side validation.
	 * @return void
	 */
	function validate()
	{
		parent::validate();
		if (!$this->isDisabled() && $this->prompt === FALSE && $this->getValue() === NULL && $this->options) {
			$this->addError(Nette\Forms\Validator::$messages[self::VALID]);
		}
	}


}
