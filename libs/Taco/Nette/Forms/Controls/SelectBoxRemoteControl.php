<?php
/**
 * Copyright (c) since 2004 Martin Takáč (http://martin.takac.name)
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace Taco\Nette\Forms\Controls;

use Nette;
use Nette\Forms;
use Nette\Application\ISignalReceiver;
use Nette\Application\PresenterComponentReflection;
use Nette\Application\JsonResponse;
use InvalidArgumentException;
use Taco\Nette\Forms\QueryModel;


/**
 * Select, which load options from remote.
 * - The records load only this control by injected model.
 * - The records load remote by AJAX.
 * - Validate selectdata in side backend.
 * - Infinite scrolling content.
 * - In frontend support for Select2, Change, selectmenu.
 *
 * @author	 Martin Takáč <martin@takac.name>
 */
class SelectBoxRemoteControl extends Forms\SelectBox implements ISignalReceiver
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
	 * @var QueryModel
	 */
	private $model;


	/** @var {id:string, label:string} */
	private $item = NULL;


	/**
	 * @param string $label Popisek prvku.
	 * @param calback(term:string, page:numeric, pageSize:numeric) -> {total:numeric, items:array of {id:string, label:string}}
	 * @param calback(id:string) -> {id:string, label:string}
	 */
	function __construct(QueryModel $model, $label = NULL, $pageSize = NULL)
	{
		parent::__construct($label);
		$this->model = $model;
		if ($pageSize) {
			$this->pageSize = (int) $pageSize;
		}
	}



	/**
	 * @param int $val Set optional PageSize
	 */
	function setPageSize($val)
	{
		$this->pageSize = (int) $val;
		return $this;
	}



	/**
	 * Dotaz zpátky sem na komponentu ohledně balíčku záznamů.
	 * @param string $term Vyhledávaný text.
	 * @param numeric $page O kolikátou stránku se jedná. Počítáno o 1.
	 */
	function handleRange($term, $page, $pageSize = NULL)
	{
		list($term, $page, $pageSize) = $this->prepareRequestRange();
		if ($pageSize === NULL) {
			$pageSize = $this->pageSize;
		}
		$page = (int) $page;
		$pageSize = (int) $pageSize;
		if ( ! $pageSize) {
			$pageSize = $this->pageSize;
		}
		if ( ! $page) {
			$page = 1;
		}

		$payload = $this->model->range($term, $page, $pageSize);

		// Zda existuje další záznam.
		$payload->isMoreResults = ($page * $pageSize <= $payload->total);
		$payload->term = $term;
		$payload->page = (int) $page;
		$payload->pageSize = (int) $pageSize;

		// Výsledky vyhledávání.
		$payload->items = array_values($payload->items);

		$this->getPresenter()->terminate(new JsonResponse($payload));
	}



	/**
	 * @return Nette\Utils\Html
	 */
	function getControl()
	{
		/** @var Nette\Utils\Html $el */
		$el = parent::getControl();
		$el->attrs['data-type'] = 'remoteselect';
		$el->attrs['data-data-url'] = $this->link('//range!', array());
		$el->attrs['data-min-input'] = $this->minInput;
		$el->attrs['data-page-size'] = $this->pageSize;
		//~ if ($this->prompt) {
			//~ $el->data('prompt', $this->prompt);
		//~ }

		return $el;
	}



	/**
	 * Loads HTTP data.
	 * @return void
	 */
	function loadHttpData()
	{
		$path = explode('[', strtr(str_replace(array('[]', ']'), '', $this->getHtmlName()), '.', '_'));
		$value = Nette\ArrayTools::get($this->getForm()->getHttpData(), $path);
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
		if ($value = $this->model->read($id)) {
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
		$arr = $this->getPresenter()->request->params;
		unset($arr['do']);
		unset($arr['action']);
		return array(
			$arr['term'],
			isset($arr['page']) ? $arr['page'] : 1,
			isset($arr['pageSize']) ? $arr['pageSize'] : NULL,
		);
	}

}
