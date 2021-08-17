<?php
/**
 * Script used to build Joomla distribution archive packages
 * Builds packages in tmp/packages folder (for example, 'build/tmp/packages')
 *
 * Note: the new package must be tagged in your git repository BEFORE doing this
 * It uses the git tag for the new version, not trunk.
 *
 * This script is designed to be run in CLI on Linux, Mac OS X and WSL.
 * Make sure your default umask is 022 to create archives with correct permissions.
 *
 * For WSL based setups make sure there is a /etc/wsl.conf with the following content:
 * [automount]
 * enabled=true
 * options=metadata,uid=1000,gid=1000,umask=022
 *
 * Steps:
 * 1. Run the bump script
 * 2. Commit the version changes
 * 3. Tag new release in the local git repository (for example, "git tag -s 4.0.0v1")
 * 3. Run from CLI as: 'php buid/build.php --lpackage"
 * 4. Check the the tmp directory.
 *
 * Examples:
 * - php build/build.php --lpackages --v
 * - php build/build.php --crowdin --v
 * - php build/build.php --install --v
 * - php build/build.php --fullurl "https://github.com/joomla/joomla-cms/releases/download/4.0.0-rc5/Joomla_4.0.0-rc5-Release_Candidate-Full_Package.zip" --v
 *
 * @package    Joomla.Language
 * @copyright  (C) 2021 J!German <https://www.jgerman.de>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// This script is largly based on the Joomla CMS build Script
// https://github.com/joomla/joomla-cms/blob/4.0-dev/build/build.php

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
$options = getopt('', ['help', 'fullurl:', 'install', 'lpackages', 'v', 'crowdin']);

$showHelp         = isset($options['help']);
$fullReleaseUrl   = $options['fullurl'] ?? false;
$install          = isset($options['install']);
$languagePackages = isset($options['lpackages']);
$verbose          = isset($options['v']);
$crowdin          = isset($options['crowdin']);

if ($showHelp)
{
	usage($argv[0]);
	exit;
}

// Looking for the latest local tag
chdir($repo);
$tagVersion = system($systemGit . ' describe --tags `' . $systemGit . ' rev-list --tags --max-count=1`', $tagVersion);
$remote = 'tags/' . $tagVersion;
chdir($here);

message('Start build for remote '. $remote, $verbose);
message('Delete old release folder.', $verbose);
system('rm -rf ' . $tmp);
mkdir($tmp);
mkdir($fullpath);

message('Copy the files from the git repository.', $verbose);
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

// We only need this when we are building packages
if ($fullReleaseUrl || $languagePackages)
{
	system('mkdir packages');
}


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
message('Delete folders not included in packages.', $verbose);

foreach ($doNotPackage as $removeFile)
{
	system('rm -rf ' . $time . '/' . $removeFile);
}

message('Prepare packages.', $verbose);

if ($languagePackages || $crowdin)
{
	system('mkdir tmp_packages');
	chdir('tmp_packages');

	foreach (['de-DE', 'de-AT', 'de-CH', 'de-LI', 'de-LU'] as $languageCode)
	{
		system('mkdir ' . $languageCode);
		chdir($languageCode);

		message('Build package: ' . $languageCode, $verbose);

		foreach (['full', 'admin', 'site', 'api'] as $folder)
		{
			$tmpLanguagePath = $tmp . '/tmp_packages/' . $languageCode;
			$tmpLanguagePathFolder = $tmp . '/tmp_packages/' . $languageCode . '/' . $folder;

			system('mkdir ' . $tmpLanguagePathFolder);

			if ($folder === 'full')
			{
				system('cp ' . $fullpath . '/pkg_de-DE.xml ' . $tmpLanguagePathFolder . '/pkg_' . $languageCode . '.xml');
				system('cp ' . $fullpath . '/script.php ' . $tmpLanguagePathFolder . '/script.php');
				applyTranslationChanges($languageCode, $folder, $tmpLanguagePathFolder);
			}

			if ($folder === 'admin')
			{
				system('cp -r ' . $fullpath . '/administrator/language/de-DE/* ' . $tmpLanguagePathFolder);
				applyTranslationChanges($languageCode, $folder, $tmpLanguagePathFolder);
				chdir($tmpLanguagePathFolder);

				if ($languagePackages)
				{
					system('zip -r ' . $tmpLanguagePath . '/full/admin_' . $languageCode . '.zip * > /dev/null');
				}
			}

			if ($folder === 'site')
			{
				system('cp -r ' . $fullpath . '/language/de-DE/* ' . $tmpLanguagePathFolder);
				applyTranslationChanges($languageCode, $folder, $tmpLanguagePathFolder);
				chdir($tmpLanguagePathFolder);

				if ($languagePackages)
				{
					system('zip -r ' . $tmpLanguagePath . '/full/site_' . $languageCode . '.zip * > /dev/null');
				}
			}

			if ($folder === 'api')
			{
				system('cp -r ' . $fullpath . '/api/language/de-DE/* ' . $tmpLanguagePathFolder);
				applyTranslationChanges($languageCode, $folder, $tmpLanguagePathFolder);
				chdir($tmpLanguagePathFolder);

				if ($languagePackages)
				{
					system('zip -r ' . $tmpLanguagePath . '/full/api_' . $languageCode . '.zip * > /dev/null');
				}
			}

			chdir('..');
		}

		if ($languagePackages)
		{
			// Build zip package
			chdir($tmpLanguagePath . '/full');

			system('zip -r ' . $tmpLanguagePath . '/full/full_' . $languageCode . '.zip * > /dev/null');
			system('cp ' . $tmpLanguagePath . '/full/full_' . $languageCode . '.zip ' . $tmp . '/packages/' . $languageCode . '_joomla_lang_full_' . $fullVersion . '.zip');

			chdir('..');
		}

		chdir('..');
	}
}

// Build a full package when a full package url is passed.
if ($fullReleaseUrl)
{
	message('Building german full package.', $verbose);

	system('mkdir fullinstall');
	chdir('fullinstall');

	$filename = basename($fullReleaseUrl);

	message('Download full package.', $verbose);

	system("wget -q $fullReleaseUrl -O $filename");

	message('Extract en-GB full package.', $verbose);
	system("unzip $filename > '/dev/null'");

	message('Remove full zip package.', $verbose);
	system('rm ' . $filename);

	message('Copy the german language stuff in.', $verbose);
	system('cp -r ' . $fullpath . '/administrator .');
	system('cp -r ' . $fullpath . '/api .');
	system('cp -r ' . $fullpath . '/language .');
	system('cp -r ' . $fullpath . '/installation .');
	system('cp ' . $fullpath . '/pkg_de-DE.xml administrator/manifests/packages/pkg_de-DE.xml');

	$zipFilename = str_replace('.zip', '_German.zip', $filename);
	$targzFilename = str_replace('.zip', '_German.tar.gz', $filename);
	$tarbz2Filename = str_replace('.zip', '_German.tar.bz2', $filename);

	message('Build new full packages.', $verbose);

	message('Build new full zip package.', $verbose);
	system("zip -r $tmp/packages/$zipFilename * > /dev/null");
	message('Build new full tar.gz package.', $verbose);
	system("tar -czf $tmp/packages/$targzFilename * > '/dev/null'");
	message('Build new full tar.bz2 package.', $verbose);
	system("tar -cjf $tmp/packages/$tarbz2Filename * > '/dev/null'");

	chdir('..');
}

if ($install || $crowdin)
{
	message('Build install files.', $verbose);

	chdir('..');
	system('mkdir install');
	chdir('install');

	foreach (['de-DE', 'de-AT', 'de-CH', 'de-LI', 'de-LU'] as $languageCode)
	{
		message('Build install files: ' . $languageCode, $verbose);
		system('mkdir ' . $languageCode);
		chdir($languageCode);

		$tmpInstallLanguagePath = $tmp . '/install/' . $languageCode;

		system('cp -r ' . $fullpath . '/installation/language/de-DE/* ' . $tmpInstallLanguagePath);
		applyTranslationChanges($languageCode, 'install', $tmpInstallLanguagePath);
		chdir('..');
	}

	chdir('..');
}

if ($crowdin)
{
	message('Build crowdin folder', $verbose);
	system('mkdir crowdin');
	chdir('crowdin');

	system('mkdir package');
	chdir('package');

	foreach (['de-DE', 'de-AT', 'de-CH', 'de-LI', 'de-LU'] as $languageCode)
	{
		message('Build crowdin language folder for: ' . $languageCode, $verbose);
		system('mkdir ' . $languageCode);
		chdir($languageCode);

		$tmpLanguagePath = $tmp . '/tmp_packages/' . $languageCode;

		system('mkdir -p administrator/language/' . $languageCode);
		system('mkdir -p api/language/' . $languageCode);
		system('mkdir -p language/' . $languageCode);

		system('cp -r ' . $tmpLanguagePath . '/admin/* administrator/language/' . $languageCode . '/');
		system('cp -r ' . $tmpLanguagePath . '/api/* api/language/' . $languageCode . '/');
		system('cp -r ' . $tmpLanguagePath . '/site/* language/' . $languageCode . '/');
		system('cp ' . $tmpLanguagePath . '/full/pkg_' . $languageCode . '.xml pkg_' . $languageCode . '.xml');
		system('cp ' . $tmpLanguagePath . '/full/script.php script.php');

		chdir('..');
	}

	message('Build crowdin installation folder', $verbose);
	chdir('..');
	system('mkdir core');
	chdir('core');
	system('mkdir -p installation/language/');
	system('cp -r ' . $tmp . '/install/* installation/language/');
	chdir('..');
}

// Cleanup
system('rm -rf ' . $tmp . '/tmp_packages/');

message('The Build of version ' . $fullVersion . ' has been successfully completed!', $verbose);

function message(string $messagetext, $verbose)
{
	if ($verbose)
	{
		echo $messagetext . PHP_EOL;
	}
}

function usage(string $command)
{
	echo PHP_EOL;
	echo 'Usage: php ' . $command . ' [options]' . PHP_EOL;
	echo PHP_TAB . '[options]:' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--fullurl "[URL]"' . PHP_TAB . 'The URL to the full en-GB package; When provided we also build a new full package' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--install' . PHP_TAB . PHP_TAB . 'Build the installation files' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--lpackages' . PHP_TAB . PHP_TAB . 'Build the language packages' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--v' . PHP_TAB . PHP_TAB . PHP_TAB . 'Show progress messages' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--crowdin' . PHP_TAB . PHP_TAB . 'Build the folder structure for crowdin updates' . PHP_EOL;
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
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<name>German (Germany)</name>', '<name>German (Austria)</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<name>German (DE)</name>', '<name>German (AT)</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<nativeName>Deutsch (Deutschland)</nativeName>', '<nativeName>Deutsch (Österreich)</nativeName>');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', '<locale>de_DE.utf8, de_DE.UTF-8, de_DE, deu_DE, german, german-de, de, deu, germany</locale>', '<locale>de_AT.utf8, de_AT.UTF-8, de_AT, deu_AT, german-at, at, austria, de_DE.utf8, de_DE.UTF-8, de_DE, deu_DE, german, german-de, de, deu, germany</locale>');

			// install.xml
			renameStringInFile($tmpLanguagePathLangCode . '/install.xml', '<name>German (DE)</name>', '<name>German (AT)</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/install.xml', 'de-DE', 'de-AT');

			// localise.php
			renameStringInFile($tmpLanguagePathLangCode . '/localise.php', 'de-DE', 'de-AT');
			renameStringInFile($tmpLanguagePathLangCode . '/localise.php', 'De_DELocalise', 'De_ATLocalise');

			// Replace Januar with Jänner and Jan. with Jän. for de-AT
			searchAndReplaceStringInAllFiles($tmpLanguagePathLangCode, 'Januar', 'Jänner');
			searchAndReplaceStringInAllFiles($tmpLanguagePathLangCode, 'Jan.', 'Jän.');
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

			// Replace Januar with Jänner for de-AT
			searchAndReplaceStringInAllFiles($tmpLanguagePathLangCode, 'Januar', 'Jänner');
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
			renameStringInFile($tmpLanguagePathLangCode . '/script.php', 'de-DE', 'de-AT');
			renameStringInFile($tmpLanguagePathLangCode . '/script.php', 'Pkg_deDE', 'Pkg_deAT');
		}

		if ($languageCode === 'de-CH')
		{
			renameStringInFile($tmpLanguagePathLangCode . '/pkg_de-CH.xml', '<name>German (Germany) Language Pack</name>', '<name>German (Switzerland) Language Pack</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/pkg_de-CH.xml', 'de-DE', 'de-CH');
			renameStringInFile($tmpLanguagePathLangCode . '/script.php', 'de-DE', 'de-CH');
			renameStringInFile($tmpLanguagePathLangCode . '/script.php', 'Pkg_deDE', 'Pkg_deCH');
		}

		if ($languageCode === 'de-LI')
		{
			renameStringInFile($tmpLanguagePathLangCode . '/pkg_de-LI.xml', '<name>German (Germany) Language Pack</name>', '<name>German (Lichtenstein) Language Pack</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/pkg_de-LI.xml', 'de-DE', 'de-LI');
			renameStringInFile($tmpLanguagePathLangCode . '/script.php', 'de-DE', 'de-LI');
			renameStringInFile($tmpLanguagePathLangCode . '/script.php', 'Pkg_deDE', 'Pkg_deLI');
		}

		if ($languageCode === 'de-LU')
		{
			renameStringInFile($tmpLanguagePathLangCode . '/pkg_de-LU.xml', '<name>German (Germany) Language Pack</name>', '<name>German (Luxembourg) Language Pack</name>');
			renameStringInFile($tmpLanguagePathLangCode . '/pkg_de-LU.xml', 'de-DE', 'de-LU');
			renameStringInFile($tmpLanguagePathLangCode . '/script.php', 'de-DE', 'de-LU');
			renameStringInFile($tmpLanguagePathLangCode . '/script.php', 'Pkg_deDE', 'Pkg_deLU');
		}
	}

	if ($folder === 'install')
	{
		if ($languageCode === 'de-AT')
		{
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', 'Germany', 'Austria');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', 'Deutschland', 'Österreich');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', 'de-DE', 'de-AT');
			renameStringInFile($tmpLanguagePathLangCode . '/joomla.ini', 'Deutschland', 'Österreich');
		}

		if ($languageCode === 'de-CH')
		{
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', 'Germany', 'Switzerland');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', 'Deutschland', 'Schweiz');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', 'de-DE', 'de-CH');
			renameStringInFile($tmpLanguagePathLangCode . '/joomla.ini', 'Deutschland', 'Schweiz');
			renameStringInFile($tmpLanguagePathLangCode . '/joomla.ini', 'ß', 'ss');
		}

		if ($languageCode === 'de-LI')
		{
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', 'Germany', 'Lichtenstein');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', 'Deutschland', 'Lichtenstein');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml',  'de-DE', 'de-LI');
			renameStringInFile($tmpLanguagePathLangCode . '/joomla.ini', 'Deutschland', 'Lichtenstein');
			renameStringInFile($tmpLanguagePathLangCode . '/joomla.ini', 'ß', 'ss');
		}

		if ($languageCode === 'de-LU')
		{
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', 'Germany', 'Luxembourg');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', 'Deutschland', 'Luxemburg');
			renameStringInFile($tmpLanguagePathLangCode . '/langmetadata.xml', 'de-DE', 'de-LU');
			renameStringInFile($tmpLanguagePathLangCode . '/joomla.ini', 'Deutschland', 'Luxemburg');
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
