var tabJoueurDEF = [];
var tabJoueurMIL = [];
var tabJoueurATT = [];
var tabCompoDEF = [];
var tabCompoMIL = [];
var tabCompoATT = [];
var tabRempl = [];
var tabRentrant = [];
var tabSortant = [];
var DIV_TIT_DEF = 'divTitulaireDEF';
var DIV_TIT_MIL = 'divTitulaireMIL';
var DIV_TIT_ATT = 'divTitulaireATT';
var DIV_REMPLACANT = 'divRemplacant';
var DIV_REMPLACEMENT = 'divRemplacement';
var NAME_RENTRANT = 'rentrant_';
var NAME_SORTANT = 'sortant_';
var NAME_NOTE = 'note_';

$(document).ready(function() {
  selectBonusMalus($('#choixBonus').val());
  initTabJoueurSelonPoste(DIV_TIT_DEF, tabJoueurDEF);
  initTabJoueurSelonPoste(DIV_TIT_MIL, tabJoueurMIL);
  initTabJoueurSelonPoste(DIV_TIT_ATT, tabJoueurATT);
  initTabCompo();
  initTabRemplacement();
});

// Appelée lors du changement de tactique
function submitForm(name) {
  var input = $("<input>").attr("type", "hidden").attr("name", name);
  $('#formPrincipal').append($(input)).submit();
}

// Appelée lors de la validation de l'équipe
function controlerBonus() {
  var formOK = true;
  if (!$('select[name="choixJoueurBonus"]').hasClass('cache')) {
    if ($('select[name="choixJoueurBonus"]').find(":selected").val() == -1) {
      formOK = false;
    }
  }
  if (!$('select[name="choixJoueurAdvBonus"]').hasClass('cache')) {
    if ($('select[name="choixJoueurAdvBonus"]').find(":selected").val() == -1) {
      formOK = false;
    }
  }
  if (!$('select[name="choixMiTempsBonus"]').hasClass('cache')) {
    if ($('select[name="choixMiTempsBonus"]').find(":selected").val() == -1) {
      formOK = false;
    }
  }
  if (!formOK && $('#messageErreurBonus').hasClass('cache')) {
    $('#messageErreurBonus').removeClass('cache');
  }

  return formOK;
}

function suppSelectBonusMalus() {
  $('#choixBonus').val('');
  $('#imgSuppBonus').addClass('cache');

  // Gestion de la css
  $('.detail_compo_bonus_malus_bloc').each(function() {
    if ($(this).hasClass('detail_compo_bonus_malus_bloc_select')) {
      $(this).removeClass('detail_compo_bonus_malus_bloc_select');
    }
  });

  cacherAfficherSelectByName('choixJoueurBonus', true);
  cacherAfficherSelectByName('choixMiTempsBonus', true);
  cacherAfficherSelectByName('choixJoueurAdvBonus', true);
}

function selectBonusMalus(val) {
  $('#choixBonus').val(val);
  if (val != '' && $('#imgSuppBonus').hasClass('cache')) {
    $('#imgSuppBonus').removeClass('cache');
  }

  if (val == 'MAU_CRA' || val == 'BOUCHER') {
    // Affichage joueur adv
    cacherAfficherSelectByName('choixJoueurAdvBonus', false);
    cacherAfficherSelectByName('choixMiTempsBonus', true);

    $('select[name="choixMiTempsBonus"]').val(-1);

    if (val == 'BOUCHER') {
      cacherAfficherSelectByName('choixJoueurBonus', false);
    } else {
      cacherAfficherSelectByName('choixJoueurBonus', true);
      $('select[name="choixJoueurBonus"]').val(-1);
    }
  } else if (val == 'CHA_GB' || val == 'PAR_TRU' || val == 'FAM_STA') {
    // Affichage joueur equipe
    cacherAfficherSelectByName('choixJoueurBonus', false);
    cacherAfficherSelectByName('choixJoueurAdvBonus', true);

    $('select[name="choixJoueurAdvBonus"]').val(-1);
    if (val == 'PAR_TRU') {
      cacherAfficherSelectByName('choixMiTempsBonus', false);
    } else {
      $('select[name="choixMiTempsBonus"]').val(-1);
      cacherAfficherSelectByName('choixMiTempsBonus', true);
    }
  } else {
    $('select[name="choixJoueurBonus"]').val(-1);
    $('select[name="choixJoueurAdvBonus"]').val(-1);
    $('select[name="choixMiTempsBonus"]').val(-1);

    cacherAfficherSelectByName('choixJoueurAdvBonus', true);
    cacherAfficherSelectByName('choixJoueurBonus', true);
    cacherAfficherSelectByName('choixMiTempsBonus', true);
  }

  // Gestion de la css
  $('.detail_compo_bonus_malus_bloc').each(function() {
    if ($(this).attr('id') == val) {
      if (!$(this).hasClass('detail_compo_bonus_malus_bloc_select')) {
        $(this).addClass('detail_compo_bonus_malus_bloc_select');
      }
    } else if ($(this).hasClass('detail_compo_bonus_malus_bloc_select')) {
      $(this).removeClass('detail_compo_bonus_malus_bloc_select');
    }
  });
}

