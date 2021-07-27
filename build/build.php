<?php
/**
 * Script used to build Joomla distribution archive packages
 * Builds packages in tmp/packages folder (for example, 'build/tmp/packages')
 *
 * Note: the new package must be tagged in your git repository BEFORE doing this
 * It uses the git tag for the new version, not trunk.
 *
 * This script is designed to be run in CLI on Linux or Mac OS X.
 * Make sure your default umask is 022 to create archives with correct permissions.
 *
 * Steps:
 * 1. Tag new release in the local git repository (for example, "git tag 2.5.1")
 * 2. Set the $version and $release variables for the new version.
 * 3. Run from CLI as: 'php build.php" from build directory.
 * 4. Check the archives in the tmp directory.
 *
 * @package    Joomla.Language
 * @copyright  (C) 2021 J!German <https://www.jgerman.de>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

const PHP_TAB = "\t";

$time = time();

// Set path to git binary (e.g., /usr/local/git/bin/git or /usr/bin/git)
ob_start();
passthru('which git', $systemGit);
$systemGit = trim(ob_get_clean());

// Make sure file and folder permissions are set correctly
umask(022);

// Shortcut the paths to the repository root and build folder
$repo = dirname(__DIR__);
$here = __DIR__;

// Set paths for the build packages
$tmp      = $here . '/tmp';
$fullpath = $tmp . '/' . $time;

// Parse input options
$options = getopt('', ['help', 'remote::']);

$remote       = $options['remote'] ?? false;
$showHelp     = isset($options['help']);

if ($showHelp)
{
	usage($argv[0]);
	exit;
}

// If not given a remote, assume we are looking for the latest local tag
if (!$remote)
{
	chdir($repo);
	$tagVersion = system($systemGit . ' describe --tags `' . $systemGit . ' rev-list --tags --max-count=1`', $tagVersion);
	$remote = 'tags/' . $tagVersion;
	chdir($here);
}

echo "Start build for remote $remote.\n";
echo "Delete old release folder.\n";
system('rm -rf ' . $tmp);
mkdir($tmp);
mkdir($fullpath);

echo "Copy the files from the git repository.\n";
chdir($repo);
system($systemGit . ' archive ' . $remote . ' | tar -x -C ' . $fullpath);
chdir($fullpath);

$versionParts = explode('.', $tagVersion);

$languagePackAndPatchVersion = explode('v', $versionParts[2]);

// Set version information for the build
$version     = $versionParts[0] . '.' . $versionParts[1];
$release     = $languagePackAndPatchVersion[0];
$fullVersion = $versionParts[0] . '.' . $versionParts[1] . '.' . $versionParts[2];

chdir($tmp);
system('mkdir packages');

/*
 * Here we set the files/folders which should not be packaged at any time
 * These paths are from the repository root without the leading slash
 * Because this is a fresh copy from a git tag, local environment files may be ignored
 */
$doNotPackage = array(
	'.git',
	'.github',
	'.gitattributes',
	'.gitignore',
	'.editorconfig',
	'.gitignore',
	'CODE_OF_CONDUCT.md',
	'LICENSE',
	'README.md',
	'build',
);

// Delete the files and folders we exclude from the packages (tests, docs, build, etc.).
echo "Delete folders not included in packages.\n";

foreach ($doNotPackage as $removeFile)
{
	system('rm -rf ' . $time . '/' . $removeFile);
}

echo "Prepare packages.\n";

system('mkdir tmp_packages');
chdir('tmp_packages');

