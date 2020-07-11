<?php

namespace Taco\Nette\Application\UI;

use Nette;


/**
 * Vylepšení:
 * - vyžaduje callback pro získání inicializačních dat.
 */
class Form extends Nette\Application\UI\Form
{

	/**
	 * @var callback
	 */
	private $initialize;

	/**
	 * @var string
	 */
	private $name;

	function __construct($initialize, $parent = null, $name = null)
	{
		parent::__construct($parent, $name);
		$this->name = $name;
		$this->initialize = $initialize;
		$this->monitor('Nette\Application\IPresenter');
	}



	function getFlowName()
	{
		return $this->name;
	}



	protected function attached($presenter)
	{
		parent::attached($presenter);
		if ($presenter instanceof Nette\Application\IPresenter) {
			if ( ! $this->isAnchored() || ! $this->isSubmitted()) {
				if ($cb = $this->initialize) {
					$this->setDefaults((array) $cb($this));
				}
			}
		}
	}

}
