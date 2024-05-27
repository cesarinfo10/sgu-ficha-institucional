
$(document).ready(function () {
    cargarInfoConsolidada();
});

function cargarInfoConsolidada(){
    
    var url ="ficha-institucional/informacion_consolidada.php";
    $.ajax({
        type: "POST",
        url: url,
        async: false,
        success: function(data) {
          //  console.log(data);
            $("#infoCon").html(data);

        }

    });
}

function cargarOfertaPregrado(){
    
    var url ="ficha-institucional/oferta_pregrado.php";
    $.ajax({
        type: "POST",
        url: url,
        async: false,
        success: function(data) {
          //  console.log(data);
            $("#ofPregrado").html(data);

        }

    });
}

function cargarMatriculaPregrado(){
    
    var url ="ficha-institucional/matricula_pregrado.php";
    $.ajax({
        type: "POST",
        url: url,
        async: false,
        success: function(data) {
          //  console.log(data);
            $("#matPregrado").html(data);

        }

    });
}