foreach (['de-DE', 'de-AT', 'de-CH', 'de-LI', 'de-LU'] as $languageCode)
{
	system('mkdir ' . $languageCode);
	chdir($languageCode);

	echo "Build package: $languageCode.\n";

	foreach (['full', 'admin', 'site', 'api'] as $folder)
	{
		$tmpLanguagePath = $tmp . '/tmp_packages/' . $languageCode;
		$tmpLanguagePathFolder = $tmp . '/tmp_packages/' . $languageCode . '/' . $folder;
		
		system('mkdir ' . $tmpLanguagePathFolder);

		if ($folder === 'full')
		{
			system('cp "' . $fullpath . '/pkg_de-DE.xml" "' . $tmpLanguagePathFolder . '/pkg_' . $languageCode . '.xml' . '"');
			applyTranslationChanges($languageCode, $folder, $tmpLanguagePathFolder);
		}

		if ($folder === 'admin')
		{
			system('cp -r "' . $fullpath . '/administrator/language/de-DE/"* "' . $tmpLanguagePathFolder . '"');
			applyTranslationChanges($languageCode, $folder, $tmpLanguagePathFolder);
			chdir($tmpLanguagePathFolder);
			system('zip -r ' . $tmpLanguagePath . '/full/admin_' . $languageCode . '.zip * > /dev/null');
		}

		if ($folder === 'site')
		{
			system('cp -r "' . $fullpath . '/language/de-DE/"* "' . $tmpLanguagePathFolder . '"');
			applyTranslationChanges($languageCode, $folder, $tmpLanguagePathFolder);
			chdir($tmpLanguagePathFolder);
			system('zip -r ' . $tmpLanguagePath . '/full/site_' . $languageCode . '.zip * > /dev/null');
		}

		if ($folder === 'api')
		{
			system('cp -r "' . $fullpath . '/api/language/de-DE/"* "' . $tmpLanguagePathFolder . '"');
			applyTranslationChanges($languageCode, $folder, $tmpLanguagePathFolder);
			chdir($tmpLanguagePathFolder);
			system('zip -r ' . $tmpLanguagePath . '/full/api_' . $languageCode . '.zip * > /dev/null');
		}

		chdir('..');
	}

	// Build zip package
	chdir($tmpLanguagePath . '/full');
	system('zip -r ' . $tmpLanguagePath . '/full/full_' . $languageCode . '.zip * > /dev/null');
	system('cp ' . $tmpLanguagePath . '/full/full_' . $languageCode . '.zip ' . $tmp . '/packages/' . $languageCode . '_joomla_lang_full_' . $fullVersion . '.zip');

	chdir('../..');
}

system('rm -rf ' . $tmp . '/tmp_packages/');

echo "Build of version $fullVersion complete!\n";

function usage(string $command)
{
	echo PHP_EOL;
	echo 'Usage: php ' . $command . ' [options]' . PHP_EOL;
	echo PHP_TAB . '[options]:' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--remote=<remote>:' . PHP_TAB . 'The git remote reference to build from (ex: `tags/3.8.6`, `4.0-dev`), defaults to the most recent tag for the repository' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--help:' . PHP_TAB . PHP_TAB . PHP_TAB . 'Show this help output' . PHP_EOL;
	echo PHP_EOL;
}

