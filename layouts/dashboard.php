<!-- @@@@@@@@@@@@@@@ DASHBOARD @@@@@@@@@@@@@@@ -->
<div class="dashboard-layout">

<!-- @@@@@@@@@@@@@@@ SMALL GRAPH @@@@@@@@@@@@@@@ -->
<div class="row small-graph-container m-0">
	
	<h3 class="heading my-2 text-primary">
		<i class="bi bi-pie-chart-fill"></i>
		Overview Graphs
	</h3>	

	<div class="col-md-3 my-3">
		
		<div class="card carolina-blue-bg py-2 px-1">
			
			<canvas id="noOfProductsChart"></canvas>

		</div>

	</div>

	<div class="col-md-3 my-3">
		
		<div class="card violet-blue-bg py-2 px-1">
			
			<canvas id="highlySold"></canvas>

		</div>

	</div>

	<div class="col-md-3 my-3">
		
		<div class="card spanish-green-bg py-2 px-1">
			
			<canvas id="percentageProductSold"></canvas>

		</div>

	</div>

	<div class="col-md-3 my-3">
		
		<div class="card flame-bg py-2 px-1">
						
			<canvas id="todayBills"></canvas>

		</div>

	</div>

	<hr>

</div>
<!-- @@@@@@@@@@@@@@@ /SMALL GRAPH @@@@@@@@@@@@@@@ -->

<!-- @@@@@@@@@@@@@@@ PRODUCT PRICE HISTORY GRAPH @@@@@@@@@@@@@@@ -->
<div class="row price-history-graph-container m-0">
	
	<h3 class="heading my-2 text-primary">
		<i class="bi bi-graph-up"></i>
		Product Price History
	</h3>

	<div class="col my-3">
		
		<div class="card carolina-blue-bg py-2 px-2">
			
			<select class="select2" id="productName">
				<option value="">Top 10 Products</option>
			</select>
			<canvas id="productPriceHistory" class="pb-5"></canvas>

		</div>

	</div>

</div>
<!-- @@@@@@@@@@@@@@@ /PRODUCT PRICE HISTORY GRAPH @@@@@@@@@@@@@@@ -->

<!-- @@@@@@@@@@@@@@@ BILLS HISTORY GRAPH @@@@@@@@@@@@@@@ -->
<div class="row bills-history-graph-container m-0">
	
	<h3 class="heading my-2 text-primary">
		<i class="bi bi-bar-chart-line-fill"></i>
		Bills History
	</h3>

	<div class="col my-3">
		
		<div class="card violet-blue-bg py-2 px-2">
			<canvas id="billsHistory"></canvas>
		</div>

	</div>

</div>
<!-- @@@@@@@@@@@@@@@ /BILLS HISTORY GRAPH @@@@@@@@@@@@@@@ -->

</div>
<!-- @@@@@@@@@@@@@@@ /DASHBOARD @@@@@@@@@@@@@@@ -->
<script type="text/javascript" src="./thirdparty/chartJs/chart.js" defer></script>
<script type="text/javascript" src="./assets/js/dashboard.js" defer></script>