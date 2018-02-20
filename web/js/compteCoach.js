function confirmerSuppCreaLigue(nom) {
  return confirm('Tu veux vraiment abandonner lâchement la ligue : ' + nom);
}

function masquerLigue(id) {
  var input = $("<input>").attr("type", "hidden").attr("name", "masquer[" + id + "]");
  $('#formPrincipal').append($(input)).submit();
}
