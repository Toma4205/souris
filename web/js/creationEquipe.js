function selectStyleCoach(id) {
  // Valorisation du champs caché
  $('#codeStyleCoach').val(id);

  // Gestion de la css
  $('.crea_equipe_coach_style').each(function() {
    if ($(this).attr('id') == id) {
      if (!$(this).hasClass('crea_equipe_coach_select')) {
        $(this).addClass('crea_equipe_coach_select');
      }
    } else if ($(this).hasClass('crea_equipe_coach_select')) {
      $(this).removeClass('crea_equipe_coach_select');
    }
  });
}
