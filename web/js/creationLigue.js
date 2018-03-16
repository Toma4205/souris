$(document).ready(function() {
  onSelectionBonusMalus();
});

// Lors création Ligue
function onSelectionBonusMalus() {
  var val = $('select[name="bonusMalus"]').find(":selected").val();
  if (CODE_BONUS_MALUS_PERSO == val) {
    $('#libBonusMalusPerso').removeClass('cache');
  } else if (!$('#libBonusMalusPerso').hasClass('cache')) {
    $('#libBonusMalusPerso').addClass('cache');
  }
}

// Lors clic sur checkbox pour choisir les participants
function compterBonusASelect() {
  var nbCoach = $('#table_validation_coach input:checkbox:checked').length;

  if ($('#nbBonusMalusASelect').length) {
    $('#nbBonusMalusASelect').text(nbCoach);
  }
}

function confirmerValCoach()
{
  var nbCoach = $('#table_validation_coach input:checkbox:checked').length;
  if (nbCoach > 0) {
    return confirm('Es-tu sûr des confrères sélectionnés ?');
  } else {
    var err = "Il faut sélectionner des confrères avant de valider.";
    $('#messageErreurValCoach').text(err);
    if ($('#messageErreurValCoach').hasClass('cache')) {
      $('#messageErreurValCoach').removeClass('cache');
    }
    return false;
  }
}

// Lors validation participants et bonus/malus
function controlerBonus()
{
  var nbCoach = $('#table_validation_coach input:checkbox:checked').length;
  if (nbCoach > 0) {
    var nb = $('#nbBonusMalusASelect').text();

    var total = 0;
    $('#table_selection_bonus_malus tbody tr td select').each(function() {
        total += parseInt($(this).find(":selected").val());
    });

    if (total < nb) {
      return confirm('Es-tu sûr de lancer la ligue ? Tu peux encore choisir ' + (nb - total) + ' bonus.');
    } else if (total > nb) {
      var err = "C'est pas Noël ! Tu dois enlever " + (total - nb) + " bonus.";
      $('#messageErreurValCoachEtBonus').text(err);
      if ($('#messageErreurValCoachEtBonus').hasClass('cache')) {
        $('#messageErreurValCoachEtBonus').removeClass('cache');
      }
      return false;
    } else {
      return confirm('Es-tu sûr des confrères sélectionnés ?');
    }
  } else {
    var err = "Il faut sélectionner des confrères avant de valider.";
    $('#messageErreurValCoachEtBonus').text(err);
    if ($('#messageErreurValCoachEtBonus').hasClass('cache')) {
      $('#messageErreurValCoachEtBonus').removeClass('cache');
    }
    return false;
  }

}
