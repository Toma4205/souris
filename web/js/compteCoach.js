var nbActuSupp = 0;

function confirmerSuppCreaLigue(nom) {
  return confirm('Tu veux vraiment abandonner lâchement la ligue : ' + nom);
}

function masquerLigue(id) {
  var input = $("<input>").attr("type", "hidden").attr("name", "masquer[" + id + "]");
  $('#formPrincipal').append($(input)).submit();
}

function allerVersScoreLigue(idLigue) {
  var input = $("<input>").attr("type", "hidden").attr("name", "scoreLigue[" + idLigue + "]");
  $('#formPrincipal').append($(input)).submit();
}

function supprimerActualite(id) {
  /*$.post(
    '././vue/compteCoach/suppActu.php', // Le fichier cible côté serveur.
    {
        idActu : id
    },
    retourSuppActu, // Nous renseignons uniquement le nom de la fonction de retour.
    'text' // Format des données reçues.
  );*/

  $.ajax({
        'async': false,
        'type': 'POST',
        'url': '././vue/compteCoach/suppActu.php',
        'data' : 'idActu=' + id,
        'dataType' : 'text',
        success : function(code_html, statut){
           retourSuppActu(code_html);
        },

        error : function(resultat, statut, erreur){
          retourSuppActu(0);
        },
    });
}

function retourSuppActu(id){
    if (parseInt(id) > 0) {
      nbActuSupp++;

      $('#actu_'+id).addClass('cache');
      if (nbActuSupp == parseInt($('#count_actu').text())) {
        $('#message_aucune_actu').removeClass('cache');
      }

      afficherMessageTempOk("Suppression OK");
    } else {
      afficherMessageTempKo("Ca marche pas chef !");
    }
}
