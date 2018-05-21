<?php
/**
 *
 * @category        module
 * @package         wbs_teaser
 * @author          Polyakov Konstantin
 * @license         http://www.gnu.org/licenses/gpl.html
 * @lastmodified    $Date: 2017-02-16 0:00:00 +0300 $
 *
 */

$path_podnogami = WB_PATH.'/modules/wbs_core/include_all.php';
if (file_exists($path_podnogami)) include($path_podnogami);
else echo "<script>console.log('Модуль плиток требует модуль wbs_core')</script>";

class Teaser extends Addon {
    function __construct($db, $page_id, $section_id) {
        parent::__construct('wbs_teaser', $page_id, $section_id);
        $this->db = $db;
        $this->tbl_teaser = "`".TABLE_PREFIX."mod_wbs_teasers`";
        $this->tbl_teaser_type_parent = "`".TABLE_PREFIX."mod_wbs_teasers_type_parent`";
        $this->tbl_teaser_type_any_urls = "`".TABLE_PREFIX."mod_wbs_teasers_type_any_urls`";
        $this->tbl_teaser_type_dir = "`".TABLE_PREFIX."mod_wbs_teasers_type_dir`";
        }
    
    public function add_teaser($page_id, $section_id) {
        $before_tile = $this->db->escapeString("<div style='text-align:center;'>");
        $after_tile = $this->db->escapeString("</div>");
        $tile = $this->db->escapeString("<div class='tizerbox'>
                 <a href='{{page_url}}'><img src='{{wb_url}}/media/teaser/{{page_id}}.png'></a>
                 <br>
                 <a href='{{page_url}}'>{{page_title}}</a>
                 </div>");
        
        $sql = "INSERT INTO {$this->tbl_teaser}
                SET `page_id`=$page_id, `section_id`=$section_id,
                `before_tile`='$before_tile',
                `tile`='$tile',
                `after_tile`='$after_tile'
                ";
        $this->db->query($sql);
        
        $sql = "INSERT INTO {$this->tbl_teaser_type_parent}
                SET `page_id`=$page_id, `section_id`=$section_id";
        $this->db->query($sql);

        $sql = "INSERT INTO {$this->tbl_teaser_type_dir}
                SET `page_id`=$page_id, `section_id`=$section_id";
        $this->db->query($sql);
        }
    
    public function delete_teaser($section_id) {
        $this->db->query("DELETE FROM {$this->tbl_teaser} WHERE section_id = '$section_id'");
        $this->db->query("DELETE FROM {$this->tbl_teaser_type_parent} WHERE section_id = '$section_id'");
        $this->db->query("DELETE FROM {$this->tbl_teaser_type_dir} WHERE section_id = '$section_id'");
        }
    
    //public function update_teaser($section_id, $tile, $type, $before_tile, $after_tile, $is_active) {
    public function update_teaser($section_id, $fields) {
        //$tile = $this->db->escapeString($tile);
        //$type = $this->db->escapeString($type);
        //$before_tile = $this->db->escapeString($before_tile);
        //$after_tile = $this->db->escapeString($after_tile);
        //$is_active = $this->db->escapeString($is_active);
        //$sql = "UPDATE {$this->tbl_teaser} SET `tile`='$tile', `type`='$type', `before_tile`='$before_tile', `after_tile`='$after_tile', `is_active`='$is_active' WHERE `section_id`='$section_id'";
        $sql = build_update($this->tbl_teaser, $fields, glue_fields(['section_id'=>$section_id], 'AND'));
        return $this->db->query($sql);
    }

    public function update_type_parent_page($section_id, $parent_id, $except_child_ids) {
        $parent_id = $this->db->escapeString($parent_id);
        $except_child_ids = $this->db->escapeString($except_child_ids);
        $sql = "UPDATE {$this->tbl_teaser_type_parent} SET `parent_id`='$parent_id', `except_child_ids`='$except_child_ids' WHERE `section_id`='$section_id'";
        return $this->db->query($sql);
    }

    public function update_type_dir($section_id, $dir) {
        $dir = $this->db->escapeString($dir);
        $sql = "UPDATE {$this->tbl_teaser_type_dir} SET `dir`='$dir' WHERE `section_id`='$section_id'";
        return $this->db->query($sql);
    }
        
    public function update_type_any_urls($section_id, $any_urls_id, $protocol, $url, $pic_dir, $title) {
        $errs = '';
        $i = 0;
        while($i < count($any_urls_id)) {
            $any_urls_id[$i] = $this->db->escapeString($any_urls_id[$i]);
            $protocol[$i] = $this->db->escapeString($protocol[$i]);
            $url[$i] = $this->db->escapeString($url[$i]);
            $pic_dir[$i] = $this->db->escapeString($pic_dir[$i]);
            $title[$i] = $this->db->escapeString($title[$i]);

            $sql = "UPDATE {$this->tbl_teaser_type_any_urls} SET `protocol`='{$protocol[$i]}', `url`='{$url[$i]}', `pic_dir`='{$pic_dir[$i]}',  `title`='{$title[$i]}' WHERE `section_id`='{$section_id}' AND `any_urls_id`='{$any_urls_id[$i]}'";
            if (!$this->db->query($sql)) $errs .= '<br><br>\n\n'.$database->get_error();
            $i += 1;
        }
        return $errs;
    }
    
    public function add_type_any_urls($page_id, $section_id, $duplicate_count, $protocol, $url, $pic_dir) {
        $protocol = $this->db->escapeString($protocol);
        $url = $this->db->escapeString($url);
        $pic_dir = $this->db->escapeString($pic_dir);

        $sql = "INSERT INTO 
                   {$this->tbl_teaser_type_any_urls} (`page_id`, `section_id`, `protocol`, `url`, `pic_dir`)
                VALUES ";

        $rows = [];
        while ($duplicate_count > 0) {
            $rows[] = "('$page_id', '$section_id', '$protocol', '$url', '$pic_dir')";
            $duplicate_count -= 1;
        }
        $rows = implode(',', $rows);
        return $this->db->query($sql.$rows);

    }
    
}

$clsTeaser = new Teaser($database, $page_id, $section_id);

?>