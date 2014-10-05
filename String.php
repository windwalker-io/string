<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\String;

use Windwalker\Utilities\ArrayHelper;

/**
 * The String class.
 * 
 * @since  {DEPLOY_VERSION}
 */
abstract class String
{
	/**
	 * Increment styles.
	 *
	 * @var    array
	 * @since  {DEPLOY_VERSION}
	 */
	protected static $incrementStyles = array(
		'dash' => array(
			'#-(\d+)$#',
			'-%d'
		),
		'default' => array(
			array('#\((\d+)\)$#', '#\(\d+\)$#'),
			array(' (%d)', '(%d)'),
		),
	);

	/**
	 * isEmptyString
	 *
	 * @param string $string
	 *
	 * @return  boolean
	 */
	public static function isEmpty($string)
	{
		if (is_array($string) || is_object($string))
		{
			return empty($string);
		}

		$string = (string) $string;

		return !(boolean) strlen($string);
	}

	/**
	 * isZero
	 *
	 * @param string $string
	 *
	 * @return  boolean
	 */
	public static function isZero($string)
	{
		return $string === '0' || $string === 0;
	}

	/**
	 * Quote a string.
	 *
	 * @param   string $string The string to quote.
	 * @param   string $quote  The quote symbol.
	 *
	 * @return  string Quoted string.
	 */
	public static function quote($string, $quote = "''")
	{
		if (!strlen($quote))
		{
			return $string;
		}

		if (empty($quote[1]))
		{
			$quote[1] = $quote[0];
		}

		return $quote[0] . $string . $quote[1];
	}

	/**
	 * Back quote a string.
	 *
	 * @param   string $string The string to quote.
	 *
	 * @return  string Quoted string.
	 */
	public static function backquote($string)
	{
		return static::quote($string, '``');
	}

	/**
	 * Parse variable and replace it. This method is a simple template engine.
	 *
	 * Example: The {{ foo.bar.yoo }} will be replace to value of `$data['foo']['bar']['yoo']`
	 *
	 * @param   string $string The template to replace.
	 * @param   array  $data   The data to find.
	 * @param   array  $tags   The variable tags.
	 *
	 * @return  string Replaced template.
	 */
	public static function parseVariable($string, $data = array(), $tags = array('{{', '}}'))
	{
		return preg_replace_callback(
			'/\{\{\s*(.+?)\s*\}\}/',
			function($match) use ($data)
			{
				$return = ArrayHelper::getByPath($data, $match[1]);

				if (is_array($return) || is_object($return))
				{
					return print_r($return, 1);
				}
				else
				{
					return $return;
				}
			},
			$string
		);
	}

	/**
	 * Increments a trailing number in a string.
	 *
	 * Used to easily create distinct labels when copying objects. The method has the following styles:
	 *
	 * default: "Label" becomes "Label (2)"
	 * dash:    "Label" becomes "Label-2"
	 *
	 * @param   string   $string  The source string.
	 * @param   string   $style   The the style (default|dash).
	 * @param   integer  $n       If supplied, this number is used for the copy, otherwise it is the 'next' number.
	 *
	 * @return  string  The incremented string.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public static function increment($string, $style = 'default', $n = 0)
	{
		$styleSpec = isset(self::$incrementStyles[$style]) ? self::$incrementStyles[$style] : self::$incrementStyles['default'];

		// Regular expression search and replace patterns.
		if (is_array($styleSpec[0]))
		{
			$rxSearch = $styleSpec[0][0];
			$rxReplace = $styleSpec[0][1];
		}
		else
		{
			$rxSearch = $rxReplace = $styleSpec[0];
		}

		// New and old (existing) sprintf formats.
		if (is_array($styleSpec[1]))
		{
			$newFormat = $styleSpec[1][0];
			$oldFormat = $styleSpec[1][1];
		}
		else
		{
			$newFormat = $oldFormat = $styleSpec[1];
		}

		// Check if we are incrementing an existing pattern, or appending a new one.
		if (preg_match($rxSearch, $string, $matches))
		{
			$n = empty($n) ? ($matches[1] + 1) : $n;
			$string = preg_replace($rxReplace, sprintf($oldFormat, $n), $string);
		}
		else
		{
			$n = empty($n) ? 2 : $n;
			$string .= sprintf($newFormat, $n);
		}

		return $string;
	}
}
