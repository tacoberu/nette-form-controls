<?php
/**
 * This file is part of the Nella Project (http://nella-project.org).
 *
 * Copyright (c) Patrik Votoček (http://patrik.votocek.cz)
 *
 * For the full copyright and license information,
 * please view the file LICENSE.md that was distributed with this source code.
 */

namespace Taco\Nette\Forms\Controls;

use Nette\Application\AppForm as Form;
use Nette\Application\Presenter;
//~ use Nette\Application\UI\PresenterComponentReflection;
use Nette\IComponentContainer;
use Nette;


/**
 * Díky tomuto traitu je možné tomuto prvku posílat signály.
 * @author Patrik Votoček
 * @author Martin Takáč <martin@takac.name>
 */
trait SignalControl
{

    /** @var array|mixed[] */
    private $params = array();


    protected function validateParent(IComponentContainer $parent)
    {
        parent::validateParent($parent);

        $this->monitor('Nette\Application\Presenter');
    }



    /**
     * Returns a fully-qualified name that uniquely identifies the component
     * within the presenter hierarchy.
     *
     * @return string
     */
    private function getUniqueId()
    {
        return $this->lookupPath('Nette\Application\Presenter', TRUE);
    }



    /**
     * This method will be called when the component (or component's parent)
     * becomes attached to a monitored object. Do not call this method yourself.
     *
     * @param  IComponentContainer
     */
    protected function attached($component)
    {
        if (!$component instanceof Form && !$component instanceof Presenter) {
            throw new \Nette\InvalidStateException(sprintf('%s must be attached to Nette\Application\UI\Form', get_called_class()));
        }

        if ($component instanceof Presenter) {
            //~ $this->params = $component->popGlobalParameters($this->getUniqueId());
        }

        parent::attached($component);
    }



    /**
     * @param string
     */
    public function signalReceived($signal)
    {
        $methodName = sprintf('handle%s', self::firstUpper($signal));
        if (!method_exists($this, $methodName)) {
            throw new Nette\Application\BadSignalException(sprintf('Method %s does not exist', $methodName));
        }

		$params = $this->prepareRequestRange();

		//~ $presenterComponentReflection = new PresenterComponentReflection(get_called_class());
		//~ $methodReflection = $presenterComponentReflection->getMethod($methodName);
		//~ dump($presenterComponentReflection->combineArgs($methodReflection, $params));
        //~ $methodReflection->invokeArgs($this, $presenterComponentReflection->combineArgs($methodReflection, $params));
        //~ die('=====[' . __line__ . '] ' . __file__);
		$this->$methodName($params[0], $params[1]);
    }



    /**
     * @return Presenter
     */
    protected function getPresenter()
    {
        return $this->getForm()->getPresenter();
    }



    /**
     * Generates URL to presenter, action or signal.
     * @param string destination in format "signal!"
     * @param array|mixed[]
     * @return string
     */
    protected function link($destination, $args = array())
    {
        $destination = trim($destination);
        if (!Nette\String::endsWith($destination, '!') || self::contains($destination, ':')) {
            throw new InvalidArgumentException(sprintf('%s support only own signals.', get_called_class()));
        }

        $args = is_array($args) ? $args : array_slice(func_get_args(), 1);
        $fullPath = Nette\String::startsWith($destination, '//');
        if ($fullPath) {
            $destination = self::substring($destination, 2);
        }
        $destination = sprintf('%s%s-%s', $fullPath ? '//' : '', $this->getUniqueId(), $destination);
        $newArgs = [];
        foreach ($args as $key => $value) {
            $newArgs[sprintf('%s-%s', $this->getUniqueId(), $key)] = $value;
        }
        $args = $newArgs;

        return $this->getPresenter()->link($destination, $args);
    }



	/**
	 * Does $haystack contain $needle?
	 * @param  string
	 * @param  string
	 * @return bool
	 */
	private static function contains($haystack, $needle)
	{
		return strpos($haystack, $needle) !== FALSE;
	}



	/**
	 * Returns a part of UTF-8 string.
	 * @param  string
	 * @param  int in characters (code points)
	 * @param  int in characters (code points)
	 * @return string
	 */
	private static function substring($s, $start, $length = NULL)
	{
		if (function_exists('mb_substr')) {
			if ($length === NULL && PHP_VERSION_ID < 50408) {
				$length = self::length($s);
			}
			return mb_substr($s, $start, $length, 'UTF-8'); // MB is much faster
		} elseif ($length === NULL) {
			$length = self::length($s);
		} elseif ($start < 0 && $length < 0) {
			$start += self::length($s); // unifies iconv_substr behavior with mb_substr
		}
		return iconv_substr($s, $start, $length, 'UTF-8');
	}



	/**
	 * Convert first character to upper case.
	 * @param  string  UTF-8 encoding
	 * @return string
	 */
	private static function firstUpper($s)
	{
		return self::upper(self::substring($s, 0, 1)) . self::substring($s, 1);
	}



	/**
	 * Convert to upper case.
	 * @param  string  UTF-8 encoding
	 * @return string
	 */
	private static function upper($s)
	{
		return mb_strtoupper($s, 'UTF-8');
	}

}
