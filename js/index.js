/* Author: Felix Epp, Thomas Grah
basic logic for interface
*/
$(document).ready(function(){
$.getJSON('server.php?action=onload', addbands);	

 });

function addbands(data){
   console.log(data);
   $.each(data, function(i, item) {
      $("<option/>").val(item.uri).text(item.name).appendTo("#band1");
      $("<option/>").val(item.uri).text(item.name).appendTo("#band2");
   });
}

$('#band1').change(function(){
    $('#band1 option:selected').each(function(){
        alert($(this).val());
    });
});
