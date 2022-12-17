<?php /* Header Layout */ require_once '../../layouts/header.php'; /* Header Layout */ ?>

<div class="row mx-3 my-3 border-bottom pb-2">
	
	<div class="col-md-9 flex-grow">
		<h3 class="h3">Create Bill</h3>
	</div>

	<div class="col-md text-end">
		<a href="pages/billentry/index.php" class="btn btn-success fw-light">
			<i class="bi bi-arrow-left"></i>
			Back
		</a>
	</div>

</div>

<?php 
	session_start();
	
	$page = "create";
	/* Form Layout */ require_once './form.php'; /* Form Layout */ 
?>

<?php /* Footer Layout */ require_once '../../layouts/footer.php'; /* Footer Layout */ ?>