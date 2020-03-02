<?php
	header("content-type:text/html;charset=UTF-8");
	include_once 'bd/bd.php';
	include_once 'bd/functions.php';

	// Получение данных из тела запроса
	function getFormData($method) {

	    // GET или POST: данные возвращаем как есть
	    /*if ($method === 'GET') return $_GET;
	    if ($method === 'POST') return $_POST;*/

	    // PUT, PATCH или DELETE
	    $data = array();
	    $exploded = file_get_contents('php://input');
			$data = json_decode($exploded);
	    // foreach($exploded as $pair) {
	    //     $item = explode('=', $pair);
	    //     if (count($item) == 2) {
	    //         $data[urldecode($item[0])] = urldecode($item[1]);
	    //     }
	    // }

	    return $data;
	}

	// Определяем метод запроса
	$method = $_SERVER['REQUEST_METHOD'];

	// Получаем данные из тела запроса
	$formData = getFormData($method);

	// Разбираем url
	$url = (isset($_GET['q'])) ? $_GET['q'] : '';
	$url = rtrim($url, '/');
	$urls = explode('/', $url);

	// Определяем роутер и url data
	$router = $urls[0];
	$urlData = array_slice($urls, 1);

	// Подключаем файл-роутер и запускаем главную функцию
	include_once 'api/' . $router . '/rest.php';
	route($method, $urlData, $formData);

?>