function cacherAfficherSelectByName(select, cacher) {
  if (cacher && !$('select[name="' + select + '"]').hasClass('cache')) {
    $('select[name="' + select + '"]').addClass('cache');
  } else if (!cacher && $('select[name="' + select + '"]').hasClass('cache')) {
    $('select[name="' + select + '"]').removeClass('cache');
  }
}

// Appelée lors d'un changement de sélection de titulaire
function onSelectionTitulaire(selectName) {
  var val = $('select[name="' + selectName + '"]').find(":selected").val();
  var idDiv = $('select[name="' + selectName + '"]').closest('div').attr('id');

  // Si un joueur est sélectionné, on le "cache" dans les autres select titu
  // + on le cache des remplaçants et des entrants
  // + on l'affiche dans les sortants
  ajouterTituBlocRemplacement(val);
  cacherJoueurDansAutresSelect(idDiv, val, selectName);

  // Si un joueur était déjà sélectionné, on le rend visible dans les autres select titu
  // + on l'affiche dans les remplaçants
  // + on le cache dans les sortants
  afficherJoueurPrecDansAutresSelect(idDiv, tabCompoDEF, selectName);
  afficherJoueurPrecDansAutresSelect(idDiv, tabCompoMIL, selectName);
  afficherJoueurPrecDansAutresSelect(idDiv, tabCompoATT, selectName);
  supprimerTituBlocRemplacement(selectName);

  // Si un joueur est sélectionné, on stocke sa valeur
  stockerJoueurDansTabCompo(idDiv, val, selectName, DIV_TIT_DEF, tabCompoDEF);
  stockerJoueurDansTabCompo(idDiv, val, selectName, DIV_TIT_MIL, tabCompoMIL);
  stockerJoueurDansTabCompo(idDiv, val, selectName, DIV_TIT_ATT, tabCompoATT);
}

// Appelée lors d'un changement de sélection de remplaçant
function onSelectionRempl(selectName) {
  var val = $('select[name="' + selectName + '"]').find(":selected").val();

  // Si un joueur est sélectionné, on le "cache" dans les autres select
  // + on l'ajoute dans les rentrants
  if (val != -1) {
    $('#' + DIV_REMPLACANT + ' select[name!="' + selectName + '"] option[value="' + val + '"]').each(function() {
      $(this).addClass('cache');
    });
    $('#' + DIV_REMPLACEMENT + ' select[name^="' + NAME_RENTRANT + '"] option[value="' + val + '"]').each(function() {
      $(this).removeClass('cache');
    });
  }

  // Si un joueur était déjà sélectionné, on le rend visible dans les autres select
  // + on le supprime dans les rentrants
  if (tabRempl[selectName] != undefined) {
    $('#' + DIV_REMPLACANT + ' select[name!="' + selectName + '"] option[value="' + tabRempl[selectName] + '"]').each(function() {
      $(this).removeClass('cache');
    });
    supprimerRemplacantBlocRemplacement(tabRempl[selectName]);
  }

  // Si un joueur est sélectionné, on stocke sa valeur
  if (val != -1) {
    tabRempl[selectName] = val;
  } else if (tabRempl[selectName] != undefined) {
    delete tabRempl[selectName];
  }
}

