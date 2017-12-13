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

require('../../config.php');

// suppress to print the header, so no new FTAN will be set
$admin_header = false;
// Tells script to update when this page was last updated
$update_when_modified = true;
// Include WB admin wrapper script
require(WB_PATH.'/modules/admin.php');

$ret_url = ADMIN_URL.'/pages/modify.php?page_id='.$page_id;

$action = isset($_POST['action']) ? $_POST['action'] : $_GET['action'];

if ($action !== 'any_urls_add_teaser' && !$admin->checkFTAN()) {
	$admin->print_header();
	$admin->print_error($MESSAGE['GENERIC_SECURITY_ACCESS'], $ret_url);
}
// After check print the header
$admin->print_header();

// Include the WB functions file
require_once(WB_PATH.'/framework/functions.php');
include(dirname(__FILE__)."/lib.class.teaser.php");

if ($action == 'teaser_settings') {

    // -----
	$r = select_rows($clsTeaser->tbl_teaser, ['common_settings_mark'], glue_fields(['section_id'=>$section_id], 'AND'));
	if (gettype($r) == 'string') $admin->print_error($r, $ret_url);
	if ($r === null) $admin->print_error("Текущие настройки не найдены!", $ret_url);
	$row = $r->fetchRow(MYSQLI_ASSOC);

	if ($row['common_settings_mark'] !== null && $row['common_settings_mark'] !== '') $_section_id = $row['common_settings_mark'];
	else $_section_id = $section_id;
    // -----

    $type = $_POST['type'];
    $tile = $_POST['tile'];
    $before_tile = $_POST['before_tile'];
    $after_tile = $_POST['after_tile'];
    $is_active = $_POST['is_active'] == 'on' ? 1 : 0;

    $parent_page_page_parent_id = $_POST['parent_page_parent_id'];
    $parent_page_except_child_ids = $_POST['parent_page_except_child_ids'];

    $any_urls_id = $_POST['any_urls_id'];
    $any_urls_protocol = $_POST['any_urls_protocol'];
    $any_urls_url = $_POST['any_urls_url'];
    $any_urls_pic_dir = $_POST['any_urls_pic_dir'];
    $any_urls_title = $_POST['any_urls_title'];

    $dir_dir = $_POST['dir_dir'];
	
    $errs = '';

    //if (!$clsTeaser->update_teaser($_section_id, $tile, $type, $before_tile, $after_tile, $is_active)) $errs .= 'TEASER: '.$database->get_error();
    if (!$clsTeaser->update_teaser($_section_id, ['tile'=>$tile, 'before_tile'=>$before_tile, 'after_tile'=>$after_tile])) $errs .= 'TEASER: '.$database->get_error();
    if (!$clsTeaser->update_teaser($section_id, ['type'=>$type, 'is_active'=>$is_active])) $errs .= 'TEASER: '.$database->get_error();
    if (!$clsTeaser->update_type_parent_page($section_id, $parent_page_page_parent_id, $parent_page_except_child_ids)) $errs .= 'PARENT PAGE TYPE: '.$database->get_error();
    if (!$clsTeaser->update_type_dir($section_id, $dir_dir)) $errs .= 'DIR TYPE: '.$database->get_error();
    $r = $clsTeaser->update_type_any_urls($section_id, $any_urls_id, $any_urls_protocol, $any_urls_url, $any_urls_pic_dir, $any_urls_title);
    if ($r != '') $errs .= 'ANY URLS TYPE: '.$r;

    if ($errs !== '') $admin->print_error($errs, $ret_url);
    
    $admin->print_success("Успешно!", $ret_url);

} else if ($action == 'any_urls_add_teaser') {

    $count = $_GET['count'];
    $protocol = $_GET['protocol'];
    
    if (!$clsTeaser->add_type_any_urls($page_id, $section_id, (integer)$count, $protocol, '', '{{wb_url}}{{media_directory}}/')) $admin->print_error($database->get_error(), $ret_url);
    $admin->print_success("Успешно!", $ret_url);

} else {
    $admin->print_error("Не указано действие", $ret_url);
}

// Print admin footer
$admin->print_footer();
