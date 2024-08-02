
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

function cargarfichaGob(){
    
    var url ="ficha-institucional/matricula_pregrado.php";
    $.ajax({
        type: "POST",
        url: url,
        async: false,
        success: function(data) {
          //  console.log(data);
            $("#fichaGob").html(data);

        }

    });
}
    function cargarInfRecursos(){
    
        var url ="ficha-institucional/Inf_recursos17.php";
        $.ajax({
            type: "POST",
            url: url,
            async: false,
            success: function(data) {
              //  console.log(data);
                $("#fichaInf17").html(data);
    
            }
    
        });
    
}


function llamarSede(i){
    
    var url ="models/dotacion_personal.php?getSede";
    $.ajax({
        type: "POST",
        url: url,
        async: false,
        success: function(data) {
          if(i==1){
            $("#comboSede17a").html(data);
          }else if(i==2){
            $("#comboSede17b").html(data);
          }else if(i==3){
            $("#comboSede17c").html(data);
          }else if(i==4){
            $("#comboSede17d").html(data);
          }

        }

    });

}