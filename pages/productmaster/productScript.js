$(document).ready(function () {

	$('#productName').on('input', function(e) {
		let input = $(this).val();
		getSimilarProduct(input);
	});	

	$('form').on('submit', function(e) {
		e.preventDefault();
	});

	$('#createBtn').on('click', function(e) {

		createProduct();

	});

	$('#updateBtn').on('click', function(e) {		

		updateProduct();

	});

	dataTable__init();

});


function dataTable__init() {
	
	$('table#productListTable').DataTable().destroy();
	$('table#productListTable').DataTable({
		'processing': true,
		'serverSide': true,		
		'ajax': {
			'url': 'pages/productmaster/controller.php',
			'method': 'post',
			'data': {'action':'datatableList'} 
		},
		'columns':[
			{'data': 'id'},
			{'data': 'productName'},
			{'data': 'action'}
		],
		'columnDefs': [{
			'targets':[2],
			'orderable': false			
		}]
	});

}

function getSimilarProduct(productName) {
	
	if (productName.trim() != '') {

		$.ajax({
			type: 'POST',
			url: 'pages/productmaster/controller.php',
			data: {'action' : 'getSimilarProduct', 'productName' : productName},
			success: function(res) {
				var obj = JSON.parse(res);

				if (!obj.status) {
					console.log(obj.error);
					alert('Something went wrong');
				} else {

					$('#productList').html(obj.data);

				}
			},
			error: function(res) {
				console.log(res);
				alert('Something Went wrong try again later');
			}
		});

	}

}

function createProduct() {
	
	var productName = $('#productName').val();

	if (productName.trim() == '') {
		alert('Please Enter The Product Name');
	} else {

		$.ajax({
			type: 'POST',
			url: 'pages/productmaster/controller.php',
			data: {'action' : 'createProduct', 'productName' : productName},
			success: function(res) {
				var obj = JSON.parse(res);

				if (!obj.status) {
					console.log(obj.error);
					alert(obj.error);
				} else {

					alert(productName + ' is added successfully');
					console.log('Product added successfully');
					$('#productName').val('');
				}
			},
			error: function(res) {
				console.log(res);
				alert('Something Went wrong try again later');
			}
		});

	}

}

function updateProduct(id) {
	
	var id = $('#productId').val();
	var productName = $('#productName').val();

	if (id.trim() == '' || id == null) {
		alert('Something went wrong');
		return false;	
	}

	if (productName.trim() == '') {
		alert('Please Enter The Product Name');
		return false;
	} else {

		$.ajax({
			type: 'POST',
			url: 'pages/productmaster/controller.php',
			data: {'action' : 'updateProduct', 'productName' : productName, 'productId': id},
			success: function(res) {
				var obj = JSON.parse(res);

				if (!obj.status) {
					console.log(obj.error);
					alert(obj.error);
				} else {

					alert(productName + ' is updated successfully');
					console.log('Product updated successfully');
					window.location.href = 'pages/productmaster/index.php';
				}
			},
			error: function(res) {
				console.log(res);
				alert('Something Went wrong try again later');
			}
		});

	}

}

function deleteProduct(id) {
	
	if (confirm('Are you sure want to delete?')) {

		$.ajax({
			type: 'POST',
			url: 'pages/productmaster/controller.php',
			data: {'action' : 'deleteProduct', 'id': id},
			success: function(res) {
				var obj = JSON.parse(res);

				if (!obj.status) {
					console.log(obj.error);
					alert(obj.error);
				} else {
					alert('Product deleted successfully');
					dataTable__init();
				}
			}
		})

	}

}