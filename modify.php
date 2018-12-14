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

include_once(dirname(__FILE__)."/lib.class.teaser.php");
include_once(dirname(__FILE__)."/lib.functions.php");

$teaser = get_teaser($section_id);

$type_parent_page = get_teaser_type_parent_page($section_id);
$type_any_urls = get_teaser_type_any_urls($section_id);
$type_dir = get_teaser_type_dir($section_id);

if(function_exists('wbs_core_include')) wbs_core_include(['functions.js', 'windows.js', 'windows.css']);
?>

<script>
    "use strict"
    
    let section_id = <?=$section_id?>;
    let page_id = <?=$page_id?>;

    let mod_teaser = new mod_teaser_Main(section_id, page_id);
</script>

<input type="button" value="Импортировать общие настройки" onclick="W.open_by_api('window_add_common_settings', {data:{section_id:<?=$section_id?>, page_id:<?=$page_id?>}, url:mod_teaser.url_api, add_sheet:true})">

<br><br>

<?php
    if ($teaser['common_settings_mark'] !== null && $teaser['common_settings_mark'] !== '') {
        echo "<span style='color:red;'>Используются общие настройки с меткой \"{$teaser['common_settings_mark']}\"</span><br>";
    }
?>

<form action="<?php echo WB_URL; ?>/modules/wbs_teaser/save.php" method="post" name="teasers<?=$section_id?>">
    <input type="hidden" name="action" value="teaser_settings">
	<input type="hidden" name="page_id" value="<?php echo $page_id; ?>" />
	<input type="hidden" name="section_id" value="<?php echo $section_id; ?>" />
    <?php echo $admin->getFTAN(); ?> 

    <div class='block'>
    
        До плиток: <br>
        <textarea name="before_tile" id="" cols="80" rows="2"><?=$teaser['before_tile']?></textarea><br>
        
        Разметка тизерной плитки: <br>
        <textarea name="tile" id="" cols="80" rows="15"><?=$teaser['tile']?></textarea><br>
    
        После плиток: <br>
        <textarea name="after_tile" id="" cols="80" rows="2"><?=$teaser['after_tile']?></textarea><br>

        Тип тайзеров:
        <select name="type">
            <option value="parent_page" <?php if($teaser['type'] == 'parent_page') echo "selected"; ?>>Дочерние страницы</option>
            <option value="any_urls" <?php if($teaser['type'] == 'any_urls') echo "selected"; ?>>Произвольные ссылки</option>
            <option value="dir" <?php if($teaser['type'] == 'dir') echo "selected"; ?>>Картинки из паки Media</option>
            <option value="minishop" <?php if($teaser['type'] == 'minishop') echo "selected"; ?>>Товары из модуля WBS Minishop</option>
        </select><br>
        Активность: <input type="checkbox" name='is_active' <?php if($teaser['is_active'] == 1) echo "checked"; ?>>


    </div>
    
    <br>

    <div class='teaser-type-minishop block'>
        <?php
            $minishop_path = __DIR__."/../wbs_minishop/lib.class.minishop.php";
            if (file_exists($minishop_path)) {
                require_once($minishop_path);
                $clsMinishop = new ModMinishop($page_id, $section_id);
                
                $r = $clsMinishop->get_obj([
                   'is_copy_for'=>'0',
                   'prod_is_active'=>'1',
                   'order_by'=>['prod_category_id']
                ]);
                if (gettype($r) === 'string') {
                    echo $r;
                } else {
                        
                    $_prods = [];
                        
                    $sql = "SELECT `product_id` FROM {$clsTeaser->tbl_teaser_type_minishop} WHERE `is_deleted`='0' AND `section_id`=".process_value($section_id);
                    $_r = $clsTeaser->db->query($sql);
                    if ($clsTeaser->db->is_error()) {
                        echo $clsTeaser->db->get_error();
                    } else {
                        while($_r->numRows() !== 0 && $row = $_r->fetchRow()) {
                            $_prods[] = (int)$row['product_id'];
                        }
                    }
        
                    $prods = [];
                    while($r !== null && $product = $r->fetchRow()) {
                        $prods[] = $clsMinishop->get_product_vars($product);
                    }

                    foreach ($prods as $i => $prod) {
                        $checked = in_array((int)($prod['PROD_ID']), $_prods) ? "checked" : "";
                        
                        echo "<input type='checkbox' name='minishop_products[]' value='{$prod['PROD_ID']}' {$checked}>";
                        echo "<span>{$prod['PROD_TITLE']}</span>";
                        echo "<br>";
                    }
                }
            } else {
                echo "<span>Не подключён модуль магаазина! Он необходим, если Вы хотите использовать товары в слайдере</span>";
            }
        ?>
    </div>
    
    <div class='teaser-type-parent_page block'>
        ID родителя: <input type="text" name="parent_page_parent_id" value="<?=$type_parent_page['parent_id']?>"><br>
        ID детей через запятую, которых следует исключить:<br> <input type="text" name="parent_page_except_child_ids" value="<?=$type_parent_page['except_child_ids']?>">
    </div>

    <div class='teaser-type-dir block'>
        media/: <input type="text" name="dir_dir" value="<?=$type_dir['dir']?>"><br>
    </div>

    <div class='teaser-type-any_urls block'>
        <?php
        echo "<table>
                  <tr>
                      <!--<th>Результат картинки</th>-->
                      <th></th>
                      <th>Ссылка на картинку</th>
                      <th>Протокол</th>
                      <th>Ссылка на страницу</th>
                      <th>Заголовок</th>
                  </tr>";
        $i = 0;
        while ($type_any_url = $type_any_urls->fetchRow(MYSQLI_ASSOC)) {
            $vars = [
                '{WB_URL}'=>WB_URL,
                '{MEDIA_DIR}'=>MEDIA_DIRECTORY,
                '{PAGE_ID}'=>'',
                '{I}'=>$i,
                ];
            $pic_url = str_replace(array_keys($vars), array_values($vars), $type_any_url['pic_dir']);
            ?><tr>
                <!--<td><img src='{$pic_url}'></td>-->
                <td><input type="hidden" name='any_urls_id[]' value='<?=$type_any_url['any_urls_id']?>'></td>
                <td><input type='text' value='<?=$type_any_url['pic_dir']?>' name='any_urls_pic_dir[]'></td>
                <td>
                    <select name='any_urls_protocol[]'>
                        <option value="hand" <?php if($type_any_url['protocol'] == 'hand') echo "selected"; ?>>Вручную</option>
                        <option value="page" <?php if($type_any_url['protocol'] == 'page') echo "selected"; ?>>Страница</option>
                    </select>
                </td>
                <td><input type='text' value='<?=$type_any_url['url']?>' name='any_urls_url[]'></td>
                <td><input type='text' value='<?=$type_any_url['title']?>' name='any_urls_title[]'></td>
            </tr><?php
        }
        echo "</table>";
        ?>
        <div style='text-align:right'>
            <input type='number' style='width:50px' name='_count' value='1'>
             <select name='protocol'>
                <option value="hand">Вручную</option>
                <option value="page">Страница</option>
            </select>
            <input type="button" value='Добавить тизер' onclick="window.location = '<?php echo WB_URL; ?>/modules/wbs_teaser/save.php?action=any_urls_add_teaser&page_id=<?=$page_id?>&section_id=<?=$section_id?>&count='+this.form._count.value+'&protocol='+this.form.protocol.value">
        </div>
    </div>
    
    <br>
    <input type="submit" value="Сохранить">
