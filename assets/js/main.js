$(document).ready(function() {

	$('#navbarToggler').on('click', function(e) {
		$('aside#sidebar').toggleClass('active')
		$('.wrapper main').toggleClass('blur');
	})

	$(document).on('click', '#navbarCloseBtn', function(e) {
		$('aside#sidebar').toggleClass('active')	
		$('.wrapper main').toggleClass('blur');
	});

});