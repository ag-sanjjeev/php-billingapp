<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />

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

	<aside class="py-2 active" id="sidebar">
		
		<h4 class="sidebar-heading">
			Explore Billing
		</h4>		

		<a href="#mastersPageMenu" class="dropdown-toggle" data-bs-toggle="collapse" aria-expanded="false">
			Masters
		</a>
		<ul class="collapse list-unstyled" id="mastersPageMenu">
			<li>
				<a href="#">Page 1</a>
			</li>
			<li>
				<a href="#">Page 1</a>
			</li>
		</ul>

		<a href="#" class="btn-link">
			Entry
			<i class="bi bi-link-45deg"></i>
		</a>

		<a href="#reportsPageMenu" class="dropdown-toggle" data-bs-toggle="collapse" aria-expanded="false">
			Reports
		</a>
		<ul class="collapse list-unstyled" id="reportsPageMenu">
			<li>
				<a href="#">Page 1</a>
			</li>
			<li>
				<a href="#">Page 1</a>
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

		 	<h3 class="navbar-brand text-warning">Billing App</h3>

		 </nav>
