<?php

use Akeeba\Replace\Database\Driver;
use Akeeba\Replace\Engine\Core\Configuration;
use Akeeba\Replace\Engine\Core\Helper\MemoryInfo;
use Akeeba\Replace\Engine\Core\Part\Database;
use Akeeba\Replace\Logger\FileLogger;
use Akeeba\Replace\Logger\LoggerInterface;
use Akeeba\Replace\Timer\Timer;
use Akeeba\Replace\Writer\NullWriter;

require_once 'src/lib/Autoloader/Autoloader.php';

define('DB_HOST', 'localhost');
define('DB_NAME', 'client');
define('DB_USER', 'client');
define('DB_PASS', 'client');
define('BATCH_SIZE', 1000);
define('RUN_FOR_REAL', true);
define('MAX_EXEC_TIME', 1800);
define('RUNTIME_BIAS', 75);
define('EXCLUDE_TABLES', []);
define('EXCLUDE_ROWS', []);
define('DB_REPLACEMENTS', [
	'/var/www/vhosts/example.com/httpdocs'      => '/var/www/restored',
	'https://example.com'                       => 'https://restored.local.web',
	'\/var\/www\/vhosts\/example.com\/httpdocs' => '\/var\/www\/restored',
	'https:\/\/example.com'                     => 'https:\/\/restored.local.web',
]);
define('AKEEBA_REPLACE_MAXIMUM_COLUMN_SIZE', 1048576);

class CliLogger extends FileLogger
{
	/** @noinspection PhpMissingParentConstructorInspection */
	public function __construct()
	{
		// No op.
	}

	public function reset()
	{
		// No op.
	}

	protected function writeToLog($severity, $message)
	{
		echo $this->formatMessage($severity, $message) . "\n";
	}
}

try
{
	$db = Driver::getInstance([
		'driver'   => 'pdomysql',
		'database' => DB_NAME,
		'host'     => DB_HOST,
		'user'     => DB_USER,
		'password' => DB_PASS,
	]);
}
catch (Throwable $e)
{
	echo $e->getMessage();
	die;
}

$config     = new Configuration([
	'outputSQLFile'      => sprintf('replacements-%s.sql', DB_NAME),
	'backupSQLFile'      => sprintf('backup-%s.sql', DB_NAME),
	'logFile'            => sprintf('replacements-%s.log', DB_NAME),
	'liveMode'           => RUN_FOR_REAL,
	'allTables'          => true,
	'maxBatchSize'       => BATCH_SIZE,
	'excludeTables'      => EXCLUDE_TABLES,
	'excludeRows'        => EXCLUDE_ROWS,
	'regularExpressions' => false,
	'replacements'       => DB_REPLACEMENTS,
	'databaseCollation'  => '',
	'tableCollation'     => '',
	'description'        => 'ANGIE replacing data in your WordPress site',
]);
$timer      = new Timer(MAX_EXEC_TIME, RUNTIME_BIAS);
$logger     = new CliLogger();
$output     = new NullWriter('/tmp/fake_out.sql');
$backup     = new NullWriter('/tmp/fake_bak.sql');
$memoryInfo = new MemoryInfo();

$logger->setMinimumSeverity(LoggerInterface::SEVERITY_DEBUG);

$engine = new Database($timer, $db, $logger, $output, $backup, $config, $memoryInfo);
$error  = null;
$start  = microtime(true);

while (true)
{
	$timer->resetTime();
	$db->connect();

	$status = $engine->tick();

	// Are we done already?
	if ($status->isDone())
	{
		break;
	}

	// Check for an error
	$error = $status->getError();

	if (!is_object($error) || !($error instanceof ErrorException))
	{
		$error = null;

		continue;
	}

	// We hit an error
	break;
}

if ($status->isDone())
{
	$logger->debug('All done.');
}
elseif (!is_null($error))
{
	$logger->error($error->getMessage());
	$logger->debug($error->getTraceAsString());
	$logger->debug('Replacement engine died with an error.');
}

$end = microtime(true);
echo sprintf("\n\nTotal time: %0.2f seconds", $end - $start);