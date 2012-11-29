/* Author: Felix Epp, Thomas Grah
basic logic for interface
*/
$(document).ready(function(){
  $.getJSON('server.php?action=onload', addbands);	

  $('.selband').change(function(){
    var sel = $(this);
    //console.log(sel.val());
    if(sel.val()!=0){
      sel.children('option:selected').each(function(){
          var opt = $(this);
          updateBand(opt.parents('.bleft').length ? '.bleft' : '.bright', opt.val());
      });
    }
  });

  $('.btn-danger').click(function(){
    var sel = $('.selband');
    if(sel.val()!=0){
          var uris = new Array();
      sel.children('option:selected').each(function(){
          var opt = $(this);
          uris.push(opt.val());
          console.log(uris);
      });
          clash(uris);
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

function clash(uris)
{
  console.log(uris[0] + " " +uris[1]);
  $.post('server.php', {action: "clash", uri1: uris[0], uri2: uris[1]}, function (data)
  {
    console.log(data);
   //$.each(data, function(i, item) {
     // $('#bandtitle1').text(item.name);
     // $('#summary1').text(item.comment);
     // $('#bandimage1').attr('src', item.depiction);
     // console.log(item.name+" "+item.comment+" "+item.depiction);
    //});
  });
}