// Appelée lors d'un changement de sélection de rentrant
function onSelectionRentrant(numRentrant) {
  var selectName = NAME_RENTRANT + numRentrant;
  var val = $('#' + DIV_REMPLACEMENT + ' select[name="' + selectName + '"]').find(":selected").val();
  var numRemplacement = selectName.substr(selectName.length - 1)
  var nameSortant = NAME_SORTANT + numRemplacement;

  // Si un joueur était déjà sélectionné
  if (tabRentrant[selectName] != undefined) {
    // On cache les sortants
    $('#' + DIV_REMPLACEMENT + ' select[name="' + nameSortant + '"] option[value="-1"]').prop('selected', true);
    $('#' + DIV_REMPLACEMENT + ' select[name="' + nameSortant + '"] option[value!="-1"]').each(function() {
      $(this).addClass('cache');
    });

    // On affiche l'ancien rentrant dans les autres select
    $('#' + DIV_REMPLACEMENT + ' select[name!="' + selectName + '"][name^="' + NAME_RENTRANT + '"] option[value="' + tabRentrant[selectName] + '"]').each(function() {
      $(this).removeClass('cache');
    });

    if (tabSortant[nameSortant] != undefined) {
      delete tabSortant[nameSortant];
    }
  }

  // Si un joueur est sélectionné, on le "cache" dans les autres select
  if (val != -1) {
    $('#' + DIV_REMPLACEMENT + ' select[name!="' + selectName + '"][name^="' + NAME_RENTRANT + '"] option[value="' + val + '"]').each(function() {
      $(this).addClass('cache');
    });

    // On affiche les sortants possibles selon le poste
    if (!afficherSortantSelonRentrant(val, selectName, tabJoueurDEF, tabCompoDEF)) {
      if (!afficherSortantSelonRentrant(val, selectName, tabJoueurMIL, tabCompoMIL)) {
        afficherSortantSelonRentrant(val, selectName, tabJoueurATT, tabCompoATT);
      }
    }
  }

  // Si un joueur est sélectionné, on stocke sa valeur
  if (val != -1) {
    tabRentrant[selectName] = val;
  } else if (tabRentrant[selectName] != undefined) {
    $('#' + DIV_REMPLACEMENT + ' input[name="' + NAME_NOTE + numRemplacement + '"]').val('');
    delete tabRentrant[selectName];
  }
}

// Appelée lors d'un changement de sélection de sortant
function onSelectionSortant(numSortant) {
  var selectName = NAME_SORTANT + numSortant;
  var val = $('#' + DIV_REMPLACEMENT + ' select[name="' + selectName + '"]').val();

  // Si un joueur est sélectionné, on stocke sa valeur
  // + on le cache dans les autres sortans
  if (val != -1) {
    $('#' + DIV_REMPLACEMENT + ' select[name!="' + selectName + '"][name^="' + NAME_SORTANT + '"] option[value="' + val + '"]').each(function() {
      if (!$(this).hasClass('cache')) {
        $(this).addClass('cache')
      }
    });
    tabSortant[selectName] = val;
  } else if (tabSortant[selectName] != undefined) {
    $('#' + DIV_REMPLACEMENT + ' select[name!="' + selectName + '"][name^="' + NAME_SORTANT + '"] option[value="' + tabSortant[selectName] + '"]').each(function() {
      if ($(this).hasClass('cache')) {
        $(this).removeClass('cache')
      }
    });
    delete tabSortant[selectName];
  }
}

function verifierNote(numRemplacement) {
  var input = $('#' + DIV_REMPLACEMENT + ' input[name="' + NAME_NOTE + numRemplacement + '"]');
  if (input.val() != '') {
    var note = parseFloat(input.val().replace(',', '.')) || 0;

    if (note == 0) {
      if (!input.hasClass('erreurNote')) {
        input.addClass('erreurNote')
      }
    } else if (input.hasClass('erreurNote')) {
      input.removeClass('erreurNote')
    }

    input.val(note);
  }
}

function initTabJoueurSelonPoste(idDiv, tabJoueur) {
  $('#' + idDiv + ' select:first option').each(function() {
    if ($(this).val() != -1) {
      tabJoueur[$(this).val()] = $(this).text();
    }
  });
}

function initTabCompo() {
  initTabCompoSelonPoste(DIV_TIT_DEF, tabCompoDEF);
  initTabCompoSelonPoste(DIV_TIT_MIL, tabCompoMIL);
  initTabCompoSelonPoste(DIV_TIT_ATT, tabCompoATT);

  $('#' + DIV_REMPLACANT + ' select').each(function() {
    var val = $(this).find(":selected").val();
    if (val != -1) {
      tabRempl[$(this).attr('name')] = val;
    }
  });
}

