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

			if ($productName == null) {
				$sql = "SELECT * FROM products WHERE delete_status=:delete_status";
				$bindParams = array(':delete_status' => false);
			} else {
				$sql = "SELECT * FROM products WHERE productName LIKE '%" . $productName . "%' AND delete_status=:delete_status";
				$bindParams = array(':delete_status' => false);
			}			

			$prepare = $db->prepare($sql);
			$result = $prepare->execute($bindParams);

			if ($result == true) {
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
		
		default:
			# code...
			break;
	}

?>