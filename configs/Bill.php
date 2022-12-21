<?php

/**
 * Bill
 */
class Bill
{	

	static $locale = 'IN_en';
	static $currency = 'INR';
	static $acc_Month = 'APR'; // Account year starting month
	static $acc_Date = '01'; // Account year starting date

	public static function tempBillNo()
	{
		$currentYear = date('Y');
		$nextYear = date('y');
		$currentMonth = strtoupper(Date('M'));
		$timeString = self::$acc_Date . '-' . self::$acc_Month . '-' . $currentYear;
				
		$bytes = random_bytes(3);
		$uniqueString = bin2hex($bytes);

		$acc = strtotime($timeString);
		$now = strtotime( Date('d-M-Y H:i:s') );

		if ($acc <= $now) {
			$nextYear += 1;
		} else {
			$nextYear -= 1;
		}

		$tempBillNo = 'temp-' . $currentYear . $nextYear . $currentMonth .'-' . $uniqueString;

		$tempBillNo = strtoupper($tempBillNo);

		return $tempBillNo;
	}

	public static function getCurrencySymbol() 
	{	
		$locale = self::$locale;
		$currency = self::$currency;

		$c = new NumberFormatter($locale . "@currency=$currency", NumberFormatter::CURRENCY);
		return $c->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
	}

	public static function getAccountYear($date = '')
	{
		if (empty($date)) {
			$date = Date('d-M-Y H:i:s');
		}
		
		$dateTimestamp = strtotime($date);

		if ($dateTimestamp > 0) {
			
			$currentYear = date('Y', $dateTimestamp);
			$nextYear = date('Y', $dateTimestamp);
			$timeString = self::$acc_Date . '-' . self::$acc_Month . '-' . $currentYear;

			$acc = strtotime($timeString);
			$now = $dateTimestamp;

			if ($acc <= $now) {
				$nextYear += 1;
			} else {
				$nextYear -= 1;
			}

			return $currentYear . '-' . $nextYear;

		} else {
			return null;
		}
	}

	public static function billNo($date = '', $accountYear = '')
	{
		$db = new Db();
		if (empty($date)) {
			$date = Date('d-M-Y H:i:s');
		}
		
		$dateTimestamp = strtotime($date);

		if ($dateTimestamp > 0) {
			
			$currentMonth = date('M', $dateTimestamp);
			$currentYear = date('Y', $dateTimestamp);
			$nextYear = date('y', $dateTimestamp);
			$timeString = self::$acc_Date . '-' . self::$acc_Month . '-' . $currentYear;

			$acc = strtotime($timeString);
			$now = $dateTimestamp;

			if ($acc <= $now) {
				$nextYear += 1;
			} else {
				$nextYear -= 1;
			}

			$billCount = 0;

			$sql = "SELECT IFNULL(billNo,0) AS lastBillNo FROM billentry WHERE accountYear=:accountYear AND delete_status=:delete_status AND billStatus=:billStatus ORDER BY billNo DESC LIMIT 1";
			
			try {				
				
				$prepare = $db->prepare($sql);
				$prepare->bindValue(':accountYear', $accountYear, PDO::PARAM_STR);
				$prepare->bindValue(':delete_status', false, PDO::PARAM_BOOL);
				$prepare->bindValue(':billStatus', 'billed', PDO::PARAM_STR);
				$result = $prepare->execute();

				if ($result) {
					$lastBillNo = $prepare->fetchColumn() ?? 0;

					if (empty($lastBillNo)) {
						$billCount = 1;
					} else {
						$billCount = explode('-', $lastBillNo);
						$billCount = $billCount[2] ?? null;

						$billCount = intval($billCount) ?? null;

						if (is_null($billCount)) {
							return null;
						}

						$billCount++;
					}
					
					$billCount = str_pad($billCount, 5, '0', STR_PAD_LEFT);
				} else {
					return null;
				}

			} catch (PDOException $e) {
				return null;
			}

			$billNo = 'bill-' . $currentYear . $nextYear . $currentMonth .'-' . $billCount;

			$billNo = strtoupper($billNo);

			return $billNo;

		} else {
			return null;
		}
	}

}