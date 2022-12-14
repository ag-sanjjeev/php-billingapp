<?php 
	$page = $page ?? null;
	$priceId = $priceId ?? null;

	$price = '';

?>

<form method="post" id="priceForm">

	<div class="row m-0 border-bottom pb-5">
		
		<div class="col-md d-flex justify-content-between mx-3 my-2">
			
			<select name="productName" id="productName" class="select2 form-control" required autofocus>
				<option value="null">Select Product</option>
			</select>

			<input type="number" name="price" id="price" class="form-control mx-3" step=".01" min="0" placeholder="0.00" required />

			<button type="button" class="btn btn-primary" id="createBtn">
				<i class="bi bi-plus-circle-fill"></i>
			</button>

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

<script type="text/javascript" src="pages/pricemaster/priceScript.js" defer></script>