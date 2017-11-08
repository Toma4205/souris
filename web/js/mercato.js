var tabMercato = [];
var BUDGET_INITIAL = 300;

$(document).ready(function() {
    $('#tableMercatoGB').DataTable();

    // Suppression partie pagination
    $('#tableMercatoGB_paginate').remove();

    $('#tableMercatoGB tbody').on( 'click', 'tr', function () {
      var tr = $(this).clone();
      tabMercato[tr.attr('id')] = tr;

      $('#tableMercatoGB').DataTable().row($(this)).remove().draw();

      var html = '<tr id="Achat' + tr.attr('id') + '"><td>' + tr.find('td:first').html()
        + '</td><td>' + tr.find('td:nth-child(2)').html() + '</td><td>'
        + tr.find('td:nth-child(3)').html() + '</td><td>' + tr.find('td:nth-child(4)').html()
        + '</td><td><input type="text" name="name_' + tr.attr('id') + '" value="' + tr.find('td:nth-child(4)').html()
        + '" onchange="javascript:recalculerBudgetRestant();"/><td><img src="./web/img/croix.jpg" alt="Supprimer" width="15px" height="15px" '
        + 'onclick="javascript:supprimerAchatJoueur(\'' + tr.attr('id') + '\');" /></td></td></tr>';

      var table = document.getElementById('tableMercatoGBAchat');
      if (table.rows.length == 1) {
        $('#tableMercatoGBAchat tbody').append(html);
      } else {
        $('#tableMercatoGBAchat tr:last').after(html);
      }
      $('#budgetRestant').text(parseInt($('#budgetRestant').val()) - parseInt(tr.find('td:last').text()));

      effectuerControlesSuiteModif();
    });

    effectuerControlesSuiteModif();
});

function effectuerControlesSuiteModif()
{
  verifierBoutonValiderMercato();
  controlerImageBudget();
  controlerImageGB();
}

function supprimerAchatJoueur(id)
{
  var tr = $('#Achat' + id);
  $("#tableMercatoGB").DataTable().rows.add(tabMercato[id]).draw();
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

  $('#budgetRestant').text(300 - total);

  effectuerControlesSuiteModif();
}

function verifierBoutonValiderMercato()
{
  var nbGB = $('#tableMercatoGBAchat tbody tr').length;
  if (parseInt($('#budgetRestant').text()) >= 0 && nbGB >= 2) {
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

function controlerImageBudget()
{
  if (parseInt($('#budgetRestant').text()) >= 0) {
    $('#imageBudget').attr("src","./web/img/validation.jpg");
  } else {
    $('#imageBudget').attr("src","./web/img/erreur.jpg");
  }
}
