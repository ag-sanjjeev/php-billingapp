$(document).ready(function () {

	dataTable__init();

	product_sublist();

	$('#searchBill').on('click', function(e) {
		dataTable__init();
	});

});

function dataTable__init() {
	var fromDate = $('#fromDate').val();
	var toDate = $('#toDate').val();

	$('table#billListTable').DataTable().destroy();
	$('table#billListTable').DataTable({
		'scrollX': true,
		'processing': true,
		'serverSide': true,		
		'dom': 'lBfrtip',
		'lengthMenu': [
			[10, 25, 50, -1],
			[10, 25, 50, 'All']
		],
		'buttons': [
			'copyHtml5',
			'excelHtml5',
			'csvHtml5',
			'pdfHtml5',
			'print'
		],
		'ajax': {
			'url': 'pages/billreport/controller.php',
			'method': 'post',
			'data': {'action':'datatableList', 'fromDate': fromDate, 'toDate': toDate}
		},
		'columns':[
			{'data': 'id', 'name': 'id'},
			{'data': 'date', 'name': 'billDate'},
			{'data': 'billNo', 'name': 'billNo'},
			{'data': 'customerName', 'name': 'customerName'},
			{'data': 'netTotal', 'name': 'netTotal'},
			{'data': 'action'}
		],
		'columnDefs': [
			{'targets':[5],	'orderable': false},
			{ 'className': 'text-end', 'targets': [4]}
		],
		'initComplete': function(settings, json) {
			$('#netTotal').text(json.netTotal);
		},
		'rowCallback': function(row, data, index) {			
			if (data.status == 'UNSAVED') {
				$('td', row).addClass('bg-danger');
				$('td', row).addClass('text-white');
			}
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
			}]
		});

	}
}

