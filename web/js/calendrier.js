var divAffiche = 'divJournee1';

$(document).ready(function() {
  $('.detail_journee_cal').each(function() {
    if (!$(this).hasClass('cache')) {
      divAffiche = $(this).attr('id');
    }
  });
});

function afficherDivJournee(option)
{
  $('#' + divAffiche).addClass('cache');
  divAffiche = option.value;
  $('#' + divAffiche).removeClass('cache');

  if (!$('#detailMatch').hasClass('cache')) {
    $('#detailMatch').addClass('cache');
  }
}

function stockerMatch(idMatch)
{
  $("#idMatch").val(idMatch);
  $("#formPrincipal").submit();
}
