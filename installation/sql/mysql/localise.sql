--
-- German overwrites
--

--
-- Table `#__extensions`
--
UPDATE IGNORE `#__extensions` SET `params` = REPLACE(`params`, 'en-GB', 'de-DE') WHERE `extension_id` = 10;

INSERT INTO `#__extensions` (`extension_id`, `package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `manifest_cache`, `params`, `custom_data`) VALUES
(602, 605, 'German (DE)', 'language', 'de-DE', '', 0, 1, 1, '{"name":"German (Germany)","type":"language","creationDate":"2023-08-02","author":"J!German","copyright":"Copyright (C) 2005 - 2023 Open Source Matters & J!German. All rights reserved.","authorEmail":"team@jgerman.de","authorUrl":"http:\/\/www.jgerman.de","version":"5.0.0-alpha2.1","group":""}' ,'' ,''),
(603, 605, 'German (DE)', 'language', 'de-DE', '', 1, 1, 1, '{"name":"German (Germany)","type":"language","creationDate":"2023-08-02","author":"J!German","copyright":"Copyright (C) 2005 - 2023 Open Source Matters & J!German. All rights reserved.","authorEmail":"team@jgerman.de","authorUrl":"http:\/\/www.jgerman.de","version":"5.0.0-alpha2.1","group":""}' ,'' ,''),
(604, 605, 'German (DE)', 'language', 'de-DE', '', 3, 1, 1, '{"name":"German (Germany)","type":"language","creationDate":"2023-08-02","author":"J!German","copyright":"Copyright (C) 2005 - 2023 Open Source Matters & J!German. All rights reserved.","authorEmail":"team@jgerman.de","authorUrl":"http:\/\/www.jgerman.de","version":"5.0.0-alpha2.1","group":""}' ,'' ,''),
(605, 0, 'German (Germany) Language Pack', 'package', 'pkg_de-DE', '', 0, 1, 1, '{"name":"German (Germany) Language Pack","type":"package","creationDate":"2023-08-02","author":"J!German","copyright":"","authorEmail":"team@jgerman.de","authorUrl":"http:\/\/www.jgerman.de","version":"5.0.0-alpha2.1","group":"","filename":"pkg_de-DE"}' ,'' ,'');

--
-- Table `#__languages`
--
INSERT INTO `#__languages` (`lang_id`, `lang_code`, `title`, `title_native`, `sef`, `image`, `description`, `metadesc`, `published`, `access`, `ordering`) VALUES
(2, 'de-DE', 'German (DE)', 'Deutsch (Deutschland)', 'de', 'de_de', '', '', 1, 1, 2),
(3, 'de-CH', 'German (CH)', 'Deutsch (Schweiz)', 'ch', 'de_ch', '', '', 0, 1, 3),
(4, 'de-AT', 'German (AT)', 'Deutsch (Österreich)', 'at', 'de_at', '', '', 0, 1, 4),
(5, 'de-LI', 'German (LI)', 'Deutsch (Liechtenstein)', 'li', 'de_li', '', '', 0, 1, 5),
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
UPDATE IGNORE `#__usergroups` SET `title` = 'Gast' WHERE `id` = 9;

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
UPDATE IGNORE `#__modules` SET `title` = 'Benachrichtigungen' WHERE `id` = 9;
UPDATE IGNORE `#__modules` SET `title` = 'Angemeldete Benutzer' WHERE `id` = 10;
UPDATE IGNORE `#__modules` SET `title` = 'Admin Menü' WHERE `id` = 12;
UPDATE IGNORE `#__modules` SET `title` = 'Titel' WHERE `id` = 15;
UPDATE IGNORE `#__modules` SET `title` = 'Anmeldeformular' WHERE `id` = 16;
UPDATE IGNORE `#__modules` SET `title` = 'Navigationspfad' WHERE `id` = 17;
UPDATE IGNORE `#__modules` SET `title` = 'Mehrsprachenstatus' WHERE `id` = 79;
UPDATE IGNORE `#__modules` SET `title` = 'Joomla Version' WHERE `id` = 86;
UPDATE IGNORE `#__modules` SET `title` = 'Beispieldaten' WHERE `id` = 87;
UPDATE IGNORE `#__modules` SET `title` = 'Letzte Aktionen' WHERE `id` = 88;
UPDATE IGNORE `#__modules` SET `title` = 'Datenschutz Dashboard' WHERE `id` = 89;
UPDATE IGNORE `#__modules` SET `title` = 'Anmeldung Support' WHERE `id` = 90;
UPDATE IGNORE `#__modules` SET `title` = 'System Dashboard' WHERE `id` = 91;
UPDATE IGNORE `#__modules` SET `title` = 'Inhalt Dashboard' WHERE `id` = 92;
UPDATE IGNORE `#__modules` SET `title` = 'Menü Dashboard' WHERE `id` = 93;
UPDATE IGNORE `#__modules` SET `title` = 'Erweiterungen Dashboard' WHERE `id` = 94;
UPDATE IGNORE `#__modules` SET `title` = 'Benutzer Dashboard' WHERE `id` = 95;
UPDATE IGNORE `#__modules` SET `title` = 'Beliebte Beiträge' WHERE `id` = 96;
UPDATE IGNORE `#__modules` SET `title` = 'Kürzlich hinzugefügte Beiträge' WHERE `id` = 97;
UPDATE IGNORE `#__modules` SET `title` = 'Angemeldete Benutzer' WHERE `id` = 98;
UPDATE IGNORE `#__modules` SET `title` = 'Link Website' WHERE `id` = 99;
UPDATE IGNORE `#__modules` SET `title` = 'Nachrichten' WHERE `id` = 100;
UPDATE IGNORE `#__modules` SET `title` = 'Nachinstallationshinweise' WHERE `id` = 101;
UPDATE IGNORE `#__modules` SET `title` = 'Benutzermenü' WHERE `id` = 102;
UPDATE IGNORE `#__modules` SET `title` = 'Site' WHERE `id` = 103;
UPDATE IGNORE `#__modules` SET `title` = 'System' WHERE `id` = 104;
UPDATE IGNORE `#__modules` SET `title` = '3rd Party' WHERE `id` = 105;
UPDATE IGNORE `#__modules` SET `title` = 'Hilfe Dashboard' WHERE `id` = 106;
UPDATE IGNORE `#__modules` SET `title` = 'Datenschutzanfragen' WHERE `id` = 107;
UPDATE IGNORE `#__modules` SET `title` = 'Datenschutzstatus' WHERE `id` = 108;
UPDATE IGNORE `#__modules` SET `title` = 'Geführte Touren' WHERE `id` = 109;

--
-- Table `#__scheduler_tasks`
--
UPDATE IGNORE `#__scheduler_tasks` SET `title` = 'Protokolldateien rotieren' WHERE `id` = 1;
UPDATE IGNORE `#__scheduler_tasks` SET `title` = 'Sitzungsdaten bereinigen' WHERE `id` = 2;
UPDATE IGNORE `#__scheduler_tasks` SET `title` = 'Joomla-Update-Mitteilung senden' WHERE `id` = 3;
