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

	$('#resetbtn').click(function(){
   		$.get('server.php?action=reset', printResponse);
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
   		uri = encodeURI(uri);
   		$.post('server.php', { action: "crawl", uri: uri }, printResponse);
   		console.log(uri);

	});

	$("#output").bind("ajaxSend", function() {
   		$(this).html('').addClass('ajax-loading');
 	}).bind("ajaxComplete", function(){
   		$(this).removeClass('ajax-loading');
 	});

 });

function printResponse(data) {
	$('#output').html(data);
}