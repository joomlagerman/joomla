<?php
/**
 * Script used to make a version bump
 * Updates all versions xmls and version.php
 *
 * Usage: php build/bump.php -v <version> -l <languagepackversion>
 *
 * Examples:
 * - php build/bump.php -v 4.1.0 -l 1
 *
 * @package    Joomla.Language
 * @copyright  (C) 2022 J!German <https://www.jgerman.de>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// This script is largly based on the Joomla CMS Bump Script
// https://github.com/joomla/joomla-cms/blob/4.0-dev/build/bump.php

// Functions
function usage($command)
{
	echo PHP_EOL;
	echo 'Usage: php ' . $command . ' [options]' . PHP_EOL;
	echo PHP_TAB . '[options]:' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '-v <version>:' . PHP_TAB . 'Version (ex: 4.1.0, 4.1.0-rc1)' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '-l <languagepackversion>:' . PHP_TAB . 'Languagepackversion (ex: 1, 2)' . PHP_EOL;
	echo PHP_EOL;
}

// Constants.
const PHP_TAB = "\t";

// File paths.
$languageXmlFiles = array(
	'/administrator/language/de-DE/install.xml',
	'/administrator/language/de-DE/langmetadata.xml',
	'/api/language/de-DE/install.xml',
	'/api/language/de-DE/langmetadata.xml',
	'/language/de-DE/install.xml',
	'/language/de-DE/langmetadata.xml',
);

$installerXmlFile = '/installation/language/de-DE/langmetadata.xml';

$languagePackXmlFile = '/pkg_de-DE.xml';

$languagePackSqlFile = '/installation/sql/mysql/localise.sql';

/*
 * Change copyright date exclusions.
 * Some systems may try to scan the .git directory, exclude it.
 * Also exclude build resources such as the packaging space or the API documentation build
 * as well as external libraries.
 */
$directoryLoopExcludeDirectories = array(
	'/.git',
	'/build/tmp/',
);

$directoryLoopExcludeFiles = array();

// Check arguments (exit if incorrect cli arguments).
$opts = getopt("v:l:");

if (empty($opts['v']))
{
	usage($argv[0]);
	die();
}

if (empty($opts['l']))
{
	usage($argv[0]);
	die();
}

// Check version string (exit if not correct).
$versionParts = explode('-', $opts['v']);
$languagePackVersion = (integer) $opts['l'];

if (!preg_match('#^[0-9]+\.[0-9]+\.[0-9]+$#', $versionParts[0]))
{
	usage($argv[0]);
	die();
}

if (isset($versionParts[1]) && !preg_match('#(dev|alpha|beta|rc)[0-9]*#', $versionParts[1]))
{
	usage($argv[0]);
	die();
}

if (isset($versionParts[2]) && $versionParts[2] !== 'dev')
{
	usage($argv[0]);
	die();
}

if (!is_integer($languagePackVersion))
{
	usage($argv[0]);
	die();
}

// Make sure we use the correct language and timezone.
setlocale(LC_ALL, 'en_GB');
date_default_timezone_set('Europe/London');

// Make sure file and folder permissions are set correctly.
umask(022);

// Get version dev status.
$dev_status = 'Stable';

if (!isset($versionParts[1]))
{
	$versionParts[1] = '';
}
else
{
	if (preg_match('#^dev#', $versionParts[1]))
	{
		$dev_status = 'Development';
	}
	elseif (preg_match('#^alpha#', $versionParts[1]))
	{
		$dev_status = 'Alpha';
	}
	elseif (preg_match('#^beta#', $versionParts[1]))
	{
		$dev_status = 'Beta';
	}
	elseif (preg_match('#^rc#', $versionParts[1]))
	{
		$dev_status = 'Release Candidate';
	}
}

if (!isset($versionParts[2]))
{
	$versionParts[2] = '';
}
else
{
	$dev_status = 'Development';
}

// Set version properties.
$versionSubParts = explode('.', $versionParts[0]);

