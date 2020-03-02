<?
/*
*  Загрузка с Binomo
*/
// $file = $_SERVER['DOCUMENT_ROOT']."/users/".$user_id."/deals.txt";
// file_put_contents($file, $deals);
if (!empty($deals_var)) {
	$ex_deals = explode('\r\n',$deals_var);
	$dealArray = [];
	$day_close = date("Y-m-d");
	$year = date("Y");
	for($d=0; $d<count($ex_deals); $d++){
		if(intval($ex_deals[$d])>2000){
			$year = $ex_deals[$d];
		}
		else {
			$ex_find_percent = explode('%',$ex_deals[$d]);

			if(count($ex_find_percent)>1){//USD CAD 70
				$dealsData = clearValueText($ex_find_percent[0]);
				$ex_dealsData = explode(' ',$dealsData);
				$quote_open = clearValueText($ex_find_percent[1]);
				
				$percent = intval($ex_dealsData[count($ex_dealsData)-1]);
				$pair_broker = [];
				for($p=0; $p<count($ex_dealsData)-1; $p++){
					array_push($pair_broker,$ex_dealsData[$p]);
				}
				$pair = implode('',$pair_broker);
				$pair = clearValueText($pair);
				
				//03 Мая 08:22:01 1.28532
				$ex_datas = explode(' ',$ex_deals[$d+1]);
				$day = $ex_datas[0];
				$m = $ex_datas[1];
				$time_open = clearValueText($ex_datas[2]);
				$quote_close = clearValueText($ex_datas[3]);
				$day_open = $year.'-'.$months_rus_olymp[$m].'-'.$day;
				
				//03 Мая 08:25:00 52,00 0,00 
				$ex_datas = explode(' ',$ex_deals[$d+2]);
				$day = $ex_datas[0];
				$m = $ex_datas[1];
				$time_close = clearValueText($ex_datas[2]);
				
				$summa = clearValueText($ex_datas[3]);
				$summa = str_replace('$','',$summa);
				$summa = str_replace('₽','',$summa);
				$summa = str_replace(',','.',$summa);
				$summa = str_replace(' ','',$summa);
				$summa = str_replace(' ','',$summa);
				
				$summa_deal = clearValueText($ex_datas[4]);
				$summa_deal = str_replace('$','',$summa_deal);
				$summa_deal = str_replace('₽','',$summa_deal);
				$summa_deal = str_replace(',','.',$summa_deal);
				$summa_deal = str_replace(' ','',$summa_deal);
				$summa_deal = str_replace(' ','',$summa_deal);
				$ex_summa_deal = explode('.',$summa_deal);

				$result = 0;
				if(!empty($ex_summa_deal[0])){
					$result = 1;
					if($ex_datas[3]==$ex_datas[4]){
						$result = -1;
					}
				}
				
				$day_close = clearValueText($year.'-'.$months_rus_olymp[$m].'-'.$day);
				
				if(empty($ex_summa_deal[0])){
					$summa_deal = $summa * (1 + ($percent/100));
				}
				$profit = $summa_deal - $summa;
				
				$date = time();
				
				$percent = intval($percent);
				
				$arr = ["user_id=".$user_id,"type=1","broker=".$broker_var,"pair='".$pair."'","percent=".$percent,"summa=".$summa,"date=".$date,"date_deal='".$day_close."'","summa_deal=".$summa_deal,"result=".$result,"profit=".$profit,"time_open='".$time_open."'","time_close='".$time_close."'","deal_id='".$deal_id."'","load_deal=1"];
				if(count($dealArray[$day_close])==0){
					$dealArray[$day_close] = [$arr];
				} else {
					array_push($dealArray[$day_close],$arr);
				}
				$d = $d + 2;
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

		/*
		*  Вывод по сделкам
		*/
		for($v=count($values)-1; $v>=0; $v--){
			$n++;

			/*
			*  Вывод по параметрам
			*/
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
						
						/*
						*  Формируем массив обновленных значений
						*/
						if(count($updates[$date])==0){
							$updates[$date] = [$row['id']];
						}	else {
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
						} else {
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
	$title = 'Выгрузка Olymp Trade завершена';
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