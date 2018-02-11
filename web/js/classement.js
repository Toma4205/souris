var sectionAffichee = 'sectionClassement';
var choixAffiche = 'choixClassement';
var effectifAffiche;

$(document).ready(function() {
  effectifAffiche = $( ".choix_effectif" ).val();
});

function afficherSection(choixMenu, idSection)
{
  $('#' + sectionAffichee).addClass('cache');
  $('#' + choixAffiche).removeClass('bold');
  sectionAffichee = idSection;
  choixAffiche = choixMenu.id;
  $('#' + sectionAffichee).removeClass('cache');
  $('#' + choixAffiche).addClass('bold');
}

function afficherDivEffectif(option)
{
  $('#' + effectifAffiche).addClass('cache');
  effectifAffiche = option.value;
  $('#' + effectifAffiche).removeClass('cache');
}
