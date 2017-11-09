var tabMercato = [];
var filtrePoste = "Poste";
var filtreEquipe = "Equipe";
var BUDGET_INITIAL = 300;
var GB = 'Gardien';
var DEF = 'Défenseur';
var MIL = 'Milieu';
var ATT = 'Attaquant';

$(document).ready(function() {
    gererTableMercato();

    recalculerBudgetRestant();
});

function gererTableMercato()
{
  $('#tableMercato').DataTable();

  // Suppression parties inutilisées de la DataTable
  $('#tableMercato_paginate').remove();
  $('#tableMercato_length').remove();
  $('#tableMercato_info').remove();

  // Ajout des autres champs de filtre
  $('#tableMercato_filter').after(getHTMLFiltre());

  // Pour chaque ligne de la table des joueurs réels
  $('#tableMercato tbody').on( 'click', 'tr', function () {
    var tr = $(this).clone();
    tabMercato[tr.attr('id')] = tr;

    // Suppression de la ligne de la table de base
    $('#tableMercato').DataTable().row($(this)).remove().draw();

    // Ajout dans la table "Achat"
    var idTable = 'tableMercato';
    if (tr.find('td:nth-child(3)').html() == GB) {
      idTable += 'GB';
    } else if (tr.find('td:nth-child(3)').html() == DEF) {
      idTable += 'DEF';
    } else if (tr.find('td:nth-child(3)').html() == MIL) {
      idTable += 'MIL';
    } else if (tr.find('td:nth-child(3)').html() == ATT) {
      idTable += 'ATT';
    }
    var html = getHTMLLigneTable(tr, idTable);
    var table = document.getElementById(idTable + 'Achat');
    if (table.rows.length == 1) {
      $('#' + idTable + 'Achat tbody').append(html);
    } else {
      $('#' + idTable + 'Achat tr:last').after(html);
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
}

function supprimerDansListeJoueurDejaAchete(idTable)
{
  $('#tableMercato' + idTable + 'Achat tbody tr').each(function() {
    var tr = $('tr[id=\'' + $(this).attr('id').substring(6) + '\']');
    tabMercato[tr.attr('id')] = tr;

    $('#tableMercato').DataTable().row(tr).remove().draw();
  });
}

function effectuerControlesSuiteModif()
{
  verifierBoutonValiderMercato();
  controlerImageBudget();
  controlerImageTableMercato('GB', 2);
  controlerImageTableMercato('DEF', 6);
  controlerImageTableMercato('MIL', 6);
  controlerImageTableMercato('ATT', 3);
}

function supprimerAchatJoueur(id, idTable)
{
  var tr = $('tr[id=\'Achat_' + id + '\']');

  // Impact sur table de base
  appliquerFiltrePosteEquipeSurLigne(tabMercato[id]);
  $("#tableMercato").DataTable().rows.add(tabMercato[id]).draw();

  // Calcul du budget
  $('#budgetRestant').text(parseInt($('#budgetRestant').val()) + parseInt(tr.find('td:nth-child(5) input').val()));

  // Suppression dans la table "Achat"
  tr.remove();

  effectuerControlesSuiteModif();
}

function recalculerBudgetRestant()
{
  var total = 0;
  $('#tableMercatoGBAchat tbody tr').each(function() {
    total += parseInt($(this).find('td:nth-child(5) input').val());
  });
  $('#tableMercatoDEFAchat tbody tr').each(function() {
    total += parseInt($(this).find('td:nth-child(5) input').val());
  });
  $('#tableMercatoMILAchat tbody tr').each(function() {
    total += parseInt($(this).find('td:nth-child(5) input').val());
  });
  $('#tableMercatoATTAchat tbody tr').each(function() {
    total += parseInt($(this).find('td:nth-child(5) input').val());
  });

  $('#budgetRestant').text(300 - total);

  effectuerControlesSuiteModif();
}

function verifierBoutonValiderMercato()
{
  var nbGB = $('#tableMercatoGBAchat tbody tr').length;
  var nbDEF = $('#tableMercatoDEFAchat tbody tr').length;
  var nbMIL = $('#tableMercatoMILAchat tbody tr').length;
  var nbATT = $('#tableMercatoATTAchat tbody tr').length;

  if (parseInt($('#budgetRestant').text()) >= 0 && nbGB >= 2 && nbDEF >= 6 && nbMIL >= 6 && nbATT >= 3) {
    $('#validationMercato').removeAttr("disabled");
  } else {
    $('#validationMercato').attr("disabled", "disabled");
  }
}

function controlerImageTableMercato(position, nbMin)
{
  var nbATT = $('#tableMercato' + position + 'Achat tbody tr').length;
  if (nbATT >= nbMin) {
    $('#image' + position).attr("src","./web/img/validation.jpg");
  } else {
    $('#image' + position).attr("src","./web/img/erreur.jpg");
  }
}

function controlerImageBudget()
{
  if (parseInt($('#budgetRestant').text()) >= 0) {
    $('#imageBudget').attr("src","./web/img/validation.jpg");
  } else {
    $('#imageBudget').attr("src","./web/img/erreur.jpg");
  }
}

function filtrerSurPoste(option)
{
  filtrePoste = option.value;
  $('#tableMercato tbody tr').each(function() {
    appliquerFiltrePosteEquipeSurLigne($(this));
  });
}

function filtrerSurEquipe(option)
{
  filtreEquipe = option.value;
  $('#tableMercato tbody tr').each(function() {
    appliquerFiltrePosteEquipeSurLigne($(this));
  });
}

function appliquerFiltrePosteEquipeSurLigne(tr)
{
  // Si pas de filtre sur Poste ET (pas de filtre sur Equipe OU filtre OK)
  if ("Poste" == filtrePoste && ("Equipe" == filtreEquipe || tr.find('td:nth-child(4)').html() == filtreEquipe)) {
    if (tr.hasClass('cache')) {
      tr.removeClass('cache');
    }
  // Si pas de filtre sur Equipe ET filtre sur Poste OK
  } else if ("Equipe" == filtreEquipe && tr.find('td:nth-child(3)').html() == filtrePoste) {
    if (tr.hasClass('cache')) {
      tr.removeClass('cache');
    }
  // Si filtre sur Poste OK et filtre sur Equipe OK
  } else if (tr.find('td:nth-child(3)').html() == filtrePoste && tr.find('td:nth-child(4)').html() == filtreEquipe) {
    if (tr.hasClass('cache')) {
      tr.removeClass('cache');
    }
  // Sinon
  } else if (!tr.hasClass('cache')) {
    tr.addClass('cache');
  }
}

// TODO MPL voir astuce pour mettre cette liste à partir PHP puis via jQuery faire un remove() et after()
function getHTMLFiltre()
{
  return '<div id="filtrePoste" class="filtrePosteEquipe">'
    + '<select name="Poste" onchange="javascript:filtrerSurPoste(this);">'
    + '<option value="Poste" selected="selected">Poste</option>'
    + '<option value="' + GB + '">' + GB + '</option>'
    + '<option value="' + DEF + '">' + DEF + '</option>'
    + '<option value="' + MIL + '">' + MIL + '</option>'
    + '<option value="' + ATT + '">' + ATT + '</option>'
    + '</select></div>'
    + '<div id="filtreEquipe" class="filtrePosteEquipe">'
      + '<select name="Equipe" onchange="javascript:filtrerSurEquipe(this);">'
      + '<option value="Equipe" selected="selected">Equipe</option>'
      + '<option value="Amiens">Amiens</option>'
      + '<option value="Angers">Angers</option>'
      + '<option value="Bordeaux">Bordeaux</option>'
      + '<option value="Caen">Caen</option>'
      + '<option value="Dijon">Dijon</option>'
      + '<option value="St Etienne">St Etienne</option>'
      + '<option value="Guingamp">Guingamp</option>'
      + '<option value="Lille">Lille</option>'
      + '<option value="Lyon">Lyon</option>'
      + '<option value="Marseille">Marseille</option>'
      + '<option value="Metz">Metz</option>'
      + '<option value="Monaco">Monaco</option>'
      + '<option value="Montpellier">Montpellier</option>'
      + '<option value="Nice">Nice</option>'
      + '<option value="Nantes">Nantes</option>'
      + '<option value="Paris SG">Paris SG</option>'
      + '<option value="Rennes">Rennes</option>'
      + '<option value="Strasbourg">Strasbourg</option>'
      + '<option value="Toulouse">Toulouse</option>'
      + '<option value="Troyes">Troyes</option>'
      + '</select></div>';
}

// Attention, si changement => effectuer aussi dans prepaMercato.php
function getHTMLLigneTable(tr, idTable)
{
  return '<tr id="Achat_' + tr.attr('id') + '"><td>' + tr.find('td:first').html()
    + '</td><td>' + tr.find('td:nth-child(2)').html() + '</td><td>'
    + tr.find('td:nth-child(4)').html() + '</td><td>' + tr.find('td:nth-child(5)').html()
    + '</td><td><input type="text" name="name_' + tr.attr('id') + '" value="' + tr.find('td:nth-child(5)').html()
    + '" onchange="javascript:recalculerBudgetRestant();"/></td><td><img src="./web/img/croix.jpg" alt="Supprimer" width="15px" height="15px" '
    + 'onclick="javascript:supprimerAchatJoueur(\'' + tr.attr('id') + '\', \'' + idTable + '\');" /></td></tr>';
}
