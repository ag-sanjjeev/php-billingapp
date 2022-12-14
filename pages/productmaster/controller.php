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
		case 'getSimilarProduct':
			
			$productName = $_POST['productName'] ?? null;

			if (empty($productName)) {
				$sql = "SELECT * FROM products WHERE delete_status=:delete_status LIMIT 0, 10";
			} else {
				$sql = "SELECT * FROM products WHERE productName LIKE :productName AND delete_status=:delete_status LIMIT 0, 10";
			}			

			$prepare = $db->prepare($sql);

			if (empty($productName)) {
				$prepare->bindValue(':delete_status', false, PDO::PARAM_BOOL);
			} else {
				$prepare->bindValue(':productName', "%$productName%", PDO::PARAM_STR);
				$prepare->bindValue(':delete_status', false, PDO::PARAM_BOOL);
			}

			$result = $prepare->execute();

			if ($result) {
				$status = true;
			} else {
				$status = false;
				$error = 'Error fetching products';
			}

			$products = $prepare->fetchAll();			

			if (empty($products) || !is_array($products)) {
				$data = '<li class="list-group-item">No Products Available</li>';
			} else {
				$i = 1;
				foreach ($products as $key => $value) {				
					$data .= '<li class="list-group-item">' . $i . ". " . $value['productName'] . '</li>';
					$i++;
				}
			}

			$response = array('status' => $status, 'error' => $error, 'data' => $data);

			echo json_encode($response);

			break;

		case 'createProduct':
			
			$productName = $_POST['productName'] ?? null;			

			if ($productName == null) {
				$status = false;
				$error = 'Product should not be an empty';
			} else {

				$sql = "SELECT * FROM products WHERE productName=:productName AND delete_status=:delete_status";				
				$prepare = $db->prepare($sql);

				$bindParams = array(':productName' => $productName, ':delete_status' => false);

				$result = $prepare->execute($bindParams);

				$count = $prepare->rowCount();

				if ($count > 0) {
					$status = false;
					$error = $productName . ' is already exist';
				} else {
					$sql = "INSERT INTO products (productName) VALUES (:productName)";
					$prepare = $db->prepare($sql);

					$bindParams = array(':productName' => $productName);
					$result = $prepare->execute($bindParams);

					if ($result) {
						$status = true;
						$error = '';
					}
				}

				$response = array('status' => $status, 'error' => $error, 'data' => $data);
				echo json_encode($response);

			}

			break;
		
		case 'updateProduct':
			
			$productId = $_POST['productId'] ?? null;
			$productName = $_POST['productName'] ?? null;

			if (empty($productId) || empty($productName)) {
				$status = false;
				$error = 'Product details are missing';
			} else {

				$sql = "UPDATE products SET productName=:productName WHERE id=:productId AND delete_status=:delete_status";
				try {
					
					$prepare = $db->prepare($sql);
					$prepare->bindValue(':productName', $productName, PDO::PARAM_STR);
					$prepare->bindValue(':productId', $productId, PDO::PARAM_INT);
					$prepare->bindValue(':delete_status', false, PDO::PARAM_BOOL);
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

		case 'datatableList':
			
			$draw = $_POST['draw'] ?? null;
			$row = $_POST['row'] ?? 0;
			$rowLength = (int) $_POST['length'] ?? 0;
			$columnIndex = $_POST['order'][0]['column'] ?? 0;
			$columnName = $_POST['columns'][$columnIndex]['data'] ?? null;
			$columnSortOrder = $_POST['order'][0]['dir'] ?? 'asc';
			$searchQuery = $_POST['search']['value'] ?? '';

			$totalRecords = 0;
			$totalRecordsWithFilter = 0;

			$sql = "SELECT COUNT(*) AS totalRecords FROM products WHERE delete_status=:delete_status";
			
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
				$sql = "SELECT COUNT(*) AS totalRecordsWithFilter FROM products WHERE delete_status=:delete_status ORDER BY :columnName :columnSortOrder LIMIT :row, :rowLength";
			} else {
				$sql = "SELECT COUNT(*) AS totalRecordsWithFilter FROM products WHERE productName LIKE :searchQuery AND delete_status=:delete_status ORDER BY :columnName :columnSortOrder LIMIT :row, :rowLength";	
			}
			
			try {
				$prepare = $db->prepare($sql);						

				if (!empty($searchQuery)) {
					$prepare->bindValue(':searchQuery', "%$searchQuery%", PDO::PARAM_STR);
				}

				$prepare->bindValue(':delete_status', false, PDO::PARAM_INT);
				$prepare->bindParam(':columnName', $columnName, PDO::PARAM_STR);
				$prepare->bindParam(':columnSortOrder', $columnSortOrder, PDO::PARAM_STR);
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
				$sql = "SELECT * FROM products WHERE delete_status=:delete_status ORDER BY :columnName :columnSortOrder LIMIT :row, :rowLength";
			} else {
				$sql = "SELECT * FROM products WHERE productName LIKE :searchQuery AND delete_status=:delete_status ORDER BY :columnName :columnSortOrder LIMIT :row, :rowLength";	
			}

			try {
				$prepare = $db->prepare($sql);								

				if (!empty($searchQuery)) {
					$prepare->bindValue(':searchQuery', "%$searchQuery%", PDO::PARAM_STR);
				}

				$prepare->bindValue(':delete_status', false, PDO::PARAM_BOOL);
				$prepare->bindParam(':columnName', $columnName, PDO::PARAM_STR);
				$prepare->bindParam(':columnSortOrder', $columnSortOrder, PDO::PARAM_STR);
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
						$btn .= '<a href="pages/productmaster/update.php?id='. $value['id'] .'" class="btn btn-warning"><i class="bi bi-pencil"></i></a>';
						$btn .= '<button class="btn btn-danger" type="button" onclick="deleteProduct(' . $value['id'] . ')"><i class="bi bi-trash"></i></button>';
						$btn .= '</div>';

						$data[] = array(
							'id' => $i,
							'productName' => ucfirst($value['productName']),
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

		case 'deleteProduct':
			
			$id = $_POST['id'] ?? null;

			if (empty($id)) {
				$status = false;
				$error = 'Cannot Process the request';
			} else {

				try {
					
					$sql = "UPDATE products SET delete_status=:delete_status WHERE id=:id";
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