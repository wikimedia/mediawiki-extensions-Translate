$(document).ready( function() {
	$(".translationdisplay").hover(
		function() { $(this).addClass("translationdisplay-hover"); },
		function() { $(this).removeClass("translationdisplay-hover"); }
	);
	$("#tt1").click( function() {
		$("#tt1").hide();
		$("#tt2").show();
	});
	$("#tt2").click( function() {
		$("#tt2").hide();
		$("#tt1").show();
	});
});
