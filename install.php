<?php
/**
 *
 * @category        module
 * @package         wbs_teaser
 * @author          Polyakov Konstantin
 * @license         http://www.gnu.org/licenses/gpl.html
 * @lastmodified    $Date: 2017-01-24 0:00:00 +0300 $
 *
 */

if(!defined('WB_PATH')) {
	require_once(dirname(dirname(__FILE__)).'/framework/globalExceptionHandler.php');
	throw new IllegalFileException();
}
/* -------------------------------------------------------- */

if(defined('WB_URL'))
{
	$database->query("DROP TABLE IF EXISTS `".TABLE_PREFIX."mod_wbs_teasers`");
	$mod_teasers = "CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."mod_wbs_teasers` (
		`section_id` VARCHAR(100) NOT NULL DEFAULT '0',
		 `page_id` INT NOT NULL DEFAULT '0',
		 `type` VARCHAR(15) NOT NULL DEFAULT 'parent_page',
		 `tile` LONGTEXT NOT NULL DEFAULT '',
		 `before_tile` LONGTEXT NOT NULL DEFAULT '',
		 `after_tile` LONGTEXT NOT NULL DEFAULT '',
		 `is_active` INT NOT NULL DEFAULT 1,
         `common_settings_mark` VARCHAR(100),
		 PRIMARY KEY ( `section_id` )
		 ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
	$database->query($mod_teasers);
	if ($database->is_error()) $admin->print_error($database->get_error());

	$database->query("DROP TABLE IF EXISTS `".TABLE_PREFIX."mod_wbs_teasers_type_parent`");
	$mod_teasers = "CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."mod_wbs_teasers_type_parent` (
		 `section_id` VARCHAR(100) NOT NULL DEFAULT '0',
		 `page_id` INT NOT NULL DEFAULT '0',
		 `parent_id` VARCHAR(255) NOT NULL DEFAULT 'this',
		 `except_child_ids` VARCHAR(255) NOT NULL DEFAULT '',
		 `pic_dir` VARCHAR(255),
		 PRIMARY KEY ( `section_id` )
		 ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
	$database->query($mod_teasers);
	if ($database->is_error()) $admin->print_error($database->get_error());	

	$database->query("DROP TABLE IF EXISTS `".TABLE_PREFIX."mod_wbs_teasers_type_dir`");
	$mod_teasers = "CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."mod_wbs_teasers_type_dir` (
		 `section_id` VARCHAR(100) NOT NULL DEFAULT '0',
		 `page_id` INT NOT NULL DEFAULT '0',
		 `dir` VARCHAR(255) NOT NULL DEFAULT '',
		 PRIMARY KEY ( `section_id` )
		 ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
	$database->query($mod_teasers);
	if ($database->is_error()) $admin->print_error($database->get_error());	
    
	$database->query("DROP TABLE IF EXISTS `".TABLE_PREFIX."mod_wbs_teasers_type_any_urls`");
	$mod_teasers = "CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."mod_wbs_teasers_type_any_urls` (
		 `any_urls_id` INT NOT NULL AUTO_INCREMENT,
		 `section_id` VARCHAR(100) NOT NULL DEFAULT '0',
		 `page_id` INT NOT NULL DEFAULT '0',
		 `protocol` VARCHAR(50) NOT NULL DEFAULT '',
		 `url` VARCHAR(255) NOT NULL DEFAULT '',
		 `pic_dir` VARCHAR(255) NOT NULL DEFAULT '',
		 `title` VARCHAR(255) NOT NULL DEFAULT '',
		 PRIMARY KEY ( `any_urls_id` )
		 ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
	$database->query($mod_teasers);
	if ($database->is_error()) $admin->print_error($database->get_error());	
}

/*
ALTER TABLE `wb_mod_wbs_teasers_type_any_urls` CHANGE `section_id` `section_id` VARCHAR(100) NOT NULL DEFAULT '0';
ALTER TABLE `wb_mod_wbs_teasers` ADD `common_settings_mark` VARCHAR(100) NULL DEFAULT NULL AFTER `is_active`;
*/