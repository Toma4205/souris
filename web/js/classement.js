var sectionAffichee = 'sectionClassement';
var choixAffiche = 'choixClassement';
var effectifAffiche;
var statsEquipeAffiche;

$(document).ready(function() {
  effectifAffiche = $(".choix_effectif").val();
  statsEquipeAffiche = $(".choix_stats_equipe").val();
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

function afficherDivStatsEquipe(option)
{
  $('#' + statsEquipeAffiche).addClass('cache');
  statsEquipeAffiche = option.value;
  $('#' + statsEquipeAffiche).removeClass('cache');
}
