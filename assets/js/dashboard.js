$(document).ready(function() {

	$('select.select2').select2({
		width: 'resolve',
		open: false,
		ajax: {
			url: './layouts/dashboard.controller.php',
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
		placeholder: 'Top 10 Products',
		allowClear: true,
		minimumInputLength: 1		
	});	


	var obj1 = new NoOfProductChart('#noOfProductsChart');
	var obj2 = new HighlySold('#highlySold');
	var obj3 = new PercentageProductSold('#percentageProductSold');
	var obj4 = new TodayBills('#todayBills');
	var obj5 = new ProductPriceHistory('#productPriceHistory');
	var obj6 = new BillsHistory('#billsHistory');
	
	$('#productName').on('select2:select', function(e) {
		let input = e.params.data;
		if (input.selected) {			
			obj5.requestData(input.id);
		}
	});

});

class NoOfProductChart {	

	constructor(reference) {
		this.data = [];
		this.labels = [];
		this.ctx = $(reference);
		this.requestData();
	}

	requestData() {
		var _this = this;
		$.ajax({
			type: 'POST',
			url: './layouts/dashboard.controller.php',
			data: {'action' : 'noOfProductsChart'},
			success: function(res) {
				var obj = JSON.parse(res);
				if (!obj.status) {
					console.log(obj.error);
				} else {
					for(var a in obj.data) {
						_this.labels.push(a);
						_this.data.push(obj.data[a]);
					}
					_this.renderChart();
				}
			},
			error: function(res) {
				console.log(res);
				alert(res);
			}
		});		
	}

	renderChart() {
		let style = getComputedStyle(document.body);
		var maximumYellow = style.getPropertyValue('--maximum-yellow');
		var isabelline = style.getPropertyValue('--isabelline');
		var color = style.getPropertyValue('--purple');
		var color2 = style.getPropertyValue('--xanthic');

		var myChart = new Chart(this.ctx, {
			type: 'doughnut',
			data: {
				labels: this.labels,
				datasets: [{
					hoverOffset: 4,
					backgroundColor: [
						color,
						color2
					],
					data: this.data
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				title: {
					fontColor: '#fff'
				},
				plugins: {
					legend: {
						display: true,
						color: '#fff',
						labels: {
							color: '#fff'
						}
					}
				}
			}
		});
	}
}

class HighlySold {
	constructor(reference) {
		this.data = [];
		this.labels = [];
		this.ctx = $(reference);
		this.requestData();
	}

	requestData() {
		var _this = this;
		$.ajax({
			type: 'POST',
			url: './layouts/dashboard.controller.php',
			data: {'action' : 'highlySold'},
			success: function(res) {
				var obj = JSON.parse(res);
				if (!obj.status) {
					console.log(obj.error);
				} else {
					for(var a in obj.data) {
						_this.labels.push(obj.data[a].productName);
						_this.data.push(obj.data[a].quantity);						
					}
					_this.renderChart();
				}
			},
			error: function(res) {
				console.log(res);
				alert(res);
			}
		});		
	}

	renderChart() {
		let style = getComputedStyle(document.body);
		var maximumYellow = style.getPropertyValue('--maximum-yellow');
		var isabelline = style.getPropertyValue('--isabelline');
		var color = style.getPropertyValue('--purple');
		var color2 = style.getPropertyValue('--xanthic');

		var myChart = new Chart(this.ctx, {
			type: 'bar',
			data: {
				labels: this.labels,
				datasets: [{
					label: 'Products Sold By Quantity',
					data: this.data,
					backgroundColor: [
						maximumYellow,
						isabelline,
						color,
						color2
					],
					borderColor: [
						maximumYellow,
						isabelline,
						color,
						color2
					],
					borderWidth: 1
				}]
			},
			options: {	
				responsive: true,
				maintainAspectRatio: false,
				title: {
					fontColor: '#fff'
				},
				plugins: {
					legend: {
						display: true,
						color: '#fff',
						labels: {
							color: '#fff'						
						}
					}
				},
				scales: {
					x: {
						ticks: {
							color: '#fff',
							callback: function(value, index, ticks_array) {
								let charLimit = 7;
								let label = this.getLabelForValue(value);
								if (label.length > charLimit) {
									return label.slice(0, label.length).substring(0, charLimit - 1).trim() + '...';
								}
								return label;
							}
						}
					},
					y: {
						ticks: {
							color: '#fff'
						}
					}
				},
				grid: {
					color: '#fff'
				}
			}
		});
	}
}

class PercentageProductSold {
	constructor(reference) {
		this.data = [];
		this.labels = [];
		this.ctx = $(reference);
		this.requestData();
	}

	requestData() {
		var _this = this;
		$.ajax({
			type: 'POST',
			url: './layouts/dashboard.controller.php',
			data: {'action' : 'percentageProductSold'},
			success: function(res) {
				var obj = JSON.parse(res);
				if (!obj.status) {
					console.log(obj.error);
				} else {
					for(var a in obj.data) {
						_this.labels.push(obj.data[a].productName);
						_this.data.push(obj.data[a].percent);						
					}
					_this.renderChart();
				}
			},
			error: function(res) {
				console.log(res);
				alert(res);
			}
		});		
	}

	renderChart() {
		let style = getComputedStyle(document.body);
		var maximumYellow = style.getPropertyValue('--maximum-yellow');
		var isabelline = style.getPropertyValue('--isabelline');
		var color = style.getPropertyValue('--purple');
		var color2 = style.getPropertyValue('--xanthic');

		var myChart = new Chart(this.ctx, {
			type: 'bar',
			data: {
				labels: this.labels,
				datasets: [{
					label: 'Products Sold By Percentage',
					data: this.data,
					backgroundColor: [
						maximumYellow,
						isabelline,
						color,
						color2
					],
					borderColor: [
						maximumYellow,
						isabelline,
						color,
						color2
					],
					borderWidth: 1
				}]
			},
			options: {	
				responsive: true,
				maintainAspectRatio: false,
				title: {
					fontColor: '#fff'
				},
				plugins: {
					legend: {
						display: true,
						color: '#fff',
						labels: {
							color: '#fff'						
						}
					}
				},
				scales: {
					x: {
						ticks: {
							color: '#fff',
							callback: function(value, index, ticks_array) {
								let charLimit = 7;
								let label = this.getLabelForValue(value);
								if (label.length > charLimit) {
									return label.slice(0, label.length).substring(0, charLimit - 1).trim() + '...';
								}
								return label;
							}
						}
					},
					y: {
						ticks: {
							color: '#fff'
						}
					}
				},
				grid: {
					color: '#fff'
				}
			}
		});
	}
}

class TodayBills {
	constructor(reference) {
		this.data = [];
		this.labels = [];
		this.ctx = $(reference);
		this.requestData();
	}

	requestData() {
		var _this = this;
		$.ajax({
			type: 'POST',
			url: './layouts/dashboard.controller.php',
			data: {'action' : 'todayBills'},
			success: function(res) {
				var obj = JSON.parse(res);
				if (!obj.status) {
					console.log(obj.error);
				} else {
					for(var a in obj.data) {
						_this.labels.push(a);
						_this.data.push(obj.data[a]);
					}
					_this.renderChart();
				}
			},
			error: function(res) {
				console.log(res);
				alert(res);
			}
		});		
	}

	renderChart() {
		let style = getComputedStyle(document.body);
		var maximumYellow = style.getPropertyValue('--maximum-yellow');
		var isabelline = style.getPropertyValue('--isabelline');
		var color = style.getPropertyValue('--green-ryb');
		var color2 = style.getPropertyValue('--blue-green');

		var myChart = new Chart(this.ctx, {
			type: 'doughnut',
			data: {
				labels: this.labels,
				datasets: [{
					hoverOffset: 4,
					backgroundColor: [
						color,
						color2
					],
					data: this.data
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				title: {
					fontColor: '#fff'
				},
				plugins: {
					legend: {
						display: true,
						color: '#fff',
						labels: {
							color: '#fff'
						}
					}
				}
			}
		});
	}
}

class ProductPriceHistory {
	constructor(reference) {
		this.chartData = [];
		this.ctx = $('#productPriceHistory');
		this.requestData();		
	}

	requestData(id='') {
		var _this = this;
		$.ajax({
			type: 'POST',
			url: './layouts/dashboard.controller.php',
			data: {'action' : 'productPriceHistory', 'id': id},
			success: function(res) {
				var obj = JSON.parse(res);
				if (!obj.status) {
					console.log(obj.error);
				} else {
					_this.chartData = obj.data;
					if (id != '') {
						_this.renderChart('update');
					} else {
						_this.renderChart();
					}
				}
			},
			error: function(res) {
				console.log(res);
				alert(res);
			}
		});	
	}

	renderChart(action='') {
		let style = getComputedStyle(document.body);
		
		var color1 = style.getPropertyValue('--purple');
		var color2 = style.getPropertyValue('--maximum-blue');
		var color3 = style.getPropertyValue('--international-orange-golden-gate-bridge');
		var color4 = style.getPropertyValue('--blue-green');
		var color5 = style.getPropertyValue('--international-orange-golden-gate-bridge');
		var color6 = style.getPropertyValue('--green-ryb');
		var color7 = style.getPropertyValue('--smoky-black');
		var color8 = style.getPropertyValue('--isabelline');
		var color9 = style.getPropertyValue('--violet-blue');
		var color10 = style.getPropertyValue('--lime-green');		

		var colors = [color1, color2, color3, color4, color5, color6, color7, color8, color9, color10];

		var datasets = [];
		for(var a in this.chartData) {
			let b = {
				label: this.chartData[a].productName,
				data: this.chartData[a].prices,
				fill: false,
				borderColor: colors[a],
				tension: 0.1
			};

			datasets.push(b);
		}	

		if (action == '') {

			this.myChart = new Chart(this.ctx, {
				type: 'line',
				data: {
					labels: [1, 2, 3],
					datasets: datasets
				},
				options: {
					title: {
						fontColor: '#fff'
					},
					responsive: true,
					maintainAspectRatio: false,
					title: {
						fontColor: '#fff'
					},
					plugins: {
						legend: {
							display: true,
							color: '#fff',
							labels: {
								color: '#fff'
							}
						}
					},
					scales: {
						x: {
							ticks: {
							color: '#fff'
							}						
						},
						y: {
							ticks: {
								color: '#fff'
							}
						}
					},
					grid: {
						color: '#fff'
					}
				}
			});

		}

		if (action == 'update') {
			console.log(this.myChart);
			this.myChart.data = {
					labels: [1, 2, 3],
					datasets: datasets
				};
			this.myChart.update();
		}
	}
}

class BillsHistory {
	constructor(reference) {
		this.chartData = [];
		this.ctx = $('#billsHistory');
		this.requestData();		
	}

	requestData() {
		var _this = this;
		$.ajax({
			type: 'POST',
			url: './layouts/dashboard.controller.php',
			data: {'action' : 'billsHistory'},
			success: function(res) {
				var obj = JSON.parse(res);
				if (!obj.status) {
					console.log(obj.error);
				} else {
					_this.chartData = obj.data;
					_this.renderChart();
					
				}
			},
			error: function(res) {
				console.log(res);
				alert(res);
			}
		});	
	}

	renderChart() {
		let style = getComputedStyle(document.body);
		
		var color1 = style.getPropertyValue('--purple');
		var color2 = style.getPropertyValue('--maximum-blue');
		var color3 = style.getPropertyValue('--international-orange-golden-gate-bridge');
		var color4 = style.getPropertyValue('--blue-green');
		var color5 = style.getPropertyValue('--international-orange-golden-gate-bridge');
		var color6 = style.getPropertyValue('--green-ryb');
		var color7 = style.getPropertyValue('--smoky-black');
		var color8 = style.getPropertyValue('--isabelline');
		var color9 = style.getPropertyValue('--violet-blue');
		var color10 = style.getPropertyValue('--lime-green');	

		var selectiveYellow = style.getPropertyValue('--selective-yellow');	

		var colors = [color1, color2, color3, color4, color5, color6, color7, color8, color9, color10];

		var datasets = [];
		var labels = [];
		var data = [];
		for(var a in this.chartData) {		
			labels.push(this.chartData[a].billDate);
			data.push(this.chartData[a].totalBills);
		}	

		this.myChart = new Chart(this.ctx, {
			type: 'line',
			data: {
				labels: labels,
				datasets: [
					{
						label: 'Bills History',
						data: data,
						borderColor: color7,
						backgroundColor: selectiveYellow,
						fill: true				
					}
				]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: {
						display: true,
						color: '#fff',
						labels: {
							color: '#fff'
						}
					},
					filler: {
						propagate: false
					},
					pointBackgroundColor: '#fff',
					radius: 10,
					interaction: {
						intersect: false
					}
				},
				scale: {
					ticks: {
						precision: 0
					}
				},
				scales: {
					x: {
						ticks: {
						color: '#fff'
						}						
					},
					y: {					
						beginAtZero: true,
						ticks: {
							color: '#fff'
						}			
					}
				},
				grid: {
					color: '#fff'
				}				
			}
		});

	}
}