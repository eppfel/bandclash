/* Author: Felix Epp, Thomas Grah
baisc logic for interface
*/
$(document).ready(function(){
	
   //Ajax functionality
   $('.btn.ajax').click(function(){
         $.get('server.php?action=' + $(this).attr('href').substring(1), printResponse);
   });

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

   // UI enhancing
   $("#output").bind("ajaxSend", function() {
         $(this).html('').addClass('ajax-loading');
   }).bind("ajaxComplete", function(){
         $(this).removeClass('ajax-loading');
   });

   $('input[name=curi]').focus(function(){
         $('input[name=curib]').attr('checked', true);
   });

 });

function printResponse(data) {
	$('#output').html(data);
}