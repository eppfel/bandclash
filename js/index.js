/* Author: Felix Epp, Thomas Grah
basic logic for interface
*/
var releaseNode;
var side;

$(document).ready(function(){
  $.getJSON('server.php?action=onload', addbands);  
  $('#results').hide();
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
  $('.clash').click(function(){
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
  console.log(data);
  if (!data.length) {
    $("#results").before("<div class='alert alert-error'>Ooops! Seems like there is no data availible right now. Try reseting the database in the <a href='?p=admin'>Admin Panel</a></div>");
    console.log('hit it');
  }
  else {
   $.each(data, function(i, item) {
      $("<option/>").val(item.uri).text(item.name).appendTo(".selband");
   });
  }
}

function updateBand(side, uri)
{
  $('#results').hide();
  $.post('server.php', { action: "updateBand", uri: uri }, function (data){
    data = $.parseJSON(data);
    $(side + ' .bandtitle').text(data.name);
    $(side + ' .summary').text(data.comment);
    $(side + ' .bandimage').attr('src', data.depiction);
    $(side + ' .members').text(data.members.join(', '));
    $(side + ' > .band').show();
  });
}

function addReleases(i, release) {

  releaseNode.clone().text(release.name).append('<img src="' + release.thumb + '" />').appendTo('.no1hits ' + side);
}

function clash(uris)
{
  console.log(uris[0] + " " + uris[1]);
  $.post('server.php', {action: "clash", uri1: uris[0], uri2: uris[1]}, function (data)
  {
    data = $.parseJSON(data);

    var no1result = data.numberone;
    var lefty = $('.no1hits .bleft h3').text(no1result.peakleft);
    var righty = $('.no1hits .bright h3').text(no1result.peakright);
    $('div.winner').removeClass('winner');
    if(no1result.result == 0)
    {
      lefty.parent().addClass('winner');
    }
    else if (no1result.result == 1)
    {
      righty.parent().addClass('winner');
    };

    $('.release').detach();
    releaseNode = $('<div class="release"></div>');
    side = '.bleft';
    $.each(data.band1, addReleases);

    side = '.bright';
    $.each(data.band2, addReleases);

    $('#results').show();
  });
}