$(document).ready(function () {

	paymentReferenceToggle();
	product_sublist();

	$('#productName').on('select2:select', function(e) {
		let input = e.params.data;
		if (input.selected) {
			getRecentProductPrice(input.id);
		}
	});	

	$('#productPrice, #productQuantity, #productTaxPercent, #productDiscount').on('input change', function(e) {
		calcProductPrice();
	});

	$('#discount, #roundOff').on('input change', function(e) {
		calcBillPrice();
	})

	$('#paymentMode').on('change', function(e) {
		paymentReferenceToggle();
	});

	$('form').on('submit', function(e) {
		e.preventDefault();
	});

	$(document).on('click', '#productAddBtn', function(e) {
		productAction('productAdd');
	});

	$(document).on('click', '#productUpdateBtn', function(e) {		
		var id = $(this).data('id');
		productAction('productUpdate', id);
	});

	$(document).on('click', '#billSaveBtn', function(e) {
		billEntryAction('billSave');
	});

	$(document).on('click', '#billUpdateBtn', function(e) {
		var id = $(this).data('id');		
		billEntryAction('billUpdate', id);
	});

	$('#clearFields').on('click', function(e) {
		$('#billDate, #customerName, #paymentReference, #description').val('').trigger('changer');
		$('#paymentMode').val('cash').trigger('change');
		$('#discount, #roundOff').val(0).trigger('change');
	});

	$('select.select2').select2({
		width: 'resolve',
		open: false,
		ajax: {
			url: 'pages/billentry/controller.php',
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

	dataTable__init();

});


function dataTable__init() {
	
	$('table#billListTable').DataTable().destroy();
	$('table#billListTable').DataTable({
		'processing': true,
		'serverSide': true,		
		'ajax': {
			'url': 'pages/billentry/controller.php',
			'method': 'post',
			'data': {'action':'datatableList'} 
		},
		'columns':[
			{'data': 'id', 'name': 'id'},
			{'data': 'date', 'name': 'billDate'},
			{'data': 'billNo', 'name': 'billNo'},
			{'data': 'customerName', 'name': 'customerName'},
			{'data': 'netTotal', 'name': 'netTotal'},
			{'data': 'status', 'name': 'billStatus'},
			{'data': 'action'}
		],
		'columnDefs': [{
			'targets':[6],
			'orderable': false			
		}],
		'rowCallback': function(row, data, index) {			
			if (data.status == 'UNSAVED') {
				$('td', row).addClass('bg-danger');
				$('td', row).addClass('text-white');
			}
		}
	});

}

function getRecentProductPrice(id) {
	
	if (id != '') {

		$.ajax({
			type: 'POST',
			url: 'pages/billentry/controller.php',
			data: {'action' : 'getRecentProductPrice', 'productId' : id},
			success: function(res) {
				var obj = JSON.parse(res);

				if (!obj.status) {
					console.log(obj.error);					
				} else {

					$('#productPrice').val(obj.data);
					calcProductPrice();

				}
			},
			error: function(res) {
				console.log(res);				
			}
		});

	}

}

function paymentReferenceToggle() {
	var paymentMode = $('#paymentMode').val();

	if (paymentMode == 'cash' || paymentMode == 'upi') {
		$('#paymentReference, #paymentReferenceContainer').hide();
	} else {
		$('#paymentReference, #paymentReferenceContainer').show();
	}
}

function calcProductPrice() {
	var productPrice = $('#productPrice');
	var productQuantity = $('#productQuantity');
	var productTaxPercent = $('#productTaxPercent');
	var productDiscount = $('#productDiscount');

	var price = productPrice.val();
	var quantity = productQuantity.val();
	var taxPercent = productTaxPercent.val();
	var discount = productDiscount.val();

	var productSubTotal = $('#productSubTotal');
	var productTaxAmount = $('#productTaxAmount');
	var productGrandTotal = $('#productGrandTotal');
	var productNetTotal = $('#productNetTotal');



	if (price == '' || price == null || price == undefined || price <= 0 || isNaN(price) || isNaN(parseFloat(price))) {
		price = 0;
	} 

	if (quantity == '' || quantity == null || quantity == undefined || quantity <= 0 || isNaN(quantity) || isNaN(parseFloat(quantity))) {
		quantity = 0;
	} 

	if (taxPercent == '' || taxPercent == null || taxPercent == undefined || taxPercent <= 0 || isNaN(taxPercent) || isNaN(parseFloat(taxPercent))) {
		taxPercent = 0;
	} 

	if (discount == '' || discount == null || discount == undefined || discount <= 0 || isNaN(discount) || isNaN(parseFloat(discount))) {
		discount = 0;
	} 

	let subTotal = price * quantity;
	let taxAmount = subTotal * (taxPercent/100);
	let grandTotal = subTotal + taxAmount;
	let netTotal = grandTotal - discount;

	if (subTotal == '' || subTotal == null || subTotal == undefined || subTotal <= 0 || isNaN(subTotal) || isNaN(parseFloat(subTotal))) {
		subTotal = 0;
	} 

	if (taxAmount == '' || taxAmount == null || taxAmount == undefined || taxAmount <= 0 || isNaN(taxAmount) || isNaN(parseFloat(taxAmount))) {
		taxAmount = 0;
	} 

	if (grandTotal == '' || grandTotal == null || grandTotal == undefined || grandTotal <= 0 || isNaN(grandTotal) || isNaN(parseFloat(grandTotal))) {
		grandTotal = 0;
	} 

	if (netTotal == '' || netTotal == null || netTotal == undefined || netTotal <= 0 || isNaN(netTotal) || isNaN(parseFloat(netTotal))) {
		netTotal = 0;
	} 

	productSubTotal.val(subTotal.toFixed(2));
	productTaxAmount.val(taxAmount.toFixed(2));
	productGrandTotal.val(grandTotal.toFixed(2));
	netTotal = (netTotal * 100).toFixed(0) / 100; // to evaluate as number_format in php
	productNetTotal.val(netTotal);
}

function calcBillPrice() {
	var billSubTotal = $('#subTotal');
	var billDiscount = $('#discount');
	var billGrandTotal = $('#grandTotal');
	var billRoundOff = $('#roundOff');
	var billNetTotal = $('#netTotal');

	var subTotal = billSubTotal.val();
	var discount = billDiscount.val();
	var roundOff = billRoundOff.val();
	
	subTotal = parseFloat(subTotal);
	discount = parseFloat(discount);
	roundOff = parseFloat(roundOff);
	
	if (subTotal == '' || subTotal == null || subTotal == undefined || subTotal <= 0 || isNaN(subTotal) || isNaN(parseFloat(subTotal))) {
		subTotal = 0;
	} 

	if (discount == '' || discount == null || discount == undefined || discount <= 0 || isNaN(discount) || isNaN(parseFloat(discount))) {
		discount = 0;
	} 

	if (roundOff == '' || roundOff == null || roundOff == undefined || roundOff <= 0 || isNaN(roundOff) || isNaN(parseFloat(roundOff))) {
		roundOff = 0;
	} 

	grandTotal = subTotal - discount;

	if (grandTotal == '' || grandTotal == null || grandTotal == undefined || grandTotal <= 0 || isNaN(grandTotal) || isNaN(parseFloat(grandTotal))) {
		grandTotal = 0;
	} 

	if (grandTotal < 10 && roundOff == 0) {
		netTotal = Math.ceil(grandTotal);
		roundOff = netTotal - grandTotal;	
	} else if(grandTotal > 10 && roundOff ==0) {
		let a = Math.ceil(grandTotal);
		a /= 10;
		a = Math.ceil(a);
		a *= 10;

		netTotal = a;
		roundOff = netTotal - grandTotal;
	} else {
		netTotal = parseFloat(grandTotal) + roundOff;
	}

	if (netTotal == '' || netTotal == null || netTotal == undefined || netTotal <= 0 || isNaN(netTotal) || isNaN(parseFloat(netTotal))) {
		netTotal = 0;
	} 

	billGrandTotal.val(grandTotal.toFixed(2));
	billRoundOff.val(roundOff.toFixed(2));
	billNetTotal.val(netTotal.toFixed(2));

}

function productAction(action='', id = '') {
	
	var productName = $('#productName').val();
	var productPrice = $('#productPrice').val();
	var productQuantity = $('#productQuantity').val();
	var productTaxPercent = $('#productTaxPercent').val();
	var productDiscount = $('#productDiscount').val();

	var billNo = $('#billNo').val();

	var error = '';

	if (productName == '' || productName == null) {
		error = 'Please choose product to add';
	}

	if (productPrice == '' || productPrice <= 0 || productPrice == null) {
		error += "\nPrice is not to be an empty";
	}

	if (productQuantity == '' || productQuantity <= 0 || productQuantity == null) {
		error += "\nPlease add product quantity";
	}

	if (error != '') { alert(error); return false; }

	var formData = $('#productName, #productPrice, #productQuantity, #productTaxPercent, #productDiscount, #billNo').serialize();

	if (action == 'productUpdate') {

		if (id == '') {
			return false;
		}			

	}
	
	formData += '&action=' + action + '&id=' + id;

	$.ajax({
		type: 'POST',
		url: 'pages/billentry/controller.php',
		data: formData,
		success: function(res) {
			var obj = JSON.parse(res);

			if (!obj.status) {
				console.log(obj.error);
				alert(obj.error);
			} else {
				$('#productName, #productPrice, #productQuantity, #productTaxPercent, #productDiscount').val('').trigger('change');				

				if (action == 'productAdd') {
					alert('Product added successfully');
				} else if (action == 'productUpdate') {

					alert('Product updated successfully');

					$('#productUpdateBtn').removeClass('btn-warning');
					$('#productUpdateBtn').addClass('btn-success');
					$('#productUpdateBtn').html('<i class="bi bi-plus-circle-fill"></i> Add');
					$('#productUpdateBtn').attr('data-id', id);
					$('#productUpdateBtn').attr('id', 'productAddBtn');
				}

				product_sublist();
			}

		}, 
		error: function(res) {
			console.log(res);
			alert('Something went wrong');
		}
	});

}

function billEntryAction(action='', id = '') {
	
	var billNo = $('#billNo').val();
	var billDate = $('#billDate').val();
	var customerName = $('#customerName').val();
	var paymentMode = $('#paymentMode').val();
	var paymentReference = $('#paymentReference').val();
	var netTotal = $('#netTotal').val();	

	var error = '';

	if (billNo == '' || billNo == null) {
		error = "Bill number should not be an empty";
	}

	if (billDate == '' || billDate == null || isNaN(Date.parse(billDate))) {
		error += "\nDate is not valid";
	}

	if (customerName == '' || customerName == null) {
		error += "\nCustomer Name is not to be an empty";
	}

	if (paymentMode == 'cheque' || paymentMode == 'neft') {
		if (paymentReference == '' || paymentReference == null) {
			error += "\nPlease give payment reference";
		}
	}

	if (error != '') { alert(error); return false; }

	var formData = $('#billNo, #billDate, #customerName, #paymentMode, #paymentReference, #description, #subTotal, #discount, #grandTotal, #roundOff, #netTotal').serialize();

	if (action == 'billUpdate') {

		if (id == '') {
			return false;
		}

	}
	
	formData += '&action=' + action + '&id=' + id;

	$.ajax({
		type: 'POST',
		url: 'pages/billentry/controller.php',
		data: formData,
		success: function(res) {
			var obj = JSON.parse(res);

			if (!obj.status) {
				console.log(obj.error);
				alert(obj.error);
			} else {
				if (action == 'billSave') {
					alert('Bill saved successfully');
				} else if (action == 'billUpdate') {
					alert('Bill updated successfully');					
				}

				window.location.href = 'pages/billentry/index.php';
			}

		}, 
		error: function(res) {
			console.log(res);
			alert('Something went wrong');
		}
	});

}

function product_sublist() {
	var billNo = $('#billNo').val();

	if (billNo != '') {

		$('table#productBillListTable').DataTable().destroy();
		$('table#productBillListTable').DataTable({
			'processing': true,
			'serverSide': true,		
			'ajax': {
				'url': 'pages/billentry/controller.php',
				'method': 'post',
				'data': {'action':'product_sublist', 'billNo' : billNo} 
			},
			'columns':[
				{'data': 'id', 'name': 'a.id'},
				{'data': 'productName', 'name': 'b.productName'},
				{'data': 'price', 'name': 'a.price', 'className': 'text-end'},
				{'data': 'quantity', 'name': 'a.quantity', 'className': 'text-end'},
				{'data': 'tax', 'name': 'a.taxAmount', 'className': 'text-end'},
				{'data': 'netTotal', 'name': 'a.netTotal', 'className': 'text-end'},
				{'data': 'action'}
			],
			'columnDefs': [{
				'targets':[6],
				'orderable': false			
			}],
			'initComplete': function(settings, json) {
				$('#subTotal').val(json.subTotal.toFixed(2));
				calcBillPrice();
			}
		});

	}
}

function productEdit(id) {
	
	$.ajax({
		type: 'POST',
		url: 'pages/billentry/controller.php',
		data: {'action': 'productEdit', 'id' : id},
		success: function(res) {
			var obj = JSON.parse(res);

			if (!obj.status) {
				console.log(obj.error);
				alert(obj.error);
			} else {
				let data = obj.data; 
				$('#productName').append('<option value="'+data.productId+'">'+data.productName+'</option>');
				$('#productName').val(data.productId).trigger('change');
				$('#productPrice').val(data.price);
				$('#productQuantity').val(data.quantity);
				$('#productTaxPercent').val(data.taxPercent);
				$('#productDiscount').val(data.discount).trigger('change');

				$('#productAddBtn').removeClass('btn-success');
				$('#productAddBtn').addClass('btn-warning');
				$('#productAddBtn').html('<i class="bi bi-upload"></i> Update');
				$('#productAddBtn').attr('data-id', id);
				$('#productAddBtn').attr('id', 'productUpdateBtn');
			}

		}, 
		error: function(res) {
			console.log(res);
			alert('Something went wrong');
		}
	});

}

function productDelete(id) {
	if (confirm('Are you sure?')) {
		$.ajax({
			type: 'POST',
			url: 'pages/billentry/controller.php',
			data: {'action': 'productDelete', 'id': id},
			success: function(res) {
				var obj = JSON.parse(res);

				if (!obj.status) {
					console.log(obj.error);
					alert(obj.error);
				} else {
					alert('Product is deleted');
					product_sublist();
				}
			},
			error: function(res) {
				console.log(res);
				alert('Something went wrong');
			}
		})
	}
}

function billDelete(id) {
	if (confirm('Are you sure?')) {
		$.ajax({
			type: 'POST',
			url: 'pages/billentry/controller.php',
			data: {'action': 'billDelete', 'id' : id},
			success: function(res) {
				var obj = JSON.parse(res);
				if (!obj.status) {
					console.log(obj.error);
					alert(obj.error);
				} else {
					alert('Bill deleted successfully');
					dataTable__init();
				}
			}
		});
	}
}