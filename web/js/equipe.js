var modeExpert;
var tabCompo = [];
var tabPosition = [];

$(document).ready(function() {
  initTabCompo();
});

function initTabCompo() {
  $('#divTitulaire select').each(function() {
    var val = $(this).find(":selected").val();
    if (val != -1) {
      tabCompo[$(this).attr('name')] = val;
    }
  });
}

function submitForm() {
  var input = $("<input>").attr("type", "hidden").attr("name", "changerTactique");
  $('#formPrincipal').append($(input)).submit();
}

function onChoixJoueur(selectName, classeCss) {
  var val = $('select[name="' + selectName + '"]').find(":selected").val();

  // Si un joueur est sélectionné, on le "cache" dans les autres select
  if (val != -1) {
    $('select[class="' + classeCss + '"][name!="' + selectName + '"] option[value="' + val + '"]').each(function() {
      $(this).addClass('cache');
    });
  }

  // Si un joueur était déjà sélectionné, on le rend visible dans les autres select
  if (tabCompo[selectName] != undefined) {
    $('select[class="' + classeCss + '"][name!="' + selectName + '"] option[value="' + tabCompo[selectName] + '"]').each(function() {
      $(this).removeClass('cache');
    });
  }

  // Si un joueur est sélectionné, on stocke sa valeur
  if (val != -1) {
    tabCompo[selectName] = val;
  } else if (tabCompo[selectName] != undefined) {
    delete tabCompo[selectName];
  }
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