function initTabCompoSelonPoste(idDiv, tabCompo) {
  $('#' + idDiv + ' select').each(function() {
    var val = $(this).find(":selected").val();
    if (val != -1) {
      tabCompo[$(this).attr('name')] = val;
    }
  });
}

function initTabRemplacement() {
  $('#' + DIV_REMPLACEMENT + ' select').each(function() {
    var val = $(this).find(":selected").val();
    var name = $(this).attr('name');

    if (val != -1) {
      var numRemplacement = name.substr(name.length - 1);
      if (name.startsWith(NAME_RENTRANT)) {
        tabRentrant[name] = val;
      } else if (jQuery.inArray(val, tabCompoDEF) != -1
        || jQuery.inArray(val, tabCompoMIL) != -1
        || jQuery.inArray(val, tabCompoATT) != -1) {
        tabSortant[name] = val;
      } else {
        // Suite à un changement tactique, un titulaire peut être supprimé
        $('#' + DIV_REMPLACEMENT + ' select[name="' + name + '"] option[value="-1"]').prop('selected', true);
        $('#' + DIV_REMPLACEMENT + ' select[name="' + name + '"] option[value="' + val + '"]').addClass('cache');
      }
    }

    if (name.startsWith(NAME_SORTANT)) {
      $('#' + DIV_REMPLACEMENT + ' select[name="' + name + '"] option').each(function() {
        var val = $(this).attr('value');
        if (!$(this).hasClass('cache') && val != -1
          && jQuery.inArray(val, tabCompoDEF) == -1
          && jQuery.inArray(val, tabCompoMIL) == -1
          && jQuery.inArray(val, tabCompoATT) == -1) {
            $(this).addClass('cache');
        }
      });
    }
  });
}

function cacherJoueurDansAutresSelect(idDiv, val, selectName) {
  if (val != -1) {
    // Bloc titulaire du même poste
    $('#' + idDiv + ' select[name!="' + selectName + '"] option[value="' + val + '"]').each(function() {
      $(this).addClass('cache');
    });

    // Bloc remplaçant
    $('#' + DIV_REMPLACANT + ' select').each(function() {
      if ($(this).find(":selected").val() == val) {
        // Si le joueur était sélectionné comme remplaçant
        $('#' + DIV_REMPLACANT + ' select[name="' + $(this).attr("name") + '"] option[value="-1"]').prop('selected', true);
        delete tabRempl[$(this).attr("name")];

        // Bloc remplacement
        supprimerRemplacantBlocRemplacement(val);
      };
    });
    $('#' + DIV_REMPLACANT + ' select option[value="' + val + '"]').each(function() {
      $(this).addClass('cache');
    });
  }
}

function afficherJoueurPrecDansAutresSelect(idDiv, tabCompo, selectName) {
  if (tabCompo[selectName] != undefined) {

    // Si un joueur était déjà sélectionné
    $('#' + idDiv + ' select[name!="' + selectName + '"] option[value="' + tabCompo[selectName] + '"]').each(function() {
      $(this).removeClass('cache');
    });
    $('#' + DIV_REMPLACANT + ' select option[value="' + tabCompo[selectName] + '"]').each(function() {
      $(this).removeClass('cache');
    });
    $('#' + DIV_REMPLACEMENT + ' select[name^="' + NAME_SORTANT + '"] option[value="' + tabCompo[selectName] + '"]').each(function() {
      $(this).addClass('cache');
    });
  }
}

function stockerJoueurDansTabCompo(idDiv, val, selectName, idDivPoste, tabCompo) {
  if (val != -1) {
    if (idDiv == idDivPoste) {
      tabCompo[selectName] = val;
    }
  } else if (tabCompo[selectName] != undefined) {
    delete tabCompo[selectName];
  }
}

