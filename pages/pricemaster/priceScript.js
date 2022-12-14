$(document).ready(function () {

	$('#productName').on('select2:select', function(e) {
		let input = e.params.data;
		if (input.selected) {
			getRecentProductPrice(input.id);
		}
	});	

	$('form').on('submit', function(e) {
		e.preventDefault();
	});

	$('#createBtn').on('click', function(e) {

		createProductPrice();

	});

	$('#updateBtn').on('click', function(e) {		

		updateProduct();

	});

	$('select.select2').select2({
		width: 'resolve',
		open: true,
		ajax: {
			url: 'pages/pricemaster/controller.php',
			type: 'POST',
			dataType: 'json',
			delay: 250,
			data: function(params) {
				return {
					productName: params.term,
					action: 'getProducts'
				}
			},
			processResults: function(data, params) {
				params.page = params.page || 1;
				return {
					results: $.map(data.data, function(item) {
											
						return {
							text: item.productName,
							id: item.id
						}													

					}),
					pagination: {
						more: (params.page * 30) < data.data.total_count
					}
				};
			},
			cache: false
		},
		placeholder: 'Search Products',
		allowClear: true,
		minimumInputLength: 1		
	});

	$('select.select2').select2('open');

	dataTable__init();

});


function dataTable__init() {
	
	$('table#priceListTable').DataTable().destroy();
	$('table#priceListTable').DataTable({
		'processing': true,
		'serverSide': true,		
		'ajax': {
			'url': 'pages/pricemaster/controller.php',
			'method': 'post',
			'data': {'action':'datatableList'} 
		},
		'columns':[
			{'data': 'id', 'name': 'b.id'},
			{'data': 'productName', 'name': 'c.productName'},
			{'data': 'price', 'name': 'b.price'},
			{'data': 'action'}
		],
		'columnDefs': [{
			'targets':[3],
			'orderable': false			
		}]
	});

}

function getRecentProductPrice(id) {
	
	if (id != '') {

		$.ajax({
			type: 'POST',
			url: 'pages/pricemaster/controller.php',
			data: {'action' : 'getRecentProductPrice', 'productId' : id},
			success: function(res) {
				var obj = JSON.parse(res);

				if (!obj.status) {
					console.log(obj.error);					
				} else {

					$('#productList').html(obj.data);

				}
			},
			error: function(res) {
				console.log(res);				
			}
		});

	}

}

function createProductPrice() {
	
	var productId = $('#productName').val();
	var price = $('#price').val();

	if (productId == '') {
		alert('Please Enter The Product Name');
	} else if (price < 0 || price == '') {
		alert('Please Enter The Valid Price');
	}else {

		$.ajax({
			type: 'POST',
			url: 'pages/pricemaster/controller.php',
			data: {'action' : 'createProductPrice', 'productId' : productId, 'price' : price},
			success: function(res) {
				var obj = JSON.parse(res);

				if (!obj.status) {
					console.log(obj.error);
					alert(obj.error);
				} else {
					alert('Price is added successfully');
					console.log('Price is added successfully');

					$('#productName').val('').trigger('change');					
					$('#price').val(0);
				}
			},
			error: function(res) {
				console.log(res);
				alert('Something Went wrong try again later');
			}
		});

	}

}

function deletePrice(id) {
	
	if (confirm('Are you sure want to delete?')) {

		$.ajax({
			type: 'POST',
			url: 'pages/pricemaster/controller.php',
			data: {'action' : 'deletePrice', 'id': id},
			success: function(res) {
				var obj = JSON.parse(res);

				if (!obj.status) {
					console.log(obj.error);
					alert(obj.error);
				} else {
					alert('Price deleted successfully');
					dataTable__init();
				}
			}
		})

	}

}