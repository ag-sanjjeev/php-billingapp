<?php
	
	require_once '../../configs/Db.php';

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
					
					$sql = "SELECT b.productName as productName, a.price as price  FROM prices a INNER JOIN products b ON a.productId=b.id WHERE a.productId=:productId AND a.delete_status=:delete_status ORDER BY a.id DESC LIMIT 10";
					$prepare = $db->prepare($sql);
					
					$prepare->bindValue(':productId', $productId, PDO::PARAM_INT);
					$prepare->bindValue(':delete_status', false, PDO::PARAM_BOOL);

					$result = $prepare->execute();
					
					$products = $prepare->fetchAll();			

					if (empty($products) || !is_array($products)) {
						$data = '<li class="list-group-item">No Products Available</li>';
					} else {
						$i = 1;
						foreach ($products as $key => $value) {				
							$data .= '<li class="list-group-item">' . $i . ". " . $value['productName'] . ' - ' . $value['price'] . ' Rs/- </li>';
							$i++;
						}
					}

					$status = true;					

				} catch (PDOException $e) {
					$status = false;
					$error = $e->getMessage();
				}

			}						


			$response = array('status' => $status, 'error' => $error, 'data' => $data);

			echo json_encode($response);

			break;

		case 'createProductPrice':
			
			$productId = $_POST['productId'] ?? null;
			$price = $_POST['price'] ?? null;

			if ($productId == null) {
				$status = false;
				$error = 'Product should not be an empty';
			} elseif (empty($price) || $price < 0) {
				$status = false;
				$error = 'Price should not be an empty or an invalid';
			} else {

				try {
					$sql = "INSERT INTO prices (productId, price) VALUES (:productId, :price)";
					$prepare = $db->prepare($sql);

					$prepare->bindValue(':productId', $productId, PDO::PARAM_INT);
					$prepare->bindValue(':price', $price, PDO::PARAM_STR); // PARAM_STR for Float & Double as well

					$result = $prepare->execute();

					if ($result) {
						$status = true;						
					} else {
						$status = false;
						$error = 'Price is not added';
					}
					
				} catch (PDOException $e) {
					$status = false;
					$error = $e->getMessage();
				}

			}

			$response = array('status' => $status, 'error' => $error, 'data' => $data);
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

			$sql = "SELECT COUNT(*) AS totalRecords FROM (SELECT max(id) as id FROM prices WHERE delete_status=:delete_status GROUP BY productId ORDER BY id DESC) t INNER JOIN prices b ON t.id=b.id INNER JOIN products c ON b.productId=c.id";
			
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

				$sql = "SELECT COUNT(*) AS totalRecordsWithFilter FROM (SELECT max(id) as id FROM prices WHERE delete_status=:delete_status GROUP BY productId ORDER BY id DESC) t INNER JOIN prices b ON t.id=b.id INNER JOIN products c ON b.productId=c.id ORDER BY $columnName $columnSortOrder LIMIT :row, :rowLength";

			} else {

				$sql = "SELECT COUNT(*) AS totalRecordsWithFilter FROM (SELECT max(id) as id FROM prices WHERE delete_status=:delete_status GROUP BY productId ORDER BY id DESC) t INNER JOIN prices b ON t.id=b.id INNER JOIN products c ON b.productId=c.id WHERE c.productName LIKE :searchProduct OR b.price LIKE :searchPrice ORDER BY $columnName $columnSortOrder LIMIT :row, :rowLength";	

			}

			try {
				$prepare = $db->prepare($sql);						
				
				$prepare->bindValue(':delete_status', false, PDO::PARAM_INT);

				if (!empty($searchQuery)) {
					$prepare->bindValue(':searchProduct', "%$searchQuery%", PDO::PARAM_STR);
					$prepare->bindValue(':searchPrice', "%$searchQuery%", PDO::PARAM_STR);
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
				$sql = "SELECT b.id, c.productName, b.price FROM (SELECT max(id) as id FROM prices WHERE delete_status=:delete_status GROUP BY productId ORDER BY id DESC) t INNER JOIN prices b ON t.id=b.id INNER JOIN products c ON b.productId=c.id ORDER BY $columnName $columnSortOrder LIMIT :row, :rowLength";
			} else {
				$sql = "SELECT b.id, c.productName, b.price FROM (SELECT max(id) as id FROM prices WHERE delete_status=:delete_status GROUP BY productId ORDER BY id DESC) t INNER JOIN prices b ON t.id=b.id INNER JOIN products c ON b.productId=c.id WHERE c.productName LIKE :searchProduct OR b.price LIKE :searchPrice ORDER BY $columnName $columnSortOrder LIMIT :row, :rowLength";	
			}

			try {
				$prepare = $db->prepare($sql);								
				
				$prepare->bindValue(':delete_status', false, PDO::PARAM_BOOL);

				if (!empty($searchQuery)) {
					$prepare->bindValue(':searchProduct', "%$searchQuery%", PDO::PARAM_STR);
					$prepare->bindValue(':searchPrice', "%$searchQuery%", PDO::PARAM_STR);
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
						$btn .= '<button class="btn btn-danger" type="button" onclick="deletePrice(' . $value['id'] . ')"><i class="bi bi-trash"></i></button>';
						$btn .= '</div>';

						$data[] = array(
							'id' => $i,
							'productName' => ucfirst($value['productName']),
							'price' => $value['price'],
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

		case 'deletePrice':
			
			$id = $_POST['id'] ?? null;

			if (empty($id)) {
				$status = false;
				$error = 'Cannot Process the request';
			} else {

				try {
					
					$sql = "UPDATE prices SET delete_status=:delete_status WHERE id=:id";
					$prepare = $db->prepare($sql);
					$prepare->bindValue(':delete_status', true, PDO::PARAM_BOOL);
					$prepare->bindValue(':id', $id, PDO::PARAM_INT);
					$result = $prepare->execute();

					if ($result) {
						$status = true;
						$error = '';
					} else {
						$status = false;
						$error = 'Something went wrong';
					}

				} catch (PDOException $e) {
					$status = false;
					$error = $e->getMessage();
				}

			}

			$response = array('status' => $status, 'error' => $error, 'data' => $data);
			echo json_encode($response);

			break;

		default:
			# code...
			break;
	}

?>