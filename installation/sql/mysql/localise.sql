--
-- German overwrites
--

--
-- Table `#__extensions`
--
UPDATE IGNORE `#__extensions` SET `params` = REPLACE(`params`, 'en-GB', 'de-DE') WHERE `extension_id` = 11;

INSERT INTO `#__extensions` (`extension_id`, `name`, `type`, `element`, `client_id`, `access`, `manifest_cache`) VALUES
(602, 'German (DE)', 'language', 'de-DE', 0, 1, '{"name":"German (Germany)","type":"language","creationDate":"26.08.2017","author":"J!German","copyright":"Copyright (C) 2005 - 2017 Open Source Matters & J!German. All rights reserved.","authorEmail":"team@jgerman.de","authorUrl":"http:\/\/www.jgerman.de","version":"3.7.5.2","group":""}'),
(603, 'German (DE)', 'language', 'de-DE', 1, 1, '{"name":"German (Germany)","type":"language","creationDate":"26.08.2017","author":"J!German","copyright":"Copyright (C) 2005 - 2017 Open Source Matters & J!German. All rights reserved.","authorEmail":"team@jgerman.de","authorUrl":"http:\/\/www.jgerman.de","version":"3.7.5.2","group":""}'),
(604, 'German (Germany) Language Pack', 'package', 'pkg_de-DE', 0, 1, '{"name":"German (Germany) Language Pack","type":"package","creationDate":"26.08.2017","author":"J!German","copyright":"","authorEmail":"team@jgerman.de","authorUrl":"http:\/\/www.jgerman.de","version":"3.7.5.2","group":"","filename":"pkg_de-DE"}');

--
-- Table `#__languages`
--
INSERT INTO `#__languages` (`lang_id`, `lang_code`, `title`, `title_native`, `sef`, `image`, `published`, `access`, `ordering`) VALUES
(2, 'de-DE', 'German (DE)', 'Deutsch (Deutschland)', 'de', 'de_de', 1, 1, 2),
(3, 'de-CH', 'German (CH)', 'Deutsch (Schweiz)', 'ch', 'de_ch', 0, 1, 3),
(4, 'de-AT', 'German (AT)', 'Deutsch (Österreich)', 'at', 'de_at', 0, 1, 4),
(5, 'de-LI', 'German (LI)', 'Deutsch (Lichtenstein)', 'li', 'de_li', 0, 1, 5),
(6, 'de-LU', 'German (LU)', 'Deutsch (Luxemburg)', 'lu', 'de_lu', 0, 1, 6);

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
