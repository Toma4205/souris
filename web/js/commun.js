function afficherMessageTempOk(text) {
  var x = document.getElementById("messageTempOk");
  // Add the "show" class to DIV
  x.className = "show";
  x.innerHTML = text;
  // After 3 seconds, remove the show class from DIV
  setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
}

function afficherMessageTempKo(text) {
  var x = document.getElementById("messageTempKo");
  // Add the "show" class to DIV
  x.className = "show";
  x.innerHTML = text;
  // After 3 seconds, remove the show class from DIV
  setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
}