$version = array(
	'main'            => $versionSubParts[0] . '.' . $versionSubParts[1],
	'major'           => $versionSubParts[0],
	'minor'           => $versionSubParts[1],
	'patch'           => $versionSubParts[2],
	'extra'           => (!empty($versionParts[1]) ? $versionParts[1] : '') . (!empty($versionParts[2]) ? (!empty($versionParts[1]) ? '-' : '') . $versionParts[2] : ''),
	'release'         => $versionSubParts[0] . '.' . $versionSubParts[1] . '.' . $versionSubParts[2] . 'v' . $languagePackVersion,
	'full'            => $versionSubParts[0] . '.' . $versionSubParts[1] . '.' . $versionSubParts[2] . (!empty($versionParts[1]) ? '-' . $versionParts[1] : '') . (!empty($versionParts[2]) ? '-' . $versionParts[2] : '') . '.' . $languagePackVersion,
	'dev_devel'       => $versionSubParts[2] . (!empty($versionParts[1]) ? '-' . $versionParts[1] : '') . (!empty($versionParts[2]) ? '-' . $versionParts[2] : ''),
	'dev_status'      => $dev_status,
	'reldate'         => date('j-F-Y'),
	'reltime'         => date('H:i'),
	'reltz'           => 'GMT',
	'credate'         => date('Y-m-d'),
	'install_credate' => date('F Y'),
	'install_version' => $versionSubParts[0] . '.' . $versionSubParts[1] . '.' . $versionSubParts[2],
);

// Prints version information.
echo PHP_EOL;
echo 'Version data:' . PHP_EOL;
echo '- Main:' . PHP_TAB . PHP_TAB . PHP_TAB . PHP_TAB . $version['main'] . PHP_EOL;
echo '- Release:' . PHP_TAB . PHP_TAB . PHP_TAB . $version['release'] . PHP_EOL;
echo '- Full:' . PHP_TAB . PHP_TAB . PHP_TAB . PHP_TAB . $version['full'] .  PHP_EOL;
echo '- Dev Level:' . PHP_TAB . PHP_TAB . PHP_TAB . $version['dev_devel'] . PHP_EOL;
echo '- Dev Status:' . PHP_TAB . PHP_TAB . PHP_TAB . $version['dev_status'] . PHP_EOL;
echo '- Release date:' . PHP_TAB . PHP_TAB . PHP_TAB . $version['reldate'] . PHP_EOL;
echo '- Release time:' . PHP_TAB . PHP_TAB . PHP_TAB . $version['reltime'] . PHP_EOL;
echo '- Release timezone:' . PHP_TAB . PHP_TAB . $version['reltz'] . PHP_EOL;
echo '- Creation date:' . PHP_TAB . PHP_TAB . $version['credate'] . PHP_EOL;
echo '- Installer: creation date:' . PHP_TAB . $version['install_credate'] . PHP_EOL;
echo '- Installer: version:' . PHP_TAB . PHP_TAB . $version['install_version'] . PHP_EOL;


echo PHP_EOL;

$rootPath = dirname(__DIR__);

// Updates the version and creation date in language xml files.
foreach ($languageXmlFiles as $languageXmlFile)
{
	if (file_exists($rootPath . $languageXmlFile))
	{
		$fileContents = file_get_contents($rootPath . $languageXmlFile);
		$fileContents = preg_replace('#<version>[^<]*</version>#', '<version>' . $version['full'] . '</version>', $fileContents);
		$fileContents = preg_replace('#<creationDate>[^<]*</creationDate>#', '<creationDate>' . $version['credate'] . '</creationDate>', $fileContents);
        $fileContents = preg_replace('#<span class=\"jgerman-version\">(.*)<\/span>#', '<span class="jgerman-version">' . $version['main'] . '</span>', $fileContents);
		file_put_contents($rootPath . $languageXmlFile, $fileContents);
	}
}

// Updates the version and creation date in language installer xml file.
if (file_exists($rootPath . $installerXmlFile))
{
	$fileContents = file_get_contents($rootPath . $installerXmlFile);
	$fileContents = preg_replace('#<version>[^<]*</version>#', '<version>' . $version['install_version'] . '</version>', $fileContents);
	$fileContents = preg_replace('#<creationDate>[^<]*</creationDate>#', '<creationDate>' . $version['install_credate'] . '</creationDate>', $fileContents);
    $fileContents = preg_replace('#<span class=\"jgerman-version\">(.*)<\/span>#', '<span class="jgerman-version">' . $version['main'] . '</span>', $fileContents);
	file_put_contents($rootPath . $installerXmlFile, $fileContents);
}