function supprimerRemplacantBlocRemplacement(val) {
  $('#' + DIV_REMPLACEMENT + ' select[name^="' + NAME_RENTRANT + '"]').each(function() {
    if ($(this).find(":selected").val() == val) {

      // Si le joueur était sélectionné comme rentrant

      // On remet le rentrant à -1
      var numRemplacement = $(this).attr("name").substr(name.length - 1);
      $('#' + DIV_REMPLACEMENT + ' select[name="' + $(this).attr("name") + '"] option[value="-1"]').prop('selected', true);
      delete tabRentrant[$(this).attr("name")];

      // Si le sortant était sélectionné, on le remet à -1
      var nameSortant = NAME_SORTANT + numRemplacement;
      if (tabSortant[nameSortant] != undefined) {
        $('#' + DIV_REMPLACEMENT + ' select[name="' + nameSortant + '"] option[value="-1"]').prop('selected', true);
        delete tabSortant[nameSortant];
      }

      // On cache tous les sortants
      $('#' + DIV_REMPLACEMENT + ' select[name="' + nameSortant + '"] option[value!="-1"]').each(function() {
        if (!$(this).hasClass('cache')) {
          $(this).addClass('cache');
        }
      });

      // On supprime la note
      $('#' + DIV_REMPLACEMENT + ' input[name="' + NAME_NOTE + numRemplacement + '"]').val('');
    }

    // On cache le rentrant
    $('#' + DIV_REMPLACEMENT + ' select[name="' + $(this).attr("name") + '"] option[value="' + val + '"]').addClass('cache');
  });
}

function ajouterTituBlocRemplacement(valTitu) {
  $('#' + DIV_REMPLACEMENT + ' select[name^="' + NAME_RENTRANT + '"]').each(function() {
    var valRentrant = $(this).find(":selected").val();
    if (valRentrant != -1) {
      if (!ajouterTituBlocSortant(valTitu, valRentrant, $(this).attr('name'), tabJoueurDEF)) {
        if (!ajouterTituBlocSortant(valTitu, valRentrant, $(this).attr('name'), tabJoueurMIL)) {
          ajouterTituBlocSortant(valTitu, valRentrant, $(this).attr('name'), tabJoueurATT);
        }
      }
    }
  });
}

function supprimerTituBlocRemplacement(selectName) {
  var valTitu;
  if (tabCompoDEF[selectName] != undefined) {
    valTitu = tabCompoDEF[selectName]
  } else if (tabCompoMIL[selectName] != undefined) {
    valTitu = tabCompoMIL[selectName]
  } else if (tabCompoATT[selectName] != undefined) {
    valTitu = tabCompoATT[selectName]
  }

  if (valTitu != undefined) {
    $('#' + DIV_REMPLACEMENT + ' select[name^="' + NAME_SORTANT + '"]').each(function() {
      var valSortant = $(this).find(":selected").val();
      var nameSortant = $(this).attr('name');

      if (valSortant == valTitu) {
        $('#' + DIV_REMPLACEMENT + ' select[name="' + nameSortant + '"] option[value="-1"]').prop('selected', true);
        delete tabSortant[nameSortant];
      }
    });

    $('#' + DIV_REMPLACEMENT + ' select[name^="' + NAME_SORTANT + '"] option[value="' + valTitu + '"]').each(function() {
      if (!$(this).hasClass('cache')) {
        $(this).addClass('cache');
      }
    });
  }
}

function ajouterTituBlocSortant(valTitu, valRentrant, nameRentrant, tabJoueur) {
  var poste = false;
  if (valTitu in tabJoueur && valRentrant in tabJoueur) {
    poste = true;
    var nameSortant = NAME_SORTANT + nameRentrant.substr(nameRentrant.length - 1);

    $('#' + DIV_REMPLACEMENT + ' select[name="' + nameSortant + '"] option[value="' + valTitu + '"]').removeClass('cache');
  }

  return poste;
}

function afficherSortantSelonRentrant(val, selectName, tabJoueur, tabCompo) {
  var poste = false;

  if(val in tabJoueur) {
    poste = true;
    var nameSortant = NAME_SORTANT + selectName.substr(selectName.length - 1);

    for (indexTitu in tabCompo) {
      if(tabCompo[indexTitu] != undefined) {
        var dejaSortant = false;
        for (indexSortant in tabSortant) {
          if (tabSortant[indexSortant] == tabCompo[indexTitu]) {
            dejaSortant = true;
            break;
          }
        }
        if (!dejaSortant) {
          // Si le titu n'est pas déjà sortant
          $('#' + DIV_REMPLACEMENT + ' select[name="' + nameSortant + '"] option[value="' + tabCompo[indexTitu] + '"]').removeClass('cache');
        }
      }
    }
  }

  return poste;
}