</form>

<style>
    *[class~=block] {
        background:#ddd;
        border:1px solid #bbb;
        padding: 10px;
        border-radius: 5px;
    }
    
    *[class|="teaser-type"] {
        display: none;
    }
    
</style>

<script>
    function SelectableBlocks(options) {
        var self = this;
        options = options || [];
        // обязательные свойства: el_select
        // обязательные свойства: get_blocks, get_block
        this.options = options;

        this.show = function(block_name) {
            self.options['get_block'](block_name).style.display = 'block';
        }

        this.hide = function(block_name) {
            self.options['get_block'](block_name).style.display = 'none';
        }
        
        this.current_block_name = function(block_name) {
            if (block_name === undefined) {
                return self.options['el_select'].dataset.current_block_name;
            } else {
                self.options['el_select'].dataset.current_block_name = block_name;
            }
        }
        
        this.select = function(block_name) {
            self.hide(self.current_block_name());
            self.show(block_name);
            self.current_block_name(block_name);
        }
        
        this.select_by_select = function() {
            self.select(self.options['el_select'].value);
        }
        
        function init() {
            self.current_block_name(self.options['el_select'].value);
            self.options['el_select'].addEventListener('change', self.select_by_select);
            self.select_by_select();
        }
        
        init();

    }
    
    var sb = new SelectableBlocks({
        el_select: document.forms['teasers<?=$section_id?>']['type'],
        get_block: function(block_name) {
            return document.forms['teasers<?=$section_id?>'].querySelector('*[class~="teaser-type-'+block_name+'"]');
        }
    });

    window.addEventListener('load', function() {
        //sb.show('parent_page');
    });
</script>