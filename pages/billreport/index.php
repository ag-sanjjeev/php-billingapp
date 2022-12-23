<?php /* Header Layout */ require_once '../../layouts/header.php'; /* Header Layout */ ?>

<?php 
	$currentDate = Date('Y-m-d');
?>
<div class="row mx-3 my-3 border-bottom pb-2">
	
	<div class="col-md-9 flex-grow">
		<h3 class="h3">Bill Report</h3>
	</div>

</div>

<div class="row mx-3 my-3 pb-2">
	
	<div class="col-md-12 row mb-3">
		
		<div class="col-md">
			<label for="fromDate">From Date</label>
			<input type="date" name="fromDate" id="fromDate" class="form-control" value="<?php echo $currentDate ?>" required />
		</div>

		<div class="col-md">
			<label for="toDate">To Date</label>
			<input type="date" name="toDate" id="toDate" class="form-control" value="<?php echo $currentDate ?>" required />
		</div>

		<div class="col-md d-flex align-items-end">
			<button type="button" class="btn btn-success" id="searchBill">
				<i class="bi bi-search"></i>
				Search
			</button>
		</div>

	</div>

	<div class="col-md">
		
		<table class="table table-responsive-sm nowrap w-100" id="billListTable">
			<thead>
				<th width="15%">S.No</th>
				<th>Date</th>
				<th>Bill No</th>
				<th>Customer Name</th>				
				<th>Total</th>
				<th width="10%">Action</th>
			</thead>
			<tbody>
				
			</tbody>
			<tfoot>
				<tr>
					<td colspan="4" class="text-end">Net Total</td>
					<td align="left" class="text-end" id="netTotal">00.00</td>
					<td>&nbsp;</td>				
				</tr>
			</tfoot>
		</table>

	</div>

</div>

<script type="text/javascript" src="pages/billreport/billReportScript.js" defer></script>

<?php /* Footer Layout */ require_once '../../layouts/footer.php'; /* Footer Layout */ ?>