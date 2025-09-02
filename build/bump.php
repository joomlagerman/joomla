<?php
/**
 * Script to bump version, update metadata, and change copyright dates.
 *
 * Usage: php build/bump.php -v <version> -l <languagepackversion> -d <release date>
 *
 * Examples:
 * - php build/bump.php -v 5.2.4 -l 1
 * - php build/bump.php -v 5.2.4-rc1 -l 1 -d "2025-02-11 18:00"
 *
 * @package    Joomla.Language
 * @copyright  (C) 2021 - 2025 J!German <https://www.jgerman.de>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

const DATE_FORMAT = 'Y-m-d H:i';
const PHP_TAB = "\t";

// Make sure we use the correct language and timezone.
setlocale(LC_ALL, 'en_GB');
date_default_timezone_set('Europe/London');

// Make sure file and folder permissions are set correctly.
umask(022);

// File paths
$languageXmlFiles = [
	'/administrator/language/de-DE/install.xml',
	'/administrator/language/de-DE/langmetadata.xml',
	'/api/language/de-DE/install.xml',
	'/api/language/de-DE/langmetadata.xml',
	'/language/de-DE/install.xml',
	'/language/de-DE/langmetadata.xml',
];
$installerXmlFile = '/installation/language/de-DE/langmetadata.xml';
$languagePackXmlFile = '/pkg_de-DE.xml';
$languagePackSqlFile = '/installation/sql/mysql/localise.sql';

// Exclusion rules
$directoryLoopExcludeDirectories = ['/.git', '/build/tmp/'];
$directoryLoopExcludeFiles = [];

// Function to display usage
/**
 * Prints usage instructions for the bump script.
 *
 * @param string $command The command name (usually $argv[0]).
 * @return void
 */
function usage($command) {
	echo PHP_EOL;
	echo 'Usage: php ' . $command . ' [options]' . PHP_EOL;
	echo PHP_TAB . '[options]:' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '-v <version>: Version (e.g., 5.2.4, 5.2.4-rc1)' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '-l <languagepackversion>: Language pack version (e.g., 1, 2)' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '-d <release date>: Release date in ISO 8601 format [optional] (e.g., "2025-02-11 18:00")' . PHP_EOL;
	echo PHP_EOL;
}

/**
 * Validates and parses a date string.
 *
 * @param string $dateString Date string in 'Y-m-d H:i' format or 'now'.
 * @return DateTime Returns DateTime object if valid, otherwise exits script.
 */
function validateDate($dateString) {
	if ($dateString === 'now') {
		return new DateTime('now');
	}

	$date = DateTime::createFromFormat(DATE_FORMAT, $dateString);
	if (!$date || $date->format(DATE_FORMAT) !== $dateString) {
		die("Error: Invalid date format. Please use 'Y-m-d H:i'.");
	}

	return $date;
}

/**
 * Determines the development status from the version string.
 *
 * @param array $versionParts Array of version string parts (split by '-').
 * @return string Returns the development status (Stable, Development, Alpha, Beta, Release Candidate).
 */
function determineDevStatus($versionParts) {
	$devStatus = 'Stable';
	if (isset($versionParts[1])) {
		if (preg_match('#^dev#', $versionParts[1])) {
			$devStatus = 'Development';
		} elseif (preg_match('#^alpha#', $versionParts[1])) {
			$devStatus = 'Alpha';
		} elseif (preg_match('#^beta#', $versionParts[1])) {
			$devStatus = 'Beta';
		} elseif (preg_match('#^rc#', $versionParts[1])) {
			$devStatus = 'Release Candidate';
		}
	}
	return $devStatus;
}

/**
 * Updates version and creation date in language XML files.
 *
 * @param string $rootPath Root path of the repository.
 * @param array $files Array of relative file paths to update.
 * @param array $version Version information array.
 * @return void
 */
