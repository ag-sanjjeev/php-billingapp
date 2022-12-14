<?php 
	$page = $page ?? null;
	$productId = $productId ?? null;

	$productName = '';

	if ($page == 'update' && !empty($productId)) {
		
		require_once '../../configs/Db.php';
		$db = new Db();
		
		try {
			
			$sql = "SELECT productName FROM products WHERE delete_status=:delete_status AND id=:id";
			$prepare = $db->prepare($sql);
				
			$bindParams = array(
				':delete_status' => false,
				':id' => $productId
			);

			$result = $prepare->execute($bindParams);

			if ($result) {
				$productName = $prepare->fetchColumn();
			} else {
				header("Location: pages/productmaster/index.php");
			}

		} catch (PDOException $e) {
			
			header("Location: pages/productmaster/index.php");			

		}		

	}
?>

<form method="post" id="productForm">

	<div class="row m-0 border-bottom pb-5">
		
		<div class="col-md d-flex justify-content-between mx-3 my-2">
			
			<input type="text" name="productName" id="productName" class="form-control mx-3" value="<?php echo $productName; ?>" placeholder="Enter Product Name" required autofocus />

			<?php if($page == 'create'): ?>

			<button type="button" class="btn btn-primary" id="createBtn">
				<i class="bi bi-plus-circle-fill"></i>
			</button>

			<?php elseif($page == 'update'): ?>

			<input type="hidden" name="productId" id="productId" value="<?php echo $productId ?>">

			<button type="button" class="btn btn-primary" id="updateBtn">
				<i class="bi bi-arrow-up-square-fill"></i>
			</button>

			<?php endif; ?>

		</div>

	</div>

</form>

<div class="row m-0">
	
	<div class="col-md mx-3">
		
		<h4 class="h4 my-3">Products</h4>

		<ul class="list-group list-group-flush" id="productList">
			
			<li class="list-group-item">No Products Available</li>			

		</ul>

	</div>

</div>

<script type="text/javascript" src="pages/productmaster/productScript.js" defer></script>