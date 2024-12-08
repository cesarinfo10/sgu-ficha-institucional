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
  <style>
        .tab-content > .tab-pane { display: none; }
        .tab-content > .active { display: block; }
    </style>
</head>
<body>
<div class="container mt-3">
        <select id="tabSelector" class="form-control" onchange="changeTab(this.value)">
            <option value="#home">(1) Información Consolidada</option>
            <option value="#menu1">(2) Oferta de Pregrado</option>
            <option value="#menu3">(3) Matrícula de Pregrado</option>
            <option value="#menu4">(4) Puntajes promedio</option>
            <option value="#menu5">(5) Ocupación de Vacantes</option>
            <option value="#menu6">(6) Retención</option>
            <option value="#menu7">(7) Egresos</option>
            <option value="#menu8">(8) Titulación y seguimiento</option>
            <option value="#menu9">(9) Dotación académica</option>
            <option value="#menu10">(10) Oferta de Postgrado</option>
            <option value="#menu11">(11) Programas cerrados</option>
            <option value="#menu12">(12) Matrícula Postgrado</option>
            <option value="#menu13">(13) Progresión Postgrado</option>
            <option value="#menu14">(14) Dotación de Postgrado</option>
            <option value="#menu15">(15) Dimensión Gestión</option>
            <option value="#menu16">(16) Gobierno</option>
            <option value="#menu17">(17) Infraestructura y recursos</option>
            <option value="#menu18">(18) Vinculación con el Medio</option>
            <option value="#menu19">(19) Investigación</option>
        </select>
</div>
        <div class="tab-content">
          <div id="home" class="tab-pane active"><br>
            <h3>Información Consolidada</h3>
            <div id="infoCon"></div>
            </div>
            <div id="menu1" class="tab-pane">
            <br/>
               <h3>Oferta Pregrado</h3>
            <div id="ofPregrado"></div>
            </div>
            <div id="menu3" class="tab-pane">
            <br/>
            <div id="Mpregrado"></div>
            </div>
            <div id="menu4" class="tab-pane">   
            <br/>
            <div id="PProm"></div>
            </div>
            <div id="menu5" class="tab-pane">
            <br>
            <div id="ocuV"></div>
            </div>
            <div id="menu6" class="tab-pane">
            <br>
            <div id="Reten"></div>
            </div>
            <div id="menu7" class="tab-pane">
            <br>
            <div id="Egre"></div>
            </div>
            <div id="menu8" class="tab-pane">
            <br>
            <div id="TSegimiento"></div>
            </div>
            <div id="menu9" class="tab-pane">
            <br>
            <div id="DAcademica"></div>
            </div>
            <div id="menu10" class="tab-pane">
            <br>
            <div id="OPostgrado"></div>    
            </div>
            <div id="menu11" class="tab-pane">
            <br>
            <div id="PCerrado"></div>
            </div>
            <div id="menu12" class="tab-pane">
            <br>
            <div id="MPostgrado"></div>
            </div>
            <div id="menu13" class="tab-pane">
            <br>
            <div id="PPostgrado"></div>
            </div>
            <div id="menu14" class="tab-pane">
            <br>
            <div id="DPostgrado"></div>
            </div>
            <div id="menu15" class="tab-pane">
            <br>
            <div id="matPregrado"></div>
            </div>
            <div id="menu16" class="tab-pane">
            <br>
            <div id="fichaGob"></div>
            </div>
            <div id="menu17" class="tab-pane">    
            <br/>
            <div id="fichaInf17"></div>
           </div>
            <div id="menu18" class="tab-pane">
            <br>
            <div id="Vmedio"></div>
            </div>
            <div id="menu19" class="tab-pane">
            <br>
            <div id="inv"></div>
            </div>
        </div>
    <script>
        function changeTab(tabId) {
            // Ocultar todas las pestañas
            document.querySelectorAll('.tab-pane').forEach(function(tab) {
                tab.classList.remove('active');
            });

            // Mostrar la pestaña seleccionada
            document.querySelector(tabId).classList.add('active');

            // Ejecutar las funciones correspondientes a cada pestaña
            switch(tabId) {
                case '#home':
                    cargarInfoConsolidada();
                    break;
                case '#menu1':
                    cargarOfertaPregrado();
                    break;
                case '#menu3':
                    cargarMatPregrado();
                    break;
                case '#menu4':
                    cargarPuntPromedio();
                    break;
                case '#menu5':
                    cargarOcuVacantes();
                    break;
                case '#menu6':
                    cargarRetencion();
                    break;
                case '#menu7':
                    cargarEgresos();
                    break;
                case '#menu8':
                    cargarTSegimiento();
                    break;
                case '#menu9':
                    cargarDAcademica();
                    break;
                case '#menu10':
                    cargarOPostgrado();
                    break;
                case '#menu11':
                    cargarPCerrado();
                    break;
                case '#menu12':
                    cargarMPostgrado();
                    break;
                case '#menu13':
                    cargarPPostgrado();
                    break;
                case '#menu14':
                    cargarDotacioPostgrado();
                    break;
                case '#menu15':
                    cargarDimensionGestion();
                    break;
                case '#menu16':
                    cargarfichaGob();
                    break;
                case '#menu17':
                    cargarInfRecursos();
                    llamarDPesonal();
                    llamarEPropio();
                    llamarEArriendo();
                    llamarEComodato();
                    llamarEvoInfra();
                    llamarIndInfra();
                    cargarBeneficioEstudiante();
                    break;
                case '#menu18':
                    cargarVinculcionM();
                    break;
                case '#menu19':
                    cargarInvestigacion();
                    break;
            }
        }
        // Restaurar la pestaña activa al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            var activeTab = localStorage.getItem('activeTab');
            if (activeTab) {
                document.getElementById('tabSelector').value = activeTab;
                changeTab(activeTab);
            } else {
                // Si no hay una pestaña activa almacenada, mostrar la primera pestaña por defecto
                changeTab('#home');
            }
        });

        // Almacenar la pestaña activa en localStorage al cambiar de pestaña
        document.getElementById('tabSelector').addEventListener('change', function() {
            localStorage.setItem('activeTab', this.value);
        });
    </script>
<script src="./ficha-institucional/js/ficha.js"></script>
<script src="./ficha-institucional/js/query.js"></script>
</body>
</html>