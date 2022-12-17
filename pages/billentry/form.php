<?php 

	$page = $page ?? null;
	$billId = $billId ?? null;	
	$data = array();

	require_once '../../configs/Bill.php';
	require_once '../../configs/Db.php';

	$currency = Bill::getCurrencySymbol();
	
	$db = new Db();
	

	if (!isset($billId) || empty($billId)) {		
		
		if (!isset($_SESSION['billNo'])) {
			$billNo = Bill::tempBillNo();
			$_SESSION['billNo'] = $billNo;			
		} else {
			$billNo = $_SESSION['billNo'];
		}

	} else {

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
			<input type="date" name="billDate" id="billDate" class="form-control" value="<?php echo $billDate; ?>" required autofocus />

		</div>

		<div class="col-md-4 my-2">
			
			<label for="customerName">Customer Name</label>
			<input type="text" name="customerName" id="customerName" class="form-control" value="<?php echo $customerName; ?>" placeholder="Enter The Customer Name" required />

		</div>

	</div>

	<div class="row m-0 border-bottom pb-5 mx-3">
		
		<div class="col-md-6 my-2">
			
			<label for="productName">Product Name</label>
			<select name="productName" id="productName" class="select2 form-control" required>
				<option value="null">Select Product</option>
			</select>

		</div>

		<div class="col-md-3 my-2">
			
			<label for="productPrice">Price</label>
			<input type="number" name="productPrice" id="productPrice" class="form-control" min="0" step=".01" placeholder="0.00" required />

		</div>

		<div class="col-md-3 my-2">
			
			<label for="productQuantity">Quantity</label>
			<input type="number" name="productQuantity" id="productQuantity" class="form-control" min="0" step=".01" placeholder="0.00" required />

			<span class="mini-text-container text-danger">
				<em>Sub Total <?php echo $currency; ?>: </em>
				<input type="number" name="productSubTotal" id="productSubTotal" class="form-control-plaintext mini-text-input text-end text-danger" min="0" step=".01" value="0.00" readonly />
			</span>

		</div>


		<div class="col-md-3 my-2">
			
			<label for="productTaxPercent">Tax %</label>
			<input type="number" name="productTaxPercent" id="productTaxPercent" class="form-control" min="0" step=".01" max="100" placeholder="0.00" required />
			
			<span class="mini-text-container text-danger">
				<em>Tax Amount <?php echo $currency; ?>: </em>
				<input type="number" name="productTaxAmount" id="productTaxAmount" class="form-control-plaintext mini-text-input text-end text-danger" min="0" step=".01" value="0.00" readonly />
			</span>

		</div>

		<div class="col-md-3 my-2">
			
			<label for="productDiscount">Discount</label>
			<input type="number" name="productDiscount" id="productDiscount" class="form-control" min="0" step=".01" placeholder="0.00" required />

			<span class="mini-text-container text-danger">
				<em>Grand Total <?php echo $currency; ?>: </em>
				<input type="number" name="productGrandTotal" id="productGrandTotal" class="form-control-plaintext mini-text-input text-end text-danger" min="0" step=".01" value="0.00" readonly />
			</span>

		</div>

		<div class="col-md-3 my-2">
			
			<label for="productNetTotal">Total</label>
			<input type="number" name="productNetTotal" id="productNetTotal" class="form-control-plaintext" min="0" step=".01" placeholder="0.00" readonly required />

		</div>

		<div class="col-md my-auto">
			
			<button type="button" class="btn btn-success w-100" id="productAddBtn">
				<i class="bi bi-plus-circle-fill"></i>
				Add
			</button>

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
				<select name="paymentMode" id="paymentMode" class="form-control" required>
					<option value="cash" <?php if($paymentMode == 'cash') { echo "selected"; } ?>>Cash</option>
					<option value="upi" <?php if($paymentMode == 'upi') { echo "selected"; } ?>>UPI</option>
					<option value="cheque" <?php if($paymentMode == 'cheque') { echo "selected"; } ?>>Cheque</option>
					<option value="neft" <?php if($paymentMode == 'neft') { echo "selected"; } ?>>Neft</option>
				</select>
			</div>

			<!-- /Payment Mode -->

			<!-- Payment Reference -->

			<div class="col-md-6 my-auto" id="paymentReferenceContainer">
				<label for="paymentReference">Payment Reference</label>
				<input type="text" name="paymentReference" id="paymentReference" class="form-control" value="<?php echo $paymentReference; ?>" placeholder="Enter Payment Reference Number" />
			</div>

			<!-- /Payment Reference -->

			<!-- Description -->

			<div class="col-md my-3">
				<label for="description">Description</label>
				<textarea name="description" id="description" class="form-control" placeholder="Enter your description upto 256 characters"><?php echo $description; ?></textarea>
			</div>

			<!-- /Description -->

		</div>

		<div class="col-md-4 row m-0">
			
			<!-- sub total -->

			<div class="col-md-6 my-auto">
				<label for="subTotal">Sub Total</label>
			</div>

			<div class="col-md-6 my-auto">
				<input type="number" name="subTotal" id="subTotal" class="form-control-plaintext text-end fst-italic" min="0" step=".01" value="<?php echo $subTotal; ?>" readonly required />
			</div>

			<!-- /sub total -->

			<!-- discount -->

			<div class="col-md-6 my-auto">
				<label for="discount">Discount</label>
			</div>

			<div class="col-md-6 my-auto">
				<input type="number" name="discount" id="discount" class="form-control-plaintext text-end fst-italic" min="0" step=".01" value="<?php echo $discount; ?>" required />
			</div>

			<!-- /discount -->

			<!-- grandTotal -->

			<div class="col-md-6 my-auto">
				<label for="grandTotal">Grand Total</label>
			</div>

			<div class="col-md-6 my-auto">
				<input type="number" name="grandTotal" id="grandTotal" class="form-control-plaintext text-end fst-italic" min="0" step=".01" value="<?php echo $grandTotal; ?>" readonly required />
			</div>

			<!-- /grandTotal -->

			<!-- roundOff -->

			<div class="col-md-6 my-auto">
				<label for="roundOff">Round Off</label>
			</div>

			<div class="col-md-6 my-auto">
				<input type="number" name="roundOff" id="roundOff" class="form-control-plaintext text-end fst-italic" min="0" step=".01" value="<?php echo $roundOff; ?>" required />
			</div>

			<!-- /roundOff -->

			<!-- netTotal -->

			<div class="col-md-6 my-auto">
				<label for="netTotal">Net Total</label>
			</div>

			<div class="col-md-6 my-auto">
				<input type="number" name="netTotal" id="netTotal" class="form-control-plaintext text-end fst-italic" min="0" step=".01" value="<?php echo $netTotal; ?>" readonly required />
			</div>

			<!-- /netTotal -->

		</div>

	</div>

	<div class="row m-0 border-bottom pb-5">
		
		<div class="col-md text-start">
			<button type="button" class="btn btn-warning px-5" id="clearFields">Clear</button>
		</div>

		<?php if($billStatus == 'unsaved'): ?>
		<div class="col-md text-end">
			<button type="button" class="btn btn-success px-5" id="billSaveBtn">Save</button>
		</div>
		<?php else: ?>
		<div class="col-md text-end">
			<button type="button" class="btn btn-success px-5" id="billUpdateBtn" data-id="<?php echo $billId; ?>">Update</button>
		</div>
		<?php endif; ?>

	</div>

</form>

<script type="text/javascript" src="pages/billentry/billEntryScript.js" defer></script>