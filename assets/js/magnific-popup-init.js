jQuery(document).ready(function($){
	/*$('.youtubebtn').magnificPopup({
		type: 'iframe',
		iframe: {
			markup: '<div class="mfp-iframe-scaler">'+
			'<div class="mfp-close"></div>'+
			'<iframe class="mfp-iframe" frameborder="0" allowfullscreen></iframe>'+
			'</div>', 
			patterns: {
				youtube: {
					index: 'youtube.com/', 
					id: 'v=', 
					src: '//www.youtube.com/embed/%id%?autoplay=1' 
				}
			},
			srcAction: 'iframe_src', 
		}
	});*/

	$('.owl-carousel').owlCarousel({
		autoplay: true,
		autoplayHoverPause: true,
		loop: true,
		margin: 20,
		responsiveClass: true,
		nav: true,
		loop: true,
		responsive: {
			0: {
				items: 1
			},
			568: {
				items: 2
			},
			600: {
				items: 3
			},
			1000: {
				items: 4
			}
		}
	})
	$(document).ready(function() {
		$('.popup-youtube, .popup-text').magnificPopup({
			disableOn: 320,
			type: 'iframe',
			mainClass: 'mfp-fade',
			removalDelay: 160,
			preloader: false,
			fixedContentPos: true
		});
	});
	$(document).ready(function() {
		$('.popup-text').magnificPopup({
			type: 'inline',
			preloader: false,
			focus: '#name',
			callbacks: {
				beforeOpen: function() {
					if ($(window).width() < 700) {
						this.st.focus = false;
					} else {
						this.st.focus = '#name';
					}
				}
			}
		});
	});
	
});