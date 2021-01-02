<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

define('IS_WINDOWS', substr(PHP_OS, 0, 3) == 'WIN');

require_once 'phing/Task.php';

class WpreadmeTask extends ExecTask
{
    function getChangelog()
    {
        $filePath = __DIR__ . '/../../CHANGELOG';
        $ret = '';
        $lines = file($filePath);

        foreach ($lines as $line)
        {
            $line = trim($line);

            if (substr($line, 0, 5) == '<?php')
            {
                continue;
            }
            elseif (empty($line))
            {
                $ret .= "\n";
            }
            elseif (substr($line, 0, 27) == 'Akeeba Replace')
            {
                $line = substr($line, 28);
                $lineParts = explode(' ',$line);
                $ret .= "= {$lineParts[0]} =\n";
            }
            elseif (substr($line, 0, 2) == '==')
            {
                continue;
            }
            else
            {
                $ret .= '* ' . substr($line, 2) . "\n";
            }
        }

        return $ret;
    }

	/**
	 * Main entry point for task
	 *
	 * @return bool
	 */
	public function main()
	{
        $filePath = __DIR__ . '/../templates/readme.txt';
        $outFile = __DIR__ . '/../../release/readme.txt';

        $fileContents = file_get_contents($filePath);

        $changelog = $this->getChangelog();
        $fileContents = str_replace('[CHANGELOG]', $changelog, $fileContents);

        file_put_contents($outFile, $fileContents);
	}
}