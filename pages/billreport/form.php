<?php 

	$page = $page ?? null;
	$billId = $billId ?? null;	
	$data = array();

	require_once '../../configs/Bill.php';
	require_once '../../configs/Db.php';

	$currency = Bill::getCurrencySymbol();
	
	$db = new Db();
	

	if (isset($billId) && !empty($billId)) {		

		$sql = "SELECT * FROM billentry WHERE id=:id AND delete_status=:delete_status";

		try {
			
			$prepare = $db->prepare($sql);
			$prepare->bindValue(':id', $billId, PDO::PARAM_INT);
			$prepare->bindValue(':delete_status', false, PDO::PARAM_BOOL);
			$result = $prepare->execute();

			if ($result) {
				
				$data = $prepare->fetch();				

			} else {
				die('Something went wrong');
			}

		} catch (PDOException $e) {
			die($e->getMessage());
		}

	}

	$billDate = $data['billDate'] ?? Date('Y-m-d');
	$billDate = (strtotime($billDate) > 0) ? $billDate : Date('Y-m-d');
	$billNo = $data['billNo'] ?? $billNo;
	$customerName = $data['customerName'] ?? '';
	$subTotal = $data['subTotal'] ?? 0;
	$discount = $data['discount'] ?? 0;
	$grandTotal = $data['grandTotal'] ?? 0;
	$roundOff = $data['roundOff'] ?? 0;
	$netTotal = $data['netTotal'] ?? 0;
	$paymentMode = $data['paymentMode'] ?? 'cash';
	$paymentReference = $data['paymentReference'] ?? '';
	$billStatus = $data['billStatus'] ?? 'unsaved'; 
	$description = $data['description'] ?? '';
	
?>

<form method="post" id="billForm">

	<div class="row m-0 border-bottom pb-5 mx-3">
		
		<div class="col-md-4 my-2">
			
			<label for="billNo">Bill No</label>
			<input type="text" name="billNo" id="billNo" class="form-control-plaintext" value="<?php echo $billNo; ?>" readonly />

		</div>

		<div class="col-md-4 my-2">
			
			<label for="billDate">Bill Date</label>
			<input type="date" name="billDate" id="billDate" class="form-control-plaintext" value="<?php echo $billDate; ?>" readonly />

		</div>

		<div class="col-md-4 my-2">
			
			<label for="customerName">Customer Name</label>
			<input type="text" name="customerName" id="customerName" class="form-control-plaintext" value="<?php echo $customerName; ?>" placeholder="Enter The Customer Name" readonly />

		</div>

	</div>

</form>

<div class="row m-0 border-bottom pb-5 mx-3">
	
	<div class="col-md mx-3">
		
		<h4 class="h4 my-3">Products On Bill</h4>

		<table class="table table-responsive-sm w-100" id="productBillListTable">
			<thead>
				<th width="10%">S.No</th>
				<th width="30%">Product Name</th>
				<th>Price</th>
				<th>Quantity</th>				
				<th>Tax</th>
				<th>Net Total</th>
				<th width="10%">Action</th>
			</thead>
		</table>

	</div>

</div>

<form method="post" id="billForm">

	<div class="row m-0 align-items-start py-3">

		<div class="col-md row m-0">
			
			<!-- Payment Mode -->

			<div class="col-md-6 my-auto">
				<label for="paymentMode">Payment Mode</label>
				<input type="text" name="paymentMode" id="paymentMode" class="form-control-plaintext" value="<?php echo strtoupper($paymentMode); ?>" readonly />
			</div>

			<!-- /Payment Mode -->

			<!-- Payment Reference -->

			<div class="col-md-6 my-auto" id="paymentReferenceContainer">
				<label for="paymentReference">Payment Reference</label>
				<input type="text" name="paymentReference" id="paymentReference" class="form-control-plaintext" value="<?php echo $paymentReference; ?>" placeholder="-" readonly />
			</div>

			<!-- /Payment Reference -->

			<!-- Description -->

			<div class="col-md my-3">
				<label for="description">Description</label>
				<textarea name="description" id="description" class="form-control-plaintext" placeholder="-" readonly><?php echo $description; ?></textarea>
			</div>

			<!-- /Description -->

		</div>

		<div class="col-md-4 row m-0">
			
			<!-- sub total -->

			<div class="col-md-6 my-auto">
				<label for="subTotal">Sub Total</label>
			</div>

			<div class="col-md-6 my-auto">
				<input type="number" name="subTotal" id="subTotal" class="form-control-plaintext text-end fst-italic" min="0" step=".01" value="<?php echo $subTotal; ?>" readonly />
			</div>

			<!-- /sub total -->

			<!-- discount -->

			<div class="col-md-6 my-auto">
				<label for="discount">Discount</label>
			</div>

			<div class="col-md-6 my-auto">
				<input type="number" name="discount" id="discount" class="form-control-plaintext text-end fst-italic" min="0" step=".01" value="<?php echo $discount; ?>" readonly />
			</div>

			<!-- /discount -->

			<!-- grandTotal -->

			<div class="col-md-6 my-auto">
				<label for="grandTotal">Grand Total</label>
			</div>

			<div class="col-md-6 my-auto">
				<input type="number" name="grandTotal" id="grandTotal" class="form-control-plaintext text-end fst-italic" min="0" step=".01" value="<?php echo $grandTotal; ?>" readonly />
			</div>

			<!-- /grandTotal -->

			<!-- roundOff -->

			<div class="col-md-6 my-auto">
				<label for="roundOff">Round Off</label>
			</div>

			<div class="col-md-6 my-auto">
				<input type="number" name="roundOff" id="roundOff" class="form-control-plaintext text-end fst-italic" min="0" step=".01" value="<?php echo $roundOff; ?>" readonly />
			</div>

			<!-- /roundOff -->

			<!-- netTotal -->

			<div class="col-md-6 my-auto">
				<label for="netTotal">Net Total</label>
			</div>

			<div class="col-md-6 my-auto">
				<input type="number" name="netTotal" id="netTotal" class="form-control-plaintext text-end fst-italic" min="0" step=".01" value="<?php echo $netTotal; ?>" readonly />
			</div>

			<!-- /netTotal -->

		</div>

	</div>

</form>

<script type="text/javascript" src="pages/billreport/billReportScript.js" defer></script>