/* Author: Felix Epp, Thomas Grah
basic logic for interface
*/
$(document).ready(function(){
  $.getJSON('server.php?action=onload', addbands);	

  $('.selband').change(function(){
    var sel = $(this);
    console.log(sel.val());
    if(sel.val()!=0){
      sel.children('option:selected').each(function(){
          var opt = $(this);
          updateBand(opt.parents('.bleft').length ? '.bleft' : '.bright', opt.val());
      });
    }
  });

});

function addbands(data){
   $.each(data, function(i, item) {
      $("<option/>").val(item.uri).text(item.name).appendTo(".selband");
   });
}

function updateBand(side, uri)
{
 	$.post('server.php', { action: "updateBand", uri: uri }, function (data){
    data = $.parseJSON(data);
    $(side + ' .bandtitle').text(data.name);
    $(side + ' .summary').text(data.comment);
    $(side + ' .bandimage').attr('src', data.depiction);
    $(side + ' .members').text(data.members.join(', '));
    $(side + ' > .band').show();
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