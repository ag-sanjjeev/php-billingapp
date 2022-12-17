<?php /* Header Layout */ require_once '../../layouts/header.php'; /* Header Layout */ ?>

<?php session_start(); unset($_SESSION['billNo']); ?>
<div class="row mx-3 my-3 border-bottom pb-2">
	
	<div class="col-md-9 flex-grow">
		<h3 class="h3">Bill Entry</h3>
	</div>

	<div class="col-md text-end">
		<a href="pages/billentry/create.php" class="btn btn-success fw-light">
			<i class="bi bi-plus-circle"></i>
			Create
		</a>
	</div>

</div>

<div class="row mx-3 my-3 pb-2">
	
	<div class="col-md">
		
		<table class="table table-responsive-sm" id="billListTable">
			<thead>
				<th width="15%">S.No</th>
				<th>Date</th>
				<th>Bill No</th>
				<th>Customer Name</th>				
				<th>Total</th>
				<th>Bill Status</th>
				<th width="10%">Action</th>
			</thead>
			<tbody>
				
			</tbody>
		</table>

	</div>

</div>

<script type="text/javascript" src="pages/billentry/billEntryScript.js" defer></script>

<?php /* Footer Layout */ require_once '../../layouts/footer.php'; /* Footer Layout */ ?>