var tabMercato = [];
var BUDGET_INITIAL = 300;

$(document).ready(function() {
    gererTableMercato('tableMercatoGB');
    gererTableMercato('tableMercatoDEF');
    gererTableMercato('tableMercatoMIL');
    gererTableMercato('tableMercatoATT');

    recalculerBudgetRestant();
});

function gererTableMercato(idTable)
{
  $('#' + idTable).DataTable();

  // Suppression partie pagination
  $('#' + idTable + '_paginate').remove();

  $('#' + idTable + ' tbody').on( 'click', 'tr', function () {
    var tr = $(this).clone();
    tabMercato[tr.attr('id')] = tr;

    $('#' + idTable).DataTable().row($(this)).remove().draw();

    var html = getHTML(tr, idTable);

    var table = document.getElementById(idTable + 'Achat');
    if (table.rows.length == 1) {
      $('#' + idTable + 'Achat tbody').append(html);
    } else {
      $('#' + idTable + 'Achat tr:last').after(html);
    }
    $('#budgetRestant').text(parseInt($('#budgetRestant').val()) - parseInt(tr.find('td:last').text()));

    effectuerControlesSuiteModif();
  });

  supprimerDansListeJoueurDejaAchete(idTable);
}

function supprimerDansListeJoueurDejaAchete(idTable)
{
  $('#' + idTable + 'Achat tbody tr').each(function() {
    var tr = $('tr[id=\'' + $(this).attr('id').substring(6) + '\']');
    tabMercato[tr.attr('id')] = tr;

    $('#' + idTable).DataTable().row(tr).remove().draw();
  });
}

function effectuerControlesSuiteModif()
{
  verifierBoutonValiderMercato();
  controlerImageBudget();
  controlerImageGB();
  controlerImageDEF();
  controlerImageMIL();
  controlerImageATT();
}

function supprimerAchatJoueur(id, idTable)
{
  var tr = $('tr[id=\'Achat_' + id + '\']');
  $("#" + idTable).DataTable().rows.add(tabMercato[id]).draw();
  $('#budgetRestant').text(parseInt($('#budgetRestant').val()) + parseInt(tr.find('td:nth-child(5) input').val()));
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

function controlerImageGB()
{
  var nbGB = $('#tableMercatoGBAchat tbody tr').length;
  if (nbGB >= 2) {
    $('#imageGB').attr("src","./web/img/validation.jpg");
  } else {
    $('#imageGB').attr("src","./web/img/erreur.jpg");
  }
}

function controlerImageDEF()
{
  var nbDEF = $('#tableMercatoDEFAchat tbody tr').length;
  if (nbDEF >= 6) {
    $('#imageDEF').attr("src","./web/img/validation.jpg");
  } else {
    $('#imageDEF').attr("src","./web/img/erreur.jpg");
  }
}

function controlerImageMIL()
{
  var nbMIL = $('#tableMercatoMILAchat tbody tr').length;
  if (nbMIL >= 6) {
    $('#imageMIL').attr("src","./web/img/validation.jpg");
  } else {
    $('#imageMIL').attr("src","./web/img/erreur.jpg");
  }
}

function controlerImageATT()
{
  var nbATT = $('#tableMercatoATTAchat tbody tr').length;
  if (nbATT >= 3) {
    $('#imageATT').attr("src","./web/img/validation.jpg");
  } else {
    $('#imageATT').attr("src","./web/img/erreur.jpg");
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

// Attention, si changement => effectuer aussi dans prepaMercato.php
function getHTML(tr, idTable)
{
  return '<tr id="Achat_' + tr.attr('id') + '"><td>' + tr.find('td:first').html()
    + '</td><td>' + tr.find('td:nth-child(2)').html() + '</td><td>'
    + tr.find('td:nth-child(3)').html() + '</td><td>' + tr.find('td:nth-child(4)').html()
    + '</td><td><input type="text" name="name_' + tr.attr('id') + '" value="' + tr.find('td:nth-child(4)').html()
    + '" onchange="javascript:recalculerBudgetRestant();"/></td><td><img src="./web/img/croix.jpg" alt="Supprimer" width="15px" height="15px" '
    + 'onclick="javascript:supprimerAchatJoueur(\'' + tr.attr('id') + '\', \'' + idTable + '\');" /></td></tr>';
}
