var tabMercato = [];
var tabPrixBase = [];
var BUDGET_INITIAL = 300;
var GB = 'Gardien';
var DEF = 'Défenseur';
var MIL = 'Milieu';
var ATT = 'Attaquant';
var filtrePoste = "Poste";
var filtreEquipe = "Equipe";
var filtrePrixMin = 0;
var filtrePrixMax = BUDGET_INITIAL;

$(document).ready(function() {
    //console.log("deb:"+new Date().toISOString().split('T')[1]);
    gererTableMercato();
    //console.log("inter:"+new Date().toISOString().split('T')[1]);
    recalculerBudgetRestant();
    //console.log("fin:"+new Date().toISOString().split('T')[1]);
});

function gererTableMercato()
{
  $('#tableMercato').DataTable();

  // Suppression parties inutilisées de la DataTable
  $('#tableMercato_paginate').remove();
  $('#tableMercato_length').remove();
  $('#tableMercato_info').remove();

  // Ajout des autres champs de filtre
  $('#tableMercato_filter').after('<br/><br/>' + $('#filtre').html());
  $('#filtre').remove();

  // Pour chaque ligne de la table des joueurs réels
  $('#tableMercato tbody').on( 'click', 'tr', function () {
    var tr = $(this).clone();
    tabMercato[tr.attr('id')] = tr;
    tabPrixBase[tr.attr('id')] = parseInt(tr.find('td:last').text());

    // Suppression de la ligne de la table de base
    $('#tableMercato').DataTable().row($(this)).remove().draw();

    // Ajout dans le div "Achat"
    var idDiv = 'listeAchat';
    if (tr.find('td:nth-child(2)').html() == GB) {
      idDiv += 'GB';
    } else if (tr.find('td:nth-child(2)').html() == DEF) {
      idDiv += 'DEF';
    } else if (tr.find('td:nth-child(2)').html() == MIL) {
      idDiv += 'MIL';
    } else if (tr.find('td:nth-child(2)').html() == ATT) {
      idDiv += 'ATT';
    }

    var html = getHTMLLigneDivAchat(tr);
    if ($('#' + idDiv + ' p').length == 0) {
      $('#' + idDiv).append(html);
    } else {
      $('#' + idDiv + ' p:last').after(html);
    }

    // Mise à jour du budget
    $('#budgetRestant').text(parseInt($('#budgetRestant').val()) - parseInt(tr.find('td:last').text()));

    // Mises à jour des éléments de la page (bouton, images)
    effectuerControlesSuiteModif();
  });

  // Suppression dans la table de base des joueurs déjà achetés
  supprimerDansListeJoueurDejaAchete('GB');
  supprimerDansListeJoueurDejaAchete('DEF');
  supprimerDansListeJoueurDejaAchete('MIL');
  supprimerDansListeJoueurDejaAchete('ATT');
  $('#tableMercato').DataTable().draw();
}

function supprimerDansListeJoueurDejaAchete(idDiv)
{
  $('#listeAchat' + idDiv + ' p.joueurEnCours').each(function() {
    var tr = $('tr[id=\'' + $(this).attr('id').substring(6) + '\']');
    tabMercato[tr.attr('id')] = tr;
    tabPrixBase[tr.attr('id')] = parseInt(tr.find('td:last').text());

    $('#tableMercato').DataTable().row(tr).remove();
  });
}

function effectuerControlesSuiteModif()
{
  verifierBoutonValiderMercato();
  controlerImageTableMercato('GB', 2);
  controlerImageTableMercato('DEF', 6);
  controlerImageTableMercato('MIL', 6);
  controlerImageTableMercato('ATT', 3);
}

function supprimerAchatJoueur(id)
{
  var p = $('p[id=\'Achat_' + id + '\']');

  // Impact sur table de base
  appliquerTousLesFiltresSurLigne(tabMercato[id]);
  $("#tableMercato").DataTable().rows.add(tabMercato[id]).draw();
  delete tabMercato[id];

  // Calcul du budget
  $('#budgetRestant').text(parseInt($('#budgetRestant').val()) + parseInt(p.find('input').val()));

  // Suppression dans la liste "Achat"
  p.remove();

  effectuerControlesSuiteModif();
}

function recalculerBudgetRestant()
{
  var total = 0;
  $('#listeAchatGB p').each(function() {
    total += getPrixAchat($(this));
  });
  $('#listeAchatDEF p').each(function() {
    total += getPrixAchat($(this));
  });
  $('#listeAchatMIL p').each(function() {
    total += getPrixAchat($(this));
  });
  $('#listeAchatATT p').each(function() {
    total += getPrixAchat($(this));
  });

  $('#budgetRestant').text(300 - total);

  effectuerControlesSuiteModif();
}

