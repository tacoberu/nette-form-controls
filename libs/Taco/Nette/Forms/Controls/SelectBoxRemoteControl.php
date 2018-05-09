<?php
/**
 * Copyright (c) since 2004 Martin Takáč (http://martin.takac.name)
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace Taco\Nette\Forms\Controls;

use Nette;
use Nette\Utils\Validators;
use Nette\Forms\Controls;
use Nette\Application\UI\ISignalReceiver;
use Nette\Application\Responses\JsonResponse;
use Nella\Forms\SignalControl\SignalControl;


/**
 * Select, which load options from remote.
 * - The records load only this control by injected model.
 * - The records load remote by AJAX.
 * - Validate selectdata in side backend.
 * - Infinite scrolling content.
 * - In frontend support for Select2, Change, selectmenu.
 *
 * @author Martin Takáč <martin@takac.name>
 */
class SelectBoxRemoteControl extends Controls\SelectBox implements ISignalReceiver
{

	/**
	 * Díky tomuto traitu je možné tomuto prvku posílat signály.
	 */
	use SignalControl;


	/**
	 * Minimální počet znaků, než se začne dotazovat serveru.
	 * @var numeric
	 */
	private $minInput = 1;


	/**
	 * Size of page
	 * @var numeric
	 */
	private $pageSize = 10;


	/**
	 * @var calback(term:string, page:numeric, pageSize:numeric) -> {total:numeric, items:array of {id:string, label:string}}
	 */
	private $dataquery;


	/**
	 * @var calback(id:string) -> {id:string, label:string}
	 */
	private $dataread;


	/** @var {id:string, label:string} */
	private $item = NULL;


	/**
	 * @param string $label Popisek prvku.
	 * @param calback(term:string, page:numeric, pageSize:numeric) -> {total:numeric, items:array of {id:string, label:string}}
	 * @param calback(id:string) -> {id:string, label:string}
	 */
	function __construct($label = NULL, $dataquery, $dataread)
	{
		parent::__construct($label);
		$this->dataquery = $dataquery;
		$this->dataread = $dataread;
	}



	/**
	 * Dotaz zpátky sem na komponentu ohledně balíčku záznamů.
	 * @param string $term Vyhledávaný text.
	 * @param numeric $page O kolikátou stránku se jedná. Počítáno o 1.
	 */
	function handleRange($term, $page)
	{
		list($term, $page) = $this->prepareRequestRange();

		$fn = $this->dataquery;
		$payload = (object) $fn($term, $page, $this->pageSize);
		Validators::assertField((array)$payload, 'total', 'numeric');
		Validators::assertField((array)$payload, 'items', 'array');

		// Zda existuje další záznam.
		$payload->isMoreResults = ($page * $this->pageSize <= $payload->total);
		$payload->term = $term;
		$payload->page = (int) $page;
		$payload->pageSize = (int) $this->pageSize;

		// Výsledky vyhledávání.
		$payload->items = array_values($payload->items);

		$this->getPresenter()->sendResponse(new JsonResponse($payload));
	}



	/**
	 * @return Nette\Utils\Html
	 */
	function getControl()
	{
		/** @var Nette\Utils\Html $el */
		$el = parent::getControl();
		$el->data('type', 'remoteselect');
		$el->data('data-url', $this->link('//range!', array()));
		$el->data('min-input', $this->minInput);
		if ($this->getPrompt()) {
			$el->data('prompt', $this->getPrompt());
		}

		return $el;
	}



	/**
	 * Loads HTTP data.
	 * @return void
	 */
	function loadHttpData()
	{
		$value = $this->getHttpData(Nette\Forms\Form::DATA_TEXT);
		if (($value === NULL) || (is_array($this->disabled) && isset($this->disabled[$value]))){
			$this->value = NULL;
		}
		else {
			$this->setValue($value);
		}
	}



	/**
	 * Sets selected item (by key).
	 * @param  string|int|null
	 * @return self
	 * @internal
	 */
	function setValue($value)
	{
		Validators::assert($value, 'string|int|null');
		if (/*$this->checkAllowedValues && */$value !== NULL && empty($this->fetchOne($value))) {
			throw new Nette\InvalidArgumentException("Value '$value' is not found of resource.");
		}
		if ($value && $this->item = $this->fetchOne($value)) {
			$this->value = $this->item['id'];
			$this->items = [$this->item['id'] => $this->item['label']];
		}
		else {
			$this->value = NULL;
		}

		return $this;
	}



	/**
	 * Returns selected key.
	 * @return string|int
	 */
	function getValue()
	{
		if (empty($this->value)) {
			return NULL;
		}

		if ( ! $this->item = $this->fetchOne($this->value)) {
			return NULL;
		}
		$this->items = [$this->item['id'] => $this->item['label']];
		return $this->item['id'];
	}



	/**
	 * Returns selected value.
	 * @return mixed
	 */
	function getSelectedItem()
	{
		$item = $this->item;
		if ($item === NULL) {
			return NULL;
		}
		return $item['label'];
	}



	/**
	 * @param string $id
	 * @return {id:string, label:string}
	 */
	private function fetchOne($id)
	{
		Validators::assert($id, 'string');
		$fn = $this->dataread;
		if ($value = $fn($id)) {
			return (array) $value;
		}
		return NULL;
	}


	/**
	 * @FIXME
	 * Protože parametry jsou navzdory zvyklostem posílány absolutně.
	 * @return [term, page]
	 */
	private function prepareRequestRange()
	{
		$arr = $this->getPresenter()->getParameters();
		return array(
			isset($arr['term']) ? $arr['term'] : '',
			isset($arr['page']) ? $arr['page'] : 1,
		);
	}

}