function updateLanguageXmlFiles($rootPath, $files, $version) {
	foreach ($files as $file) {
		$filePath = $rootPath . $file;
		if (file_exists($filePath)) {
			$contents = file_get_contents($filePath);
			$contents = preg_replace('#<version>[^<]*</version>#', '<version>' . $version['full'] . '</version>', $contents);
			$contents = preg_replace('#<creationDate>[^<]*</creationDate>#', '<creationDate>' . $version['credate'] . '</creationDate>', $contents);
			$contents = preg_replace('#<span class=\"jgerman-version\">(.*)<\/span>#', '<span class="jgerman-version">' . $version['main'] . '</span>', $contents);
			file_put_contents($filePath, $contents);
			echo "Updated: $filePath" . PHP_EOL;
		} else {
			echo "Skipped: File not found - $filePath" . PHP_EOL;
		}
	}
}

/**
 * Updates version and creation date in the installer XML file.
 *
 * @param string $rootPath Root path of the repository.
 * @param string $file Relative file path to update.
 * @param array $version Version information array.
 * @return void
 */
function updateInstallerXmlFile($rootPath, $file, $version) {
	$filePath = $rootPath . $file;
	if (file_exists($filePath)) {
		$contents = file_get_contents($filePath);
		$contents = preg_replace('#<version>[^<]*</version>#', '<version>' . $version['install_version'] . '</version>', $contents);
		$contents = preg_replace('#<creationDate>[^<]*</creationDate>#', '<creationDate>' . $version['install_credate'] . '</creationDate>', $contents);
		$contents = preg_replace('#<span class=\"jgerman-version\">(.*)<\/span>#', '<span class="jgerman-version">' . $version['main'] . '</span>', $contents);
		file_put_contents($filePath, $contents);
		echo "Updated: $filePath" . PHP_EOL;
	} else {
		echo "Skipped: File not found - $filePath" . PHP_EOL;
	}
}

/**
 * Updates version, creation date, and description in the language pack XML file.
 *
 * @param string $rootPath Root path of the repository.
 * @param string $file Relative file path to update.
 * @param array $version Version information array.
 * @return void
 */
function updateLanguagePackXmlFile($rootPath, $file, $version) {
	$filePath = $rootPath . $file;
	if (file_exists($filePath)) {
		$contents = file_get_contents($filePath);
		$contents = preg_replace('#<version>[^<]*</version>#', '<version>' . $version['full'] . '</version>', $contents);
		$contents = preg_replace('#<creationDate>[^<]*</creationDate>#', '<creationDate>' . $version['credate'] . '</creationDate>', $contents);
		$contents = preg_replace(
			'#<h2>(.*)<\/h2>#',
			'<h2>Deutsches Sprachpaket (Version: ' . $version['full'] . ' vom ' . $version['credate_de'] . ') für Joomla! ' . $version['main'] . ' von <a title="J!German" href="https://www.jgerman.de" target="_blank" rel="noopener noreferrer">J!German</a></h2>',
			$contents
		);
		file_put_contents($filePath, $contents);
		echo "Updated: $filePath" . PHP_EOL;
	} else {
		echo "Skipped: File not found - $filePath" . PHP_EOL;
	}
}

/**
 * Updates version and creation date in the language pack SQL file.
 *
 * @param string $rootPath Root path of the repository.
 * @param string $file Relative file path to update.
 * @param array $version Version information array.
 * @return void
 */
function updateLanguagePackSqlFile($rootPath, $file, $version) {
	$filePath = $rootPath . $file;
	if (file_exists($filePath)) {
		$contents = file_get_contents($filePath);
		$contents = preg_replace('#"version":"[^"]*"#', '"version":"' . $version['full'] . '"', $contents);
		$contents = preg_replace('#"creationDate":"[^"]*"#', '"creationDate":"' . $version['credate'] . '"', $contents);
		file_put_contents($filePath, $contents);
		echo "Updated: $filePath" . PHP_EOL;
	} else {
		echo "Skipped: File not found - $filePath" . PHP_EOL;
	}
}

