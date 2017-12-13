<?php
function get_number($string){
    return preg_replace("/[^0-9]+/", '', mysql_escape_string($string));
}

$action = isset($_GET['action']) ? $_GET['action'] : $_POST['action'];
$section_id = get_number(isset($_GET['section_id']) ? $_GET['section_id'] : $_POST['section_id']);
$page_id = get_number(isset($_GET['page_id']) ? $_GET['page_id'] : $_POST['page_id']);

$PAGE_SECTION_FIELDS = "<input type='hidden' name='page_id' value='{$page_id}'>
<input type='hidden' name='section_id' value='{$section_id}'>
";

require('../../config.php');
require_once(WB_PATH.'/framework/functions.php');
$admin_header = false;
$update_when_modified = false;

include(__DIR__.'/lib.class.teaser.php');
$clsTeaser = new Teaser($database, $page_id, $section_id);

if (startsWith($action, 'window') || startsWith($action, 'content')) {
    $loader = new Twig_Loader_Filesystem($clsTeaser->pathTemplates);
    $twig = new Twig_Environment($loader);
}

if ($action == 'window_add_common_settings') {

    require(WB_PATH.'/modules/admin.php');

	$r = select_rows($clsTeaser->tbl_teaser, ['common_settings_mark'], glue_fields(['section_id'=>$section_id], 'AND'));
	if (gettype($r) == 'string') print_error($r);
	if ($r === null) print_error("Текущие настройки не найдены!");
	$row = $r->fetchRow(MYSQLI_ASSOC);
	//$row = get_teaser($section_id, ['common_settings_mark'], false);

	$r = select_rows($clsTeaser->tbl_teaser, ['section_id'], glue_fields(['page_id'=>'0'], 'AND'));
	if (gettype($r) == 'string') print_error($r);
	$exists_marks = [];
	while ($r && $exists_mark = $r->fetchRow(MYSQLI_ASSOC)) $exists_marks[] = $exists_mark['section_id'];

    print_success(
   	  $twig->render('add_common_settings.twig', [
		//'FTAN'=>$admin->getFTAN(),
		'props'=> $props,
		'PAGE_SECTION_FIELDS' => $PAGE_SECTION_FIELDS,
		'mark'=>$row['common_settings_mark'],
		'exists_marks'=>$exists_marks,
      ]),
   	  ['title'=>'Управление характеристиками товаров']
   );

} else if ($action == 'add_common_settings') {
    
	$mark = $clsFilter->f('mark', [['1', "Вы не указали метку!"]], 'append', '');

	if ($clsFilter->is_error()) $clsFilter->print_error();
	
	// проверяем существование метки
	$r = select_rows($clsTeaser->tbl_teaser, ['section_id'], glue_fields(['section_id'=>$mark, 'page_id'=>'0'], 'AND'));
	if (gettype($r) == 'string') print_error($r);
	
	$msg = "";

	// если общих настроек с такой меткой не существует, то:
	if ($r === null) {
	    // вынимаем текущие настройки
        $r = select_rows($clsTeaser->tbl_teaser, "`tile`, `before_tile`, `after_tile`", glue_fields(['section_id'=>$section_id], 'AND'));
    	if (gettype($r) == 'string') print_error($r);
    	if ($r === null) print_error("Текущие настройки не найдены!");
    	$fields = $r->fetchRow(MYSQLI_ASSOC);

	    // изменяем в массиве настроек page_id и section_id
	    $fields['section_id'] = $mark;
	    $fields['page_id'] = 0;

	    // вставляем запись новых общих настроек в базу
        $r = insert_rows($clsTeaser->tbl_teaser, $fields);
    	if (gettype($r) == 'string') print_error($r);
        //$r = insert_rows($clsTeaser->tbl_teaser_type_parent, ['section_id'=>$mark, 'page_id'=>0]);
    	//if (gettype($r) == 'string') print_error($r);
        //$r = insert_rows($clsTeaser->tbl_teaser_type_dir, ['section_id'=>$mark, 'page_id'=>0]);
    	//if (gettype($r) == 'string') print_error($r);
    	
    	$msg = "Добавлена новая метка.";
	}

	// обновляем запись текущих настроек, указывая в них метку.
	$r = update_row($clsTeaser->tbl_teaser, ['common_settings_mark'=>$mark], glue_fields(['section_id'=>$section_id], 'AND'));
	if (gettype($r) == 'string') print_error($r);

	print_success("Успешно! $msg Обновите данную страницу.", ['absent_fields'=>[]]);

} else {
	print_error('Невверный action!');
}

?>