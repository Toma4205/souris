var modeExpert;
var tabCompo = [];
var tabPosition = [];

$(document).ready(function() {
    modeExpert = $('#cache_mode_expert').val();
});

function submitForm() {
  var input = $("<input>").attr("type", "hidden").attr("name", "changerTactique");
  $('#formPrincipal').append($(input)).submit();
}

function changerTactique(nouvelle) {
  tabCompo = [];
  $('#contenuCompoEquipe p select').each(function() {
    if ($(this).find(":selected").val() != -1) {
      tabCompo[$(this).attr('name')] = $(this).find(":selected").val();
    }
  });

  for (joueur in tabCompo) {
    // alert(joueur + '=' + tabCompo[joueur]);
  }

  if (modeExpert == 1) {
    alert('Expert à venir...');
  } else {
    var tabNbParPosition = $('#cache_classique_' + nouvelle.value).val().split(',');
    tabPosition = [];

    completerTabPosition('choixDEF', tabNbParPosition[0]);
    completerTabPosition('choixMIL', tabNbParPosition[1]);
    completerTabPosition('choixATT', tabNbParPosition[2]);

    $('#contenuCompoEquipe');
  }
}

function completerTabPosition(lib, nbMax) {
  for (i = 1; i <= nbMax; i++) {
    var name = lib + i;
    if (tabCompo[name] != undefined) {
      tabPosition[name] = tabCompo[name];
    } else {
      tabPosition[name] = -1;
    }
  }
}
