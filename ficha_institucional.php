<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@4/dark.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
</head>
<body>

<div class="container mt-3"></div>
  <h3>Ficha Institucional</h3>
  <br>

  <!-- Nav tabs -->
  <ul class="nav nav-tabs">
    <li class="nav-item">
      <a class="nav-link active"  data-toggle="tab" href="#home" onclick="cargarInfoConsolidada();">(1) Información Consolidada</a>
    </li>
    <li class="nav-item">   
      <a class="nav-link" data-toggle="tab" href="#menu1" onclick="cargarOfertaPregrado();">(2) Ofera de Pregrado</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-toggle="tab" href="#menu3" onclick="cargarMatPregrado();" >(3) Matrícula de Pregrado</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-toggle="tab" href="#menu4" onclick="cargarPuntPromedio();" >(4) Puntajes promedio</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-toggle="tab" href="#menu5" onclick="cargarOcuVacantes();" >(5) Ocupación de Vacantes</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-toggle="tab" href="#menu6" onclick="cargarRetencion();" >(6) Retencion</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-toggle="tab" href="#menu7" onclick="cargarEgresos();" >(7) Egresos</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-toggle="tab" href="#menu8" onclick="cargarTSegimiento();" >(8) Titulación y seguimiento</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-toggle="tab" href="#menu9" onclick="cargarDAcademica();" >(9) Dotación académica</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-toggle="tab" href="#menu10" onclick="cargarOPostgrado();" >(10) Oferta de Postgrado</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-toggle="tab" href="#menu11" onclick="cargarPCerrado();" >(11) Programas cerrados</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-toggle="tab" href="#menu12" onclick="cargarMPostgrado();" >(12) Matrícula Postgrado</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-toggle="tab" href="#menu13" onclick="cargarPPostgrado();" >(13) Progresión Postgrado</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-toggle="tab" href="#menu14" onclick="cargarDotacioPostgrado();" >(14) Dotación de Postgrado</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-toggle="tab" href="#menu15" onclick="cargarDimensionGestion();" >(15) Dimensión Gestión</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-toggle="tab" href="#menu16" onclick="cargarfichaGob();" >(16) Gobierno</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-toggle="tab" href="#menu17" onclick="cargarInfRecursos(); llamarDPesonal(); llamarEPropio(); llamarEArriendo(); llamarEComodato(); llamarEvoInfra(); llamarIndInfra();" >(17) Infraestructura y recursos</a>
    </li>
  </ul>

  <!-- Tab panes -->
  <div class="tab-content" style="">
  <!--<div id="home" class="container tab-pane active"><br>-->
    <div id="home" class="tab-pane active"><br>
      <h3>Información Consolidada</h3>
      <div id="infoCon"></div>
    </div>
   <!-- <div id="menu1" class="container tab-pane fade"><br>-->
    <div id="menu1" class="tab-pane fade"><br>
    <h3>Oferta Pregrado</h3>
      <div id="ofPregrado"></div>
    </div>
    <!-- <div id="menu3" class="container tab-pane fade"><br>-->
    <div id="menu3" class="tab-pane fade"><br>
        <div id="Mpregrado"></div>
    </div>
    <!-- <div id="menu4" class="container tab-pane fade"><br>-->
    <div id="menu4" class="tab-pane fade"><br>
        <div id="PProm"></div>
    </div>
    <!-- <div id="menu5" class="container tab-pane fade"><br>-->
    <div id="menu5" class="tab-pane fade"><br>
        <div id="ocuV"></div>
    </div>
    <!-- <div id="menu6" class="container tab-pane fade"><br>-->
    <div id="menu6" class="tab-pane fade"><br>
        <div id="Reten"></div>
    </div>
    <!-- <div id="menu9" class="container tab-pane fade"><br>-->
    <div id="menu7" class="tab-pane fade"><br>
        <div id="Egre"></div>
    </div>
    <!-- <div id="menu9" class="container tab-pane fade"><br>-->
    <div id="menu8" class="tab-pane fade"><br>
        <div id="TSegimiento"></div>
    </div>
    <!-- <div id="menu9" class="container tab-pane fade"><br>-->
    <div id="menu9" class="tab-pane fade"><br>
        <div id="DAcademica"></div>
    </div>
    <!-- <div id="menu10" class="container tab-pane fade"><br>-->
    <div id="menu10" class="tab-pane fade"><br>
      <div id="OPostgrado"></div>
    </div>
    <!-- <div id="menu11" class="container tab-pane fade"><br>-->
    <div id="menu11" class="tab-pane fade"><br>
      <div id="PCerrado"></div>
    </div>
    <!-- <div id="menu12" class="container tab-pane fade"><br>-->
     <div id="menu12" class="tab-pane fade"><br>
      <div id="MPostgrado"></div>
    </div>
    <!-- <div id="menu13" class="container tab-pane fade"><br>-->
    <div id="menu13" class="tab-pane fade"><br>
      <div id="PPostgrado"></div>
    </div>
     <!-- <div id="menu14" class="container tab-pane fade"><br>-->
     <div id="menu14" class="tab-pane fade"><br>
      <div id="DPostgrado"></div>
    </div>
   <!-- <div id="menu15" class="container tab-pane fade"><br>-->
    <div id="menu15" class="tab-pane fade"><br>
      <div id="matPregrado"></div>
    </div>
    <!--<div id="menu16" class="container tab-pane fade"><br>-->
    <div id="menu16" class="tab-pane fade"><br>
      <div id="fichaGob"></div>
    </div>
    <!--<div id="menu17" class="container tab-pane fade"><br>-->
    <div id="menu17" class="tab-pane fade"><br>
      <div id="fichaInf17"></div>
    </div>
  </div>
<script src="./ficha-institucional/js/ficha.js"></script>
<script src="./ficha-institucional/js/query.js"></script>
</body>
</html>