function applyTranslationChanges(string $languageCode, string $folder, string $tmpLanguagePathLangCode)
{
	if (in_array($folder, ['admin', 'site']))
	{
		if ($languageCode === 'de-AT')
		{
			// langmetadata.xml
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<tag>de-DE</tag>', '<tag>de-AT</tag>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<name>German (Germany)</name>', '<name>German (Germany)</name>" "<name>German (Austria)</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<name>German (DE)</name>', '<name>German (AT)</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<nativeName>Deutsch (Deutschland)</nativeName>', '<nativeName>Deutsch (Österreich)</nativeName>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<locale>de_DE.utf8, de_DE.UTF-8, de_DE, deu_DE, german, german-de, de, deu, germany</locale>', '<locale>de_AT.utf8, de_AT.UTF-8, de_AT, deu_AT, german-at, at, austria, de_DE.utf8, de_DE.UTF-8, de_DE, deu_DE, german, german-de, de, deu, germany</locale>');
			
			// install.xml
			renameStringInFile($tmpLanguagePathLangCode . '/install.xml', '<name>German (DE)</name>', '<name>German (AT)</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/install.xml', 'de-DE', 'de-AT');

			// localise.php
			renameStringInFile($tmpLanguagePathLangCode . '/localise.php', 'de-DE', 'de-AT');
			renameStringInFile($tmpLanguagePathLangCode . '/localise.php', 'De_DELocalise', 'De_ATLocalise');
		}

		if ($languageCode === 'de-CH')
		{
			// Das ß wird heute ausschließlich beim Schreiben in deutscher Sprache sowie im Niederdeutschen verwendet, allerdings nicht in der Schweiz und Liechtenstein.
			// https://de.wikipedia.org/wiki/%C3%9F
			searchAndReplaceStringInAllFiles($tmpLanguagePathLangCode, 'ß', 'ss');
			
			// langmetadata.xml
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<tag>de-DE</tag>', '<tag>de-CH</tag>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<name>German (Germany)</name>', '<name>German (Switzerland)</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<name>German (DE)</name>', '<name>German (CH)</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<nativeName>Deutsch (Deutschland)</nativeName>', '<nativeName>Deutsch (Schweiz)</nativeName>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<locale>de_DE.utf8, de_DE.UTF-8, de_DE, deu_DE, german, german-de, de, deu, germany</locale>', '<locale>de_CH.utf8, de_CH.UTF-8, de_CH, deu_CH, german-ch, ch, switzerland, de_DE.utf8, de_DE.UTF-8, de_DE, deu_DE, german, german-de, de, deu, germany</locale>');
			
			// install.xml
			renameStringInFile($tmpLanguagePathLangCode . '/install.xml', '<name>German (DE)</name>', '<name>German (CH)</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/install.xml', 'de-DE', 'de-CH');

			// localise.php
			renameStringInFile($tmpLanguagePathLangCode . '/localise.php', 'de-DE', 'de-CH');
			renameStringInFile($tmpLanguagePathLangCode . '/localise.php', 'De_DELocalise', 'De_CHLocalise');
		}
		if ($languageCode === 'de-LI')
		{
			// Das ß wird heute ausschließlich beim Schreiben in deutscher Sprache sowie im Niederdeutschen verwendet, allerdings nicht in der Schweiz und Liechtenstein.
			// https://de.wikipedia.org/wiki/%C3%9F
			searchAndReplaceStringInAllFiles($tmpLanguagePathLangCode, 'ß', 'ss');

			// langmetadata.xml
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<tag>de-DE</tag>', '<tag>de-LI</tag>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<name>German (Germany)</name>', '<name>German (Lichtenstein)</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<name>German (DE)</name>', '<name>German (LI)</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<nativeName>Deutsch (Deutschland)</nativeName>', '<nativeName>Deutsch (Lichtenstein)</nativeName>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<locale>de_DE.utf8, de_DE.UTF-8, de_DE, deu_DE, german, german-de, de, deu, germany</locale>', '<locale>de_LI.utf8, de_LI.UTF-8, de_LI, deu_LI, german-li, li, lichtenstein, de_DE.utf8, de_DE.UTF-8, de_DE, deu_DE, german, german-de, de, deu, germany</locale>');
			
			// install.xml
			renameStringInFile($tmpLanguagePathLangCode . '/install.xml', '<name>German (DE)</name>', '<name>German (LI)</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/install.xml', 'de-DE', 'de-LI');

			// localise.php
			renameStringInFile($tmpLanguagePathLangCode . '/localise.php', 'de-DE', 'de-LI');
			renameStringInFile($tmpLanguagePathLangCode . '/localise.php', 'De_DELocalise', 'De_LILocalise');
		}
		if ($languageCode === 'de-LU')
		{
			// langmetadata.xml
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<tag>de-DE</tag>', '<tag>de-LU</tag>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<name>German (Germany)</name>', '<name>German (Luxemburg)</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<name>German (DE)</name>', '<name>German (LU)</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<nativeName>Deutsch (Deutschland)</nativeName>', '<nativeName>Deutsch (Luxemburg)</nativeName>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<locale>de_DE.utf8, de_DE.UTF-8, de_DE, deu_DE, german, german-de, de, deu, germany</locale>', '<locale>de_LU.utf8, de_LU.UTF-8, de_LU, deu_LU, german-lu, lu, luxembourg, de_DE.utf8, de_DE.UTF-8, de_DE, deu_DE, german, german-de, de, deu, germany</locale>');
			
			// install.xml
			renameStringInFile($tmpLanguagePathLangCode . '/install.xml', '<name>German (DE)</name>', '<name>German (LU)</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/install.xml', 'de-DE', 'de-LU');

			// localise.php
			renameStringInFile($tmpLanguagePathLangCode . '/localise.php', 'de-DE', 'de-LU');
			renameStringInFile($tmpLanguagePathLangCode . '/localise.php', 'De_DELocalise', 'De_LULocalise');
		}

	}

	if ($folder === 'api')
	{
		if ($languageCode === 'de-AT')
		{
			// langmetadata.xml
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<tag>de-DE</tag>', '<tag>de-AT</tag>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<name>German (Germany)</name>', '<name>German (Germany)</name>" "<name>German (Austria)</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<name>German (DE)</name>', '<name>German (AT)</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<nativeName>Deutsch (Deutschland)</nativeName>', '<nativeName>Deutsch (Österreich)</nativeName>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<locale>de_DE.utf8, de_DE.UTF-8, de_DE, deu_DE, german, german-de, de, deu, germany</locale>', '<locale>de_AT.utf8, de_AT.UTF-8, de_AT, deu_AT, german-at, at, austria, de_DE.utf8, de_DE.UTF-8, de_DE, deu_DE, german, german-de, de, deu, germany</locale>');
			
			// install.xml
			renameStringInFile($tmpLanguagePathLangCode . '/install.xml', '<name>German (DE)</name>', '<name>German (AT)</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/install.xml', 'de-DE', 'de-AT');
		}
		if ($languageCode === 'de-CH')
		{
			// Das ß wird heute ausschließlich beim Schreiben in deutscher Sprache sowie im Niederdeutschen verwendet, allerdings nicht in der Schweiz und Liechtenstein.
			// https://de.wikipedia.org/wiki/%C3%9F
			searchAndReplaceStringInAllFiles($tmpLanguagePathLangCode, 'ß', 'ss');
			
			// langmetadata.xml
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<tag>de-DE</tag>', '<tag>de-CH</tag>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<name>German (Germany)</name>', '<name>German (Switzerland)</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<name>German (DE)</name>', '<name>German (CH)</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<nativeName>Deutsch (Deutschland)</nativeName>', '<nativeName>Deutsch (Schweiz)</nativeName>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<locale>de_DE.utf8, de_DE.UTF-8, de_DE, deu_DE, german, german-de, de, deu, germany</locale>', '<locale>de_CH.utf8, de_CH.UTF-8, de_CH, deu_CH, german-ch, ch, switzerland, de_DE.utf8, de_DE.UTF-8, de_DE, deu_DE, german, german-de, de, deu, germany</locale>');
			
			// install.xml
			renameStringInFile($tmpLanguagePathLangCode . '/install.xml', '<name>German (DE)</name>', '<name>German (CH)</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/install.xml', 'de-DE', 'de-CH');
		}
		if ($languageCode === 'de-LI')
		{
			// Das ß wird heute ausschließlich beim Schreiben in deutscher Sprache sowie im Niederdeutschen verwendet, allerdings nicht in der Schweiz und Liechtenstein.
			// https://de.wikipedia.org/wiki/%C3%9F
			searchAndReplaceStringInAllFiles($tmpLanguagePathLangCode, 'ß', 'ss');

			// langmetadata.xml
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<tag>de-DE</tag>', '<tag>de-LI</tag>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<name>German (Germany)</name>', '<name>German (Lichtenstein)</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<name>German (DE)</name>', '<name>German (LI)</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<nativeName>Deutsch (Deutschland)</nativeName>', '<nativeName>Deutsch (Lichtenstein)</nativeName>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<locale>de_DE.utf8, de_DE.UTF-8, de_DE, deu_DE, german, german-de, de, deu, germany</locale>', '<locale>de_LI.utf8, de_LI.UTF-8, de_LI, deu_LI, german-li, li, lichtenstein, de_DE.utf8, de_DE.UTF-8, de_DE, deu_DE, german, german-de, de, deu, germany</locale>');
			
			// install.xml
			renameStringInFile($tmpLanguagePathLangCode . '/install.xml', '<name>German (DE)</name>', '<name>German (LI)</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/install.xml', 'de-DE', 'de-LI');
		}
		if ($languageCode === 'de-LU')
		{
			// langmetadata.xml
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<tag>de-DE</tag>', '<tag>de-LU</tag>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<name>German (Germany)</name>', '<name>German (Luxemburg)</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<name>German (DE)</name>', '<name>German (LU)</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<nativeName>Deutsch (Deutschland)</nativeName>', '<nativeName>Deutsch (Luxemburg)</nativeName>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<locale>de_DE.utf8, de_DE.UTF-8, de_DE, deu_DE, german, german-de, de, deu, germany</locale>', '<locale>de_LU.utf8, de_LU.UTF-8, de_LU, deu_LU, german-lu, lu, luxembourg, de_DE.utf8, de_DE.UTF-8, de_DE, deu_DE, german, german-de, de, deu, germany</locale>');
			
			// install.xml
			renameStringInFile($tmpLanguagePathLangCode . '/install.xml', '<name>German (DE)</name>', '<name>German (LU)</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/install.xml', 'de-DE', 'de-LU');
		}
	}

	if ($folder === 'full')
	{
		if ($languageCode === 'de-AT')
		{
			renameStringInFile($tmpLanguagePathLangCode . '/pkg_de-AT.xml', '<name>German (Germany) Language Pack</name>', '<name>German (Austria) Language Pack</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/pkg_de-AT.xml', 'de-DE', 'de-AT');
		}
		if ($languageCode === 'de-CH')
		{
			renameStringInFile($tmpLanguagePathLangCode . '/pkg_de-CH.xml', '<name>German (Germany) Language Pack</name>', '<name>German (Switzerland) Language Pack</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/pkg_de-CH.xml', 'de-DE', 'de-CH');
		}
		if ($languageCode === 'de-LI')
		{
			renameStringInFile($tmpLanguagePathLangCode . '/pkg_de-LI.xml', '<name>German (Germany) Language Pack</name>', '<name>German (Lichtenstein) Language Pack</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/pkg_de-LI.xml', 'de-DE', 'de-LI');
		}
		if ($languageCode === 'de-LU')
		{
			renameStringInFile($tmpLanguagePathLangCode . '/pkg_de-LU.xml', '<name>German (Germany) Language Pack</name>', '<name>German (Luxembourg) Language Pack</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/pkg_de-LU.xml', 'de-DE', 'de-LU');
		}
	}
	
}

function searchAndReplaceStringInAllFiles($pathToFolder, $search, $replace)
{
	$directory = new RecursiveDirectoryIterator($pathToFolder);
	$iterator  = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);

	foreach ($iterator as $file)
	{
		if ($file->isFile())
		{
			renameStringInFile($file->getPathname(), $search, $replace);
		}
	}
}

function renameStringInFile($pathToFile, $search, $replace)
{
	// Read the entire string
	$str = file_get_contents($pathToFile);

	// Replace something in the file string - this is a VERY simple example
	$str = str_replace($search, $replace, $str);

	// Write the entire string
	file_put_contents($pathToFile, $str);
}