<?
/*
*  Загрузка с Pocket Option
*/
// $file = $_SERVER['DOCUMENT_ROOT']."/users/".$user_id."/deals.txt";
// file_put_contents($file, $deals);
if (!empty($deals_var)) {
	$ex_deals = explode('\r\n',$deals_var);
	$dealArray = [];
	$dealsArray = [];
	for($d=0; $d<count($ex_deals); $d++){
		$ex_deals[$d] = str_replace('  ','',$ex_deals[$d]);
		$ex_datas = explode(' ',$ex_deals[$d]);

		if(count($ex_datas)==10){
			$deal_id = clearValueText($ex_datas[0]);
			array_push($dealsArray,$deal_id);
			$pair_broker = clearValueText($ex_datas[1]);
			$pair = clearValueText(str_replace('/','',$pair_broker));
			
			$time_open = clearValueText($ex_datas[3]);
			$time_open = str_replace(',','',$time_open);
			$ex_time_open = explode('.',$time_open);
			$time_open = $ex_time_open[0];
			$day_open = clearValueText($ex_datas[2]);
			
			$time_close = clearValueText($ex_datas[5]);
			$time_close = str_replace(',','',$time_close);
			$ex_time_close = explode('.',$time_close);
			$time_close = $ex_time_close[0];
			$day_close = clearValueText($ex_datas[4]);
			
			$open = clearValueText($ex_datas[6]);
			$close = clearValueText($ex_datas[7]);
			
			$summa = clearValueText($ex_datas[8]);
			$summa = str_replace('$','',$summa);

			$summa_deal = clearValueText($ex_datas[9]);
			$summa_deal = str_replace('$','',$summa_deal);
			
			$result = 0;
			$percent = 80;
			if(!empty($summa_deal)){
				$result = 1;
				if($ex_datas[8]==$ex_datas[9]){
					$result = -1;
				}

				$percent = (($summa_deal/$summa) - 1) * 100;
			}
			
			if(empty($summa_deal)){
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
		}
	}
}

/*
*  Перебираем все сделки
*/
$action = false;
$title = 'Ошибка загрузки';
$text = 'Повторите запрос позднее';
$updates = [];
$inserts = [];
if(count($dealArray)>0){

	/*
	*  Вывод по датам
	*/
	foreach($dealArray as $date => $values){
		$n = 0;

		/*
		*  Вывод по сделкам
		*/
		for($v=count($values)-1; $v>=0; $v--){
			$n++;

			/**
			*  Вывод по параметрам
			**/
			for($d=0; $d<count($values[$v]); $d++){
				$ex_pars = explode('=',$values[$v][$d]);
				
				if($ex_pars[0]=='time_open'){
					$hash_deal = md5($user_id.'|'.$broker_var.'|'.$date.'|'.str_replace("'","",$ex_pars[1]).'|'.$n);
				}
				
				if($ex_pars[0]=='deal_id'){
					$paramsArray = $values[$v];
					$r = mysqli_query($db,"
						SELECT id
						FROM deals
						WHERE deal_id='".str_replace("'","",$ex_pars[1])."' && user_id=".$user_id." && broker=".$broker_var."
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
						
						/*
						*  Формируем массив добавленных значений
						*/
						if(count($inserts[$date])==0){
							$inserts[$date] = [$last_id];
						}	else {
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
	$title = 'Выгрузка Pocket Option завершена';
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