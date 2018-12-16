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

//$sql = 'SELECT * FROM `'.TABLE_PREFIX.'mod_teasers` WHERE `section_id`='.(int)$section_id;
//$teaser = $database->query($sql);
//if ($database->is_error()) echo $database->get_error();
//$teaser = $teaser->fetchRow();

$loader = new Twig_Loader_Array(array(
    'tile' => $teaser['tile'],
));
$twig = new Twig_Environment($loader);

$common_vars = [
	'media_dir' => MEDIA_DIRECTORY,
 	'wb_url' => WB_URL,
];

if ($teaser['is_active'] == 1 && $teaser['type'] == 'parent_page') {
    $parent_page = get_teaser_type_parent_page($section_id);
    if ($parent_page['parent_id'] == 'this') $parent_page['parent_id'] = $parent_page['page_id'];

	echo $teaser['before_tile'];

	$sql = "SELECT * FROM `".TABLE_PREFIX."pages` WHERE `parent`={$parent_page['parent_id']} AND `visibility`='public'";
    if ($database->is_error()) {
        echo $database->get_error();
        $pages = null;
    } else { $pages = $database->query($sql); }
	$i = 0;
	$except_pages = explode(',', $parent_page['except_child_ids']);
	while($pages && $page = $pages->fetchRow(MYSQLI_ASSOC)) {
	    if (in_array($page['page_id'], $except_pages)) continue;
		$vars = [
    		'pic_dir' => WB_URL.MEDIA_DIR.'/'.$teaser['pic_dir'],
	    	'page_id' => $page['page_id'],
    		'page_title' => $page['page_title'],
	    	'menu_title' => $page['menu_title'],
		    'page_description' => $page['description'],
		    'page_url' => page_link($page['link']),
		    'I' => $i,
		];

        echo $twig->render('tile', array_merge($vars, $common_vars));
		//echo str_replace(array_keys($vars), array_values($vars), $teaser['tile']);
		$i += 1;
	}
	echo $teaser['after_tile'];
} else if ($teaser['is_active'] == 1 && $teaser['type'] == 'any_urls') {
    $any_urls = get_teaser_type_any_urls($section_id);

	echo $teaser['before_tile'];

    $any_urls = get_teaser_type_any_urls($section_id);
	$i = 0;
	while($any_url = $any_urls->fetchRow(MYSQLI_ASSOC)) {
		$loader->setTemplate('pic_dir', $any_url['pic_dir']);
		//$any_url['pic_dir'] = $twig->render('pic_dir', $common_vars); #str_replace(array_keys($common_vars), array_values($common_vars), $any_url['pic_dir']);

        if ($any_url['protocol'] == 'page') {
            if ($any_url['url'] == '') continue;
        	$sql = "SELECT * FROM `".TABLE_PREFIX."pages` WHERE `page_id`='".$database->escapeString($any_url['url'])."'";
            if ($database->is_error()) {echo $database->get_error(); $any_url['url']= '';}
            else {
            	$_page = $database->query($sql);
            	if ($_page->numRows() == 0) $any_url['url'] = '';
                else {
                    $_page = $_page->fetchRow();
                    $any_url['url'] = page_link($_page['link']);
                    $any_url['title'] = $_page['menu_title'];
                    $any_url['description'] = $_page['description'];
                    $any_url['page_id'] = $_page['page_id'];
				}
            }
        } else {
			$any_url['description'] = '';
			$any_url['page_id'] = '';
			}

		$vars = [
    		'pic_url' => $any_url['pic_dir'],
    		'page_url' => $any_url['url'],
    		'page_title' => $any_url['title'],
    		'page_description' => $any_url['description'],
    		'page_id' => $any_url['page_id'],
		    'i' => $i,
		];
		$vars['pic_url'] = $twig->render('pic_dir', array_merge($vars, $common_vars));
		//$teaser['tile'] =  str_replace(array_keys($vars), array_values($vars), $teaser['tile']);
        echo $twig->render('tile', array_merge($vars, $common_vars));
		//echo str_replace(array_keys($common_vars), array_values($common_vars), $teaser['tile']);
		$i += 1;
	}
	echo $teaser['after_tile'];
} else if ($teaser['is_active'] == 1 && $teaser['type'] == 'dir') {
    $dir = get_teaser_type_dir($section_id);

	echo $teaser['before_tile'];

	$pics_dir = preg_replace("/\.{2:10}/", '', $dir['dir']);
	$pics_dir = preg_replace("/\/+/", '/', $dir['dir']);
	$pics_dir = preg_replace("/^\//", '', $dir['dir']);
	$pics_dir = preg_replace("/\/$/", '', $dir['dir']);
	//echo $pics_dir;

    $names = scandir(WB_PATH.MEDIA_DIRECTORY.'/'.$pics_dir);
    
    $i = 0;
    foreach($names as $i=>$name) {
        if ($name == '.' || $name == '..') continue;
        if (!in_array(pathinfo($name)['extension'], ['jpeg', 'jpg', 'png', 'gif'])) continue;

    	$vars = [
		    'i' => $i,
    		'pic_url' => WB_URL.MEDIA_DIRECTORY.'/'.$pics_dir.'/'.$name,
		];
        echo $twig->render('tile', array_merge($vars, $common_vars));
		//echo str_replace(array_keys($vars), array_values($vars), $teaser['tile']);
		$i += 1;
    }
	
	echo $teaser['after_tile'];	

} else if ($teaser['is_active'] == 1 && $teaser['type'] == 'minishop') {
        
    $r = get_teaser_type_minishop($section_id);
    
    echo $teaser['before_tile'];

    $minishop_path = __DIR__."/../wbs_minishop/lib.class.minishop.php";
    if (file_exists($minishop_path)) {
        require_once($minishop_path);
        $clsMinishop = new ModMinishop($page_id, $section_id);        

        while ($r !== null && $product = $r->fetchRow()) {

            $product = $clsMinishop->get_product_vars($product);

            echo $twig->render('tile', array_merge($product, $common_vars));
        }

    }
        
    echo $teaser['after_tile'];

}

