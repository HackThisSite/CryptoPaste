// Scale body's margin-bottom to corespond with footer height
function scaleFooter(){
  $("body").css('margin-bottom', $(".footer").height() + 'px');
}

// Scale footer on document ready
$(document).ready(function(){
	scaleFooter();
});

// Scale footer on window resize
$(window).resize(function(){
	scaleFooter();
});
