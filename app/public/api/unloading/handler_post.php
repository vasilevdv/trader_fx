<?php
	/*
	*  Сохранение
	*/
	if(isset($formData) && property_exists($formData, 'deals')){
		$user_id = 2;
		$data = ["user_id=".$user_id, "date=".time()];

		foreach($formData as $key => $value) {
			$var = $key."_var";
			if ($key == 'deals') {
				$value = clearValueText($value);
				$value = str_replace('	',' ',$value);
			} else {
				$value = intval($value);
			}

			$$var = $value;

			array_push($data, $key."='".$value."'");
		}
		
		$res = mysqli_query($db,"
			INSERT INTO uploading
			SET ".implode(',',$data)."
		") or die(mysqli_error($db));
		if (mysqli_insert_id($db) > 0) {
			$last_id = mysqli_insert_id($db);
			
			/*
			*  Binary
			*/
			if ($broker_var == 1) {
				include("brokers/binary.php");
			}

			/*
			*  Binomo
			*/
			if ($broker_var == 2) {
				include("brokers/binomo.php");
			}

			/*
			*  Finmax
			*/
			if ($broker_var == 3) {
				include("brokers/finmax.php");
			}

			/*
			*  Olymp Trade
			*/
			if ($broker_var == 5) {
				include("brokers/olymp.php");
			}

			/*
			*  Pocket Option
			*/
			if ($broker_var == 6) {
				include("brokers/pocket.php");		
			}

			/*
			*  Intrade
			*/
			if ($broker_var == 9) {
				include("brokers/intrade.php");		
			}
		}
		return false;
	}
?>