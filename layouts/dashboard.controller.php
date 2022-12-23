<?php
	
	require_once '../configs/Db.php';
	require_once '../configs/Bill.php';
	$db = new Db();
	$currency = Bill::getCurrencySymbol();	
	$accountYear = Bill::getAccountYear();

	$status = false; $error = ''; $data = '';

	$action = $_POST['action'] ?? null;

	if (empty($action)) {
		$status = false;
		$error = 'Unauthorized access';
	}

	switch ($action) {

		case 'getProducts':
			
			$productName = $_POST['productName'] ?? null;

			if (empty($productName)) {
				$status = false;
				$error = 'Enter Product Name';
			} else {

				$status = true;

				try {
					$sql = "SELECT id, productName FROM products WHERE delete_status=:delete_status AND productName LIKE :productName";
					$prepare = $db->prepare($sql);
					
					$prepare->bindValue(':delete_status', false, PDO::PARAM_BOOL);
					$prepare->bindValue(':productName', "%" . $productName . "%", PDO::PARAM_STR);

					$result = $prepare->execute();

					if ($result) {
						$status = true;

						$result = $prepare->fetchAll();

						$data = array();
						foreach ($result as $key => $value) {
							$data[] = array('id' => $value['id'], 'productName' => $value['productName']);
						}
					}

				} catch (PDOException $e) {
					$status = false;
					$error = $e->getMessage();
				}				

			}

			$response = array('status' => $status, 'error' => $error, 'data' => $data);

			echo json_encode($response);

			break;

		case 'noOfProductsChart':
			
			try {
				
				$sql = "SELECT (SELECT COUNT(a.id) FROM products a WHERE a.delete_status=0) AS 'Total Products', (SELECT COUNT(DISTINCT b.productId) FROM prices b WHERE b.delete_status=0) AS 'Total Priced'";
				$prepare = $db->prepare($sql);
				$result = $prepare->execute();

				if ($result) {
					$status = true;
					$data = $prepare->fetch(PDO::FETCH_ASSOC);					
				} else {
					$status = false;
					$error = 'Something went wrong';
				}

			} catch (PDOException $e) {
				$status = false;
				$error = $e->getMessage();
			}

			$response = array('status' => $status, 'error' => $error, 'data' => $data);
			echo json_encode($response);

			break;
		
		case 'highlySold':

			try {
				
				$sql = "SELECT * FROM (SELECT b.productId, c.productName, SUM(b.quantity) AS productQuantity FROM billentry_sublist b INNER JOIN products c ON b.productId = c.id WHERE b.billNo IN (SELECT a.billNo FROM billentry a WHERE a.delete_status = 0 AND a.billStatus = 'billed' AND a.accountYear = '2022-2023' AND a.billDate = CURRENT_DATE) AND b.delete_status = 0 GROUP BY b.productId) t GROUP BY productId ORDER BY productQuantity DESC LIMIT 4";

				$prepare = $db->prepare($sql);
				$result = $prepare->execute();
				
				if ($result) {
					$status = true;
					$details = $prepare->fetchAll(PDO::FETCH_ASSOC);									
					$data = array();
					foreach ($details as $rowKey => $row) {						
						$data[] = array('productName' => $row['productName'], 'quantity' => $row['productQuantity']);
					}

				} else {
					$status = false;
					$error = 'Something went wrong';
				}

			} catch (PDOException $e) {
				$status = false;
				$error = $e->getMessage();
			}

			$response = array('status' => $status, 'error' => $error, 'data' => $data);
			echo json_encode($response);			

			break;

		case 'percentageProductSold':
			try {
				
				$sql = "SELECT productId, productName, productQuantity, totalQuantity, ((productQuantity / totalQuantity) * 100) AS percent FROM (SELECT *, (SELECT SUM(b.quantity) FROM billentry_sublist b INNER JOIN products c ON b.productId=c.id WHERE b.billNo IN (SELECT a.billNo FROM billentry a WHERE a.delete_status=0 AND a.billStatus='billed' AND a.accountYear='2022-2023' AND a.billDate=CURRENT_DATE) AND b.delete_status=0) AS totalQuantity FROM (SELECT b.productId, c.productName, SUM(b.quantity) AS productQuantity FROM billentry_sublist b INNER JOIN products c ON b.productId=c.id WHERE b.billNo IN (SELECT a.billNo FROM billentry a WHERE a.delete_status=0 AND a.billStatus='billed' AND a.accountYear='2022-2023' AND a.billDate=CURRENT_DATE) AND b.delete_status=0 GROUP BY b.productId) t GROUP BY productId) t1 ORDER BY percent DESC";

				$prepare = $db->prepare($sql);
				$result = $prepare->execute();
				
				if ($result) {
					$status = true;
					$details = $prepare->fetchAll(PDO::FETCH_ASSOC);
					$percent = 0;
					$totalPercent = 0;
					$i = 1;
					$data = array();
					foreach ($details as $rowKey => $row) {
						$totalPercent += $row['percent'];
						$percent = number_format($row['percent'], 2, '.', "");
						$data[] = array('productName' => $row['productName'], 'percent' => $percent);
						
						$i++;
						
						if ($i > 3) {
							$percent = 100 - $totalPercent;
							$percent = number_format($percent, 2, '.', "");
							$data[] = array('productName' => 'Other', 'percent' => $percent);

							break;
						}						

					}

				} else {
					$status = false;
					$error = 'Something went wrong';
				}

			} catch (PDOException $e) {
				$status = false;
				$error = $e->getMessage();
			}

			$response = array('status' => $status, 'error' => $error, 'data' => $data);
			echo json_encode($response);			

			break;

		case 'todayBills':
		
			try {
				
				$sql = "SELECT (SELECT COUNT(a.id) FROM billentry a WHERE a.delete_status=0 AND a.billStatus='billed' AND a.billDate=CURRENT_DATE) AS 'Saved Bills', (SELECT COUNT(a.id) FROM billentry a WHERE a.delete_status=0 AND a.billStatus='unsaved') AS 'Unsaved Bills'";
				$prepare = $db->prepare($sql);
				$result = $prepare->execute();

				if ($result) {
					$status = true;
					$data = $prepare->fetch(PDO::FETCH_ASSOC);								
				} else {
					$status = false;
					$error = 'Something went wrong';
				}

			} catch (PDOException $e) {
				$status = false;
				$error = $e->getMessage();
			}

			$response = array('status' => $status, 'error' => $error, 'data' => $data);
			echo json_encode($response);

			break;

		case 'productPriceHistory':
			
			$id = $_POST['id'] ?? null;

			try {				
				
				if (!empty($id)) {
					$sql = "SELECT a.productId, b.productName, GROUP_CONCAT(a.price) AS prices FROM prices a INNER JOIN products b ON a.productId=b.id WHERE a.delete_status=0 AND a.productId=:productId GROUP BY a.productId ORDER BY a.id ASC";
				} else {
					$sql = "SELECT a.productId, b.productName, GROUP_CONCAT(a.price) AS prices FROM prices a INNER JOIN products b ON a.productId=b.id WHERE a.delete_status=0 GROUP BY a.productId ORDER BY a.id ASC LIMIT 10";					
				}

				$prepare = $db->prepare($sql);

				if (!empty($id)) {
					$prepare->bindValue(':productId', $id, PDO::PARAM_INT);
				}

				$result = $prepare->execute();

				if ($result) {
					$status = true;
					$details = $prepare->fetchAll(PDO::FETCH_ASSOC);

					$data = array();
					foreach ($details as $key => $value) {
						$prices = explode(',', $value['prices']);
						$prices = array_slice($prices, -3, 3, false);
						$data[] = array('productName' => $value['productName'], 'prices' => $prices);
					}

				} else {
					$status = false;
					$error = 'Something went wrong';
				}

			} catch (PDOException $e) {
				$status = false;
				$error = $e->getMessage();
			}

			$response = array('status' => $status, 'error' => $error, 'data' => $data);
			echo json_encode($response);
			break;

		case 'billsHistory':				

			try {				
				
				$sql = "SELECT * FROM (SELECT billDate, COUNT(*) AS totalBills FROM billentry WHERE delete_status=0	AND billStatus='billed' GROUP BY billDate ORDER BY billDate DESC LIMIT 100) t ORDER BY billDate ASC";

				$prepare = $db->prepare($sql);
				$result = $prepare->execute();

				if ($result) {
					$status = true;
					$details = $prepare->fetchAll(PDO::FETCH_ASSOC);

					$data = array();
					foreach ($details as $key => $value) {						
						$data[] = array('billDate' => $value['billDate'], 'totalBills' => $value['totalBills']);
					}

				} else {
					$status = false;
					$error = 'Something went wrong';
				}

			} catch (PDOException $e) {
				$status = false;
				$error = $e->getMessage();
			}

			$response = array('status' => $status, 'error' => $error, 'data' => $data);
			echo json_encode($response);
			break;

		default:
			$response = array('status' => $status, 'error' => $error, 'data' => $data);
			echo json_encode($response);
			break;
	}


?>