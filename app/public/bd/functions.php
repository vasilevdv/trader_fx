<? session_start();
include('setup.php');
function children_pages($id,$show=''){
	global $lang,$template,$db;
	$addParams = '';
	if($show=='main'){
		$addParams = " && show_main='1'";
	}
	if($show=='left'){
		$addParams = " && show_left='1'";
	}
	if($show=='bottom'){
		$addParams = " && show_bottom='1'";
	}
	if($show=='index'){
		$addParams = " && show_index='1'";
	}
	$res = mysqli_query($db,"
		SELECT * 
		FROM ".$template."_m_nav_top 
		WHERE activation='1' && id_parent='".$id."' && lang='".$lang."'".$addParams." 
		ORDER BY num
	");
	if(mysqli_num_rows($res)>0){
		return true;
	}
	else {
		return false;
	}
}

/*
*  Функция замены изменяего пути или имени у изображения
*/
function searchImageInText($searchWay,$newWay,$type='full'){
	global $db,$template;
	$arrTable = array(
		$template."_m_nav_top",
		$template."_m_catalogue_left",
		$template."_m_news_left",
		$template."_m_partners_left",
		$template."_m_vacancy_left",
		$template."_m_discount_left",
		$template."_m_gallery_left",
	);
	for($r=0; $r<count($arrTable); $r++){
		$res = mysqli_query($db,"
			SELECT text,id
			FROM ".$arrTable[$r]."
			WHERE text LIKE '%$searchWay%'
		");
		if(mysqli_num_rows($res)>0){
			while($row = mysqli_fetch_assoc($res)){
				$text = str_replace($searchWay,$newWay,$row['text']);
				mysqli_query($db,"
					UPDATE ".$arrTable[$r]."
					SET text='".$text."'
					WHERE id=".$row['id']."
				") or die(mysqli_error($db));
			}
		}
	}
}

/*
*  Формирование сортировки по кол-ву товаров
*/
$sizePages = array(
	'catalogue' => array(5,6,7),
	'news' => array(2,4,6),
	'discounts' => array(1,2,3),
	'gallery' => array(2,4,6),
	'partners' => array(2,4,6),
	'vacancy' => array(1,2,3),
);
function formatSizeGoods($size=0,$type='catalogue',$show=true){
	global $showCounts,$sizePages;
	$li = '';
	if($show){
		if(count($sizePages[$type])>0){
			$li = '<li>Показывать по</li>';
			for($e=0; $e<count($sizePages[$type]); $e++){
				$current = '';
				if(empty($size)){
					if($e==0){
						$current = ' class="current"';
					}
				}
				else {
					if($size==$sizePages[$type][$e]){
						$current = ' class="current"';
					}
				}
				$li .= '<li'.$current.'><a href="'.full_link_filter('size',$sizePages[$type][$e]).'">'.$sizePages[$type][$e].'</a></li>';
			}
			$showCounts = '<div class="showCounts">
				<ul>
					'.$li.'
				</ul>
			</div>';
		}
	}
}

/*
*  Удаление параметра из ссылки
*/
function cut_param_link($nameParam){
	global $pageParams;
	$fullLinkParam = '';
	$pageArrayNew = $pageParams;
	$paramFound = false;
	$arr = array();
	if(count($pageArrayNew)>0){
		foreach($pageArrayNew as $key => $value){
			$ex_pageArray = explode('=',$pageArrayNew[$k]);
			if(is_array($nameParam)){
				if(in_array($key,$nameParam)){
					unset($pageArrayNew[$key]);
				}
			}
			else {
				if($key==$nameParam){
					unset($pageArrayNew[$key]);
				}
			}
		}
		foreach($pageArrayNew as $k => $v){
			array_push($arr,$k.'='.$v);
		}
		$fullLinkParam = implode('&',$arr);
	}
	return $fullLinkParam;
}

function nav_install($id_parent){
	global $lang,$template,$list,$db;
	$res = mysqli_query($db,"
		SELECT * 
		FROM ".$template."_m_nav_top 
		WHERE activation='1' && show_main='1' && id_parent='".$id_parent."' && lang='".$lang."' 
		ORDER BY num
	");
	if(mysqli_num_rows($res)>0){
		$list .= '<ul>';
		while($row = mysqli_fetch_assoc($res)){
			$link = '';
			if(empty($row['home'])) {
				$link = $row['link'];
			}
			$linkOfPage == $row['link'] ? $class = 'class="current"' : $class='';
			if(!$linkOfPage && $row['home'] == '1') {
				$class = 'class="current"';
			}
			$bar = '';
			if(children_pages($row['id'],'main') && $id_parent==0){
				$bar = '<i class="fa fa-bars" aria-hidden="true"></i>';
			}
			$cat = '';
			if($row['modules']==11){
				if(!empty($class)){
					$class = 'class="current cat"';
				}
				else {
					$cat = 'class="cat"';
				}
			}
			$list .= '<li '.$class.$cat.'><a href="/'.$link.'"><span>'.$bar.$row['name'].'</span></a>';
			nav_install($row['id']);
		}
		$list .= '</li>';
		$list .= '</ul>';
	}
	return $list;
}

/*
*  Формирование пагинатора
*/
$formsPage = array("Оба варианта","Применение canonical","Формирование title");

/*
*  
*  Формирование ссылки для фильтра v.1.0
*
*  $pageArray - массив параметров ссылки
*  $nameParam - наименование параметра
*  $rowsValue - значение из массива параметра
*  $checkParam - свойство, определяющее снятие параметра
   по клику на выбранный. false - запрещает, true - разрешает
*  
*/
function full_link_filter($nameParam,$rowsValue='',$checkParam=false){
	global $pageArray;
	// $fullLinkParam = $_SERVER['REQUEST_URI'];
	$fullLinkParam = $_SERVER['QUERY_STRING'];
	$pageArrayNew = $pageArray;
	$paramFound = false;
	if(count($pageArrayNew)>0){
		for($k=0; $k<count($pageArrayNew); $k++){
			$ex_pageArray = explode('=',$pageArrayNew[$k]);
			if($ex_pageArray[0]==$nameParam){
				if(empty($rowsValue) || $checkParam && $rowsValue==$ex_pageArray[1]){
					unset($pageArrayNew[$k]);
					sort($pageArrayNew);
				}
				else {
					$pageArrayNew[$k] = $nameParam.'='.$rowsValue;
				}
				$paramFound = true;
				break;
			}
		}
		if(!$paramFound){
			if(!empty($rowsValue)){
				$pageArrayNew[] = $nameParam.'='.$rowsValue;
			}
		}
		if(count($pageArrayNew)>0){
			$fullLinkParam = '/'.$fullLinkParam."?".implode('&',$pageArrayNew);
		}
		else {
			$fullLinkParam = '/'.$fullLinkParam;
		}
		if($singleParam){
			$fullLinkParam = '/'.$_SERVER['QUERY_STRING']."?".$nameParam."=".$rowsValue;
		}
	}
	else {
		if(!empty($rowsValue)){
			$fullLinkParam = '/'.$fullLinkParam.'?'.$nameParam.'='.$rowsValue;
		}
		else {
			$fullLinkParam = '/'.$fullLinkParam;
		}
	}
	return $fullLinkParam;
}

function translateFunction($translate){
	global $lang,$_TRANSLATE;
	if($lang==0){
		return $translate;
	}
	else {
		$n = 0;
		for($c=0;$c<count($_TRANSLATE);$c++){
			if($_TRANSLATE[$c][0]==$translate){
				if(!empty($_TRANSLATE[$c][$lang])){
					return $_TRANSLATE[$c][$lang];
					$n = $n+1;
					break;
				}
			}
		}
		if($n==0){
			return $translate;
		}
	}
}

function translateFunctionEcho($translate){
	global $lang,$_TRANSLATE;
	if($lang==0){
		echo $translate;
	}
	else {
		$n = 0;
		for($c=0;$c<count($_TRANSLATE);$c++){
			if($_TRANSLATE[$c][0]==$translate){
				if(!empty($_TRANSLATE[$c][$lang])){
					echo $_TRANSLATE[$c][$lang];
					$n = $n+1;
					break;
				}
			}
		}
		if($n==0){
			echo $translate;
		}
	}
}

/*
*  Поиск и перевод любой ссылки в тексте в ссылку
*/
function link_it($text){
	// $text= preg_replace("/(^|[\n ])([\w]*?)((ht|f)tp(s)?:\/\/[\w]+[^ \,\"\n\r\t<]*)/is", "$1$2&lt;a href=\"$3&quot;>$3&lt;/a&gt;", $text);
	$text= preg_replace("/(^|[\n ])([\w]*?)((ht|f)tp(s)?:\/\/[\w]+[^ \,\"\n\r\t<]*)/is", "$1$2<a target=\"_blank\" href=\"/away.php?to=$3\">$3</a>", $text);
	$text= preg_replace("/(^|[\n ])([\w]*?)((www|ftp)\.[^ \,\"\t\n\r<]*)/is", "$1$2<a target=\"_blank\" href=\"/away.php?to=http://$3\">$3</a>", $text);
	$text= preg_replace("/(^|[\n ])([a-z0-9&\-_\.]+?)@([\w\-]+\.([\w\-\.]+)+)/i", "$1<a href=\"mailto:$2@$3\">$2@$3</a>", $text);
	$text = htmlspecialchars_decode($text);
	$text = nl2br($text);
	return($text);
}

/*
*  Генератор случайного пароля
*/
function generate_password($number) {  
    $arr = array('a','b','c','d','e','f',  
                 'g','h','i','j','k','l',  
                 'm','n','o','p','r','s',  
                 't','u','v','x','y','z',  
                 'A','B','C','D','E','F',  
                 'G','H','I','J','K','L',  
                 'M','N','O','P','R','S',  
                 'T','U','V','X','Y','Z',  
                 '1','2','3','4','5','6',  
                 '7','8','9','0');  
    // Генерируем пароль  
    $pass = "";  
    for($i = 0; $i < $number; $i++){  
      // Вычисляем случайный индекс массива  
      $index = rand(0, count($arr) - 1);  
      $pass .= $arr[$index];  
    }
    return $pass;  
}

/**
*  Последняя активность
**/
function lastOnSite($user,$activity){
	global $template,$db;
	$user = (int)$user;
	mysqli_query($db,"
		UPDATE ".$template."_users
		SET was_on_site='".time()."',active='".$activity."'
		WHERE id='".$user."' && activation='1'
	");
}

// Мытье рук или чистка зубов :)
function clearValueText($value){
	global $db;
	$value = stripslashes($value);
	$value = htmlspecialchars($value);
	$value = trim($value);
	$value = mysqli_real_escape_string($db,$value);
	return $value;
}

// Форматирование текста
function replaceEmpty($text){
	$arr = array("<p><br></p>");
	$cut = str_replace($arr, "", $text);
	return $cut;
}

// Форматирование текста 2
function formatTextClear($text){
	$cut = strip_tags($text);
	if(empty($cut)){
		return $cut;
	}
	else {
		return $text;
	}
}

function formatText($text){
	$substr_count = substr_count($text,"<p><br></p>");
	if($substr_count==1){
		replaceEmpty($text);
	}
	else {
		return $text;
	}
}

// Инициализация главной навигации
function initialMenu($id=0,$local='top'){
	global $db,$template,$linkOfPage,$linkLastStruct,$lang;
	$show = " && show_bottom='1'";
	if($local=='top'){
		$show = " && show_main='1'";
	}
	if($local=='left'){
		$show = " && show_left='1'";
	}
	if($local=='index'){
		$show = " && show_index='1'";
	}
	$res = mysqli_query($db,"
		SELECT * 
		FROM ".$template."_m_nav_top 
		WHERE activation='1'".$show." && id_parent='".$id."' && lang=".$lang."
		ORDER BY num
	");
	if(mysqli_num_rows($res)>0){
		echo '<ul>';
		while($row = mysqli_fetch_assoc($res)){
			$arrayClass = [];

			if($id==0){
				$linkOfPage == $row['link'] ? array_push($arrayClass,'current') : '';
				if(!$linkOfPage && $row['home'] == '1') {
					if(!in_array('current',$arrayClass)){
						array_push($arrayClass,'current');
					}
				}
			}
			else {
				$linkLastStruct == $row['link'] ? array_push($arrayClass,'current') : '';
			}
			
			if(!empty($row['add_highlight'])){
				array_push($arrayClass,'sale');
			}
			
			$class = '';
			if(count($arrayClass)>0){
				$class = ' class="'.implode(' ',$arrayClass).'"';
			}
			echo '<li '.$class.'><a href="'.get_link_page($row['id']).'"><span>'.$row['name'].'</span></a>';
			initialMenu($row['id'],$local);
		}
		if($id>0){
			echo '</ul></li>';		
		}
		else {
			echo '</li></ul>';
		}
	}
	else {
		if($id>0){
			echo '</li>';
		}
	}
}

/**
*  Подсчёт всех товаров в разделе
**/
function count_goods($array){
	global $db,$template,$lang;
	
	if(is_array($array)){
		if(count($array)>0){
			$res = mysqli_query($db,"
				SELECT id
				FROM ".$template."_m_catalogue_left
				WHERE p_main IN (".implode(',',$array).")
			") or die(mysqli_error($db));
			return mysqli_num_rows($res);
		}
		else {
			return 0;
		}
	}
}

// Обрезка длинного текста
function cutString($string, $maxlen = 250) {
    $len = (mb_strlen($string) > $maxlen)
        ? mb_strripos(mb_substr($string, 0, $maxlen), ' ')
        : $maxlen
    ;
    $cutStr = mb_substr($string, 0, $len);
    return (mb_strlen($string) > $maxlen)
        ? '' . $cutStr . '...'
        : '' . $cutStr . ''
    ;
}

// Получение ссылки для меню
function getLinks($mod,$home,$simple,$module){
	if($home == '1') {
		$link = '';
	}
	else if($mod == 1){
		$link = 'gallery/';
	}
	else if($mod == 2){
		if($module == 0){
			$link = 'catalogue/';
		}
		else {
			$link = 'catalogue/';
		}
	}
	else if($mod == 3){
		if($module == 0){
			$link = 'news/';
		}
		else {
			$link = 'news/'.$simple;
		}
	}
	else if($mod == 4){
		if($module == 0){
			$link = 'discount/';
		}
		else {
			$link = 'discount/'.$simple;
		}
	}
	else if($mod == 6){
		if($module == 0){
			$link = 'guestbook/';
		}
		else {
			$link = 'guestbook/';
		}
	}
	else if($mod == 7){
		if($module == 0){
			$link = 'feedback/';
		}
		else {
			$link = 'feedback/'.$simple;
		}
	}
	else if($mod == 8){
		if($module == 0){
			$link = 'vacancy/';
		}
		else {
			$link = 'vacancy/'.$simple;
		}
	}
	else if($mod == 9){
		if($module == 0){
			$link = 'partners/';
		}
		else {
			$link = 'partners/'.$simple;
		}
	}
	else {
		$link = $simple.'/';
	}
	return $link;
}

/**
*  Формирование тысячи у цен
**/
function price_cell($price,$n) {
	$pr = number_format($price, $n, ',', ' ');
	return $pr;
}

/**
*  Получение последнего значения 
**/
function get_last($table,$parent=false,$id=0,$type=0,$name_param='data[num]'){
	global $db,$template,$lang;
	$tableName = $template."_m_nav_top";
	$p_main = "id_parent";
	if(!empty($table)){
		$tableName = $table;
		if($table!=$template."_m_nav_top"){
			$p_main = "p_main";
		}
	}
	$addParam = '';
	if($parent!==false){
		$addParam = " && ".$p_main."='".$parent."'";
	}
	$rows = mysqli_query($db,"
		SELECT num 
		FROM ".$tableName."
		WHERE lang=".$lang.$addParam."
		ORDER BY num DESC 
		LIMIT 1
	");
	if(mysqli_num_rows($rows)>0){
		$num = mysqli_fetch_assoc($rows);
		if(empty($id)){
			if(!empty($type)){
				$param = '<input type="hidden" name="'.$name_param.'" value="'.($num['num']+1).'">';
			}
			else {
				$param = $num['num']+1;
			}
		}
	}
	else {
		if(empty($id)){
			if(!empty($type)){
				$param = '<input type="hidden" name="'.$name_param.'" value="1">';
			}
			else {
				$param = 1;
			}
		}
	}
	return $param;
}

/**
*  Получение данных у произвольных параметров
**/
function get_param_values($id,$method,$value){
	global $db,$template,$lang;
	$param_value = '<input style="background:none;box-shadow:none;border:none;padding:0;" type="text" disabled="disabled" class="text" value="Данные не заполнены">';
	$res = mysqli_query($db,"
		SELECT *
		FROM ".$template."_table_values
		WHERE p_main='".$id."' && activation=1 && lang=".$lang."
		ORDER BY num
	");
	if(mysqli_num_rows($res)>0){
		if($method==3){
			$sel = '';
			// if(empty($value)){
				// $sel = ' selected="selected"';
			// }
			$param_value = '<select style="width:229px;" name="params['.$id.']"><option'.$sel.' value="0">Не выбран</option>';
		}
		if($method==4){
			$param_value = '<select multiple="multiple" class="multiple" size="5" style="width:229px;margin-top:4px;height:auto!important;">';
		}
		while($row = mysqli_fetch_assoc($res)){
			if($method==3){
				$selected = '';
				echo $value;
				if($value==$row['id']){
					$selected = ' selected="selected"';
				}
				$param_value .= '<option'.$selected.' value="'.$row['id'].'">'.$row['name'].'</option>';
			}
			if($method==4){
				$selected = '';
				$ex_value = explode(',',$value);
				if(in_array($row['id'],$ex_value)){
					$selected = ' selected="selected"';
				}
				$param_value .= '<option'.$selected.' value="'.$row['id'].'">'.$row['name'].'</option>';
			}
		}
		if($method==3 || $method==4){
			$param_value .= '</select>';
		}
	}
	return $param_value;
}

/**
*  Получение данных активных модулей
**/
function get_modules($value=0,$word_name=''){
	global $db,$template,$lang;
	$add = "";
	if(!empty($word_name)){
		$add = " && word_name='".$word_name."'";
	}
	if(!empty($value)){
		$add = " && id='".intval($value)."'";
	}
	$result = [];
	$res = mysqli_query($db,"
		SELECT *
		FROM ".$template."_tab_modules
		WHERE m_activation=1".$add."
	");
	if(mysqli_num_rows($res)>0){
		if(mysqli_num_rows($res)>1){
			while($row = mysqli_fetch_assoc($res)){
				array_push($result,$row);
			}
		}
		else {
			$result = mysqli_fetch_assoc($res);
		}
	}
	return $result;
}

$date_week_full = ["Воскресенье","Понедельник","Вторник","Среда","Четверг","Пятница","Суббота"];
$date_week_cut = ["ВС","ПН","ВТ","СР","ЧТ","ПТ","СБ"];

/**
*  Получение дня недели
**/
function get_day_week($value,$type='full'){
	global $date_week_full,$date_week_cut;
	$ex_date = explode('-',$value);
	if(count($ex_date)==3){
		$value = strtotime($value);
		if($type=='full'){
			$w = $date_week_full[date("w",$value)];
		}
		else {
			$w = $date_week_cut[date("w",$value)];
		}
	}
	else {
		if($type=='full'){
			$w = $date_week_full[date("w",$value)];
		}
		else {
			$w = $date_week_cut[date("w",$value)];
		}
	}
	return $w;
}

/**
*  Получение даты
**/
function get_date($value,$type='clear',$empty='н/д'){
	$ex_date = explode('-',$value);
	if(count($ex_date)==3){
		if($type=='clear'){
			$date = $value;
		}
		if($type=='point'){
			$date = date_point($value);
		}
		if($type=='rus'){
			$date = date_rus($value);
		}
		if($type=='rus_cut'){
			$date = date_rus_short($value);
		}
		if($type=='rus_cut_year'){
			$date = date_rus_short($value,true);
		}
	}
	else {
		if(!empty($value)){
			if($type=='clear'){
				$date = date("Y-m-d",$value);
			}
			if($type=='point'){
				$date = date_point(date("Y-m-d",$value));
			}
			if($type=='rus'){
				$date = date_rus(date("Y-m-d",$value));
			}
			if($type=='rus_cut'){
				$date = date_rus_short(date("Y-m-d",$value));
			}
			if($type=='rus_cut_year'){
				$date = date_rus_short(date("Y-m-d",$value),true);
			}
		}
		else {
			$date = $empty;
		}
	}
	return $date;
}

/**
*  Получение времени
**/
function get_time($value,$count=3,$empty='н/д',$schema=''){
	$ex_time = explode(':',$value);
	if(count($ex_time)==3){
		if($count==1){
			$time = $ex_time[0];
		}
		if($count==2){
			$time = $ex_time[0].':'.$ex_time[1];
		}
		if($count==3){
			$time = $ex_time[0].':'.$ex_time[1].':'.$ex_time[2];
		}
	}
	else {
		if(!empty($value)){
			if($count==1){
				$time = date("H",$value);
			}
			if($count==2){
				$time = date("H:i",$value);
			}
			if($count==3){
				$time = date("H:i:s",$value);
			}
		}
		else {
			$time = $empty;
		}
	}
	return $time;
}

/**
*  Формирование даты dd месяц yyyy
**/
function date_rus($data) {
	$mounth = substr($data, 5, 2);
	if ($mounth=='01'){$mounth = 'января';}
	if ($mounth=='02'){$mounth = 'февраля';}
	if ($mounth=='03'){$mounth = 'марта';}
	if ($mounth=='04'){$mounth = 'апреля';}
	if ($mounth=='05'){$mounth = 'мая';}
	if ($mounth=='06'){$mounth = 'июня';}
	if ($mounth=='07'){$mounth = 'июля';}
	if ($mounth=='08'){$mounth = 'августа';}
	if ($mounth=='09'){$mounth = 'сентября';}
	if ($mounth=='10'){$mounth = 'октября';}
	if ($mounth=='11'){$mounth = 'ноября';}
	if ($mounth=='12'){$mounth = 'декабря';}
	$year = substr($data, 0, 4);
	$day = substr($data, 8, 2);
return $day.' '.$mounth.' '.$year;
}

// Формирование даты dd месяц
function date_rus_short($data,$_year=false) {
	$mounth = substr($data, 5, 2);
	if ($mounth=='01'){$mounth = 'янв';}
	if ($mounth=='02'){$mounth = 'фев';}
	if ($mounth=='03'){$mounth = 'мар';}
	if ($mounth=='04'){$mounth = 'апр';}
	if ($mounth=='05'){$mounth = 'мая';}
	if ($mounth=='06'){$mounth = 'июн';}
	if ($mounth=='07'){$mounth = 'июл';}
	if ($mounth=='08'){$mounth = 'авг';}
	if ($mounth=='09'){$mounth = 'сен';}
	if ($mounth=='10'){$mounth = 'окт';}
	if ($mounth=='11'){$mounth = 'ноя';}
	if ($mounth=='12'){$mounth = 'дек';}
	$year = '';
	if($_year){
		$year = ", \'".substr($data, 2, 2);
	}
	$day = substr($data, 8, 2);
return $day.' '.$mounth.$year;
}

// Формирование даты yyyy-mm-dd из акции
function date_discount($data) {
	$mounth = substr($data, 3, 2);
	$year = substr($data, 6, 4);
	$day = substr($data, 0, 2);

	return $year.'-'.$mounth.'-'.$day;
}

// Формирование даты dd.mm.yy
function date_point($data) {
	$mounth = substr($data, 5, 2);
	$year = substr($data, 0, 4);
	$day = substr($data, 8, 2);
	
	return $day.'.'.$mounth.'.'.$year;		
}

/*
* Формирование ссылки на картинку 
*/
function show_image(
	$img='', 
	$type='',
	$args = []
){
	$arr = [
		'image_id'    => '',
		'image_class' => '',
		'wrap_before' => '',
		'wrap_after'  => '',
		'image_src'   => false,
		'title_image' => '',
		'alt_image'   => '',
		'params'      => '',
		'no_photo'    => '<span class="no_photo_block"><i class="fa fa-camera"></i></span>',
	];
	
	/*
	*  Если свойства не заданы при вызове функции, присваиваем стандартные 
	*/
	if(!isset($args['image_id'])){
		$args['image_id'] = $arr['image_id'];
	}
	
	if(!isset($args['image_class'])){
		$args['image_class'] = $arr['image_class'];
	}
	
	if(!isset($args['wrap_before'])){
		$args['wrap_before'] = $arr['wrap_before'];
	}
	
	if(!isset($args['wrap_after'])){
		$args['wrap_after'] = $arr['wrap_after'];
	}
	
	if(!isset($args['title_image'])){
		$args['title_image'] = $arr['title_image'];
	}
	
	if(!isset($args['alt_image'])){
		$args['alt_image'] = $arr['alt_image'];
	}
	
	if(!isset($args['params'])){
		$args['params'] = $arr['params'];
	}
	
	if(!isset($args['no_photo'])){
		$args['no_photo'] = $arr['no_photo'];
	}
	
	$arrImageSizes = ['small','medium','large','sc','bc'];
	$way = '/admin_2/uploads/';
	$fullNameImage = $args['no_photo'];
	if(!empty($img)){
		$ex_images = explode(',',$img);
		if(count($ex_images)>1){
			$type_image = 5;
			if(!empty($type)){
				$type_image = array_search($type,$arrImageSizes);
			}
			$ex_image = $ex_images[$type_image];
			$ex_image = explode('.',$ex_image);
		}
		else {
			$ex_image = explode('.',$img);
		}
		$name_image = $ex_image[0];
		$ext = $ex_image[count($ex_image)-1];
		
		$typeName = '';
		if(!empty($type)){
			$name_image = str_replace('_'.$type,'',$name_image);
			$typeName = '_'.$type;
		}
		
		$params = $args['params'];
		
		/*
		*  Если изображение в тэге img
		*/
		if($args['image_src']){
			/*
			*  Если у изображения есть ID
			*/
			$image_id = '';
			if(!empty($args['image_id'])){
				$image_id = ' id="'.$args['image_id'].'"';
			}
			
			/*
			*  Если у изображения есть class
			*/
			$image_class = '';
			if(!empty($args['image_class'])){
				$image_class = ' class="'.$args['image_class'].'"';
			}
			
			/*
			*  Если у изображения есть alt
			*/
			$alt = '';
			if(!empty($args['alt_image'])){
				$alt = ' alt="'.$args['alt_image'].'"';
			}
			
			/*
			*  Если у изображения есть title
			*/
			$title = '';
			if(!empty($args['title_image'])){
				$title = ' title="'.$args['title_image'].'"';
			}
			
			$fullNameImage = $args['wrap_before'].'<img'.$image_id.$image_class.' src="'.$way.$name_image.$typeName.'.'.$ext.$params.'"'.$alt.$title.'>'.$args['wrap_after'];
		}
		else {
			/*
			*  Если нужно вывести просто ссылку на изображение
			*/
			$fullNameImage = $way.$name_image.$typeName.'.'.$ext.$params;		
		}
	}
	return $fullNameImage;
}

/*
*  Извлекаем массив значений атрибутов для товара
*/
function get_attributes(
	$id, 
	$array=true,
	$type=0,
	$args = []
){
	global $db,$template,$lang;
	$arr = [
		'id_attr'     => 0,
		'wrap_before' => '<li class="table">',
		'wrap_after'  => '</li>',
		'show_goods'  => true,
		'colon'  	  => true,
		'order_by'    => 'num',
		'desc'    	  => '',
		'table_style' => false,
		'wrappers'    => [
			"name" => ['<div class="name ceil">','</div>'],
			"text" => ['<div class="text ceil">','</div>'],
		],
	];
	
	/*
	*  Если свойства не заданы при вызове функции, присваиваем стандартные 
	*/
	$params_search = '';
	$arr_params_search = [];
	if(!isset($args['id_attr'])){
		$args['id_attr'] = $arr['id_attr'];
	}
	if(!isset($args['wrap_before'])){
		$args['wrap_before'] = $arr['wrap_before'];
	}
	if(!isset($args['wrap_after'])){
		$args['wrap_after'] = $arr['wrap_after'];
	}
	if(!isset($args['show_goods'])){
		$args['show_goods'] = $arr['show_goods'];
	}
	if(!isset($args['colon'])){
		$args['colon'] = $arr['colon'];
	}
	if(!isset($args['order_by'])){
		$args['order_by'] = $arr['order_by'];
	}
	if(!isset($args['desc'])){
		$args['desc'] = $arr['desc'];
	}
	if(!isset($args['table_style'])){
		$args['table_style'] = $arr['table_style'];
	}
	if(!isset($args['wrappers'])){
		$args['wrappers'] = $arr['wrappers'];
	}
	
	if(!empty($args['id_attr'])){
		array_push($arr_params_search,"id='".$args['id_attr']."'");
	}
	
	$showAttributes = [];
	if(count($arr_params_search)>0){
		$params_search = implode(' && ',$arr_params_search);
	}
	$res = mysqli_query($db,"
		SELECT *
		FROM ".$template."_tech_chars
		WHERE activation=1 && p_main=".$id." && show_goods=1 && lang=".$lang.$params_search."
		ORDER BY ".$args['order_by']." ".$args['desc']."
	");
	if(mysqli_num_rows($res)>0){
		while($row = mysqli_fetch_assoc($res)){
			if($array){
				array_push($showAttributes,$row);
			}
			else {
				/**
				*  Двоеточие
				**/
				$colon = '';
				if($args['colon']){
					$colon = ':';
				}
				
				/**
				*  Табличный стиль оформления: name | text
				**/
				if($args['table_style']){
					$p  = $args['wrap_before'];
					$p .= $args['wrappers']['name'][0].$row['name'].$colon.$args['wrappers']['name'][1];
					$p .= $args['wrappers']['text'][0].$row['text'].$args['wrappers']['text'][1];
					$p .= $args['wrap_after'];
				}
				else {
					$p = $args['wrap_before'].$row['name'].$colon.' '.$row['text'].$args['wrap_after'];
				}				
				array_push($showAttributes,$p);
			}
		}
	}
	return $showAttributes;
}

// Обрезка строки до указанных символов
// function cut_word($text,$length) {
	// $text3 = strlen($text);
	// $length >= $text3 ? $text2 = $text : $text2 = (substr($text,0,$length)).'...';
	// return $text2;
// }

/*
*  Родительские страницы
*/
function parentsFunction($id_parent,$id_pages=0,$parts='&mdash;&nbsp;',$modues_id=0){
	global $db,$template,$lang,$partSelect;
	$addModules = "";
	if(!empty($modues_id)){
		$ex_modues_id = explode(',',$modues_id);
		$addModules = " && modules IN (".implode(',',$ex_modues_id).")";
	}
	$r = mysqli_query($db,"
		SELECT *
		FROM ".$template."_m_nav_top
		WHERE lang=".$lang." && id_parent='".$id_parent."'".$addModules."
		ORDER BY num
	") or die(mysqli_error($db));
	if(mysqli_num_rows($r)>0){
		while($rw = mysqli_fetch_assoc($r)){
			$selected = '';
			if($id_pages==$rw['id']){
				$selected = ' selected="selected"';
			}
			$disabled = '';
			if(!empty($modues_id)){
				if($rw['modules']==11){
					$disabled = ' disabled="disabled"';
				}
				$selected = '';
				if($id_pages==$rw['id']){
					$selected = ' selected="selected"';
				}
			}
			$partSelect .= '<option'.$selected.$disabled.' value="'.$rw['id'].'">'.$parts.' '.$rw['name'].'</option>';
			parentsFunction($rw['id'],$id_pages,'&mdash;&nbsp;'.$parts,$modues_id);
		}
	}
	return $partSelect;
}

/*
*  Подразделы каталога
*/
function parentsProductsFunction($id_parent,$id_pages,$module=11,$parts='&mdash;&nbsp;'){
	global $db,$template,$lang,$simulars;
	
	if($module==2){
		$table = $template."_m_catalogue_left";
		$whereParam = " && p_main='".$id_parent."'";
	}
	else {
		$table = $template."_m_nav_top";
		$whereParam = " && id_parent='".$id_parent."' && modules IN (2,11)";
	}

	$r = mysqli_query($db,"
		SELECT *
		FROM ".$table."
		WHERE lang=".$lang.$whereParam."
		ORDER BY num
	") or die(mysqli_error($db));
	if(mysqli_num_rows($r)>0){
		while($rw = mysqli_fetch_assoc($r)){
			if($module==11){
				if(haveGoodsPart($rw['id'])){
					$simulars .= '<option disabled="disabled">'.$parts.' '.$rw['name'].'</option>';
				}
				parentsProductsFunction($rw['id'],$id_pages,$rw['modules'],'&mdash;&nbsp;'.$parts);
			}
			else {
				$r = mysqli_query($db,"
					SELECT *
					FROM ".$table."
					WHERE lang=".$lang.$whereParam."
					ORDER BY num
				") or die(mysqli_error($db));
				if(mysqli_num_rows($r)>0){
					while($rw = mysqli_fetch_assoc($r)){
						$selected = '';
						if($id_pages==$rw['id']){
							$selected = ' selected="selected"';
						}
						// $parts = str_replace('&mdash;',' ',$parts);
						$parts = '';
						$simulars .= '<option'.$selected.' value="'.$rw['id'].'">'.$parts.' '.$rw['id'].' - '.$rw['name'].'</option>';
					}
				}
			}
		}
	}
	return $simulars;
}

/**
*  Проверка на наличие товаров у раздела
**/
function haveGoodsPart($id){
	global $db,$template,$lang;
	$result = false;
	$res = mysqli_query($db,"
		SELECT *
		FROM ".$template."_m_catalogue_left
		WHERE activation=1 && lang=".$lang." && p_main='".$id."'
	");
	if(mysqli_num_rows($res)>0){
		$result = true;
	}
	
	return $result;
}

/**
*  Вывод похожих товаров
**/
function get_similars($id,$count=5,$title='Вам могут понравиться'){
	global $db,$arrayLinkWay,$template,$lang,$params;	
	$similars = '<div id="products">';
	$similars .= '<div class="inner_block">';
	$similars .= '<div class="catalogue_list">';
	$similars .= '<div class="title_block">'.$title.'</div>';
	$similars .= '<div class="list_block">';
	
	$res = mysqli_query($db,"
		SELECT c.*
		FROM ".$template."_similar_products AS s
		LEFT JOIN ".$template."_m_catalogue_left AS c
		ON c.id=s.good_id && c.activation=1
		WHERE s.activation=1 && s.lang=".$lang." && s.p_main='".$id."'
		ORDER BY s.num
		LIMIT ".$count."
	");
	if(mysqli_num_rows($res)>0){
		while($row = mysqli_fetch_assoc($res)){
			$arrayLinkWay = [];
			productArrayLink($row['p_main']);
			$linkWay = "";
			if(count($arrayLinkWay)>0){
				for($c=count($arrayLinkWay)-1; $c>=0; $c--){
					$linkWay .= "/".$arrayLinkWay[$c]['link'];
				}
			}			
			$link = "/".$params[0].$linkWay."/".$row["link"];
			
			/**
			*  Цена
			**/
			$price_full = '';
			if(empty($row['hide_price'])){
				if(!empty($row['price'])){
					if(!empty($row["price_offer"])){
						$price_full = '<div class="price"><span class="old">'.price_cell($row['price'],0).' '.$_MCUR[$row['currency']].'</span><span class="offer">'.price_cell($row['price_offer'],0).' '.$_MCUR[$row['currency']].'</span></div>';
					}
					else {
						$price_full = '<div class="price">'.price_cell($row['price'],0).'</div>';
					}
				}
			}

			/**
			*  Изображение
			**/
			$args = [ 
				'wrap_before' => '<div class="image"><div class="table"><div class="middle">',
				'wrap_after'  => '</div></div></div>',
				'image_src'   => true,
				'alt_image'   => $row['name'],
				'no_photo'    => '<div class="image"><div class="table"><div class="middle"><span class="no_photo_block"><i class="fa fa-camera"></i></span></div></div></div>',
			];				
			$image = show_image($row['images'],'sc',$args);
			
			/**
			*  Название товара
			**/
			$name = '<div class="name"><a href="'.$link.'">'.$row['name'].'</a></div>';
			
			/**
			*  Метка
			**/
			$flag = '';
			if(!empty($row["metka"])){
				$flag = '<span>'.$_MCAT[$row["metka"]].'</span>';
			}
			
			/**
			*  Кнопка избранное
			**/
			$favorite = '';
			if(!empty($row['btn_favorite'])){
				$favorite = '<div class="favorite"></div>';
			}
			
			$similars .= '<div class="item">
				<div class="hover">
					'.$image.'
					'.$name.'
					'.$price_full.'
					'.$favorite.'
				</div>
			</div>';
		}
	}
	$similars .= '</div>';
	$similars .= '</div>';
	$similars .= '</div>';
	$similars .= '</div>';
	
	return $similars;
}

/**
*  Вывод любых отзывов/комментариев
**/
function get_opinions(
	$id=0,
	$type='guestbook',
	$title='Отзывы',
	$args=[]
){
	global $db,$template,$lang;
	$add = " && type='".$type."'";
	if(!empty($id)){
		$add .= " && p_main='".$id."'";
	}
	$res = mysqli_query($db,"
		SELECT *
		FROM ".$template."_opinions
		WHERE activation=1 && lang=".$lang."".$add."
	");
	if(mysqli_num_rows($res)>0){
		$opinions = '<div id="opinions_block">';
		$opinions .= '<div class="title_block">';
		$opinions .= '<div class="center_block">'.$title.'</div>';
		$opinions .= '</div>';
		$opinions .= '<div class="inner_block">';
		
		$opinions .= '<div class="send_opinion">';
		$opinions .= '<form action="/include/handler.php" method="post">
			<input type="hidden" name="s[type]" value="'.$type.'">
			<input type="hidden" name="s[p_main]" value="'.$id.'">
			<input type="text" name="s[msg]" placeholder="Оставить свой отзыв">
			<input type="submit" value="Отправить">
		</form>';
		$opinions .= '</div>';
		$opinions .= '<div class="opinions_list">';
		while($row = mysqli_fetch_assoc($res)){
			$opinions .= '<div class="opinion">
				<div class="name">'.$row['name'].' ('.get_date($row['date'],'rus').')</div>
				<div class="text">'.nl2br($row['msg']).'</div>
			</div>';
		}
		$opinions .= '</div>';
		$opinions .= '</div>';
		$opinions .= '</div>';
	}
	
	return $opinions;
}

/*
*  Дублирование дочерних страниц
*/
function copyChildrenPages($id_parent,$id2,$table){
	global $db,$template;
	$par = "id_parent";
	if($table!='sh1_m_nav_top'){
		$par = "p_main";
	}
	
	/*
	*  Собираем все поля таблицы $table в массив $tableArray
	*/
	$tableArray = array();
	$tableArrayAlias = array();
	$res = mysqli_query($db,"
		SELECT *
		FROM ".$table."
		ORDER BY id DESC
		LIMIT 1
	") or die(mysqli_error($db));
	if(mysqli_num_rows($res)>0){
		$row = mysqli_fetch_assoc($res);
		if(is_array($row)){
			foreach($row as $key => $value){
				if($key!='id'){
					array_push($tableArray,$key);
					array_push($tableArrayAlias,'p.'.$key);
				}
			}
		}
	}
	
	/*
	*  Создаём полную копию дочерних страниц
	*/
	$res = mysqli_query($db,"
		SELECT *
		FROM ".$table."
		WHERE `".$par."`='".$id_parent."'
	") or die(mysqli_error($db));
	if(mysqli_num_rows($res)>0){
		while($row = mysqli_fetch_assoc($res)){
			$id = $row['id'];
			mysqli_query($db,"
				INSERT INTO ".$table."  (".implode(',',$tableArray).")
				SELECT ".implode(',',$tableArrayAlias)."		
				FROM ".$table." AS p
				WHERE p.id='".$id."'
			") or die(mysqli_error($db));
			$last_id = mysqli_insert_id($db);
			
			if($last_id){
				$resultat = mysqli_query($db,"
					SELECT * 
					FROM ".$table." 
					WHERE id='".$last_id."'
				");
				$myrow = mysqli_fetch_assoc($resultat);
				$blocks = $myrow['blocks'];
				$modules_var = $myrow['modules'];
				
				$rs = mysqli_query($db,"
					SELECT *
					FROM ".$template."_tab_modules
					WHERE id=".$modules_var."
				") or die(mysqli_error($db));
				$rw = mysqli_fetch_assoc($rs);
				$word_name = $rw['word_name']; 
				if(empty($blocks)) {
					$res_array = mysqli_query($db,"
						INSERT INTO ".$template."_blocks_main (name_mod,id_pages,p_main) 
						VALUES('".$word_name."','".$table."','".$last_id."')
					");
					$blocks = mysqli_insert_id($db);
				}
				else {
					$res_array = mysqli_query($db,"
						UPDATE ".$template."_blocks_main 
						SET name_mod='".$word_name."',id_pages='".$table."',p_main='".$last_id."' 
						WHERE id='".$blocks."'
					");
				}
				$rs = mysqli_query($db,"
					UPDATE ".$table." 
					SET blocks='".$blocks."',".$par."='".$id2."'
					WHERE id='".$last_id."'
				");
			}
			copyChildrenPages($id,$last_id,$table);
		}
	}
}

/*
*  Проверка на дубликат ссылки и замена, в случае совпадения
*/
function newLinkCreate($text,$table,$return=false){
	global $db;
	$res = mysqli_query($db,"
		SELECT id
		FROM ".$table."
		WHERE link='".$text."'
	");
	if(mysqli_num_rows($res)>0){
		$ex_link = explode('-',$text);
		$numeric = intval($ex_link[count($ex_link)-1]);
		if($numeric){
			$new_link = array();
			for($c=0; $c<count($ex_link)-1; $c++){
				array_push($new_link,$ex_link[$c]);
			}
			$newLink = implode('-',$new_link).'-'.($numeric+1);
			newLinkCreate($newLink,$table,$return);
		}
		else {
			$new_link = array();
			for($c=0; $c<count($ex_link); $c++){
				array_push($new_link,$ex_link[$c]);
			}
			$newLink = implode('-',$new_link).'-1';
		}
		$text = $newLink;
	}
	else {
		$text = $text;
	}
	if($return){
		return $text;
	}
	else {
		echo $text;
	}
}

// Транслит кириллицы в латиницу
function ru2Lat($string,$table=false){
	global $db,$template;
	$string = trim($string);
	$tr = array(
		"А"=>"a","Б"=>"b","В"=>"v","Г"=>"g",
		"Д"=>"d","Е"=>"e","Ё"=>"e","Ж"=>"j","З"=>"z","И"=>"i",
		"Й"=>"y","К"=>"k","Л"=>"l","М"=>"m","Н"=>"n",
		"О"=>"o","П"=>"p","Р"=>"r","С"=>"s","Т"=>"t",
		"У"=>"u","Ф"=>"f","Х"=>"h","Ц"=>"ts","Ч"=>"ch",
		"Ш"=>"sh","Щ"=>"sch","Ъ"=>"","Ы"=>"yi","Ь"=>"",
		"Э"=>"e","Ю"=>"yu","Я"=>"ya","а"=>"a","б"=>"b",
		"в"=>"v","г"=>"g","д"=>"d","е"=>"e","ё"=>"e","ж"=>"j",
		"з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
		"м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
		"с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
		"ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
		"ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya", 
		"*"=>"-"," "=>"-", "."=>"-", "/"=>"-", "|"=>"", "  "=>"-", "«"=>"-", "»"=>"-","»"=>"-","©"=>"-","®"=>"-","§"=>"-","☺"=>"-",
		" | "=>"-", "| "=>"-", " |"=>"-", "--"=>"-", "—"=>"-", "#"=>"-", "№"=>"-","@"=>"-","'"=>"-","\""=>"-",  
		"---"=>"-", "----"=>"-",  "-----"=>"-", "'"=>"", "&"=>"-", "%"=>"-", "$"=>"-", "^"=>"-", 
		"-+-"=>"-", ":-"=>"-", ":-:"=>"-", "-:"=>"-", ","=>"-", "*"=>"-", "+"=>"-", 
		"-("=>"-", "-)"=>"-", ")-"=>"-", "(-"=>"-", "!"=>"-", "?"=>"-", "`"=>"-", ";"=>"-", "("=>"-", ")"=>"-"
	);
	$text = strtr($string,$tr);
	$text = strtr($text,$tr);
	$text = strtr($text,$tr);
	$text = strtr($text,$tr);
	$text = strtolower($text);
	if(substr($text,-1,1)=='-'){
		$text = substr($text,0,strlen($text)-1);
	}
	if(substr($text,0,1)=='-'){
		$text = substr($text,1,strlen($text));
	}
	if($table){
		$table_name = $table;
	}
	else {
		$table_name = $template."_m_nav_top";
	}
	// newLinkCreate($text,$table_name);
	echo $text;
}

function ru2Lat2($string,$table=false){
	$string = trim($string);
	$tr = array(
		"А"=>"a","Б"=>"b","В"=>"v","Г"=>"g",
		"Д"=>"d","Е"=>"e","Ё"=>"e","Ж"=>"j","З"=>"z","И"=>"i",
		"Й"=>"y","К"=>"k","Л"=>"l","М"=>"m","Н"=>"n",
		"О"=>"o","П"=>"p","Р"=>"r","С"=>"s","Т"=>"t",
		"У"=>"u","Ф"=>"f","Х"=>"h","Ц"=>"ts","Ч"=>"ch",
		"Ш"=>"sh","Щ"=>"sch","Ъ"=>"","Ы"=>"yi","Ь"=>"",
		"Э"=>"e","Ю"=>"yu","Я"=>"ya","а"=>"a","б"=>"b",
		"в"=>"v","г"=>"g","д"=>"d","е"=>"e","ё"=>"e","ж"=>"j",
		"з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
		"м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
		"с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
		"ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
		"ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya",
		"*"=>"-"," "=>"-", "."=>"-", "/"=>"-", "|"=>"", "  "=>"-", "«"=>"-", "»"=>"-","»"=>"-","©"=>"-","®"=>"-","§"=>"-","☺"=>"-",
		" | "=>"-", "| "=>"-", " |"=>"-", "--"=>"-", "—"=>"-", "#"=>"-", "№"=>"-","@"=>"-","'"=>"-","\""=>"-",  
		"---"=>"-", "----"=>"-",  "-----"=>"-", "'"=>"", "&"=>"-", "%"=>"-", "$"=>"-", "^"=>"-", 
		"-+-"=>"-", ":-"=>"-", ":-:"=>"-", "-:"=>"-", ","=>"-", "*"=>"-", "+"=>"-", 
		"-("=>"-", "-)"=>"-", ")-"=>"-", "(-"=>"-", "!"=>"-", "?"=>"-", "`"=>"-", ";"=>"-", "("=>"-", ")"=>"-"
	);
	$text = strtr($string,$tr);
	$text = strtr($text,$tr);
	$text = strtr($text,$tr);
	$text = strtr($text,$tr);
	$text = strtolower($text);
	if(substr($text,-1,1)=='-'){
		$text = substr($text,0,strlen($text)-1);
	}
	if(substr($text,0,1)=='-'){
		$text = substr($text,1,strlen($text));
	}
	if($table){
		$table_name = $table;
	}
	else {
		$table_name = $template."_m_nav_top";
	}
	// $text = newLinkCreate($text,$table_name,true);
	return $text;
}

/**
*  Создание уникальной ссылки
**/
function tranLink($link,$id,$table=''){
	global $db,$template;
	if(empty($table)){
		$table = $template."_m_nav_top";
	}
	$res = mysqli_query($db,"
		SELECT *
		FROM ".$table."
		WHERE link='".$link."'
	") or die(mysqli_error($db));
	if(mysqli_num_rows($res)>0){
		$link = $link.'-'.$id;
	}
	return $link;
}

// Форматирование цены
function price_format($price) {
    $n_price = str_replace(" ","",$price);
    $rest = substr("abcdef", -1, 3); // возвращает "d"
}

// Шифрование клиентского пароля и вывод на экран
function shifrSee($pass,$id,$time){
	$password = md5($id).strrev($pass).md5($time);
	$lengthPassword = iconv_strlen($pass, 'UTF-8');
	echo $password;
}

// Шифрование клиентского пароля
function shifr($pass,$id,$time){
	$password = md5($id).strrev($pass).md5($time);
	$lengthPassword = iconv_strlen($pass, 'UTF-8');
	return $password;
}

// Расшифровка до состояния клиентского пароля и вывод на экран
function unshifrSee($pass,$nl,$col){
	$strLengthPASS = iconv_strlen($pass, 'UTF-8');
	$strLengthID = iconv_strlen(md5($_SESSION['id']), 'UTF-8');
	$minus_ = -($strLengthPASS - $strLengthID);
	$first_replace = substr_replace($pass, '', $nl, $minus_);
	$fr = iconv_strlen($first_replace, 'UTF-8');
	$second_replace = substr_replace($first_replace, '', $col, $fr);
	$passwd = strrev($second_replace);
	echo $passwd;
}

// Расшифровка до состояния клиентского пароля
function unshifr($pass,$nl,$col){
	$strLengthPASS = iconv_strlen($pass, 'UTF-8');
	$strLengthID = iconv_strlen(md5($_SESSION['id']), 'UTF-8');
	$minus_ = -($strLengthPASS - $strLengthID);
	$first_replace = substr_replace($pass, '', $nl, $minus_);
	$fr = iconv_strlen($first_replace, 'UTF-8');
	$second_replace = substr_replace($first_replace, '', $col, $fr);
	$passwd = strrev($second_replace);
	return $passwd;
}

// Инициализация верха главной страницы до body
function indexInstalMainCode($title){
echo '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>'.$title.'</title>
';
	include("include/structure/head.php");
	echo '</head>';
}

// Инициализация главной страницы входа
function showIndexEnter($title){
	print '<div class="centera">
		<form class="autoriz" onsubmit="return formAutorizUser();" action="#">
			<h3>'.$title.'</h3>
			<div class="wrap">
				<table>
					<tr>
						<td>
							<label for="login_auto">Логин:</label><br/>
							<input id="login_auto" class="auto-btn" type="text" name="login" />
						</td>
						<td>
							<label for="password_auto">Пароль:</label><br/>
							<input id="password_auto" class="auto-btn" type="password" name="password" />
						</td>
						<td>
							<input class="sub-btn" type="submit" value="Вход"/>
						</td>
					</tr>
				</table>
			</div>
			<div class="forgot"><a href="/admin_2/forgot.php">Забыли пароль?</a></div>
			<div class="create">Разработано в <a target="_blank" href="http://www.vasilev-dv.ru">DV-Studio</a></div>
		</form>
		<div class="link"><a href="/">Перейти на сайт</a></div>
	</div>';
}

// Получение "чистой" ссылки
function clearLink($count){
	global $params;
	preg_match('/^[a-z0-9-\_]+$/', $params[$count], $matches, PREG_OFFSET_CAPTURE);
	$link = $matches[0][0];
	return $link;
}

// Функция проверки всей ссылки на реальность
function checkLinkFunction($link){
	global $lang,$template,$db;
	$res = mysqli_query($db,"
		SELECT *
		FROM ".$template."_m_nav_top
		WHERE activation='1' && lang='".$lang."' && link='".$link."'
	");
	return (mysqli_num_rows($res) > 0);
}

/*
*  Модули имеющие пагинацию и их таблицы
*/
$_pagerModules = array(
	"discount"  => array(
		"table" => $template."_m_discount_left",
		"num"   => $discountNumItems,
	),
	"news" => array(
		"table" => $template."_m_news_left",
		"num"   => $newsNumItems
	),
	"catalogue" => array(
		"table" => $template."_m_catalogue_left",
		"num"   => $catalogueNumItems
	),
);

/*
*  Типы форм обратной связи
*/
$_TYPE_FEEDBACK = array(
	0 => "Форма обратной связи",
	1 => "Обратный звонок"
);

// Проверка пагинации на адекваное число страниц
function pagerAdequacy($table){
	global $lang,$template,$_pagerModules,$db;
	if($_pagerModules[$table]){
		$res = mysqli_query($db,"
			SELECT COUNT(*) AS count
			FROM ".$_pagerModules[$table]['table']."
			WHERE activation='1' && lang='".$lang."'
		");
		if(mysqli_num_rows($res)>0){
			$row = mysqli_fetch_assoc($res);
			$count = $row['count'];
			$num = $_pagerModules[$table]['num'];
			$total = (($count - 1) / $num) + 1;
			$total =  intval($total);
			return $total;
		}	
	}
	else {
		return 0;
	}
}

// Сбор идентификаторов, имеющих продукцию
function catalogueArray($id){
	global $lang,$arrayIdInfo,$template,$db;
	$res = mysqli_query($db,"
		SELECT *
		FROM ".$template."_m_nav_top
		WHERE activation='1' && lang='".$lang."' && id_parent='".$id."'
	") or die(mysql_errno());
	if(mysqli_num_rows($res)>0){
		while($row = mysqli_fetch_assoc($res)){
			if($row['modules']==11){
				catalogueArray($row['id']);
			}
			if($row['modules']==2){
				array_push($arrayIdInfo,$row['id']);
			}
		}
	}
}

/**
*  Получение любой ссылки
**/
function get_link_page($id,$parents=true){
	global $arrayLinkWay,$pref;
	$arrayLinkWay = [];
	if(!empty($id)){
		productArrayLink($id,$parents);
	}
	$linkWay = [];
	if(count($arrayLinkWay)>0){
		for($c=count($arrayLinkWay)-1; $c>=0; $c--){
			array_push($linkWay,$arrayLinkWay[$c]['link']);
		}
	}
	$link = $pref.'/'.implode('/',$linkWay);
	return $link;
}

/**
*  Получение данных страницы
**/
function get_name_page($id){
	global $lang,$template,$db;
	$row = [];
	$add = " && id='".$id."'";
	if(empty($id)){
		$add = " && home=1";
	}
	$res = mysqli_query($db,"
		SELECT *
		FROM ".$template."_m_nav_top
		WHERE lang='".$lang."'".$add."
	");
	if(mysqli_num_rows($res)>0){
		$row = mysqli_fetch_assoc($res);
	}
	return $row;
}

/**
*  Получение данных модуля
**/
function get_module_page($name=0){
	global $lang,$template,$db;
	$row = [];
	$add = "name_tab='".$name."'";
	
	if(is_int($name)){
		$add = "id=".intval($name)."";
	}
	if(empty($name)){
		$add = "name_tab=0";
	}
	$res = mysqli_query($db,"
		SELECT *
		FROM ".$template."_block_tab
		WHERE ".$add."
	");
	if(mysqli_num_rows($res)>0){
		$row = mysqli_fetch_assoc($res);
	}
	return $row;
}

/**
*  Чистка телефона от лишнего
**/
function get_clear_phone($phone,$type=3){
	$ph = $phone;
	$ph = preg_replace("/[\(]/", '', $ph);
	$ph = preg_replace("/[\)]/", '', $ph);
	
	// Без скобок
	if($type==1){
		$ph = $ph;
	}
	// без тире
	else if($type==2){
		$ph = str_replace('-','',$ph);
		$ph = str_replace('-','',$ph);
	}
	// без пробелов
	else {
		$ph = str_replace('-','',$ph);
		$ph = str_replace('-','',$ph);
		$ph = str_replace(' ','',$ph);
		$ph = str_replace(' ','',$ph);
	}
	return $ph;
}

// Инициализация ссылки для продукции
function productArrayLink($id,$parents=false){
	global $lang,$template,$db,$arrayLinkWay;
	$res = mysqli_query($db,"
		SELECT *
		FROM ".$template."_m_nav_top
		WHERE activation='1' && lang='".$lang."' && id='".$id."'
	") or die(mysql_error());
	if(mysqli_num_rows($res)>0){
		$row = mysqli_fetch_assoc($res);
		if(!$parents){
			if(!empty($row['id_parent'])){
				$arr = array(
					"id" => $row['id'],
					"link" => $row['link'],
					"bread" => $row['breadcrumb']
				);
				array_push($arrayLinkWay,$arr);
				productArrayLink($row['id_parent']);
			}
		}
		else {
			if(empty($row['home'])){
				$arr = array(
					"id" => $row['id'],
					"link" => $row['link'],
					"bread" => $row['breadcrumb']
				);
				array_push($arrayLinkWay,$arr);
				if(!empty($row['id_parent'])){
					productArrayLink($row['id_parent'],true);
				}
			}
		}
	}
}

/**
*  Получение числового окончания
**/
function get_numeric_ending($value,$zero='товаров',$num='товар',$second='товара',$nums='товаров'){
	if(substr($value,-1,1)==1 && substr($value,-2,2)!=11){
		$result = $value.' '.$num;
	}
	else if(substr($value,-1,1)==2 && substr($value,-2,2)!=12 || substr($value,-1,1)==3 && substr($value,-2,2)!=13 || substr($value,-1,1)==4 && substr($value,-2,2)!=14){
		$result = $value.' '.$second;
	}
	else if($value==0){
		$result = $value.' '.$zero;
	}
	else {
		$result = $value.' '.$nums;
	}
	return $result;
}

// Инициализация ссылки крошек для продукции
function breadArrayLink($id,$home=false){
	global $lang,$arrayLinkWay,$template,$db;
	$res = mysqli_query($db,"
		SELECT *
		FROM ".$template."_m_nav_top
		WHERE activation='1' && lang='".$lang."' && id='".$id."'
	") or die(mysql_error());
	if(mysqli_num_rows($res)>0){
		$row = mysqli_fetch_assoc($res);
		if(!$home){
			$arr = array(
				"id" => $id,
				"link" => $row['link'],
				"bread" => $row['breadcrumb']
			);
			if(empty($row['hide_breadcrumb'])){
				array_push($arrayLinkWay,$arr);
			}
			breadArrayLink($row['id_parent']);
		}
		else {
			if(empty($row['home'])){
				$arr = array(
					"id" => $id,
					"link" => $row['link'],
					"bread" => $row['breadcrumb']
				);
				if(empty($row['hide_breadcrumb'])){
					array_push($arrayLinkWay,$arr);
				}
				breadArrayLink($row['id_parent']);
			}
		}
	}
}

// Считаем количество блоков в форсированном модуле
function countBlocksInForsModule($template,$name_mod,$id_mod,$lang,$db){
	$table = $template."_m_".$name_mod."_left";
	$result = mysqli_query($db,"SELECT COUNT(*) FROM ".$table." WHERE p_main='".$id_mod."' && lang='".$lang."'");
	if ($result > 0) {
		$row = mysqli_fetch_array($result);
		$col = $row[0];
	}
	else {
		$col = 0;
	}	
	return $col;
}

// Считаем количество блоков в любом модуле модуле не форсированном
function countBlocksInModule($template,$name_mod,$lang,$db){
	$table = $template."_m_".$name_mod."_left";
	$adds = "";
	if($name_mod=='guestbook'){
		$table = $template."_opinions";
		$adds = " && type='guestbook'";
	}
	// $adds = " && p_main='".$id_mod."'";
	$result = mysqli_query($db,"SELECT COUNT(*) FROM ".$table." WHERE lang='".$lang."'".$adds."");
	if ($result > 0) {
		$myrow = mysqli_fetch_array($result);
		$col = $myrow[0];
	}
	else {
		$col = 0;
	}	
	return $col;
}

function fast_get_table($start) {
	global $db, $template;

	if (!isset($item_b)) $item_b = array();
		$sql_breadcrumb = mysqli_query($db,"SELECT id, id_parent, name FROM sh1_m_nav_top WHERE id='".(int)$start."' && activation='1'");
		$data = mysqli_fetch_array($sql_breadcrumb);
	if (isset($data['id_parent']) && $data['id_parent'] > 0){
		$item_b[$start]=$data['name'];
		fast_get_table($data['id_parent']);
	} 
	else {
		if (isset($data['name'])) $item_b[$start]=$data['name'];
	}
	return array_reverse($item_b,true);
}

function breadcrumbs($ID, $table, $db, $limit=0) { // 2, sh1_b_table_inner, 0
	// Массив "хлебных крошек" от текущего элемента до элемента с ID == 0 или $limit
	$res = mysqli_query($db,"SELECT * FROM ".$table." WHERE id_parent='".array($ID)."' && activation='1' && lang='".$lang."'");
	$row = mysqli_fetch_array($res);
	if ($row['id_parent'] != 0 && $row['id_parent'] != $limit) {
		$way = breadcrumbs($row['id_parent'],$table,$limit);
	}
	$way[] = $row;
	echo $way[0];
}

function resize($file_input, $file_output, $w_o, $h_o, $percent = false, $quality=100) {
	list($w_i, $h_i, $type) = getimagesize($file_input);
	if (!$w_i || !$h_i) {
		echo 'Невозможно получить длину и ширину изображения';
		return;
	}
	$types = array('jpg','gif','jpeg','png');
	$ext = $types[$type];
	if ($ext) {
		$func = 'imagecreatefrom'.$ext;
		$img = $func($file_input);
	} else {
		echo 'Некорректный формат файла';
		return;
	}
	if ($percent) {
		$w_o *= $w_i / 100;
		$h_o *= $h_i / 100;
	}
	if (!$h_o) $h_o = $w_o/($w_i/$h_i);
	if (!$w_o) $w_o = $h_o/($h_i/$w_i);
	$img_o = imagecreatetruecolor($w_o, $h_o);
	imageAlphaBlending($img_o, false);
	imageSaveAlpha($img_o, true);
	// if($w_i<668){
		// $w_o = $w_i;
		// $h_o = $h_i;
	// }
	imagecopyresampled($img_o, $img, 0, 0, 0, 0, $w_o, $h_o, $w_i, $h_i);
	if ($type == 2) {
		return imagejpeg($img_o,$file_output,$quality);
	} else {
		$func = 'image'.$ext;
		return $func($img_o,$file_output);
	}
}

function crop($file_input, $file_output, $crop = 'square', $percent = false, $im, $position = 'top',$type_img = 'H',$margin = 0,$quality=100) {
	list($w_i, $h_i, $type) = getimagesize($file_input);
	if (!$w_i || !$h_i) {
		echo 'Невозможно получить длину и ширину изображения';
		return;
	}
	$types = array('jpg','gif','jpeg','png');
	$ext = $types[$type];
	if ($ext) {
		$func = 'imagecreatefrom'.$ext;
		$img = $func($file_input);
	} else {
		echo 'Некорректный формат файла';
		return;
	}
	if ($crop == 'square') {
		$min = $w_i;
		if ($w_i > $h_i) $min = $h_i;
		$w_o = $h_o = $min;
	} else {
		list($x_o, $y_o, $w_o, $h_o) = $crop;
		if ($percent) {
			$w_o *= $w_i / 100;
			$h_o *= $h_i / 100;
			$x_o *= $w_i / 100;
			$y_o *= $h_i / 100;
		}
	}
	$img_o = imagecreatetruecolor($w_o, $h_o);
	imageAlphaBlending($img_o, false);
	imageSaveAlpha($img_o, true);
	if($position == 'center'){
		if($type_img=='H'){
			if($margin==0){
				$pos = ($w_i-$im)*0.5;
				$pos1 = 0;
			} else {
				list($pos, $pos1) = $margin;
			}
		}
		else {
			if($margin==0){
				$pos = ($h_i-$im)*0.5;
				$pos1 = 0;
			} else {
				list($pos, $pos1) = $margin;
			}
		}
	}
	else {
		$pos = 0;
	}
	if($type_img=='H'){
		imagecopy($img_o, $img, $x_o, $y_o, $pos, $pos1, $w_o, $h_o);
	}
	else {
		imagecopy($img_o, $img, $x_o, $y_o, $pos1, $pos, $w_o, $h_o);
	}
	if ($type == 2) {
		return imagejpeg($img_o,$file_output,$quality);
	} else {
		$func = 'image'.$ext;
		return $func($img_o,$file_output);
	}
}

function crop_avatar($file_input, $file_output, $crop = 'square',$quality=100) {
	list($w_i, $h_i, $type) = getimagesize($file_input);
	if (!$w_i || !$h_i) {
		echo 'Невозможно получить длину и ширину изображения';
		return;
	}
	$types = array('jpg','gif','jpeg','png');
	$ext = $types[$type];
	if ($ext) {
		$func = 'imagecreatefrom'.$ext;
		$img = $func($file_input);
	} else {
		echo 'Некорректный формат файла';
		return;
	}

	list($x_o, $y_o, $w_o, $h_o, $pos, $pos1) = $crop;
	$img_o = imagecreatetruecolor($w_o, $h_o);
	imageAlphaBlending($img_o, false);
	imageSaveAlpha($img_o, true);
	
	imagecopy($img_o, $img, 0, 0, $x_o, $y_o, $w_o, $h_o);
	if ($type == 2) {
		return imagejpeg($img_o,$file_output,$quality);
	} else {
		$func = 'image'.$ext;
		return $func($img_o,$file_output);
	}
}

function file_load($file){
	if(isset($file)){
	  
		// проверка расширений загружаемых изображений
		if($file['type'] == "application/x-shockwave-flash" || $file['type'] == "image/gif"){
				
			$type = explode('.',$file['name']);
			$type = $type[1];
			
			// директория загрузок
			$uploaddir = "../uploads/$type/"; // Оригинал
			
			// новое имя изображения
			$apend  = substr(md5(date('YmdHis').rand(100,1000)),0,10).".".$type;
			// $apend  = $file['name'];
			
			// путь к новому изображению
			$uploadfile = "$uploaddir$apend"; // original

			// черный список типов файлов
			$blacklist = array(".php", ".phtml", ".php3", ".php4");
			foreach ($blacklist as $item){
				if(preg_match("/$item\$/i", $file['name'])){
					echo "Нельзя загружать скрипты.";
					exit;
				}
			}
	 
			// перемещаем файл из временного хранилища
			if(move_uploaded_file($file['tmp_name'], $uploadfile)){
				return $apend;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}
}

function formatSizeUnits($bytes){
	if ($bytes >= 1073741824){
		$bytes = number_format($bytes / 1073741824, 2) . ' Гб';
	}
	elseif ($bytes >= 1048576){
		$bytes = number_format($bytes / 1048576, 2) . ' Мб';
	}
	elseif ($bytes >= 1024){
		$bytes = number_format($bytes / 1024, 2) . ' Кб';
	}
	elseif ($bytes > 1){
		$bytes = $bytes . ' байт';
	}
	elseif ($bytes == 1){
		$bytes = $bytes . ' байт';
	}
	else {
		$bytes = '0 байт';
	}

	return $bytes;
}

/*
*  Загрузка файлов
*/
function file_load2($file,$key){
	if(isset($file) && isset($key)){
		// проверка расширений загружаемых изображений
		// if($file['type'][$key] == "application/x-shockwave-flash" || $file['type'][$key] == "image/gif"){
				
			$type = explode('.',$file['name'][$key]);
			$type = $type[count($type)-1];
			
			// директория загрузок
			$uploaddir = $_SERVER['DOCUMENT_ROOT']."/admin_2/uploads/files/"; // Оригинал
			
			// новое имя изображения
			$apend  = substr(md5(date('YmdHis').rand(100,1000)),0,10).".".$type;
			// $apend  = $file['name'][$key];
			
			// путь к новому изображению
			$uploadfile = "$uploaddir$apend"; // original

			// черный список типов файлов
			$blacklist = array(".php", ".phtml", ".php3", ".php4");
			foreach ($blacklist as $item){
				if(preg_match("/$item\$/i", $file['name'][$key])){
					echo "Нельзя загружать скрипты.";
					exit;
				}
			}
	 
			// перемещаем файл из временного хранилища
			if(move_uploaded_file($file['tmp_name'][$key], $uploadfile)){
				return $apend;
			}
			else {
				return false;
			}
		// }
		// else {
			// return false;
		// }
	}
}

/**
*  Простое подключение к CURL
**/
function curl_simple($link){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $link);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 20);
	$json_result = curl_exec($ch);
	curl_close($ch);
	return json_decode( $json_result );
}

/**
 * Возвращает сумму прописью
 * @author runcore
 * @uses morph(...)
 */
function num2str($num) {
	$nul='ноль';
	$ten=array(
		array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),
		array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'),
	);
	$a20=array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать' ,'пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать');
	$tens=array(2=>'двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят' ,'восемьдесят','девяносто');
	$hundred=array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот','восемьсот','девятьсот');
	$unit=array( // Units
		array('копейка' ,'копейки' ,'копеек',	 1),
		array('рубль'   ,'рубля'   ,'рублей'    ,0),
		array('тысяча'  ,'тысячи'  ,'тысяч'     ,1),
		array('миллион' ,'миллиона','миллионов' ,0),
		array('миллиард','милиарда','миллиардов',0),
	);
	//
	list($rub,$kop) = explode('.',sprintf("%015.2f", floatval($num)));
	$out = array();
	if (intval($rub)>0) {
		foreach(str_split($rub,3) as $uk=>$v) { // by 3 symbols
			if (!intval($v)) continue;
			$uk = sizeof($unit)-$uk-1; // unit key
			$gender = $unit[$uk][3];
			list($i1,$i2,$i3) = array_map('intval',str_split($v,1));
			// mega-logic
			$out[] = $hundred[$i1]; # 1xx-9xx
			if ($i2>1) $out[]= $tens[$i2].' '.$ten[$gender][$i3]; # 20-99
			else $out[]= $i2>0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
			// units without rub & kop
			if ($uk>1) $out[]= morph($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]);
		} //foreach
	}
	else $out[] = $nul;
	$out[] = morph(intval($rub), $unit[1][0],$unit[1][1],$unit[1][2]); // rub
	$out[] = $kop.' '.morph($kop,$unit[0][0],$unit[0][1],$unit[0][2]); // kop
	return trim(preg_replace('/ {2,}/', ' ', join(' ',$out)));
}

/**
 * Склоняем словоформу
 * @ author runcore
 */
function morph($n, $f1, $f2, $f5) {
	$n = abs(intval($n)) % 100;
	if ($n>10 && $n<20) return $f5;
	$n = $n % 10;
	if ($n>1 && $n<5) return $f2;
	if ($n==1) return $f1;
	return $f5;
}

/**
*  Красивый вывод массива
**/
function pre($arr){
	echo '<pre>';
	print_r($arr);
	echo '</pre>';
}

/**
*  Типы выборки
**/
$_TYPES_CHOICE = [1=>"Одно","Много"];

/**
*  Способы выборки
**/
$_METHODS_CHOICE = [
	1 => "Строка",
	2 => "Описание",
	3 => 'Число (select)',
	4 => "Список (select multiple)",
];

/**
*  Проверка на HTTPS
**/
$isHttps = !empty($_SERVER['HTTPS']) && 'off' !== strtolower($_SERVER['HTTPS']);
$isHttps ? $_protocol = 'https://' : $_protocol = 'http://';

/**
*  Адаптивная версия сайта
**/
$_ADAPTIVE = '<meta name="MobileOptimized" content="320" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<meta name="HandheldFriendly" content="True"/>';

$_RELATIONS = [
	0=>[
		"class"=>"",
		"name"=>"Выберите вариант",
	],
	1=>[
		"class"=>"recall",
		"name"=>"Не взяли трубку",
	],
	2=>[
		"class"=>"attention",
		"name"=>"Недоступно",
	],
	3=>[
		"class"=>"call",
		"name"=>"Позвонить позже",
	]
];
$_STATUS = [0=>"Входящий",1=>"Исполнен",2=>"В обработке/активный",3=>"Отменён",4=>"Возврат"];
$_STATUS_USER = [0=>"В обработке",1=>"Исполнен",2=>"В работе",3=>"Отменён",4=>"Возврат"];
$_PAYMENT = [0=>"Не оплачен",1=>"Оплачен"];
$_DELIVERY = [1 => "Самовывоз",2 => "Курьерская доставка"];
$_PAYMENT_TYPE = [1 => "Оплата при получении",2=>"Онлайн-оплата картой"];

/*
* TODO
*/
$months = ["Jan"=>"01","Feb"=>"02","Mar"=>"03","Apr"=>"04","May"=>"05","Jun"=>"06","Jul"=>"07","Aug"=>"08","Sep"=>"09","Oct"=>"10","Nov"=>"11","Dec"=>"12"];
$months_rus = ["янв"=>"01","фев"=>"02","мар"=>"03","апр"=>"04","май"=>"05","июн"=>"06","июл"=>"07","авг"=>"08","сен"=>"09","окт"=>"10","ноя"=>"11","дек"=>"12"];
$months_rus_full = ["янв."=>"01","фев."=>"02","март"=>"03","апр."=>"04","май"=>"05","июнь"=>"06","июль"=>"07","авг."=>"08","сен."=>"09","окт."=>"10","ноябрь"=>"11","дек."=>"12"];
$months_rus_olymp = ["Января"=>"01","Февраля"=>"02","Марта"=>"03","Апреля"=>"04","Мая"=>"05","Июня"=>"06","Июля"=>"07","Августа"=>"08","Сентября"=>"09","Октября"=>"10","Ноября"=>"11","Декабря"=>"12"];
?>