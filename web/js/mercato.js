$(document).ready(function() {
    $('#tableMercatoGB').DataTable();
    $('#tableMercatoGBAchat').DataTable();

    $('#tableMercatoGB tbody').on( 'click', 'tr', function () {
      var tr = $(this).clone();
      $('#tableMercatoGB').DataTable().row($(this)).remove().draw();
      $("#tableMercatoGBAchat").DataTable().rows.add(tr).draw();
      $('#budgetRestant').text(parseInt($('#budgetRestant').val()) - parseInt(tr.find('td:last').text()));
    });

    $('#tableMercatoGBAchat tbody').on( 'click', 'tr', function () {
      var tr = $(this).clone();
      $('#tableMercatoGBAchat').DataTable().row($(this)).remove().draw();
      $("#tableMercatoGB").DataTable().rows.add(tr).draw();
      $('#budgetRestant').text(parseInt($('#budgetRestant').val()) + parseInt(tr.find('td:last').text()));
    });

} );
