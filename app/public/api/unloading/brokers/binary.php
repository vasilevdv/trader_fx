<?
global $months;
/*
*  Загрузка с Binary
*/
// $file = $_SERVER['DOCUMENT_ROOT']."/users/".$user_id."/deals.txt";
// file_put_contents($file, $deals);
if (!empty($deals_var)) {
	$deals = str_replace('\r\n','',$deals_var);
	$ex_deals = explode('GMT',$deals);
	$dealArray = [];
	$dealsArray = [];
	pre($ex_deals);
	for($d=0; $d<count($ex_deals); $d++){
		$ex_deals[$d] = str_replace('  ','',$ex_deals[$d]);
		$ex_datas = explode(' ',$ex_deals[$d]);
		
		$deal_id = clearValueText($ex_datas[0]);
		array_push($dealsArray,$deal_id);
		$pair_broker = $ex_datas[1];
		$pair = str_replace('/','',$pair_broker);
		
		$time_open = $ex_datas[3];
		$time_open = str_replace(',','',$time_open);
		$day_open = $ex_datas[2];
		
		$time_close = $ex_datas[5];
		$time_close = str_replace(',','',$time_close);
		$day_close = $ex_datas[4];
		
		$open = $ex_datas[6];
		$close = $ex_datas[7];
		
		$summa = clearValueText($ex_datas[8]);
		$summa = str_replace('$','',$summa);

		$summa_deal = clearValueText($ex_datas[9]);
		$summa_deal = str_replace('$','',$summa_deal);
		
		$result = 0;
		$percent = 80;
		if(!empty($summa_deal)){
			$result = 1;
			$percent = (($summa_deal/$summa) - 1) * 100;
		}
		
		if(empty($summa_deal)){
			$summa_deal = $summa * (1 + ($percent/100));
		}
		$profit = $summa_deal - $summa;
		
		$date = time();
		
		$arr = ["user_id=".$user_id,"type=1","broker=".$broker_var,"pair='".$pair."'","percent=".$percent,"summa=".$summa,"date=".$date,"date_deal='".$day_close."'","summa_deal=".$summa_deal,"result=".$result,"profit=".$profit,"deal_id='".$deal_id."'"];
		array_push($dealArray,$arr);		
		// mysqli_query($db,"
			// INSERT INTO deals_upload
			// SET ".implode(',',$arr)."
		// ") or die(mysqli_error($db));			
	}
}
// pre($dealArray);

// for($c=0; $c<count($dealsArray); $c++){
	// $r = mysqli_query($db,"
		// SELECT id
		// FROM deals
		// WHERE deal_id='".$dealsArray[$c]."'
	// ") or die(mysqli_error($db));
	// if(mysqli_num_rows($r)>0){
		// $row = mysqli_fetch_assoc($r);
		// mysqli_query($db,"
			// UPDATE deals
			// SET ".implode(',',$dealArray[$c])."
			// WHERE deal_id='".$deal_id."' && broker=".$broker_var." && id=".$row['id']."
		// ");
	// }
	// else {
		// mysqli_query($db,"
			// INSERT INTO deals
			// SET ".implode(',',$dealArray[$c])."
		// ") or die(mysqli_error($db));
	// }
// }
?>