function getPrixAchat(p)
{
  var prixBase = tabPrixBase[p.attr('id').substring(6)];
  // Si le parsing en Int est impossible => prix = 0
  var prix = parseInt(p.find('input').val()) || 0;

  if (prix < prixBase) {
    if (!p.find('input').hasClass('erreurPrix')) {
      p.find('input').addClass('erreurPrix')
    }
    p.find('input').val(prixBase);
  } else if (p.find('input').hasClass('erreurPrix')) {
    p.find('input').removeClass('erreurPrix')
  }
  return parseInt(p.find('input').val());
}

function verifierBoutonValiderMercato()
{
  var nbGB = $('#listeAchatGB p').length;
  var nbDEF = $('#listeAchatDEF p').length;
  var nbMIL = $('#listeAchatMIL p').length;
  var nbATT = $('#listeAchatATT p').length;

  var nbAjout = 0;
  for (joueur in tabMercato) {
    nbAjout++;
  }

  if (parseInt($('#budgetRestant').text()) >= 0
    && nbGB >= 2 && nbDEF >= 6 && nbMIL >= 6 && nbATT >= 3 && nbAjout > 0) {
    $('#validationMercato').removeAttr("disabled");
  } else {
    $('#validationMercato').attr("disabled", "disabled");
  }
}

function controlerImageTableMercato(position, nbMin)
{
  var nbAchat = $('#listeAchat' + position + ' p').length;
  if (nbAchat >= nbMin) {
    $('#image' + position).attr("src","./web/img/validation.jpg");
  } else {
    $('#image' + position).attr("src","./web/img/erreur.jpg");
  }
}

function filtrerSurPoste(option)
{
  filtrePoste = option.value;
  $('#tableMercato tbody tr').each(function() {
    appliquerTousLesFiltresSurLigne($(this));
  });
}

function filtrerSurEquipe(option)
{
  filtreEquipe = option.value;
  $('#tableMercato tbody tr').each(function() {
    appliquerTousLesFiltresSurLigne($(this));
  });
}

function filtrerSurPrixMin(prix)
{
  var input = $('#' + prix.id);

  if (prix.value != '') {
    filtrePrixMin = parseInt(prix.value) || 0;
    // Saisie en erreur
    if (filtrePrixMin == 0 && prix.value != '0') {
      if (!input.hasClass('erreurPrix')) {
        input.addClass('erreurPrix');
      }
      input.val(0);
    } else {
      if (input.hasClass('erreurPrix')) {
        input.removeClass('erreurPrix');
      }
    }
  } else {
    filtrePrixMin = 0;
    if (input.hasClass('erreurPrix')) {
      input.removeClass('erreurPrix');
    }
  }

  $('#tableMercato tbody tr').each(function() {
    appliquerTousLesFiltresSurLigne($(this));
  });
}

function filtrerSurPrixMax(prix)
{
  var input = $('#' + prix.id);

  if (prix.value != '') {
    filtrePrixMax = parseInt(prix.value) || 0;
    // Saisie en erreur
    if (filtrePrixMax == 0 && prix.value != '0') {
      if (!input.hasClass('erreurPrix')) {
        input.addClass('erreurPrix');
      }
      input.val(0);
    } else {
      if (input.hasClass('erreurPrix')) {
        input.removeClass('erreurPrix');
      }
    }
  } else {
    filtrePrixMax = BUDGET_INITIAL;
    if (input.hasClass('erreurPrix')) {
      input.removeClass('erreurPrix');
    }
  }

  $('#tableMercato tbody tr').each(function() {
    appliquerTousLesFiltresSurLigne($(this));
  });
}

function appliquerTousLesFiltresSurLigne(tr)
{
  if (("Equipe" == filtreEquipe || tr.find('td:nth-child(3)').html() == filtreEquipe)
    && ("Poste" == filtrePoste || tr.find('td:nth-child(2)').html() == filtrePoste)
    && parseInt(tr.find('td:last').html()) >=  filtrePrixMin
    && parseInt(tr.find('td:last').html()) <=  filtrePrixMax) {
      if (tr.hasClass('cache')) {
        tr.removeClass('cache');
      }
  } else if (!tr.hasClass('cache')) {
    tr.addClass('cache');
  }
}

// Attention, si changement => effectuer aussi dans prepaMercato.php
function getHTMLLigneDivAchat(tr)
{
  return '<p id="Achat_' + tr.attr('id') + '" class="joueurEnCours"><b>'
  + tr.find('td:first').html() + '</b> - ' + tr.find('td:nth-child(3)').html() + ' (prix = ' + tr.find('td:last').html() + ')'
  + '<span class="floatRight">'
  + '<input type="text" class="inputPrix" name="name_' + tr.attr('id') + '" value="' + tr.find('td:last').html() + '" onchange="javascript:recalculerBudgetRestant();"/>'
  + ' <img src="./web/img/croix.jpg" alt="Supprimer" width="15px" height="15px" onclick="javascript:supprimerAchatJoueur(\'' + tr.attr('id') + '\');" />'
  + '</span></p>';
}
