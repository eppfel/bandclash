/* Author: Felix Epp, Thomas Grah
baisc logic for interface
*/
$(document).ready(function(){
	var test = $('#foo'); //.checked = true;

	$('input[name=curi]').focus(function(){
   		$('input[name=curib]').attr('checked', true);
	});

	$('#printbtn').click(function(){
   		$.get('server.php?action=print', printResponse);
	});

	//lacks url encoding to use bbc with '#' sign
   	$('#crawlbtn').click(function(){
   		var uri
   		if ($('input[name=curib]').is(':checked')) {
			uri = $('input[name=curi]').val();
   		}
   		else {
			uri = $('select[name=uri] option:selected').val();
   		};
   		$.get('server.php?action=crawl&uri='+uri, printResponse);
	});

 });

function printResponse(data) {
	$('#output').html(data);
}