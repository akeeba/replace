<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Detection;

/**
 * Magic Loader of the WordPress configuration. It tries to go through the wp-config.php file without actually loading
 * it, therefore bypassing any problems which might arise in the process.
 */
class WordPress implements DetectionInterface
{
	/**
	 * The site root path the object was created with
	 *
	 * @var  string
	 */
	protected $path = null;

	/**
	 * The name of the script we are detecting
	 *
	 * @var  string
	 */
	protected $scriptName = 'wordpress';

	/**
	 * The name of the configuration file I am going to be reading, defaults to wp-config.php
	 *
	 * @var   string
	 */
	protected $configFile = 'wp-config.php';

	/**
	 * Creates a new oracle objects
	 *
	 * IMPORTANT: The $configFile parameter is used for testing. You should not need this in production.
	 *
	 * @param   string  $path        The directory path to scan
	 * @paream  string  $configFile  The name of the configuration file, default wp-config.php
	 *
	 * @codeCoverageIgnore
	 */
	public function __construct($path, $configFile = 'wp-config.php')
	{
		$this->path = $path;

		if (!empty($configFile))
		{
			$this->configFile = $configFile;
		}
	}

	/**
	 * Does this class recognises the CMS type as Wordpress?
	 *
	 * @return  boolean
	 */
	public function isRecognised()
	{
		if (!@file_exists($this->path . '/' . $this->configFile) && !@file_exists($this->path . '/../' . $this->configFile))
		{
			return false;
		}

		if (!@file_exists($this->path . '/wp-login.php'))
		{
			return false;
		}

		if (!@file_exists($this->path . '/xmlrpc.php'))
		{
			return false;
		}

		if (!@is_dir($this->path . '/wp-admin'))
		{
			return false;
		}

		return true;
	}

	/**
	 * Return the name of the CMS / script
	 *
	 * @return  string
	 *
	 * @codeCoverageIgnore
	 */
	public function getName()
	{
		return $this->scriptName;
	}

	/**
	 * Return the database connection information for this CMS / script
	 *
	 * @param   bool  $allowTokenizer  Allow using the (faster, better) PHP tokenizer extension?
	 *
	 * @return  array
	 */
	public function getDbInformation($allowTokenizer = true)
	{
		$ret = array(
			'driver'	=> 'mysqli',
			'host'		=> '',
			'port'		=> '',
			'username'	=> '',
			'password'	=> '',
			'name'		=> '',
			'prefix'	=> '',
			'charset'	=> '',
			'collate'	=> '',
		);

		$filePath = $this->path . '/' . $this->configFile;

		if (!@file_exists($filePath))
		{
			$filePath = $this->path . '/../' . $this->configFile;
		}

		if (!@file_exists($filePath))
		{
			return $ret;
		}

		$hasTokenizer = $allowTokenizer && function_exists('token_get_all');
		$fileContents = file_get_contents($filePath);

		if ($hasTokenizer)
		{
			$newValues = $this->parseWithTokenizer($fileContents);
		}
		else
		{
			$newValues = $this->parseWithoutTokenizer($fileContents);
		}

		return array_merge($ret, $newValues);
	}

	/**
	 * Return extra databases required by the CMS / script (ie Drupal multi-site)
	 *
	 * @return array
	 *
	 * @codeCoverageIgnore
	 */
	public function getExtraDb()
	{
		return array();
	}

	/**
	 * Parse a PHP file line with a define statement and return the constant name and its value
	 *
	 * @param   string  $line  The line to parse
	 *
	 * @return  array  array($key, $value)
	 */
	protected function parseDefine($line)
	{
		$pattern = '#define\s*\(\s*(["\'][A-Z_]*["\'])\s*,\s*(["\'].*["\'])\s*\)\s*;#u';
		$numMatches = preg_match($pattern, $line, $matches);

		if ($numMatches < 1)
		{
			return array('', '');
		}

		$key = trim($matches[1], '"\'');
		$value = $matches[2];

		$value = $this->parseStringDefinition($value);

		if (is_null($value))
		{
			return array('', '');
		}

		return array($key, $value);
	}

