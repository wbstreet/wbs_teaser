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

function get_teaser($section_id) {
    global $database;

    $sql = "SELECT * FROM `".TABLE_PREFIX."mod_wbs_teasers` WHERE `section_id`=".(int)$section_id;
    $row = $database->query($sql);
    if ($database->is_error()) {echo $database->get_error();}
    $row = $row->fetchRow();
    
    $mark = $row['common_settings_mark'];
    $type = $row['type'];
    $is_active = $row['is_active'];
        if ($row['common_settings_mark'] !== null && $row['common_settings_mark'] !== '') {
        $sql = "SELECT * FROM `".TABLE_PREFIX."mod_teasers` WHERE `section_id`='{$mark}'";
        $row = $database->query($sql);
        if ($database->is_error()) {echo $database->get_error();}
        $row = $row->fetchRow();
        $row['common_settings_mark'] = $mark;
        $row['type'] = $type;
        $row['is_active'] = $is_active;
        }
    
    return $row;
}

function get_teaser_type_parent_page($section_id) {
    global $database;
    $sql = 'SELECT * FROM `'.TABLE_PREFIX.'mod_wbs_teasers_type_parent` WHERE `section_id`='.(int)$section_id;
    $r = $database->query($sql);
    if ($database->is_error()) echo $database->get_error();
    $r = $r->fetchRow();
    return $r;
}

function get_teaser_type_dir($section_id) {
    global $database;
    $sql = 'SELECT * FROM `'.TABLE_PREFIX.'mod_wbs_teasers_type_dir` WHERE `section_id`='.(int)$section_id;
    $r = $database->query($sql);
    if ($database->is_error()) echo $database->get_error();
    $r = $r->fetchRow();
    return $r;
}

function get_teaser_type_any_urls($section_id) {
    global $database;
    $sql = 'SELECT * FROM `'.TABLE_PREFIX.'mod_wbs_teasers_type_any_urls` WHERE `section_id`='.(int)$section_id;
    $r = $database->query($sql);
    if ($database->is_error()) echo $database->get_error();
    return $r;
}

/*function replace_vars($str, $vars) {
        $_vars = [];
        foreach ($vars as $name => $value) {
                $_vars['{{'.strtoupper($name).'}}'] = $value;
        }
        return str_replace(array_keys($_vars), array_values($_vars), $vars);
}
function process_pic_dir($pic_dir, $prefix=WB_URL) {
    replace_vars($pic_dir, $vars);
    return $prefix.MEDIA_DIRECTORY.'/'.$pic_dir.'/';
}*/

?>