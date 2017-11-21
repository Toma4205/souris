var sectionAffichee = 'sectionClassement';
var choixAffiche = 'choixClassement';

function afficherSection(choixMenu, idSection)
{
  $('#' + sectionAffichee).addClass('cache');
  $('#' + choixAffiche).removeClass('bold');
  sectionAffichee = idSection;
  choixAffiche = choixMenu.id;
  $('#' + sectionAffichee).removeClass('cache');
  $('#' + choixAffiche).addClass('bold');
}
