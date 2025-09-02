# Copilot Instructions for J!German Joomla Translation Repository

## Repository Overview

This repository contains the **J!German translation project** - German language packs for the Joomla! Content Management System. It provides comprehensive German translations for Joomla! 5.x and maintains regional variants for Germany, Austria, Switzerland, Liechtenstein, and Luxembourg.

**Repository Type**: Translation/Language Pack Project  
**Primary Language**: PHP (build scripts), INI files (translations)  
**Target Runtime**: PHP 8.1+ (per script.php requirements)  
**Size**: Medium (~300+ translation files)  
**Framework**: Joomla! CMS Language Pack

## Project Structure & Architecture

### Core Directories
- `administrator/language/de-DE/` - Backend/admin interface translations (~300 .ini files)
- `language/de-DE/` - Frontend/site interface translations (~50 .ini files)  
- `api/language/de-DE/` - REST API translations (~10 .ini files)
- `installation/language/de-DE/` - Installation wizard translations
- `build/` - Build and versioning scripts (build.php, bump.php)

### Key Files
- `pkg_de-DE.xml` - Main language package manifest and metadata
- `script.php` - Installation/update script with cleanup logic  
- `langmetadata.xml` - Language metadata (locale, timezone, calendar settings)
- `localise.php` - Localization helper class for date/time formatting

### Translation File Structure
- `.ini` files contain translations in `KEY="Value"` format
- `.sys.ini` files contain system/installation related strings
- Files are named by component: `com_content.ini`, `mod_menu.ini`, `plg_system_cache.ini`
- All files must be UTF-8 encoded

## Build System & Commands

### Prerequisites
- PHP 8.1 or higher
- Git command line tools
- Linux/Mac OS X/WSL environment
- Umask set to 022 for correct file permissions

### Version Management
```bash
# Update version numbers across all files
php build/bump.php -v 5.3.2 -l 1

# Where:
# -v = Joomla version (e.g., 5.3.2, 5.4.0-rc1)  
# -l = Language pack version (integer: 1, 2, 3, etc.)
```

**Always run bump.php before building packages.** This updates:
- Version numbers in all XML files
- Creation dates 
- Copyright years
- Package descriptions

### Build Commands
```bash
# Build language packages (most common)
php build/build.php --lpackages --v

# Build installation files
php build/build.php --install --v

# Build Crowdin integration files
php build/build.php --crowdin --v

# Build with custom tag version
php build/build.php --lpackages --v --tagversion "5.3.2v1"

# Build full Joomla package with German language
php build/build.php --fullurl "https://github.com/joomla/joomla-cms/releases/download/5.3.2/Joomla_5.3.2-Stable-Full_Package.zip" --v
```

**Build output location**: `build/tmp/packages/` (created automatically)

### Pre-build Requirements
1. Commit all changes to git
2. Create git tag: `git tag -s 5.3.2v1` (where `5.3.2` is the Joomla version and `v1` is the language pack version; tag format: `<JoomlaVersion>v<PackVersion>`)
3. Verify umask is 022: `umask 022`

### Build Timing
- Language packages: ~30 seconds
- Installation files: ~10 seconds  
- Full package with download: ~2-3 minutes (depending on download speed)

## Regional Language Variants

The build system automatically creates 5 regional variants:

| Variant | Changes Applied |
|---------|----------------|
| de-DE | Base German (Germany) - no changes |
| de-AT | Austria - "Januar" → "Jänner", "Jan." → "Jän." |
| de-CH | Switzerland - "ß" → "ss" replacement |
| de-LI | Liechtenstein - "ß" → "ss" replacement |
| de-LU | Luxembourg - no character changes |

Each variant updates:
- Language tags in XML files
- Country names in metadata
- Regional-specific terminology
- File names and internal references

## Validation & Quality Assurance

### File Validation
- INI files must be UTF-8 encoded
- No syntax errors in key=value pairs
- Proper XML structure in metadata files
- Git tags must exist before building

### Manual Testing Steps
1. Install generated language pack in Joomla! test site
2. Switch backend language to German
3. Check for untranslated strings (English fallbacks)
4. Verify special characters display correctly
5. Test installation process

### Common Issues & Solutions
- **Build fails with "tag not found"**: Create git tag first
- **Permission errors**: Run `umask 022` before building
- **Character encoding issues**: Ensure files are UTF-8
- **Missing translations**: Check for new/updated keys in source

## GitHub Workflow & Contributing

### Issue Management
- Use German language in issue templates
- Bug reports require: location, current text, suggested improvement
- Version compatibility is tracked (3.x, 4.x, 5.x)

### Contributing Guidelines
1. Follow "issue-first principle" - create issue before PR
2. Use branch naming: `<issue-number>` or descriptive name
3. Commit messages in German: "fix #123" or descriptive text
4. Test translations in actual Joomla! installation

### Release Process
1. Run version bump: `php build/bump.php -v X.Y.Z -l N`
2. Commit version changes
3. Create git tag: `git tag -s X.Y.ZvN`
4. Build packages: `php build/build.php --lpackages --v`
5. Upload to release/distribution channels

## Development Environment Setup

### Required Tools
- PHP 8.1+ with CLI
- Git
- Text editor with UTF-8 support
- Local Joomla! installation for testing

### File Editing Guidelines
- Always maintain UTF-8 encoding
- Preserve INI file structure and comments
- Keep consistent translation style/terminology
- Test special characters (umlauts: ä, ö, ü, ß)

### Debugging Build Issues
- Run with `--v` flag for verbose output
- Check git status and tags: `git tag -l`
- Verify file permissions: `ls -la`
- Test individual PHP scripts: `php -l build/build.php`

## Trust These Instructions

These instructions are comprehensive and tested. Only search for additional information if:
- Commands fail with unexpected errors  
- New Joomla! versions require different approaches
- Build output doesn't match expected structure
- Translation files show encoding problems

The build system is stable and well-established. Follow the documented process for reliable results.