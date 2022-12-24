<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<base href="http://localhost/php-billingapp/" />

	<!-- @@@@@@@@@@@@@@@ STYLES @@@@@@@@@@@@@@@ -->

	<link rel="stylesheet" type="text/css" href="./thirdparty/bootstrap/bootstrap.css" media="all" />
	<link rel="stylesheet" type="text/css" href="./thirdparty/css/animate.css" media="all" />
	<link rel="stylesheet" type="text/css" href="./thirdparty/DataTables/datatables.css" media="all" />
	<link rel="stylesheet" type="text/css" href="./thirdparty/icons/bootstrap-icons.css" media="all" />
	<link rel="stylesheet" type="text/css" href="./thirdparty/select2/css/select2.css" media="all" />

	<link rel="stylesheet" type="text/css" href="./assets/css/style.css" media="all" />

	<!-- @@@@@@@@@@@@@@@ /STYLES @@@@@@@@@@@@@@@ -->

	<title>Billing App</title>
</head>
<body>

<!-- @@@@@@@@@@@@@@@ WRAPPER @@@@@@@@@@@@@@@ -->

<div class="wrapper">
	
	<!-- @@@@@@@@@@@@@@@ SIDEBAR @@@@@@@@@@@@@@@ -->

	<aside class="py-2" id="sidebar">
		
		<h4 class="sidebar-heading">
			Explore Billing
			<button type="button" class="btn btn-outline-primary p-1 py-0" id="navbarCloseBtn">
				<i class="bi bi-x"></i>
			</button>
		</h4>		

		<a href="#mastersPageMenu" class="dropdown-toggle" data-bs-toggle="collapse" aria-expanded="false">
			Masters
		</a>
		<ul class="collapse list-unstyled" id="mastersPageMenu">
			<li>
				<a href="http://localhost/php-billingapp/pages/productmaster/index.php">Products</a>
			</li>
			<li>
				<a href="http://localhost/php-billingapp/pages/pricemaster/index.php">Price</a>
			</li>
		</ul>

		<a href="http://localhost/php-billingapp/pages/billentry/index.php" class="btn-link">
			Entry
			<i class="bi bi-link-45deg"></i>
		</a>

		<a href="#reportsPageMenu" class="dropdown-toggle" data-bs-toggle="collapse" aria-expanded="false">
			Reports
		</a>
		<ul class="collapse list-unstyled" id="reportsPageMenu">
			<li>
				<a href="http://localhost/php-billingapp/pages/billreport/index.php">Bill Report</a>
			</li>
		</ul>

	</aside>

	<!-- @@@@@@@@@@@@@@@ /SIDEBAR @@@@@@@@@@@@@@@ -->

	<!-- @@@@@@@@@@@@@@@ MAIN @@@@@@@@@@@@@@@ -->

	<main class="container-fluid px-0">
		 
		 <nav class="navbar navbar-light bg-primary px-2">
		 	
		 	<button type="button" class="btn btn-warning" id="navbarToggler">
		 		<i class="bi bi-list"></i> Menu
		 	</button>

		 	<a href="./" class="h3 navbar-brand text-warning">Billing App</a>

		 </nav>
