
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


/*==========================================================================================
                                  DOTACION DE PERSONAL
==========================================================================================*/

function llamarSede(i){
    
    var url ="models/dotacion_personal_17.php?getSede";
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

/*=============================================
INSERTAR - UPDATE DOTACION DE PERSONAL
=============================================*/
function insertarDP(){
  

  let idSede =$("#tipoBeneficio").val();
  let sedeAno = $("#sedeAno").val();
  let porc_mujeres = $("#porMujerDP").val();
  let total = $("#sedeTotal").val();

  let cftSede = $("#optradioDp1").is(':checked') ? 1 : 0;
  let ipSede = $("#optradioDp2").is(':checked') ? 1 : 0;
  let uniSede = $("#optradioDp3").is(':checked') ? 1 : 0;

if(idSede == 0 || sedeAno == "" || porc_mujeres == "" || total == ""){
  Swal.fire("Todos los campos son obligatorios!");
  return false;
}
  let dataString = 'idSede='+idSede.trim()+'&sedeAno='+sedeAno.trim()+'&porc_mujeres='+porc_mujeres.trim()+'&total='+total.trim() 
                    +'&cftSede='+cftSede+'&ipSede='+ipSede +'&uniSede='+uniSede;


$.ajax({
            type: "POST",
            url: "models/dotacion_personal_17.php?postDtPersonal",
            data: dataString,
            success: function(data) {
             if (data == 1) {
              Swal.fire({
                title: "Registo guardado con exito!",
                icon: "success"
              });
              llamarDPesonal();
              limpiarFormularioDP();

            } else if (data == 3) {
              Swal.fire({
                title: "Registro actualizado con exito!",
                icon: "success"
              });
              llamarDPesonal();
              limpiarFormularioDP();

            } else {
              Swal.fire({
                title: "Error al guardar el registro!",
                icon: "error"
              });
              limpiarFormularioDP();
            }
            }

        });
}

function limpiarFormularioDP(){
  $("#tipoBeneficio").val(0);
  $("#sedeAno").val("");
  $("#porMujerDP").val("");
  $("#sedeTotal").val("");
  $("#optradioDp1").prop("checked", false);
  $("#optradioDp2").prop("checked", false);
  $("#optradioDp3").prop("checked", true);
}
/*=============================================
LLAMAR A TODAS LAS DOTACION DE PERSONAL
=============================================*/
function llamarDPesonal(){
  setTimeout(() => {
  var url ="models/dotacion_personal_17.php?getDPersonal";
  $.ajax({
      type: "POST",
      url: url,
      async: false,
      success: function(data) {
          $("#tblDPersonal").html(data);
      }

  });
}, 1000);
}
/*==========================================================================================
                                  FIN DOTACION DE PERSONAL
==========================================================================================*/

/*==========================================================================================
                                  EDIFICIO PROPIO
==========================================================================================*/

/*=============================================
INSERTAR - UPDATE EDIFICIO PROPIO
=============================================*/
function insertarEP(){
  

  let idSede =$("#tipoBeneficio").val();
  let direccion = $("#dirEp").val();
  let mtEp = $("#mtEp").val();
  let anoAdquisision = $("#epAno").val();

  let cftSede = $("#optradioEP1").is(':checked') ? 1 : 0;
  let ipSede = $("#optradioEP2").is(':checked') ? 1 : 0;
  let uniSede = $("#optradioEP3").is(':checked') ? 1 : 0;

if(idSede == 0 || direccion == "" || mtEp == "" || anoAdquisision == ""){
  Swal.fire("Todos los campos son obligatorios!");
  return false;
}
  let dataString = 'idSede='+idSede.trim()+'&direccion='+direccion.trim()+'&mtEp='+mtEp.trim()+'&anoAdquisision='+anoAdquisision.trim() 
                    +'&cftSede='+cftSede+'&ipSede='+ipSede +'&uniSede='+uniSede;


$.ajax({
            type: "POST",
            url: "models/dotacion_personal_17.php?postEPropio",
            data: dataString,
            success: function(data) {
             if (data == 1) {
              Swal.fire({
                title: "Registo guardado con exito!",
                icon: "success"
              });
              llamarEPropio();
              limpiarFormularioEP();

            } else if (data == 3) {
              Swal.fire({
                title: "Registro actualizado con exito!",
                icon: "success"
              });
              llamarEPropio();
              limpiarFormularioEP();

            } else {
              Swal.fire({
                title: "Error al guardar el registro!",
                icon: "error"
              });
              limpiarFormularioEP();
            }
            }

        });
}

function limpiarFormularioEP(){
  $("#tipoBeneficio").val(0);
  $("#dirEp").val("");
  $("#mtEp").val("");
  $("#epAno").val("");
  $("#optradioEP1").prop("checked", false);
  $("#optradioEP2").prop("checked", false);
  $("#optradioEP3").prop("checked", true);
}
/*=============================================
LLAMAR A TODAS LOS EDIFICIO PROPIO
=============================================*/
function llamarEPropio(){
  setTimeout(() => {
  var url ="models/dotacion_personal_17.php?getEPropio";
  $.ajax({
      type: "POST",
      url: url,
      async: false,
      success: function(data) {
          $("#tblEPropio").html(data);
      }

  });
}, 1000);
}
/*==========================================================================================
                                  FIN EDIFICIO PROPIO
==========================================================================================*/

/*==========================================================================================
                                  EDIFICIO DE ARRIENDO
==========================================================================================*/

/*=============================================
INSERTAR - UPDATE EDIFICIO DE ARRIENDO
=============================================*/
function insertarEA(){
  

  let idSede =$("#tipoBeneficio").val();
  let propEA = $("#propEA").val();
  let fecIniEA = $("#fecIniEA").val();
  let plazoEA = $("#plazoEA").val();
  let arriendoEA = $("#arriendoEA").val();
  let metrosCuaEA = $("#metrosCuaEA").val();

  let cftSede = $("#optradioArriendo1").is(':checked') ? 1 : 0;
  let ipSede = $("#optradioArriendo2").is(':checked') ? 1 : 0;
  let uniSede = $("#optradioArriendo3").is(':checked') ? 1 : 0;

if(idSede == 0 || propEA == "" || arriendoEA == "" || metrosCuaEA == ""){
  Swal.fire("Todos los campos son obligatorios!");
  return false;
}
  let dataString = 'idSede='+idSede.trim()+'&propEA='+propEA.trim()+'&fecIniEA='+fecIniEA.trim()+'&plazoEA='+plazoEA.trim() 
                    +'&arriendoEA='+arriendoEA.trim()+'&metrosCuaEA='+metrosCuaEA.trim()+'&cftSede='+cftSede
                    +'&ipSede='+ipSede +'&uniSede='+uniSede;


$.ajax({
            type: "POST",
            url: "models/dotacion_personal_17.php?postEArrendado",
            data: dataString,
            success: function(data) {
             if (data == 1) {
              Swal.fire({
                title: "Registo guardado con exito!",
                icon: "success"
              });
              llamarEArriendo();
              limpiarFormularioEP();

            } else if (data == 3) {
              Swal.fire({
                title: "Registro actualizado con exito!",
                icon: "success"
              });
              llamarEArriendo();
              limpiarFormularioEP();

            } else {
              Swal.fire({
                title: "Error al guardar el registro!",
                icon: "error"
              });
              limpiarFormularioEP();
            }
            }

        });
}

function limpiarFormularioEA(){
  $("#tipoBeneficio").val(0);
  $("#dirEp").val("");
  $("#mtEp").val("");
  $("#epAno").val("");
  $("#optradioEP1").prop("checked", false);
  $("#optradioEP2").prop("checked", false);
  $("#optradioEP3").prop("checked", true);
}
/*=============================================
LLAMAR A TODAS LOS EDIFICIO DE ARRIENDO
=============================================*/
function llamarEArriendo(){
  setTimeout(() => {
  var url ="models/dotacion_personal_17.php?getEArendado";
  $.ajax({
      type: "POST",
      url: url,
      async: false,
      success: function(data) {
          $("#tblEArriendo").html(data);
      }

  });
}, 1000);
}
/*==========================================================================================
                                  FIN EDIFICIO DE ARRIENDO
==========================================================================================*/