<?php /* Header Layout */ require_once '../../layouts/header.php'; /* Header Layout */ ?>

<div class="row mx-3 my-3 border-bottom pb-2">
	
	<div class="col-md-9 flex-grow">
		<h3 class="h3">Price Master</h3>
	</div>

	<div class="col-md text-end">
		<a href="pages/pricemaster/create.php" class="btn btn-success fw-light">
			<i class="bi bi-plus-circle"></i>
			Create
		</a>
	</div>

</div>

<div class="row mx-3 my-3 pb-2">
	
	<div class="col-md">
		
		<table class="table table-responsive-sm" id="priceListTable">
			<thead>
				<th width="15%">S.No</th>
				<th>Product Name</th>
				<th>Price</th>
				<th width="10%">Action</th>
			</thead>
			<tbody>
				
			</tbody>
		</table>

	</div>

</div>

<script type="text/javascript" src="pages/pricemaster/priceScript.js" defer></script>

<?php /* Footer Layout */ require_once '../../layouts/footer.php'; /* Footer Layout */ ?>