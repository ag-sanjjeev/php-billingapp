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

});

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

	if (id.trim() == '' || id == null) {
		alert('Something went wrong');
	}

}