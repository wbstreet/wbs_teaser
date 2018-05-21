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
        $database->query("DROP TABLE IF EXISTS `".TABLE_PREFIX."mod_wbs_teasers_type_parent`");
        $database->query("DROP TABLE IF EXISTS `".TABLE_PREFIX."mod_wbs_teasers_type_dir`");
        $database->query("DROP TABLE IF EXISTS `".TABLE_PREFIX."mod_wbs_teasers_type_any_urls`");
}

/*
ALTER TABLE `wb_mod_wbs_teasers_type_any_urls` CHANGE `section_id` `section_id` VARCHAR(100) NOT NULL DEFAULT '0';
ALTER TABLE `wb_mod_wbs_teasers` ADD `common_settings_mark` VARCHAR(100) NULL DEFAULT NULL AFTER `is_active`;
*/ 
