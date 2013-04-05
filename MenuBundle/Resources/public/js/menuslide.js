/*
 * jQuery JavaScript Library v1.3.2 
 *
 * Copyright (C) Covalense Technologies Private Limited
 * http://www.covalense.com
 *
 * Extension to the jquery.min.js to close the menu by swiping (clicking) screen from right to left
 *
 * Date: 2013-04-05
*/
$(function(){
	var s = $('#slide'), w = $(s.find('span')[0]).width(); 
	s.css({width: 398, overflow: 'hidden', whiteSpace: 'nowrap'});
	$('#clicky').toggle
		(function(){
			s.stop(true).animate({width: 0}, {duration: 'slow', queue: false, complete: function(){s.hide();}});
			$('#clicky').removeClass("sliderArrowLeft");
			$('#clicky').addClass("sliderArrowRight");
		}, function(){
			s.stop().animate({width: 398}, {duration: 'slow', queue: false});
			$('#clicky').removeClass("sliderArrowRight");
			$('#clicky').addClass("sliderArrowLeft");
		}
		);
 }
);