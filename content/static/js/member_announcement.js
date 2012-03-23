$(document).ready(function() {
	var announcement = getcookie('announcement');
	if(announcement==null || announcement=='') {
		$("#announcement").fadeIn("slow");
	}
});