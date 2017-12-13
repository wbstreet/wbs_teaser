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

if (! defined('WB_PATH')) { die('Cannot access this file directly'); }

include(dirname(__FILE__)."/lib.class.teaser.php");

$clsTeaser->add_teaser($page_id, $section_id);