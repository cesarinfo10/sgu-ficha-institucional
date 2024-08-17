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
      <a class="nav-link active"  data-toggle="tab" href="#home" onclick="cargarInfoConsolidada();">Información Consolidada</a>
    </li>
    <li class="nav-item">   
      <a class="nav-link" data-toggle="tab" href="#menu1" onclick="cargarOfertaPregrado();">Ofera de Pregrado</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-toggle="tab" href="#menu2" onclick="cargarDimensionGestion();" >(15) Dimensión Gestión</a>
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
   <!-- <div id="menu2" class="container tab-pane fade"><br>-->
    <div id="menu2" class="tab-pane fade"><br>
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