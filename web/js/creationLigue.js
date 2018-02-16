function gererClicValidationCoach() {
  var nbCoach = $('#table_validation_coach input:checkbox:checked').length;
  if ($('#table_validation_coach input:checkbox:checked').length > 0) {
    if ($('#boutonValCoach').is('[disabled=disabled]')) {
      $('#boutonValCoach').removeAttr('disabled');
    }
  } else {
    $('#boutonValCoach').attr('disabled', 'disabled');
  }
}
