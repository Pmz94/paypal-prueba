$(function() {
	$('img').attr('draggable', 'false');
	$('img').bind('contextmenu', function() {
		return false;
	});
});