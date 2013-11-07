--
-- German overwrites
--

--
-- Table `#__extensions`
--
UPDATE IGNORE `#__extensions` SET `params` = REPLACE(`params`, 'en-GB', 'de-DE') WHERE `extension_id` = 11;
UPDATE IGNORE `#__extensions` SET `params` = REPLACE(`params`, '"mode":"1"', '"mode":"2"') WHERE `extension_id` = 412;
UPDATE IGNORE `#__extensions` SET `params` = REPLACE(`params`, '"lang_mode":"0"', '"lang_mode":"1"') WHERE `extension_id` = 412;

INSERT INTO `#__extensions` (`extension_id`, `name`, `type`, `element`, `client_id`, `access`) VALUES
(602, 'German (DE-CH-AT)', 'language', 'de-DE', 0, 1),
(603, 'German (DE-CH-AT)', 'language', 'de-DE', 1, 1),
(604, 'German Language Pack', 'package', 'pkg_de-DE', 0, 1);

--
-- Table `#__languages`
--
INSERT INTO `#__languages` (`lang_id`, `lang_code`, `title`, `title_native`, `sef`, `image`, `published`, `ordering`) VALUES
(2, 'de-DE', 'German (DE-CH-AT)', 'Deutsch', 'de', 'de', 1, 2);

--
-- Table `#__update_sites_extensions`
--
INSERT INTO `#__update_sites_extensions` VALUES (3, 604);

--
-- Table `#__usergroups`
--
UPDATE IGNORE `#__usergroups` SET `title` = 'Öffentlich' WHERE `id` = 1;
UPDATE IGNORE `#__usergroups` SET `title` = 'Registriert' WHERE `id` = 2;
UPDATE IGNORE `#__usergroups` SET `title` = 'Autor' WHERE `id` = 3;
UPDATE IGNORE `#__usergroups` SET `title` = 'Super Benutzer' WHERE `id` = 8;
UPDATE IGNORE `#__usergroups` SET `title` = 'Gast' WHERE `id` = 9;

--
-- Table `#__viewlevels`
--
UPDATE IGNORE `#__viewlevels` SET `title` = 'Öffentlich' WHERE `id` = 1;
UPDATE IGNORE `#__viewlevels` SET `title` = 'Registriert' WHERE `id` = 2;
UPDATE IGNORE `#__viewlevels` SET `title` = 'Spezial' WHERE `id` = 3;
UPDATE IGNORE `#__viewlevels` SET `title` = 'Gast' WHERE `id` = 5;
UPDATE IGNORE `#__viewlevels` SET `title` = 'Super Benutzer' WHERE `id` = 6;