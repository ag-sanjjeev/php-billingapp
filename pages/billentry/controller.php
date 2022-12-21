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

		case 'getRecentProductPrice':
			
			$productId = $_POST['productId'] ?? null;
			$data = '';

			if (!empty($productId)) {

				try {
					
					$sql = "SELECT IFNULL(a.price, 0) as price  FROM prices a WHERE a.productId=:productId AND a.delete_status=:delete_status ORDER BY a.id DESC LIMIT 1";
					$prepare = $db->prepare($sql);
					
					$prepare->bindValue(':productId', $productId, PDO::PARAM_INT);
					$prepare->bindValue(':delete_status', false, PDO::PARAM_BOOL);

					$result = $prepare->execute();
					
					$data = $prepare->fetchColumn();

					$status = true;					

				} catch (PDOException $e) {
					$status = false;
					$error = $e->getMessage();
				}

			}						

			$response = array('status' => $status, 'error' => $error, 'data' => $data);

			echo json_encode($response);

			break;

		case 'productAdd':
			
			$productId = $_POST['productName'] ?? null;
			$productPrice = $_POST['productPrice'] ?? 0;
			$productQuantity = $_POST['productQuantity'] ?? 0;
			$productTaxPercent = $_POST['productTaxPercent'] ?? 0;
			$productDiscount = $_POST['productDiscount'] ?? 0;
			$billNo = $_POST['billNo'] ?? null;

			if (empty($productId)) {
				$status = false;
				$error = 'Please choose product to add';
			}

			if (empty($productPrice)) {
				$status = false;
				$error .= "\nPrice is not to be an empty";
			}				

			if (empty($productQuantity)) {
				$status = false;
				$error .= "\nPlease add product quantity";
			} 				
			
			if (empty($billNo)) {
				$status = false;
				$error .= "\nSomething went wrong";
			}								
			

			if (empty($error)) {
				
				$sql = "SELECT COUNT(*) AS totalRecords FROM billentry WHERE billNo=:billNo AND delete_status=:delete_status";

				try {
					$prepare = $db->prepare($sql);
					$prepare->bindValue(':billNo', $billNo, PDO::PARAM_STR);
					$prepare->bindValue(':delete_status', false, PDO::PARAM_BOOL);
					$result = $prepare->execute();

					$totalRecords = $prepare->fetchColumn();

					if ($totalRecords <= 0) {

						$sql = "INSERT INTO billentry (billNo, billStatus) VALUES (:billNo, :billStatus)";
						$prepare = $db->prepare($sql);
						$prepare->bindValue(':billNo', $billNo, PDO::PARAM_STR);
						$prepare->bindValue(':billStatus', 'unsaved', PDO::PARAM_STR);
						$prepare->execute();
					}

				} catch (PDOException $e) {
					$status = false;
					$error = $e->getMessage() . __LINE__;
				}

				$sql = "SELECT COUNT(*) AS totalRecords FROM billentry_sublist WHERE billNo=:billNo AND productId=:productId AND delete_status=:delete_status";

				try {
					
					$prepare = $db->prepare($sql);
					$prepare->bindValue(':billNo', $billNo, PDO::PARAM_STR);
					$prepare->bindValue(':productId', $productId, PDO::PARAM_INT);
					$prepare->bindValue(':delete_status', false, PDO::PARAM_BOOL);
					$result = $prepare->execute();

					$totalRecords = $prepare->fetchColumn();

					if ($totalRecords > 0) {
						$status = false;
						$error = 'The product is already exist in this bill.';
					} else {

						$sql = "INSERT INTO billentry_sublist(billNo, productId, price, quantity, subTotal, taxPercent, taxAmount, grandTotal, discount, netTotal) VALUES (:billNo, :productId, :price, :quantity, :subTotal, :taxPercent, :taxAmount, :grandTotal, :discount, :netTotal)";

						$productPrice = floatval($productPrice);
						$productQuantity = floatval($productQuantity);
						$productTaxPercent = floatval($productTaxPercent);
						$productDiscount = floatval($productDiscount);

						$subTotal = $productPrice * $productQuantity;
						$taxAmount = $subTotal * ($productTaxPercent/100);
						$grandTotal = $subTotal + $taxAmount;
						$netTotal = $grandTotal - $productDiscount;

						$netTotal = number_format($netTotal, 2, '.', ""); // allow two decimal places

						$prepare = $db->prepare($sql);
						$prepare->bindValue(':billNo', $billNo, PDO::PARAM_STR);
						$prepare->bindValue(':productId', $productId, PDO::PARAM_STR);
						$prepare->bindValue(':price', $productPrice, PDO::PARAM_STR);
						$prepare->bindValue(':quantity', $productQuantity, PDO::PARAM_STR);
						$prepare->bindValue(':subTotal', $subTotal, PDO::PARAM_STR);
						$prepare->bindValue(':taxPercent', $productTaxPercent, PDO::PARAM_STR);
						$prepare->bindValue(':taxAmount', $taxAmount, PDO::PARAM_STR);
						$prepare->bindValue(':grandTotal', $grandTotal, PDO::PARAM_STR);
						$prepare->bindValue(':discount', $productDiscount, PDO::PARAM_STR);
						$prepare->bindValue(':netTotal', $netTotal, PDO::PARAM_STR);
						
						$result = $prepare->execute();

						if ($result) {
							$status = true;
						} else {
							$status = false;
							$error .= "\nProduct is not added";
						}

					}

				} catch (PDOException $e) {
					$status = false;
					$error = $e->getMessage() . __LINE__;
				}

			}

			$response = array('status' => $status, 'error' => $error, 'data' => $data);
			echo json_encode($response);

			break;
		
		case 'productUpdate':
			
			$id = $_POST['id'] ?? null;
			$productId = $_POST['productName'] ?? null;
			$productPrice = $_POST['productPrice'] ?? 0;
			$productQuantity = $_POST['productQuantity'] ?? 0;
			$productTaxPercent = $_POST['productTaxPercent'] ?? 0;
			$productDiscount = $_POST['productDiscount'] ?? 0;
			$billNo = $_POST['billNo'] ?? null;

			if (empty($id)) {
				$status = false;
				$error = 'Something went wrong';
			}

			if (empty($productId)) {
				$status = false;
				$error = 'Please choose product to add';
			}

			if (empty($productPrice)) {
				$status = false;
				$error .= "\nPrice is not to be an empty";
			}				

			if (empty($productQuantity)) {
				$status = false;
				$error .= "\nPlease add product quantity";
			} 				
			
			if (empty($billNo)) {
				$status = false;
				$error .= "\nSomething went wrong";
			}								
			

			if (empty($error)) {
				
				$sql = "SELECT COUNT(*) AS totalRecords FROM billentry_sublist WHERE billNo=:billNo AND productId=:productId AND delete_status=:delete_status AND id!=:id";

				try {
					
					$prepare = $db->prepare($sql);
					$prepare->bindValue(':billNo', $billNo, PDO::PARAM_STR);
					$prepare->bindValue(':productId', $productId, PDO::PARAM_INT);
					$prepare->bindValue(':delete_status', false, PDO::PARAM_BOOL);
					$prepare->bindValue(':id', $id, PDO::PARAM_INT);
					$result = $prepare->execute();

					$totalRecords = $prepare->fetchColumn();

					if ($totalRecords > 0) {
						$status = false;
						$error = 'The product is already exist in this bill.';
					} else {

						$sql = "UPDATE billentry_sublist SET productId=:productId, price=:price, quantity=:quantity, subTotal=:subTotal, taxPercent=:taxPercent, taxAmount=:taxAmount, grandTotal=:grandTotal, discount=:discount, netTotal=:netTotal WHERE id=:id";

						$productPrice = floatval($productPrice);
						$productQuantity = floatval($productQuantity);
						$productTaxPercent = floatval($productTaxPercent);
						$productTaxPercent = floatval($productTaxPercent);

						$subTotal = $productPrice * $productQuantity;
						$taxAmount = $subTotal * ($productTaxPercent/100);
						$grandTotal = $subTotal + $taxAmount;
						$netTotal = $grandTotal - $productDiscount;

						$netTotal = number_format($netTotal, 2, '.', "");

						$prepare = $db->prepare($sql);
						$prepare->bindValue(':productId', $productId, PDO::PARAM_STR);
						$prepare->bindValue(':price', $productPrice, PDO::PARAM_STR);
						$prepare->bindValue(':quantity', $productQuantity, PDO::PARAM_STR);
						$prepare->bindValue(':subTotal', $subTotal, PDO::PARAM_STR);
						$prepare->bindValue(':taxPercent', $productTaxPercent, PDO::PARAM_STR);
						$prepare->bindValue(':taxAmount', $taxAmount, PDO::PARAM_STR);
						$prepare->bindValue(':grandTotal', $grandTotal, PDO::PARAM_STR);
						$prepare->bindValue(':discount', $productDiscount, PDO::PARAM_STR);
						$prepare->bindValue(':netTotal', $netTotal, PDO::PARAM_STR);
						$prepare->bindValue(':id', $id, PDO::PARAM_INT);
						
						$result = $prepare->execute();

						if ($result) {
							$status = true;
						} else {
							$status = false;
							$error .= "\nProduct is not updated";
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
		
		case 'productEdit':
			
			$id = $_POST['id'] ?? null;

			if (empty($id)) {
				$status = false;
				$error = 'Something went wrong';
			} else {
				$sql = "SELECT a.id as id, b.id as productId, b.productName as productName, a.price, a.quantity, a.taxPercent, a.discount FROM billentry_sublist a INNER JOIN products b ON a.productId=b.id WHERE a.id=:id";

				try {
					
					$prepare = $db->prepare($sql);
					$prepare->bindValue(':id', $id, PDO::PARAM_INT);
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
					$error = $e->getMessage() . __LINE__;
				}
			}

			$response = array('status' => $status, 'error' => $error, 'data' => $data);
			echo json_encode($response);

			break;

		case 'productDelete':
			
			$id = $_POST['id'] ?? null;

			if (empty($id)) {
				$status = false;
				$error = 'Something went wrong';
			} else {
				$sql = "UPDATE billentry_sublist SET delete_status=:delete_status WHERE id=:id";
				
				try {
					
					$prepare = $db->prepare($sql);
					$prepare->bindValue(':delete_status', true, PDO::PARAM_BOOL);
					$prepare->bindValue(':id', $id, PDO::PARAM_INT);
					$result = $prepare->execute();

					if ($result) {
						$status = true;
					} else {
						$status = false;
						$error = 'Something went wrong';
					}

				} catch (PDOException $e) {
					$status = false;
					$error = $e->getMessage() . __LINE__;
				}
			}

			$response = array('status' => $status, 'error' => $error, 'data' => $data);
			echo json_encode($response);

			break;

		case 'billSave':
			
			$billNo = $_POST['billNo'] ?? null;

			if (empty($billNo)) {
				$status = false;
				$error = 'Bill number is missing';
			} else {

				$billDate = $_POST['billDate'] ?? null;
				$customerName = $_POST['customerName'] ?? null;
				$paymentMode = $_POST['paymentMode'] ?? null;
				$paymentReference = $_POST['paymentReference'] ?? null;
				$description = $_POST['description'] ?? '';

				$subTotal = $_POST['subTotal'] ?? 0;
				$discount = $_POST['discount'] ?? 0;
				$grandTotal = $_POST['grandTotal'] ?? 0;
				$roundOff = $_POST['roundOff'] ?? 0;
				$netTotal = $_POST['netTotal'] ?? 0;

				if (!(strtotime($billDate) > 0)) {
					$status = false;
					$error = "Date is invalid";
				}

				if (empty($customerName)) {
					$status = false;
					$error .= "\nCustomer Name should not to be an empty";
				}

				if (empty($subTotal)) {
					$status = false;
					$error .= "\nSub Total should not to be an empty";
				}

				if (empty($grandTotal)) {
					$status = false;
					$error .= "\nGrand Total should not to be an empty";
				}

				if (empty($netTotal)) {
					$status = false;
					$error .= "\nNet Total should not to be an empty";
				}

				if (empty($error)) {					

					try {

						$prepare = $db->prepare('SET AUTOCOMMIT = 0;');
						$prepare->execute();

						$prepare = $db->prepare('START TRANSACTION;');
						$prepare->execute();

						$sql = "UPDATE billentry SET billNo=:newBillNo, billDate=:billDate, customerName=:customerName, subTotal=:subTotal, discount=:discount, grandTotal=:grandTotal, roundOff=:roundOff, netTotal=:netTotal, paymentMode=:paymentMode, paymentReference=:paymentReference, billStatus=:billStatus, description=:description, accountYear=:accountYear WHERE billNo=:billNo AND billStatus=:oldBillStatus AND delete_status=:delete_status";
					
						$newBillNo = Bill::billNo($billDate, $accountYear);						
						
						if (empty($newBillNo)) {
							$status = false;
							$error = 'Something went wrong';
						} else {
							
							$prepare = $db->prepare($sql);
							$prepare->bindValue(':newBillNo', $newBillNo, PDO::PARAM_STR);
							$prepare->bindValue(':billDate', $billDate, PDO::PARAM_STR);
							$prepare->bindValue(':customerName', $customerName, PDO::PARAM_STR);
							$prepare->bindValue(':subTotal', $subTotal, PDO::PARAM_STR);
							$prepare->bindValue(':discount', $discount, PDO::PARAM_STR);
							$prepare->bindValue(':grandTotal', $grandTotal, PDO::PARAM_STR);
							$prepare->bindValue(':roundOff', $roundOff, PDO::PARAM_STR);
							$prepare->bindValue(':netTotal', $netTotal, PDO::PARAM_STR);
							$prepare->bindValue(':paymentMode', $paymentMode, PDO::PARAM_STR);
							$prepare->bindValue(':paymentReference', $paymentReference, PDO::PARAM_STR);
							$prepare->bindValue(':billStatus', 'billed', PDO::PARAM_STR);
							$prepare->bindValue(':description', $description, PDO::PARAM_STR);
							$prepare->bindValue(':accountYear', $accountYear, PDO::PARAM_STR);
							$prepare->bindValue(':billNo', $billNo, PDO::PARAM_STR);
							$prepare->bindValue(':oldBillStatus', 'unsaved', PDO::PARAM_STR);
							$prepare->bindValue(':delete_status', false, PDO::PARAM_BOOL);
							$result = $prepare->execute();

							if ($result) {
								$status = true;	

								$sql = "UPDATE billentry_sublist SET billNo=:newBillNo WHERE delete_status=:delete_status AND billNo=:billNo";
								$prepare = $db->prepare($sql);
								$prepare->bindValue(':newBillNo', $newBillNo, PDO::PARAM_STR);
								$prepare->bindValue(':delete_status', false, PDO::PARAM_BOOL);
								$prepare->bindValue(':billNo', $billNo, PDO::PARAM_STR);
								$result = $prepare->execute();								

								if ($result) {
									$status = true;
									$prepare = $db->prepare('COMMIT;');
									$prepare->execute();
								} else {
									$status = false;
									$error = 'Unable to save bill';

									$prepare = $db->prepare('ROLLBACK;');
									$prepare->execute();
								}


							} else {
								$status = false;
								$error = 'Bill is not created';

								$prepare = $db->prepare('ROLLBACK;');
								$prepare->execute();
							}

						}

						
					} catch (PDOException $e) {
						$status = false;
						$error = $e->getMessage();
					}
					
				}				

			}

			$response = array('status' => $status, 'error' => $error, 'data' => $data);
			echo json_encode($response);

			break;

		case 'billUpdate':
			
			$id = $_POST['id'] ?? null;
			$billNo = $_POST['billNo'] ?? null;

			if (empty($id)) {
				$status = false;
				$error = 'Something went wrong';
			} else if (empty($billNo)) {
				$status = false;
				$error = 'Bill number is missing';
			} else {

				$billDate = $_POST['billDate'] ?? null;
				$customerName = $_POST['customerName'] ?? null;
				$paymentMode = $_POST['paymentMode'] ?? null;
				$paymentReference = $_POST['paymentReference'] ?? null;
				$description = $_POST['description'] ?? '';

				$subTotal = $_POST['subTotal'] ?? 0;
				$discount = $_POST['discount'] ?? 0;
				$grandTotal = $_POST['grandTotal'] ?? 0;
				$roundOff = $_POST['roundOff'] ?? 0;
				$netTotal = $_POST['netTotal'] ?? 0;

				if (!(strtotime($billDate) > 0)) {
					$status = false;
					$error = "Date is invalid";
				}

				if (empty($customerName)) {
					$status = false;
					$error .= "\nCustomer Name should not to be an empty";
				}

				if (empty($subTotal)) {
					$status = false;
					$error .= "\nSub Total should not to be an empty";
				}

				if (empty($grandTotal)) {
					$status = false;
					$error .= "\nGrand Total should not to be an empty";
				}

				if (empty($netTotal)) {
					$status = false;
					$error .= "\nNet Total should not to be an empty";
				}

				if (empty($error)) {					

					try {

						$prepare = $db->prepare('SET AUTOCOMMIT = 0;');
						$prepare->execute();

						$prepare = $db->prepare('START TRANSACTION;');
						$prepare->execute();

						$sql = "UPDATE billentry SET billDate=:billDate, customerName=:customerName, subTotal=:subTotal, discount=:discount, grandTotal=:grandTotal, roundOff=:roundOff, netTotal=:netTotal, paymentMode=:paymentMode, paymentReference=:paymentReference, description=:description WHERE id=:id AND billNo=:billNo AND delete_status=:delete_status";						

						$prepare = $db->prepare($sql);
						$prepare->bindValue(':billDate', $billDate, PDO::PARAM_STR);
						$prepare->bindValue(':customerName', $customerName, PDO::PARAM_STR);
						$prepare->bindValue(':subTotal', $subTotal, PDO::PARAM_STR);
						$prepare->bindValue(':discount', $discount, PDO::PARAM_STR);
						$prepare->bindValue(':grandTotal', $grandTotal, PDO::PARAM_STR);
						$prepare->bindValue(':roundOff', $roundOff, PDO::PARAM_STR);
						$prepare->bindValue(':netTotal', $netTotal, PDO::PARAM_STR);
						$prepare->bindValue(':paymentMode', $paymentMode, PDO::PARAM_STR);
						$prepare->bindValue(':description', $description, PDO::PARAM_STR);
						$prepare->bindValue(':paymentReference', $paymentReference, PDO::PARAM_STR);
						$prepare->bindValue(':id', $id, PDO::PARAM_INT);
						$prepare->bindValue(':billNo', $billNo, PDO::PARAM_STR);
						$prepare->bindValue(':delete_status', false, PDO::PARAM_BOOL);
						$result = $prepare->execute();

						if ($result) {
							$status = true;	

							$prepare = $db->prepare('COMMIT;');
							$prepare->execute();
						} else {
							$status = false;
							$error = 'Bill is not created';

							$prepare = $db->prepare('ROLLBACK;');
							$prepare->execute();
						}
						
					} catch (PDOException $e) {
						$status = false;
						$error = $e->getMessage();
					}
					
				}				

			}

			$response = array('status' => $status, 'error' => $error, 'data' => $data);
			echo json_encode($response);

			break;

		case 'billDelete':
			
			$id = $_POST['id'] ?? null;

			if (empty($id)) {
				$status = false;
				$error = 'Unable to delete bill';
			} else {

				try {
					
					$prepare = $db->prepare('SET AUTOCOMMIT = 0;');
					$prepare->execute();

					$prepare = $db->prepare('START TRANSACTION;');
					$prepare->execute();

					$sql = "SELECT billNo FROM billentry WHERE id=:id AND delete_status=:delete_status";
					$prepare = $db->prepare($sql);
					$prepare->bindValue(':id', $id, PDO::PARAM_INT);
					$prepare->bindValue(':delete_status', false, PDO::PARAM_BOOL);
					$result = $prepare->execute();

					$billNo = $prepare->fetchColumn();

					if (empty($billNo)) {
						$status = false;
						$error = 'Unable to delete bill';

						$prepare = $db->prepare('ROLLBACK;');
						$prepare->execute();
					} else {
						$sql = "UPDATE billentry_sublist SET delete_status=:delete_status WHERE billNo=:billNo AND delete_status=:old_delete_status";
						$prepare = $db->prepare($sql);
						$prepare->bindValue(':delete_status', true, PDO::PARAM_BOOL);
						$prepare->bindValue(':billNo', $billNo, PDO::PARAM_STR);
						$prepare->bindValue(':old_delete_status', false, PDO::PARAM_BOOL);
						$result = $prepare->execute();

						if ($result) {
							$sql = "UPDATE billentry SET delete_status=:delete_status WHERE id=:id";
							$prepare = $db->prepare($sql);
							$prepare->bindValue(':delete_status', true, PDO::PARAM_BOOL);
							$prepare->bindValue(':id', $id, PDO::PARAM_INT);
							$result = $prepare->execute();

							if ($result) {
								$status = true;
								$prepare = $db->prepare('COMMIT;');
								$prepare->execute();
							} else {
								$status = false;
								$error = 'Unable to delete bill';
							}
						} else {
							$status = false;
							$error = 'Unable to delete bill';
							$prepare = $db->prepare('ROLLBACK;');
							$prepare->execute();
						}
					}


				} catch (PDOException $e) {
					$status = false;
					$error = $e->getMessage() . __LINE__;
				}

			}

			$response = array('status' => $status, 'error' => $error, 'data' => $data);
			echo json_encode($response);

			break;

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

			$totalRecords = 0;
			$totalRecordsWithFilter = 0;

			$sql = "SELECT COUNT(*) AS totalRecords FROM billentry WHERE delete_status=:delete_status";
			
			try {
				$prepare = $db->prepare($sql);
				
				$bindParams = array(
					':delete_status' => false
				);

				$result = $prepare->execute($bindParams);

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

			if (empty($searchQuery)) {				

				$sql = "SELECT COUNT(*) AS totalRecordsWithFilter FROM billentry WHERE delete_status=:delete_status ORDER BY $columnName $columnSortOrder LIMIT :row, :rowLength";

			} else {

				$sql = "SELECT COUNT(*) AS totalRecordsWithFilter FROM billentry WHERE delete_status=:delete_status AND (billNo LIKE :searchBillNo OR billDate LIKE :searchBillDate OR customerName LIKE :searchCustomerName OR netTotal LIKE :searchNetTotal) ORDER BY $columnName $columnSortOrder LIMIT :row, :rowLength";	

			}

			try {
				$prepare = $db->prepare($sql);						
				
				$prepare->bindValue(':delete_status', false, PDO::PARAM_INT);

				if (!empty($searchQuery)) {
					$prepare->bindValue(':searchBillNo', "%$searchQuery%", PDO::PARAM_STR);
					$prepare->bindValue(':searchBillDate', "%$searchQuery%", PDO::PARAM_STR);
					$prepare->bindValue(':searchCustomerName', "%$searchQuery%", PDO::PARAM_STR);
					$prepare->bindValue(':searchNetTotal', "%$searchQuery%", PDO::PARAM_STR);
				}

				$prepare->bindParam(':row', $row, PDO::PARAM_INT);
				$prepare->bindParam(':rowLength', $rowLength, PDO::PARAM_INT);

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
				$sql = "SELECT id, billNo, billDate, customerName, netTotal, billStatus FROM billentry WHERE delete_status=:delete_status ORDER BY $columnName $columnSortOrder LIMIT :row, :rowLength";
			} else {
				$sql = "SELECT id, billNo, billDate, customerName, netTotal, billStatus FROM billentry WHERE delete_status=:delete_status AND (billNo LIKE :searchBillNo OR billDate LIKE :searchBillDate OR customerName LIKE :searchCustomerName OR netTotal LIKE :searchNetTotal) ORDER BY $columnName $columnSortOrder LIMIT :row, :rowLength";	
			}

			try {
				$prepare = $db->prepare($sql);								
				
				$prepare->bindValue(':delete_status', false, PDO::PARAM_BOOL);

				if (!empty($searchQuery)) {
					$prepare->bindValue(':searchBillNo', "%$searchQuery%", PDO::PARAM_STR);
					$prepare->bindValue(':searchBillDate', "%$searchQuery%", PDO::PARAM_STR);
					$prepare->bindValue(':searchCustomerName', "%$searchQuery%", PDO::PARAM_STR);
					$prepare->bindValue(':searchNetTotal', "%$searchQuery%", PDO::PARAM_STR);
				}

				$prepare->bindParam(':row', $row, PDO::PARAM_INT);
				$prepare->bindParam(':rowLength', $rowLength, PDO::PARAM_INT);

				$result = $prepare->execute();

				if ($result) {
					$status = true;					
					
					$records = $prepare->fetchAll();
					$data = array();
					$i = 1;
					foreach ($records as $key => $value) {

						$btn = '<div class="d-flex justify-content-between">';						
						$btn .= '<a href="pages/billentry/update.php?id='. $value['id'] .'" class="btn btn-warning"><i class="bi bi-pencil"></i></a>';
						
						if ($value['billStatus'] == 'unsaved') {
							$btn .= '<button class="btn btn-light" type="button" onclick="billDelete(' . $value['id'] . ')"><i class="bi bi-trash"></i></button>';
						} else {
							$btn .= '<button class="btn btn-danger" type="button" onclick="billDelete(' . $value['id'] . ')"><i class="bi bi-trash"></i></button>';
						}
						
						$btn .= '</div>';

						$data[] = array(
							'id' => $i,
							'date' => $value['billDate'],
							'billNo' => strtoupper($value['billNo']),
							'customerName' => ucfirst($value['customerName']),
							'netTotal' => $value['netTotal'],
							'status' => strtoupper($value['billStatus']),
							'action' => $btn
						);

						$i++;
					}

				} else {
					$status = false;
					$error = 'Something went wrong';
				}
			} catch (PDOException $e) {
				$error = $e->getMessage() . ' ' . __LINE__;
			}

			$response = array(
				'draw' => intval($draw),
				'iTotalRecords' => $totalRecords,
				'iTotalDisplayRecords' => $totalRecordsWithFilter,
				'aaData' => $data,
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