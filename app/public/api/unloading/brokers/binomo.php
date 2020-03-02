<?
/*
*  Загрузка с Binomo
*/
// $file = $_SERVER['DOCUMENT_ROOT']."/users/".$user_id."/deals.txt";
// file_put_contents($file, $deals);
if (!empty($deals_var)) {
	$ex_deals = explode('\n\n\n',$deals_var);
	$dealArray = [];
	$day_close = date("Y-m-d");
	for($d=0; $d<count($ex_deals); $d++){
		$ex_datas = explode('\n\n',$ex_deals[$d]);
		if(count($ex_datas)==1){
			$ex_date_deals = explode(' ',$ex_datas[0]);
			$day_close = clearValueText(date("Y").'-'.$months_rus_full[$ex_date_deals[1]].'-'.$ex_date_deals[0]);
		}
		if(count($ex_datas)==4 || count($ex_datas)==5){
			$n++;
			
			$ex_pairs_info = explode('  ',$ex_datas[0]);
			$pair_broker = $ex_pairs_info[0];
			$pair = clearValueText(str_replace('/','',$pair_broker));

			$summa = clearValueText($ex_datas[3]);
			$summa = str_replace('$','',$summa);
			$summa = str_replace('₽','',$summa);
			$summa = str_replace(',','.',$summa);
			$summa = str_replace(' ','',$summa);
			$summa = str_replace(' ','',$summa);

			$summa_deal = clearValueText($ex_datas[2]);
			$summa_deal = str_replace('$','',$summa_deal);
			$summa_deal = str_replace('₽','',$summa_deal);
			$summa_deal = str_replace(',','.',$summa_deal);
			$summa_deal = str_replace(' ','',$summa_deal);
			$summa_deal = str_replace(' ','',$summa_deal);
			$ex_summa_deal = explode('.',$summa_deal);
			
			$time_open = clearValueText($ex_datas[1]);
			$time_close = '';
			
			$result = 0;
			$percent = $ex_pairs_info[1];
			$percent = str_replace('%','',$percent);
			if(!empty($ex_summa_deal[0])){
				$result = 1;
				if($ex_datas[2]==$ex_datas[3]){
					$result = -1;
				}
				$percent = (($summa_deal/$summa) - 1) * 100;
			}
			
			if(empty($ex_summa_deal[0])){
				$summa_deal = $summa * (1 + ($percent/100));
			}
			$profit = $summa_deal - $summa;
			
			$date = time();
			
			$percent = intval($percent);
			
			$arr = ["user_id=".$user_id,"type=1","broker=".$broker_var,"pair='".$pair."'","percent=".$percent,"summa=".$summa,"date=".$date,"date_deal='".$day_close."'","summa_deal=".$summa_deal,"result=".$result,"profit=".$profit,"time_open='".$time_open."'","time_close='".$time_close."'","deal_id='".$deal_id."'","load_deal=1"];
			if(count($dealArray[$day_close])==0){
				$dealArray[$day_close] = [$arr];
			}
			else {
				array_push($dealArray[$day_close],$arr);
			}
			if(count($ex_datas)==5){
				$ex_date_deals = explode(' ',$ex_datas[4]);
				$day_close = clearValueText(date("Y").'-'.$months_rus_full[$ex_date_deals[1]].'-'.$ex_date_deals[0]);
			}
		}
	}
}

/**
*  Перебираем все сделки
**/
$action = false;
$title = 'Ошибка загрузки';
$text = 'Повторите запрос позднее';
$updates = [];
$inserts = [];
if(count($dealArray)>0){
	/**
	*  Вывод по датам
	**/
	foreach($dealArray as $date => $values){
		$n = 0;
		/**
		*  Вывод по сделкам
		**/
		for($v=count($values)-1; $v>=0; $v--){
			$n++;
			/**
			*  Вывод по параметрам
			**/
			for($d=0; $d<count($values[$v]); $d++){
				$ex_pars = explode('=',$values[$v][$d]);
				if($ex_pars[0]=='time_open'){
					$hash_deal = md5($user_id.'|'.$broker_var.'|'.$date.'|'.str_replace("'","",$ex_pars[1]).'|'.$n);
					
					$paramsArray = $values[$v];					
					$r = mysqli_query($db,"
						SELECT id
						FROM deals
						WHERE hash_deal='".$hash_deal."' && user_id=".$user_id." && broker=".$broker_var."
					") or die(mysqli_error($db));
					if(mysqli_num_rows($r)>0){
						$row = mysqli_fetch_assoc($r);
						mysqli_query($db,"
							UPDATE deals
							SET ".implode(',',$paramsArray)."
							WHERE id=".$row['id']."
						");
						
						/**
						*  Формируем массив обновленных значений
						**/
						if(count($updates[$date])==0){
							$updates[$date] = [$row['id']];
						}
						else {
							array_push($updates[$date],$row['id']);
						}
					}
					else {
						mysqli_query($db,"
							INSERT INTO deals
							SET ".implode(',',$paramsArray).",hash_deal='".$hash_deal."'
						") or die(mysqli_error($db));
						
						$last_id = mysqli_insert_id($db);
						
						/**
						*  Формируем массив добавленных значений
						**/
						if(count($inserts[$date])==0){
							$inserts[$date] = [$last_id];
						}
						else {
							array_push($inserts[$date],$last_id);
						}
					}
					break;
				}
			}
		}
	}
	/**
	*  Формируем текст алерта
	**/
	$text = '<div class="deals">';
	$action = true;
	$title = 'Выгрузка Binomo завершена';
	if(count($inserts)>0){
		foreach($inserts as $date => $values){
			$text .= '<p><strong>'.get_date($date,'point').'</strong>&mdash; '.get_numeric_ending(count($values),'сделок добавлено','сделка добавлена','сделки добавлены','сделок добавлены').'</p>';
		}
	}
	if(count($updates)>0){
		foreach($updates as $date => $values){
			$text .= '<p><strong>'.get_date($date,'point').'</strong>&mdash; '.get_numeric_ending(count($values),'сделок обновлено','сделка обновлена','сделки обновлены','сделок обновлены').'</p>';
		}
	}
	$text .= '</div>';
}
$data = array( 
	"success" => $action,
	"title" => $title,
	"text" => $text,
);
echo json_encode($data);
?>