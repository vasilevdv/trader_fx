<?
//Настройки шаблона
$name_site = 'Trader-FX.RU';
$version_site = 'CMS DV-Studio v.5.23';
$link_site = $_SERVER['SERVER_NAME'];
$year_created = 2019;
$email_info = array('dmitri1988@mail.ru');	// куда будут приходить письма
$localhost = 'localhost';
$name_db = 'trader';
$user_db = 'root';
$pass_db = '';

/*
*  Дата создания сайта
*/
$_DATE_CREATE = "01-11-2019";

if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
	$ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
	$ip = $_SERVER['REMOTE_ADDR'];
}
?>