# MySQL Navigator Xport
# Database: dream
# root@vedro.skynet

CREATE DATABASE vedro;
USE vedro;

#
# Table structure for table 'external_ip'
#

# DROP TABLE IF EXISTS external_ip;
CREATE TABLE `external_ip` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `ip` int(11) NOT NULL default '0',
  `location` tinyint(3) unsigned NOT NULL default '0',
  `description` varchar(255) character set utf8 default NULL,
  `date_create` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `ip` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;

#
# Table structure for table 'groups'
#

# DROP TABLE IF EXISTS groups;
CREATE TABLE `groups` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `name` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `is_deleted` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Dumping data for table 'groups'
#

INSERT INTO groups VALUES (1,'Администраторы',0);
INSERT INTO groups VALUES (2,'Разработчики',0);

#
# Table structure for table 'groups_menus'
#

# DROP TABLE IF EXISTS groups_menus;
CREATE TABLE `groups_menus` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `group_id` smallint(5) unsigned NOT NULL default '0',
  `menu_id` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

#
# Table structure for table 'groups_users'
#

# DROP TABLE IF EXISTS groups_users;
CREATE TABLE `groups_users` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `user_id` smallint(5) unsigned NOT NULL default '0',
  `group_id` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

#
# Dumping data for table 'groups_users'
#

INSERT INTO groups_users VALUES (1,1,1);
INSERT INTO groups_users VALUES (2,1,2);

#
# Table structure for table 'locations'
#

# DROP TABLE IF EXISTS locations;
CREATE TABLE `locations` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) character set utf8 NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;

#
# Table structure for table 'menus'
#

# DROP TABLE IF EXISTS menus;
CREATE TABLE `menus` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `translit_name` varchar(255) character set latin1 NOT NULL default '',
  `full_name` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `script_name` varchar(255) character set latin1 default NULL,
  `parent_id` mediumint(8) unsigned NOT NULL default '0',
  `module_id` mediumint(8) unsigned NOT NULL default '0',
  `position` smallint(5) unsigned NOT NULL default '0',
  `is_module_root` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Table structure for table 'modules'
#

# DROP TABLE IF EXISTS modules;
CREATE TABLE `modules` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(128) character set utf8 collate utf8_unicode_ci default NULL,
  `path` varchar(255) character set latin1 default NULL,
  `kernel` varchar(255) character set latin1 default NULL,
  `install_time` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`,`path`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Table structure for table 'modules_depend'
#

# DROP TABLE IF EXISTS modules_depend;
CREATE TABLE `modules_depend` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `module_id` mediumint(8) unsigned NOT NULL default '0' COMMENT '?�?�?????�???? ?????????�??',
  `extend_module_id` mediumint(8) unsigned NOT NULL default '0' COMMENT '???� ?????�?????????? ?�?�?????????� ???�???� ?????????�??. ?? ???�?? ?????�?� ???�?�???�?? ?????�?�???�???�?? id ?????????�?�??, ?? ?????�?????�?� ???�?� kernel-?�',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

#
# Table structure for table 'users'
#

# DROP TABLE IF EXISTS users;
CREATE TABLE `users` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `login` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `password` varchar(32) character set ascii default NULL,
  `name` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `family` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `daddy` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `mobile_telephone` varchar(20) collate utf8_bin default NULL,
  `register` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

#
# Dumping data for table 'users'
#

INSERT INTO users VALUES (1,'root','1a1dc91c907325c69271ddf0c944bc72','','','','','0000-00-00 00:00:00',0);

#
# Table structure for table 'users_connection_history'
#

# DROP TABLE IF EXISTS users_connection_history;
CREATE TABLE `users_connection_history` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `uid` smallint(5) unsigned NOT NULL default '0',
  `ip` int(11) unsigned NOT NULL default '0',
  `time_active` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `state` tinyint(1) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

#
# Table structure for table 'users_online'
#

# DROP TABLE IF EXISTS users_online;
CREATE TABLE `users_online` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `uid` smallint(5) unsigned NOT NULL default '0',
  `ip` int(11) unsigned NOT NULL default '0',
  `sessid` varchar(32) NOT NULL default '',
  `last_view` varchar(255) default NULL,
  `time_login` timestamp NOT NULL default '0000-00-00 00:00:00',
  `time_last_active` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

