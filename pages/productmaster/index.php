<?php /* Header Layout */ require_once '../../layouts/header.php'; /* Header Layout */ ?>

<div class="row mx-3 my-3 border-bottom pb-2">
	
	<div class="col-md-9 flex-grow">
		<h3 class="h3">Product Master</h3>
	</div>

	<div class="col-md text-end">
		<a href="pages/productmaster/create.php" class="btn btn-success fw-light">
			<i class="bi bi-plus-circle"></i>
			Create
		</a>
	</div>

</div>

<div class="row mx-3 my-3 pb-2">
	
	<div class="col-md">
		
		<table class="table table-responsive-sm">
			<thead>
				<th width="15%">S.No</th>
				<th>Product Name</th>
				<th width="10%">Action</th>
			</thead>
			<tbody>
				<tr>
					<td colspan="3" align="center">No Products</td>
				</tr>
			</tbody>
		</table>

	</div>

</div>

<?php /* Footer Layout */ require_once '../../layouts/footer.php'; /* Footer Layout */ ?>