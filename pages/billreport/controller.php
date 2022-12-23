<?php
	
	require_once '../../configs/Db.php';
	require_once '../../configs/Bill.php';
	$currency = Bill::getCurrencySymbol();	
	$accountYear = Bill::getAccountYear();

	$status = false; $error = ''; $data = ''; $response = array(); $bindParams = array();

	$db = new Db();

	$requestMethod = strtolower($_SERVER['REQUEST_METHOD']);

	if ($requestMethod != 'post') {
		$error = 'Unauthorized access';
	}

	$action = $_POST['action'] ?? null;

	if ($action == null) {
		$error = 'Uncategorized action access';
	}

	switch ($action) {

		case 'product_sublist':
			
			$billNo = $_POST['billNo'] ?? null;
			$subTotal = 0;

			$draw = $_POST['draw'] ?? null;
			$row = $_POST['row'] ?? 0;
			$rowLength = (int) $_POST['length'] ?? 0;
			$columnIndex = $_POST['order'][0]['column'] ?? 0;
			$columnName = $_POST['columns'][$columnIndex]['name'] ?? null;
			$columnSortOrder = $_POST['order'][0]['dir'] ?? 'asc';
			$searchQuery = $_POST['search']['value'] ?? '';

			$totalRecords = 0;
			$totalRecordsWithFilter = 0;

			if (!empty($billNo)) {
				
				$sql = "SELECT COUNT(*) AS totalRecords FROM billentry_sublist WHERE delete_status=:delete_status AND billNo=:billNo";

				try {
					
					$prepare = $db->prepare($sql);
					$prepare->bindValue(':delete_status', false, PDO::PARAM_BOOL);
					$prepare->bindValue(':billNo', $billNo, PDO::PARAM_STR);
					$result = $prepare->execute();

					if ($result) {
						$status = true;
						$totalRecords = (int) $prepare->fetchColumn() ?? 0;
					} else {
						$status = false;	
						$error = 'Something went wrong try again';
					}

				} catch (PDOException $e) {
					$status = false;
					$error = $e->getMessage() . __LINE__;
				}

				if (empty($searchQuery)) {				

					$sql = "SELECT COUNT(*) AS totalRecordsWithFilter FROM billentry_sublist a INNER JOIN products b ON a.productId=b.id WHERE a.delete_status=:delete_status AND a.billNo=:billNo ORDER BY $columnName $columnSortOrder LIMIT :row, :rowLength";

				} else {

					$sql = "SELECT COUNT(*) AS totalRecordsWithFilter FROM billentry_sublist a INNER JOIN products b ON a.productId=b.id WHERE a.delete_status=:delete_status AND a.billNo=:billNo AND B.productName LIKE :productName ORDER BY $columnName $columnSortOrder LIMIT :row, :rowLength";	

				}

				try {
					
					$prepare = $db->prepare($sql);
					$prepare->bindValue(':delete_status', false, PDO::PARAM_BOOL);
					$prepare->bindValue(':billNo', $billNo, PDO::PARAM_STR);
					
					if (!empty($searchQuery)) {
						$prepare->bindValue(':productName', "%$searchQuery%", PDO::PARAM_STR);
					}

					$prepare->bindValue(':row', $row, PDO::PARAM_INT);
					$prepare->bindValue(':rowLength', $rowLength, PDO::PARAM_INT);
					$result = $prepare->execute();

					if ($result) {
						$status = true;					
						$totalRecordsWithFilter = (int) $prepare->fetchColumn() ?? 0;
					} else {
						$status = false;
						$error = 'Something went wrong';
					}

				} catch (PDOException $e) {
					$status = false;
					$error = $e->getMessage() . __LINE__;
				}

				if (empty($searchQuery)) {				

					$sql = "SELECT a.id, b.productName, a.price, a.quantity, a.taxPercent, a.taxAmount, a.grandTotal, a.discount, a.netTotal FROM billentry_sublist a INNER JOIN products b ON a.productId=b.id WHERE a.delete_status=:delete_status AND a.billNo=:billNo ORDER BY $columnName $columnSortOrder LIMIT :row, :rowLength";

				} else {

					$sql = "SELECT a.id, b.productName, a.price, a.quantity, a.taxPercent, a.taxAmount, a.grandTotal, a.discount, a.netTotal FROM billentry_sublist a INNER JOIN products b ON a.productId=b.id WHERE a.delete_status=:delete_status AND a.billNo=:billNo AND B.productName LIKE :productName ORDER BY $columnName $columnSortOrder LIMIT :row, :rowLength";	

				}

				try {
					
					$prepare = $db->prepare($sql);
					$prepare->bindValue(':delete_status', false, PDO::PARAM_BOOL);
					$prepare->bindValue(':billNo', $billNo, PDO::PARAM_STR);
					
					if (!empty($searchQuery)) {
						$prepare->bindValue(':productName', "%$searchQuery%", PDO::PARAM_STR);
					}

					$prepare->bindValue(':row', $row, PDO::PARAM_INT);
					$prepare->bindValue(':rowLength', $rowLength, PDO::PARAM_INT);
					$result = $prepare->execute();

					if ($result) {
						$status = true;					
						
						$records = $prepare->fetchAll();
						$data = array();
						$i = 1;
						foreach ($records as $key => $value) {

							$btn = '<div class="d-flex justify-content-between">';						
							$btn .= '<button class="btn btn-warning" type="button" onclick="productEdit(' . $value['id'] . ')"><i class="bi bi-pencil"></i></button>';
							$btn .= '<button class="btn btn-danger" type="button" onclick="productDelete(' . $value['id'] . ')"><i class="bi bi-trash"></i></button>';
							$btn .= '</div>';

							$data[] = array(
								'id' => $i,
								'productName' => ucfirst($value['productName']),
								'price' => $currency . ' ' . $value['price'],
								'quantity' => $value['quantity'],
								'tax' => $currency . ' ' . $value['taxAmount'] . ' (' . $value['taxPercent'] . ' %)',
								'netTotal' => $currency . ' ' . $value['netTotal'],
								'action' => $btn
							);

							$i++;
						}

					} else {
						$status = false;
						$error = 'Something went wrong';
					}

				} catch (PDOException $e) {
					$status = false;
					$error = $e->getMessage() . __LINE__;
				}

				if (empty($searchQuery)) {				

					$sql = "SELECT IFNULL(SUM(a.netTotal),0) AS subTotal FROM billentry_sublist a INNER JOIN products b ON a.productId=b.id WHERE a.delete_status=:delete_status AND a.billNo=:billNo ORDER BY $columnName $columnSortOrder LIMIT :row, :rowLength";

				} else {

					$sql = "SELECT IFNULL(SUM(a.netTotal),0) AS subTotal FROM billentry_sublist a INNER JOIN products b ON a.productId=b.id WHERE a.delete_status=:delete_status AND a.billNo=:billNo AND B.productName LIKE :productName ORDER BY $columnName $columnSortOrder LIMIT :row, :rowLength";	

				}

				try {
					
					$prepare = $db->prepare($sql);
					$prepare->bindValue(':delete_status', false, PDO::PARAM_BOOL);
					$prepare->bindValue(':billNo', $billNo, PDO::PARAM_STR);
					
					if (!empty($searchQuery)) {
						$prepare->bindValue(':productName', "%$searchQuery%", PDO::PARAM_STR);
					}

					$prepare->bindValue(':row', $row, PDO::PARAM_INT);
					$prepare->bindValue(':rowLength', $rowLength, PDO::PARAM_INT);
					$result = $prepare->execute();

					if ($result) {
						$status = true;					
						$subTotal = $prepare->fetchColumn() ?? 0;
					} else {
						$status = false;
						$error = 'Something went wrong';
					}

				} catch (PDOException $e) {
					$status = false;
					$error = $e->getMessage();
				}

			} 

			$response = array(
				'draw' => intval($draw),
				'iTotalRecords' => $totalRecords,
				'iTotalDisplayRecords' => $totalRecordsWithFilter,
				'aaData' => $data,
				'subTotal' => $subTotal,
				'status' => $status,
				'error' => $error
			);

			echo json_encode($response);

			break;

		case 'datatableList':
			
			$draw = $_POST['draw'] ?? null;
			$row = $_POST['row'] ?? 0;
			$rowLength = (int) $_POST['length'] ?? 0;
			$columnIndex = $_POST['order'][0]['column'] ?? 0;
			$columnName = $_POST['columns'][$columnIndex]['name'] ?? null;
			$columnSortOrder = $_POST['order'][0]['dir'] ?? 'asc';
			$searchQuery = $_POST['search']['value'] ?? '';

			$fromDate = $_POST['fromDate'] ?? Date('Y-m-d');
			$toDate = $_POST['toDate'] ?? Date('Y-m-d');

			$limitString = '';

			$totalRecords = 0;
			$totalRecordsWithFilter = 0;

			$netTotal = 0;

			$sql = "SELECT COUNT(*) AS totalRecords FROM billentry WHERE delete_status=:delete_status AND billDate BETWEEN :fromDate AND :toDate";
			
			try {
				$prepare = $db->prepare($sql);
				
				$prepare->bindValue(':delete_status', false, PDO::PARAM_BOOL);
				$prepare->bindValue(':fromDate', $fromDate, PDO::PARAM_STR);
				$prepare->bindValue(':toDate', $toDate, PDO::PARAM_STR);

				$result = $prepare->execute();

				if ($result) {
					$status = true;

					$totalRecords = (int) $prepare->fetchColumn() ?? 0;
				} else {
					$status = false;	
					$error = 'Something went wrong try again';
				}				
			} catch (PDOException $e) {
				$status = false;	
				$error = $e->getMessage() . ' ' . __LINE__;
			}

			if ($rowLength >= 0) {
				$limitString = "LIMIT :row, :rowLength";
			} else {
				$limitString = "";
			}

			if (empty($searchQuery)) {				

				$sql = "SELECT COUNT(*) AS totalRecordsWithFilter FROM billentry WHERE delete_status=:delete_status AND billDate BETWEEN :fromDate AND :toDate ORDER BY $columnName $columnSortOrder $limitString";

			} else {

				$sql = "SELECT COUNT(*) AS totalRecordsWithFilter FROM billentry WHERE delete_status=:delete_status AND billDate BETWEEN :fromDate AND :toDate AND (billNo LIKE :searchBillNo OR customerName LIKE :searchCustomerName OR netTotal LIKE :searchNetTotal) ORDER BY $columnName $columnSortOrder $limitString";	

			}

			try {
				$prepare = $db->prepare($sql);						
				
				$prepare->bindValue(':delete_status', false, PDO::PARAM_BOOL);
				$prepare->bindValue(':fromDate', $fromDate, PDO::PARAM_STR);
				$prepare->bindValue(':toDate', $toDate, PDO::PARAM_STR);

				if (!empty($searchQuery)) {
					$prepare->bindValue(':searchBillNo', "%$searchQuery%", PDO::PARAM_STR);
					$prepare->bindValue(':searchCustomerName', "%$searchQuery%", PDO::PARAM_STR);
					$prepare->bindValue(':searchNetTotal', "%$searchQuery%", PDO::PARAM_STR);
				}

				if ($rowLength >= 0) {
					$prepare->bindParam(':row', $row, PDO::PARAM_INT);
					$prepare->bindParam(':rowLength', $rowLength, PDO::PARAM_INT);
				}				

				$result = $prepare->execute();

				if ($result) {
					$status = true;					
					$totalRecordsWithFilter = (int) $prepare->fetchColumn() ?? 0;
				} else {
					$status = false;
					$error = 'Something went wrong';
				}
			} catch (PDOException $e) {
				$error = $e->getMessage() . ' ' . __LINE__;
			}

			if (empty($searchQuery)) {
				$sql = "SELECT id, billNo, billDate, customerName, netTotal FROM billentry WHERE delete_status=:delete_status AND billDate BETWEEN :fromDate AND :toDate ORDER BY $columnName $columnSortOrder $limitString";
			} else {
				$sql = "SELECT id, billNo, billDate, customerName, netTotal FROM billentry WHERE delete_status=:delete_status AND billDate BETWEEN :fromDate AND :toDate AND (billNo LIKE :searchBillNo OR customerName LIKE :searchCustomerName OR netTotal LIKE :searchNetTotal) ORDER BY $columnName $columnSortOrder $limitString";	
			}

			try {
				$prepare = $db->prepare($sql);								
				
				$prepare->bindValue(':delete_status', false, PDO::PARAM_BOOL);
				$prepare->bindValue(':fromDate', $fromDate, PDO::PARAM_STR);
				$prepare->bindValue(':toDate', $toDate, PDO::PARAM_STR);

				if (!empty($searchQuery)) {
					$prepare->bindValue(':searchBillNo', "%$searchQuery%", PDO::PARAM_STR);
					$prepare->bindValue(':searchCustomerName', "%$searchQuery%", PDO::PARAM_STR);
					$prepare->bindValue(':searchNetTotal', "%$searchQuery%", PDO::PARAM_STR);
				}

				if ($rowLength >= 0) {
					$prepare->bindParam(':row', $row, PDO::PARAM_INT);
					$prepare->bindParam(':rowLength', $rowLength, PDO::PARAM_INT);
				}

				$result = $prepare->execute();				

				if ($result) {
					$status = true;					
					
					$records = $prepare->fetchAll();
					$data = array();
					$i = 1;
					$netTotal = 0;
					foreach ($records as $key => $value) {

						$btn = '<div class="d-flex justify-content-between">';						
						$btn .= '<a href="pages/billreport/view.php?id='. $value['id'] .'" class="btn btn-warning"><i class="bi bi-eye"></i></a>';
						
						$btn .= '</div>';

						$data[] = array(
							'id' => $i,
							'date' => $value['billDate'],
							'billNo' => strtoupper($value['billNo']),
							'customerName' => ucfirst($value['customerName']),
							'netTotal' => "$currency : " . number_format($value['netTotal'], 2, '.', ""),
							'action' => $btn
						);

						$netTotal += $value['netTotal'];

						$i++;
					}

				} else {
					$status = false;
					$error = 'Something went wrong';
				}
			} catch (PDOException $e) {
				$error = $e->getMessage() . ' ' . __LINE__;
			}

			$netTotal = $currency . " : " . number_format($netTotal, 2, '.', "");

			$response = array(
				'draw' => intval($draw),
				'iTotalRecords' => $totalRecords,
				'iTotalDisplayRecords' => $totalRecordsWithFilter,
				'aaData' => $data,
				'netTotal' => $netTotal,
				'status' => $status,
				'error' => $error
				// 'params' => $prepare->debugDumpParams()
			);

			echo json_encode($response);

			break;

		default:
			# code...
			break;
	}

?>