var divAffiche = 'divJournee1';

function afficherDivJournee(option)
{
  $('#' + divAffiche).addClass('cache');
  divAffiche = option.value;
  $('#' + divAffiche).removeClass('cache');
}