/*
Шаблоны.

--------------------------------------------------------------------------------------------------------------------
До плиток:
<nav class="slidernav">
<div id="navbtns" class="clearfix"><a href="#" class="previous"><img src="http://вашсайт.инфо-рф.рф/media/img/arrow-left.png" width="50px" alt="" /></a> <a href="#" class="next"><img src="http://вашсайт.инфо-рф.рф/media/img/arrow-right.png" width="50px" alt="" /></a></div>
</nav>

<div style='text-align:center;'>
<div class="crsl-items" data-navigation="navbtns">
<div class="crsl-wrap">

Плитка:
<div class="crsl-item">
<div class="thumbnail"><a href='{PIC_URL}' class="fm"><img src="{PIC_URL}" width="100%" /></a></div>
</div>

После плиток:
</div></div></div>

<script type="text/javascript">
$(function(){
  $('.crsl-items').carousel({
    visible: 3,
    itemMinWidth: 180,
    itemEqualHeight: 370,
    itemMargin: 9,
  });
});
</script>

Тип тайзеров: Media
Активность: Да
     ---------------
media/: portfolio

--------------------------------------------------------------------------------------------------------------------
До плиток:
<div class="img-slider">
	 <script>
                
		setTimeout(function () {
		  $("#slider1").responsiveSlides({
			auto: true,
			pager: true,
			nav: true,
			speed: 600,
			namespace: "callbacks",
			before: function () {
			  $('.events').append("<li>before event fired.</li>");
			},
			after: function () {
			  $('.events').append("<li>after event fired.</li>");
			}
		  });
	
		}, 500);
	  </script>
<div class="callbacks_container"><ul class="rslides" id="slider1">

Плитка:
        <li><img class="img-responsive" src="http://vagvicew.bget.ru/kinetrac/media/slide1/slide{I}.jpg">
       </li>

	   
После плиток:
</ul> </div><div class="clearfix"> </div></div>

Тип тайзеров: Произвольные ссылки
Активность: Да

--------------------------------------------------------------------------------------------------------------------
До плиток:


Плитка:
После плиток:

Тип тайзеров: Media
Активность: Да

--------------------------------------------------------------------------------------------------------------------
До плиток:


Плитка:
После плиток:

Тип тайзеров: Media
Активность: Да

--------------------------------------------------------------------------------------------------------------------
До плиток:


Плитка:
После плиток:

Тип тайзеров: Media
Активность: Да
*/

?>