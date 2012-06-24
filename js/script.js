/* Author: Felix Epp, Thomas Grah
baisc logic for interface
*/
$(document).ready(function(){
	$('#printbtn').click(function(){
   		$.get('server.php?action=print', printResponse);
	});

	//lacks url encoding to use bbc with '#' sign
   	$('#crawlbtn').click(function(){
   		var uri = $('select[name=uri] option:selected').val();
   		$.get('server.php?action=crawl&uri='+uri, printResponse);
	});

 });

function printResponse(data) {
	$('#output').html(data);
}





