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


use Nette\Utils\Html;
use Nette\Forms\Helpers as Orig;


/**
 * Forms helpers.
 *
 * @author     Martin Takáč
 */
class Helpers extends Orig
{


	/**
	 * @return Nette\Utils\Html
	 */
	public static function createSelectBox(array $items, array $optionAttrs = NULL)
	{
		list($optionAttrs, $optionTag) = self::prepareAttrs($optionAttrs, 'option');
		$option = Html::el();
		$res = $tmp = '';
		foreach ($items as $group => $subitems) {
			if (is_array($subitems)) {
				$res .= Html::el('optgroup')->label($group)->startTag();
				$tmp = '</optgroup>';
			}
			elseif ($subitems instanceof OptGroupItem) {
				$attribs = $subitems->attribs;
				$attribs['label'] = $subitems->label;
				$res .= Html::el('optgroup', $attribs)->startTag();
				$tmp = '</optgroup>';
				$subitems = $subitems->items;
			}
			else {
				$subitems = array($group => $subitems);
			}

			foreach ($subitems as $value => $caption) {
				$option->value = $value;
				foreach ($optionAttrs as $k => $v) {
					$option->attrs[$k] = isset($v[$value]) ? $v[$value] : NULL;
				}

				if ($caption instanceof OptionItem) {
					$option->addAttributes($caption->attribs);
					$caption = $caption->name;
				}

				if ($caption instanceof Html) {
					$caption = clone $caption;
					$res .= $caption->setName('option')->addAttributes($option->attrs);
				}
				else {
					$res .= $optionTag . $option->attributes() . '>'
						. htmlspecialchars($caption)
						. '</option>';
				}
			}
			$res .= $tmp;
			$tmp = '';
		}
		return Html::el('select')->setHtml($res);
	}


	private static function prepareAttrs($attrs, $name)
	{
		$dynamic = array();
		foreach ((array) $attrs as $k => $v) {
			$p = str_split($k, strlen($k) - 1);
			if ($p[1] === '?' || $p[1] === ':') {
				unset($attrs[$k], $attrs[$p[0]]);
				if ($p[1] === '?') {
					$dynamic[$p[0]] = array_fill_keys((array) $v, TRUE);
				} elseif (is_array($v) && $v) {
					$dynamic[$p[0]] = $v;
				} else {
					$attrs[$p[0]] = $v;
				}
			}
		}
		return array($dynamic, '<' . $name . Html::el(NULL, $attrs)->attributes());
	}

}