/**
 * Updates copyright year in all files except excluded ones.
 *
 * @param string $rootPath Root path of the repository.
 * @param string $year Year to set in copyright.
 * @param array $excludeDirectories Array of directory paths to exclude.
 * @param array $excludeFiles Array of file paths to exclude.
 * @return void
 */
function changeCopyrightDate($rootPath, $year, $excludeDirectories, $excludeFiles) {
	$changedFiles = 0;
	$directory = new RecursiveDirectoryIterator($rootPath);
	$iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);

	foreach ($iterator as $file) {
		if ($file->isFile()) {
			$filePath = $file->getPathname();
			$relativePath = str_replace($rootPath, '', $filePath);

			// Skip excluded files and directories
			if (preg_match('#\.(png|jpeg|jpg|gif|bmp|ico|webp|svg|woff|woff2|ttf|eot)$#', $filePath) || in_array($relativePath, $excludeFiles)) {
				continue;
			}

			$skip = false;
			foreach ($excludeDirectories as $excludeDir) {
				if (strpos($relativePath, $excludeDir) === 0) {
					$skip = true;
					break;
				}
			}

			if ($skip) {
				continue;
			}

			// Load file contents and replace copyright year
			$contents = file_get_contents($filePath);
			$updated = false;

			if (preg_match('#2008\s+-\s+[0-9]{4}\s+J\!German#', $contents)) {
				$contents = preg_replace('#2008\s+-\s+[0-9]{4}\s+J\!German#', '2008 - ' . $year . ' J!German', $contents);
				$updated = true;
			}

			if ($updated) {
				file_put_contents($filePath, $contents);
				echo "Copyright updated: $filePath" . PHP_EOL;
				$changedFiles++;
			}
		}
	}

	echo "Copyright updated in $changedFiles files." . PHP_EOL;
}

$opts = getopt("v:l:d:");
if (empty($opts['v']) || empty($opts['l'])) {
	usage($argv[0]);
	die();
}

$versionParts = explode('-', $opts['v']);
if (!preg_match('#^[0-9]+\.[0-9]+\.[0-9]+$#', $versionParts[0])) {
	usage($argv[0]);
	die();
}

$languagePackVersion = (int)$opts['l'];
if (!is_int($languagePackVersion)) {
	usage($argv[0]);
	die();
}

$date = validateDate($opts['d'] ?? 'now');
$devStatus = determineDevStatus($versionParts);
$versionSubParts = explode('.', $versionParts[0]);
$year = $date->format('Y');

$version = [
	'main'            => "{$versionSubParts[0]}.{$versionSubParts[1]}",
	'release'         => $versionSubParts[0] . '.' . $versionSubParts[1] . '.' . $versionSubParts[2] . 'v' . $languagePackVersion,
	'full'            => "{$opts['v']}.{$languagePackVersion}",
	'dev_status'      => $devStatus,
	'reldate'         => $date->format('j-F-Y'),
	'reltime'         => $date->format('H:i'),
	'reltz'           => 'GMT',
	'credate'         => $date->format('Y-m-d'),
	'credate_de'      => $date->format('d.m.Y'),
	'install_credate' => $date->format('Y-m'),
	'install_version' => "{$versionSubParts[0]}.{$versionSubParts[1]}.{$versionSubParts[2]}",
];

$rootPath = dirname(__DIR__);

changeCopyrightDate($rootPath, $year, $directoryLoopExcludeDirectories, $directoryLoopExcludeFiles);
updateLanguageXmlFiles($rootPath, $languageXmlFiles, $version);
updateInstallerXmlFile($rootPath, $installerXmlFile, $version);
updateLanguagePackXmlFile($rootPath, $languagePackXmlFile, $version);
updateLanguagePackSqlFile($rootPath, $languagePackSqlFile, $version);

echo PHP_EOL . "Version information:" . PHP_EOL;
foreach ($version as $key => $value) {
	echo "- " . ucfirst($key) . ": " . $value . PHP_EOL;
}
echo PHP_EOL . "Version bump complete!" . PHP_EOL;