// Updates the version and creation date in language package xml file.
if (file_exists($rootPath . $languagePackXmlFile))
{
	$fileContents = file_get_contents($rootPath . $languagePackXmlFile);
	$fileContents = preg_replace('#<version>[^<]*</version>#', '<version>' . $version['full'] . '</version>', $fileContents);
	$fileContents = preg_replace('#<creationDate>[^<]*</creationDate>#', '<creationDate>' . $version['credate'] . '</creationDate>', $fileContents);
	$fileContents = preg_replace('#<h2>(.*)<\/h2>#', '<h2>Deutsches Sprachpaket (Version: ' . $version['full'] . ') für Joomla! ' . $version['main'] . ' von <a title="J!German" href="https://www.jgerman.de" target="_blank" rel="noopener noreferrer">J!German</a></h2>', $fileContents);
	file_put_contents($rootPath . $languagePackXmlFile, $fileContents);
}

// Updates the version and creation date in localise.sql file.
if (file_exists($rootPath . $languagePackSqlFile))
{
	$fileContents = file_get_contents($rootPath . $languagePackSqlFile);
	$fileContents = preg_replace('#"version":"[^"]*"#', '"version":"' . $version['full'] . '"', $fileContents);
	$fileContents = preg_replace('#"creationDate":"[^"]*"#', '"creationDate":"' . $version['credate'] . '"', $fileContents);
	file_put_contents($rootPath . $languagePackSqlFile, $fileContents);
}

// Updates the copyright date in core files.
$changedFilesCopyrightDate = 0;
$year                      = date('Y');
$directory                 = new RecursiveDirectoryIterator($rootPath);
$iterator                  = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);

foreach ($iterator as $file)
{
	if ($file->isFile())
	{
		$filePath     = $file->getPathname();
		$relativePath = str_replace($rootPath, '', $filePath);

		// Exclude certain extensions.
		if (preg_match('#\.(png|jpeg|jpg|gif|bmp|ico|webp|svg|woff|woff2|ttf|eot)$#', $filePath))
		{
			continue;
		}

		// Exclude certain files.
		if (in_array($relativePath, $directoryLoopExcludeFiles))
		{
			continue;
		}

		// Exclude certain directories.
		$continue = true;

		foreach ($directoryLoopExcludeDirectories as $excludeDirectory)
		{
			if (preg_match('#^' . preg_quote($excludeDirectory) . '#', $relativePath))
			{
				$continue = false;
				break;
			}
		}

		if ($continue)
		{
			$changeCopyrightDate = false;

			// Load the file.
			$fileContents = file_get_contents($filePath);

			// Check if need to change the copyright date.
			if (preg_match('#2005\s+-\s+[0-9]{4}\s+Open\s+Source\s+Matters#', $fileContents) && !preg_match('#2005\s+-\s+' . $year . '\s+Open\s+Source\s+Matters#', $fileContents))
			{
				$changeCopyrightDate = true;
				$fileContents = preg_replace('#2005\s+-\s+[0-9]{4}\s+Open\s+Source\s+Matters#', '2005 - ' . $year . ' Open Source Matters', $fileContents);
				$changedFilesCopyrightDate++;
			}

			// Check if need to change the copyright date.
			if (preg_match('#2008\s+-\s+[0-9]{4}\s+J\!German#', $fileContents) && !preg_match('#2008\s+-\s+' . $year . '\s+J\!German#', $fileContents))
			{
				$changeCopyrightDate = true;
				$fileContents = preg_replace('#2008\s+-\s+[0-9]{4}\s+J!German#', '2008 - ' . $year . ' J!German', $fileContents);
				$changedFilesCopyrightDate++;
			}

			// Save the file.
			if ($changeCopyrightDate)
			{
				echo $filePath;
				file_put_contents($filePath, $fileContents);
			}
		}
	}
}

if ($changedFilesCopyrightDate > 0)
{
	echo '- Copyright Date changed in ' . $changedFilesCopyrightDate . ' files.' . PHP_EOL;
	echo PHP_EOL;
}

echo 'Version bump complete!' . PHP_EOL;