	/**
	 * Parses a string definition, surrounded by single or double quotes, removing any comments which may be left tucked
	 * to its end, reducing escaped characters to their unescaped equivalent and returning the clean string.
	 *
	 * @param   string  $value
	 *
	 * @return  null|string  Null if we can't parse $value as a string.
	 */
	protected function parseStringDefinition($value)
	{
		// At this point the value may be in the form 'foobar');#comment'gargh" if the original line was something like
		// define('DB_NAME', 'foobar');#comment'gargh");

		$quote = $value[0];

		// The string ends in a different quote character. Backtrack to the matching quote.
		if (substr($value, -1) != $quote)
		{
			$lastQuote = strrpos($value, $quote);

			// WTF?!
			if ($lastQuote <= 1)
			{
				return null;
			}

			$value = substr($value, 0, $lastQuote + 1);
		}

		// At this point the value may be cleared but still in the form 'foobar');#comment'
		// We need to parse the string like PHP would. First, let's trim the quotes
		$value = trim($value, $quote);

		$pos = 0;

		while ($pos !== false)
		{
			$pos = strpos($value, $quote, $pos);

			if ($pos === false)
			{
				break;
			}

			if (substr($value, $pos - 1, 1) == '\\')
			{
				$pos++;

				continue;
			}

			$value = substr($value, 0, $pos);
		}

		// Finally, reduce the escaped characters.

		if ($quote == "'")
		{
			// Single quoted strings only escape single quotes and backspaces
			$value = str_replace(array("\\'", "\\\\",), array("'", "\\"), $value);
		}
		else
		{
			// Double quoted strings just need stripslashes.
			$value = stripslashes($value);
		}

		return $value;
	}

	/**
	 * Parse the wp-config.php file using the PHP tokenizer extension. We use the tokenizer to remove all comments, then
	 * our regular code to parse the resulting file. Profit!
	 *
	 * @param   string $fileContents The contents of the file
	 *
	 * @return  array
	 */
	protected function parseWithTokenizer($fileContents)
	{
		$tokens = token_get_all($fileContents);

		$commentTokens = [T_COMMENT];

		if (defined('T_DOC_COMMENT'))
		{
			$commentTokens[] = T_DOC_COMMENT;
		}

		if (defined('T_ML_COMMENT'))
		{
			$commentTokens[] = T_ML_COMMENT;
		}

		$newStr  = '';

		foreach ($tokens as $token)
		{
			if (is_array($token))
			{
				if (in_array($token[0], $commentTokens))
				{
					/**
					 * If the comment ended in a newline we need to output the newline. Otherwise we will have
					 * run-together lines which won't be parsed correctly by parseWithoutTokenizer.
					 */
					if (substr($token[1], -1) == "\n")
					{
						$newStr .= "\n";
					}

					continue;
				}

				$token = $token[1];
			}

			$newStr .= $token;
		}

		return $this->parseWithoutTokenizer($newStr);
	}

	/**
	 * Parse the wp-config.php file without using the PHP tokenizer extension
	 *
	 * @param   string $fileContents The contents of the wp-config.php file
	 *
	 * @return  array
	 */
	protected function parseWithoutTokenizer($fileContents)
	{
		$fileContents = explode("\n", $fileContents);
		$fileContents = array_map('trim', $fileContents);
		$ret          = [];

		foreach ($fileContents as $line)
		{
			$line = trim($line);

			if (strpos($line, 'define') !== false)
			{
				list ($key, $value) = $this->parseDefine($line);

				switch (strtoupper($key))
				{
					case 'DB_NAME':
						$ret['name'] = $value;
						break;

					case 'DB_USER':
						$ret['username'] = $value;
						break;

					case 'DB_PASSWORD':
						$ret['password'] = $value;
						break;

					case 'DB_HOST':
						$ret['host'] = $value;
						break;

					case 'DB_CHARSET':
						$ret['charset'] = $value;
						break;

					case 'DB_COLLATE':
						$ret['collate'] = $value;
						break;

				}
			}
			elseif (strpos($line, '$table_prefix') === 0)
			{
				$parts         = explode('=', $line, 2);
				$prefixData    = trim($parts[1]);
				$ret['prefix'] = $this->parseStringDefinition($prefixData);
			}
		}

		return $ret;
	}
}
