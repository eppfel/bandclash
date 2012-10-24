/* Author: Felix Epp, Thomas Grah
basic logic for interface
*/
$(document).ready(function(){

$.getJSON('server.php?action=onload', addbands);	
$('#band1').hide();
$('#band2').hide();

console.log('Ab gehts');

 });

function addbands(data){
   console.log(data);
   $.each(data, function(i, item) {
      $("<option/>").val(item.uri).text(item.name).appendTo("#selband1");
      $("<option/>").val(item.uri).text(item.name).appendTo("#selband2");
   });
}

$('#selband1').change(function(){
   console.log($(this).val());
   if($(this).val()!=0){
    $('#selband1 option:selected').each(function(){
        updateBand(0, $(this).val());
    });
}
});

$('#selband2').change(function(){
  if($(this).val()!=0){
    $('#selband2 option:selected').each(function(){
       updateBand(1, $(this).val());
    });
  }
});



function updateBand(side, uri)
{
 	$.post('server.php', { action: "updateBand", uri: uri }, function (data){
    //console.log(data);
   if(side==0){
   data = $.parseJSON(data);
    $.each(data, function(i, item) {
      $('#bandtitle1').text(item.name);
      $('#summary1').text(item.comment);
      $('#bandimage1').attr('src', item.depiction);
      $('#band1').show();
      //console.log(item.name+" "+item.comment+" "+item.depiction);
    });
  }
  else if(side==1){
   data = $.parseJSON(data);
    $.each(data, function(i, item) {
      $('#bandtitle2').text(item.name);
      $('#summary2').text(item.comment);
      $('#bandimage2').attr('src', item.depiction);
      $('#band2').show();
      //console.log(item.name+" "+item.comment+" "+item.depiction);
    });
  }

  });
}

function clash(uri1, uri2)
{
  $.post('server.php', {action: "clash", uri1: uri1, uri2: uri2}, function (data)
  {
    $.each(data, function(i, item) {
      $('#bandtitle1').text(item.name);
      $('#summary1').text(item.comment);
      $('#bandimage1').attr('src', item.depiction);
      //console.log(item.name+" "+item.comment+" "+item.depiction);
    });
  });
}