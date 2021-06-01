--
-- German overwrites
--

--
-- Table `#__extensions`
--
UPDATE IGNORE `#__extensions` SET `params` = REPLACE(`params`, 'en-GB', 'de-DE') WHERE `extension_id` = 11;

INSERT INTO `#__extensions` (`extension_id`, `package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `manifest_cache`, `params`, `custom_data`) VALUES
(602, 604, 'German (DE)', 'language', 'de-DE', '', 0, 1, 1, '{"name":"German (Germany)","type":"language","creationDate":"01.06.2021","author":"J!German","copyright":"Copyright (C) 2005 - 2021 Open Source Matters & J!German. All rights reserved.","authorEmail":"team@jgerman.de","authorUrl":"http:\/\/www.jgerman.de","version":"4.0.0-rc1.1","group":""}' ,'' ,''),
(603, 604, 'German (DE)', 'language', 'de-DE', '', 1, 1, 1, '{"name":"German (Germany)","type":"language","creationDate":"01.06.2021","author":"J!German","copyright":"Copyright (C) 2005 - 2021 Open Source Matters & J!German. All rights reserved.","authorEmail":"team@jgerman.de","authorUrl":"http:\/\/www.jgerman.de","version":"4.0.0-rc1.1","group":""}' ,'' ,''),
(604, 0, 'German (Germany) Language Pack', 'package', 'pkg_de-DE', '', 0, 1, 1, '{"name":"German (Germany) Language Pack","type":"package","creationDate":"01.06.2021","author":"J!German","copyright":"","authorEmail":"team@jgerman.de","authorUrl":"http:\/\/www.jgerman.de","version":"4.0.0-rc1.1","group":"","filename":"pkg_de-DE"}' ,'' ,'');

--
-- Table `#__languages`
--
INSERT INTO `#__languages` (`lang_id`, `lang_code`, `title`, `title_native`, `sef`, `image`, `description`, `metadesc`, `published`, `access`, `ordering`) VALUES
(2, 'de-DE', 'German (DE)', 'Deutsch (Deutschland)', 'de', 'de_de', '', '', 1, 1, 2),
(3, 'de-CH', 'German (CH)', 'Deutsch (Schweiz)', 'ch', 'de_ch', '', '', 0, 1, 3),
(4, 'de-AT', 'German (AT)', 'Deutsch (Österreich)', 'at', 'de_at', '', '', 0, 1, 4),
(5, 'de-LI', 'German (LI)', 'Deutsch (Lichtenstein)', 'li', 'de_li', '', '', 0, 1, 5),
(6, 'de-LU', 'German (LU)', 'Deutsch (Luxemburg)', 'lu', 'de_lu', '', '', 0, 1, 6);

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
UPDATE IGNORE `#__usergroups` SET `title` = 'Gast' WHERE `id` = 13;

--
-- Table `#__viewlevels`
--
UPDATE IGNORE `#__viewlevels` SET `title` = 'Öffentlich' WHERE `id` = 1;
UPDATE IGNORE `#__viewlevels` SET `title` = 'Registriert' WHERE `id` = 2;
UPDATE IGNORE `#__viewlevels` SET `title` = 'Spezial' WHERE `id` = 3;
UPDATE IGNORE `#__viewlevels` SET `title` = 'Gast' WHERE `id` = 5;
UPDATE IGNORE `#__viewlevels` SET `title` = 'Super Benutzer' WHERE `id` = 6;

--
-- Table `#__modules`
--
UPDATE IGNORE `#__modules` SET `title` = 'Hauptmenü' WHERE `id` = 1;
UPDATE IGNORE `#__modules` SET `title` = 'Anmeldung' WHERE `id` = 2;
UPDATE IGNORE `#__modules` SET `title` = 'Beliebte Beiträge' WHERE `id` = 3;
UPDATE IGNORE `#__modules` SET `title` = 'Kürzlich hinzugefügte Beiträge' WHERE `id` = 4;
UPDATE IGNORE `#__modules` SET `title` = 'Symbolleiste' WHERE `id` = 8;
UPDATE IGNORE `#__modules` SET `title` = 'Schnellzugriff' WHERE `id` = 9;
UPDATE IGNORE `#__modules` SET `title` = 'Angemeldete Benutzer' WHERE `id` = 10;
UPDATE IGNORE `#__modules` SET `title` = 'Admin Menü' WHERE `id` = 12;
UPDATE IGNORE `#__modules` SET `title` = 'Admin Submenü' WHERE `id` = 13;
UPDATE IGNORE `#__modules` SET `title` = 'Benutzerstatus' WHERE `id` = 14;
UPDATE IGNORE `#__modules` SET `title` = 'Titel' WHERE `id` = 15;
UPDATE IGNORE `#__modules` SET `title` = 'Anmeldeformular' WHERE `id` = 16;
UPDATE IGNORE `#__modules` SET `title` = 'Navigationspfad' WHERE `id` = 17;
UPDATE IGNORE `#__modules` SET `title` = 'Mehrsprachenstatus' WHERE `id` = 79;
UPDATE IGNORE `#__modules` SET `title` = 'Joomla Version' WHERE `id` = 86;
UPDATE IGNORE `#__modules` SET `title` = 'Beispieldaten' WHERE `id` = 87;
UPDATE IGNORE `#__modules` SET `title` = 'Letzte Aktionen' WHERE `id` = 88;
UPDATE IGNORE `#__modules` SET `title` = 'Datenschutz Dashboard' WHERE `id` = 89;
