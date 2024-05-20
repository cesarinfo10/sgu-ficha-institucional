<?php

setlocale(LC_ALL,"es_CL");
setlocale(LC_ALL,"es_CL.UTF8");
//setlocale(LC_ALL,"es_CL@euro");
date_default_timezone_set("America/Santiago");
setlocale(LC_MONETARY,"es_CL.UTF8");
include("integracion.php");
include("conversor_num2palabras.php");

//$authbd = " user=sgu password=blanco";
$authbd = " user=sgu host=10.111.2.113";
$bdcon = pg_connect("dbname=regacad" . $authbd);
$enlbase = "principal.php?modulo";
$enlbase_sm = "principal_sm.php?modulo";

$ANO = 2024;
$SEMESTRE = 1;

// Inicio y término 1er semestre
$Fec_Ini_Sem1 = strtotime("2024-03-18");
$Fec_Fin_Sem1 = strtotime("2024-07-20");

// Inicio y término 1ra nota de cátedra del 1er semestre
$f_ini_nc1_sem1  = strtotime("2024-04-08");
$f_fin_nc1_sem1  = strtotime("2024-04-22");

// Inicio y término Solemnes I 1er semestre
$f_ini_sol1_sem1  = strtotime("2024-05-06");
$f_fin_sol1_sem1  = strtotime("2024-05-18");

// Inicio y término Solemnes II 1er semestre
$f_ini_sol2_sem1  = strtotime("2024-05-24");
$f_fin_sol2_sem1  = strtotime("2024-07-06");

// Inicio y término Recuperativas 1er semestre
$f_ini_recup_sem1 = strtotime("2024-07-15");
$f_fin_recup_sem1 = strtotime("2024-07-20");

// Inicio y término 2do semestre
$Fec_Ini_Sem2 = strtotime("2024-08-05");
$Fec_Fin_Sem2 = strtotime("2024-12-16");

// Inicio y término Solemnes I 2do semestre
$f_ini_sol1_sem2  = strtotime("2024-09-23");
$f_fin_sol1_sem2  = strtotime("2024-10-05");

// Inicio y término Solemnes II 2do semestre
$f_ini_sol2_sem2  = strtotime("2024-11-18");
$f_fin_sol2_sem2  = strtotime("2024-11-30");

// Inicio y término Recuperativas 2do semestre
$f_ini_recup_sem2 = strtotime("2024-12-09");
$f_fin_recup_sem2 = strtotime("2024-12-14");


$FEC_INI_TOMA_RAMOS = strtotime("2024-03-01");
$FEC_FIN_TOMA_RAMOS = strtotime("2024-03-23");

// Fechas de Postulación a Beca UMC (afecta sólo a estudiantes)
$FEC_INI_POSTBECAUMC = strtotime("2023-11-02");
$FEC_FIN_POSTBECAUMC = strtotime("2024-03-31");

// Fechas de Solicitudes (Periodo en que los alumnos pueden hacerlas)
$FEC_INI_SOLICITUDES = strtotime("2021-03-01");
$FEC_FIN_SOLICITUDES = strtotime("2025-01-26");

$SEMESTRE_InsAsig = 1;
$ANO_InsAsig = 2024;

$ANO_MATRICULA = 2024;
$SEMESTRE_MATRICULA = 1;

$REPRESENTANTE_LEGAL = "Vicerrector de Administración y Finanzas don Mauricio Antonio Espinosa Sanhueza";

//$SECRETARIA_GENERAL_nombre = "Mercedes Aubá Asvisio";
$SECRETARIA_GENERAL_nombre = "Verónica Peñalosa ";
$JEFE_REGACAD_nombre       = "Andrea Aranela Suazo";

$sino = array(array('id'=>"t",'nombre'=>"Si"),
              array('id'=>"f",'nombre'=>"No")
             );

$JORNADAS = array(array('id'=>'D','nombre'=>"Diurna"),
                  array('id'=>'V','nombre'=>"Vespertina")
                 );

$generos = array(array('id'=>"f",'nombre'=>"Femenino"),
                 array('id'=>'m','nombre'=>"Masculino")
                );

$ADMISION = array(array('id'=>"1", 'nombre'=>"Regular"),
                  array('id'=>"2", 'nombre'=>"Extraordinaria"),
                  array('id'=>"3", 'nombre'=>"Especial"),
                  array('id'=>"4", 'nombre'=>"Prosecución"),
                  array('id'=>"10",'nombre'=>"Modular"),
                  array('id'=>"20",'nombre'=>"Modular (Extr.)")
                 );

$parentezcos = array(array('id' => "Ninguno"   , 'nombre' => "Ninguno (mismo postulante)"),
                     array('id' => "Madre"     , 'nombre' => "Madre"),
                     array('id' => "Padre"     , 'nombre' => "Padre"),
                     array('id' => "Hermano(a)", 'nombre' => "Hermano(a)"),
                     array('id' => "Esposo(a)" , 'nombre' => "Esposo(a)"),
                     array('id' => "Tío(a)"    , 'nombre' => "Tío(a)"),
                     array('id' => "Primo(a)"  , 'nombre' => "Primo(a)"),
                     array('id' => "Otro"      , 'nombre' => "Otro")
                    );

$becas_mat = array(array("id" => "UMC","nombre" => "Promoción"),
                   array("id" => "GMO","nombre" => "Presidencia")
                  );

$financiamientos = array(array("id" => "CREDITO","nombre" => "Crédito"),
                         array("id" => "CONTADO","nombre" => "Contado")
                        );

$NIVELES = array(array('id'=>1 , 'nombre'=>"Primer"),
                 array('id'=>2 , 'nombre'=>"Segundo"),
                 array('id'=>3 , 'nombre'=>"Tercer"),
                 array('id'=>4 , 'nombre'=>"Cuarto"),
                 array('id'=>5 , 'nombre'=>"Quinto"),
                 array('id'=>6 , 'nombre'=>"Sexto"),
                 array('id'=>7 , 'nombre'=>"Séptimo"),
                 array('id'=>8 , 'nombre'=>"Octavo"),
                 array('id'=>9 , 'nombre'=>"Noveno"),
                 array('id'=>10, 'nombre'=>"Décimo"),
                 array('id'=>11, 'nombre'=>"Undécimo"),
                 array('id'=>12, 'nombre'=>"Duodécimo"));

$REGIMENES = array(array('id'=>"PRE", 'nombre'=>"Pregrado"),
                   array('id'=>"POST",'nombre'=>"Postgrado"));

$CANT_REGS = array(array('id'=>30 , 'nombre'=>"30"),
                   array('id'=>60 , 'nombre'=>"60"),
                   array('id'=>90 , 'nombre'=>"90"));                   

$vocales = array("á","é","í","ó","ú","a","e","i","o","u");
$voc_regexp = array('a'=>"[aá]",'e'=>"[eé]",'i'=>"[ií]",'o'=>"[oó]",'u'=>"[uú]",
                    'á'=>"[aá]",'é'=>"[eé]",'í'=>"[ií]",'ó'=>"[oó]",'ú'=>"[uú]");
$vocales_regexp = array("[aá]","[eé]","[ií]","[oó]","[uú]","[aá]","[eé]","[ií]","[oó]","[uú]");

$LF="\n";

$anos = array();
for($ano_x=date("Y")+1;$ano_x>=1998;$ano_x--) {
	$anos = array_merge($anos,array($ano_x => array("id" => $ano_x,"nombre" => $ano_x)));
};

$semestres = array(array("id" => 0,"nombre" => "Verano"),
                   array("id" => 1,"nombre" => "Primero"),
                   array("id" => 2,"nombre" => "Segundo"));

$semestres_academicos = array(array("id" => 0,"nombre" => "Verano"),
                              array("id" => 1,"nombre" => "Otoño"),
                              array("id" => 2,"nombre" => "Primavera"));

$dias_fn = array();
for($x=1;$x<=31;$x++) { $dias_fn = array_merge( $dias_fn , array(array('id' => "$x", 'nombre' => "$x")) ); }

$meses_fn = array();
for($x=1;$x<=12;$x++) { $meses_fn = array_merge( $meses_fn , array(array('id'=>"$x",'nombre'=>meses($x) )) ); }

$anos_fn = array();
$ano_actual = intval(strftime("%Y"));
for($x=($ano_actual-17);$x>=($ano_actual-90);$x--) { $anos_fn = array_merge( $anos_fn , array(array('id'=>"$x",'nombre'=>"$x")) ); }

$anos_contratos = array();
for($ano_x=date("Y")+1;$ano_x>=2010;$ano_x--) {
	$anos_contratos = array_merge($anos_contratos,array($ano_x => array("id" => $ano_x,"nombre" => $ano_x)));
}

$estados_contratos = array(array("id"=>'E', "nombre"=>'Emitido'),
                           array("id"=>'N', "nombre"=>'Nulo'),
                           array("id"=>'1', "nombre"=>'Todos NO Nulo'),
                           array("id"=>'D', "nombre"=>'Todos Desertados'),
                           array("id"=>'S', "nombre"=>'Suspendido'),
                           array("id"=>'R', "nombre"=>'Retirado'),
                           array("id"=>'A', "nombre"=>'Abandonado'),                           
                           array("id"=>'Z', "nombre"=>'Reemplazado'),                           
                           array("id"=>'F', "nombre"=>'Firmado'));

$tipos_contratos = array(array("id"=>'Anual',     "nombre"=>'Anual'),
                         array("id"=>'Semestral', "nombre"=>'Semestral'),
                         array("id"=>'Estival',   "nombre"=>'Estival'),
                         array("id"=>'Egresado',  "nombre"=>'Egresado'),
                         array("id"=>'Modular',   "nombre"=>'Modular')); 

$tipos_alumnos = array(array("id"=>'N', "nombre"=>'Nuevos'),
                       array("id"=>'A', "nombre"=>'Antiguos'));

#$ANO = date("Y");
#if (date("n") < 9) { $SEMESTRE = 1; } else { $SEMESTRE = 2; }

$dias_palabra = array(array('id'=>1,'nombre'=>"Lunes"),
                      array('id'=>2,'nombre'=>"Martes"),
                      array('id'=>3,'nombre'=>"Miércoles"),
                      array('id'=>4,'nombre'=>"Jueves"),
                      array('id'=>5,'nombre'=>"Viernes"),
                      array('id'=>6,'nombre'=>"Sábado"));

$meses_palabra = array(array('id'=>1, 'nombre'=>"Enero"),
                       array('id'=>2, 'nombre'=>"Febrero"),
                       array('id'=>3, 'nombre'=>"Marzo"),
                       array('id'=>4, 'nombre'=>"Abril"),
                       array('id'=>5, 'nombre'=>"Mayo"),
                       array('id'=>6, 'nombre'=>"Junio"),
                       array('id'=>7, 'nombre'=>"Julio"),
                       array('id'=>8, 'nombre'=>"Agosto"),
                       array('id'=>9, 'nombre'=>"Septiembre"),
                       array('id'=>10,'nombre'=>"Octubre"),
                       array('id'=>11,'nombre'=>"Noviembre"),
                       array('id'=>12,'nombre'=>"Diciembre"));
 
$estados = array(array("id"=>1,'nombre'=>"Vigentes"),
                 array("id"=>5,"nombre"=>"Titulados"),
                 array("id"=>7,"nombre"=>"Suspendidos"));
                 
$estados_civiles = array(array("id"=>"S","nombre"=>"Soltero(a)"),
                         array("id"=>"C","nombre"=>"Casado(a)"),
                         array("id"=>"U","nombre"=>"Unido(a)"),
                         array("id"=>"P","nombre"=>"Vive en Pareja"),
                         array("id"=>"D","nombre"=>"Divorciado(a)"),
                         array("id"=>"V","nombre"=>"Viudo(a)"));

$CATEG_DOCENTE = array(array("id"=>"Adjunto", "nombre"=>"Adjunto(a)"),
                       array("id"=>"Asociado","nombre"=>"Asociado(a)"),
                       array("id"=>"Asociado Superior","nombre"=>"Asociado(a) Superior"),
                       array("id"=>"Titular", "nombre"=>"Titular"));
 
function auth_imap($usuario,$clave,$servidor) {
	$auth = imap_open("{" . $servidor . ":143}Maildir",$usuario,$clave);
	if (!$auth) {
		$auth = imap_open("{" . $servidor . ":993/novalidate-cert/ssl}INBOX",$usuario,$clave);
	}
	$foo = imap_errors();
	return $auth;
};

function auth_bd($usuario,$tipo) {
	global $authbd;
	$bdcon = pg_connect("dbname=regacad" . $authbd);	
	$SQLtxt = "SELECT * FROM usuarios WHERE nombre_usuario='$usuario' AND tipo=$tipo AND activo;";
	$resultado = pg_query($bdcon, $SQLtxt);
	if (pg_numrows($resultado) == 1) {
		return true;
	} else {
		return false;
	};	
};

function tipos_usuario($tipo) {
	if (isset($tipo)) {		
		$tipo_usuario = consulta_sql("SELECT nombre,servidor FROM usuarios_tipo WHERE id=$tipo;");
		return $tipo_usuario[0];
	} else {
		$tipos_usuario = consulta_sql("SELECT id,nombre FROM usuarios_tipo WHERE id<>3 ORDER BY jerarquia,id;");
		return $tipos_usuario;
	}
}

function grados_academicos($id) {
	global $authbd;
	$bdcon = pg_connect("dbname=regacad" . $authbd);	
	if (isset($id)) {
		$SQLtxt = "SELECT nombre FROM grado_acad WHERE id=$id;";
		$resultado = pg_query($bdcon, $SQLtxt);
		$grados_academicos = pg_fetch_all($resultado);
		return $grados_academicos[0]['nombre'];
	} else {
		$SQLtxt = "SELECT * FROM grado_acad ORDER BY id;";
		$resultado = pg_query($bdcon, $SQLtxt);
		$grados_academicos = pg_fetch_all($resultado);
		return $grados_academicos;
	};	
};

function nombre_real_usuario($usuario,$tipo) {
	$nombre = consulta_sql("SELECT nombre FROM vista_usuarios WHERE nombre_usuario='$usuario' AND id_tipo=$tipo");
	return $nombre[0]['nombre'];
}

function utf2html($matriz) {
	$filas = count($matriz);
	for ($x=0;$x < $filas; $x++) {
		foreach ($matriz[$x] as $clave => $valor) {
			$matriz[$x][$clave] = nl2br(htmlentities(utf8_decode($valor)));
		};
	};
	return $matriz;
};

function select($aOpciones,$seleccionado) {
	$select = "";
	for ($y=0;$y<count($aOpciones);$y++) {
		$id = $aOpciones[$y]['id'];
		$nombre = $aOpciones[$y]['nombre'];
		if ($id == $seleccionado && !is_null($seleccionado)) {
			$select .= "  <option value='$id' class='$id' selected style='color: #0000ff'>$nombre</option>\n";
		} else {
			$select .= "  <option value='$id' class='$id'>$nombre</option>\n";
		};
	};
	return $select;
};

function select_group($aOpciones,$seleccionado) {
	$select = $grupo = ""; 
	for ($y=0;$y<count($aOpciones);$y++) {
		if ($grupo <> $aOpciones[$y]['grupo']) {
			$grupo   = $aOpciones[$y]['grupo'];
			$select .= "<optgroup label='$grupo'>";
		}
		$id     = $aOpciones[$y]['id'];
		$nombre = $aOpciones[$y]['nombre'];
		if ($id == $seleccionado && !is_null($seleccionado)) {
			$select .= "  <option value='$id' class='$id' selected style='color: #0000ff'>$nombre</option>\n";
		} else {
			$select .= "  <option value='$id' class='$id'>$nombre</option>\n";
		}
	}
	return $select;
}

function arr2sqlupdate($aDatos,$aCampos) {
	$arr2sqlupdate = "";
	for($x=0;$x<count($aCampos);$x++) {
		$campo = $aCampos[$x];
		$tupla = "$campo=";		
		$valor = $aDatos[$aCampos[$x]];
		if ($valor <> "") {
			$tupla .= "'$valor'";
		} else {
			$tupla .= "null";
		}
		$arr2sqlupdate .= "$tupla,";
	}
	
//	foreach ($aDatos as $campo => $valor) {
//		$indice = array_search($campo,$aCampos);
//		if (is_numeric($indice)) {
//			if ($valor <> "") {
//				$arr2sqlupdate .= "$campo='$valor',";
//			} else {
//				$arr2sqlupdate .= "$campo=null,";
//			};
//		};
//	};

	return substr($arr2sqlupdate,0,strlen($arr2sqlupdate) - 1);
};
				
function arr2sqlinsert($aDatos,$aCampos) {
	$arr2sqlinsert = "(" . implode(",", $aCampos) . ") VALUES (";
	for($x=0;$x<count($aCampos);$x++) {
		$valor_campo = $aDatos[$aCampos[$x]];
		if ($valor_campo <> "") {
			$valor_campo = "'$valor_campo'";
		} else {
			$valor_campo = "null";
		}
		$arr2sqlinsert .= "$valor_campo,";
	}
	
//	foreach ($aDatos as $campo => $valor) {
//		$indice = array_search($campo,$aCampos);
//		if (is_numeric($indice)) {
//			if ($valor <> "") {
//				$arr2sqlinsert .= "'$valor',";
//			} else {
//				$arr2sqlinsert .= "null,";
//			}
//		}
//	}

	return substr($arr2sqlinsert,0,strlen($arr2sqlinsert) - 1) . ");";
};

function msje($mensaje) {
	$msje = "<table cellpadding='0' cellspacing='1' bgcolor='#4c6082' width='100%'>
	           <tr bgcolor='#D6EEFF' align='center'>
	             <td class='texto'><br>$mensaje<br><br></td>
	           </tr>
	         </table><br>";
	return $msje;
};

function msje_js($mensaje) {
	$msje = "<script language='Javascript1.2'>
	           alert('$mensaje');
	         </script>";
	return $msje;
};

function js($ejec) {
	$js = "<script language='Javascript1.2'>
	           $ejec;
	         </script>";
	return $js;
};


function confirma_js($mensaje,$url_si,$url_no) {
	$msje = "<script language='Javascript1.2'>
					if (confirm(\"$mensaje\")) {
						location.href='$url_si';
					} else {
						location.href='$url_no';
					}
	         </script>";
	return $msje;
};

function log_sgu($accion,$usuario,$mensaje,$sesion) {
	$man_arch = fopen("/var/log/sgu/acceso.log","a");
	$linea_log = strftime("%b %d %X") . " $sesion: $accion: $usuario: $mensaje\n";
	fwrite($man_arch,$linea_log);
	fclose($man_arch);
};

function id_usuario($usuario,$tipo) {
	global $authbd;
	$bdcon = pg_connect("dbname=regacad" . $authbd);
	$SQLtxt = "SELECT id FROM usuarios WHERE nombre_usuario='$usuario' AND tipo=$tipo;";
	$resultado = pg_query($bdcon, $SQLtxt);
	$fila = pg_fetch_all($resultado);
	$id_usuario = $fila[0]['id'];
	if (pg_numrows($resultado) == 1) {
		return $id_usuario;
	} else {
		return "";
	};	
};

function escuela_usuario($id_usuario) {
	global $authbd;
	$bdcon = pg_connect("dbname=regacad" . $authbd);
	$SQLtxt = "SELECT id_escuela FROM usuarios WHERE id=$id_usuario;";
	$resultado = pg_query($bdcon, $SQLtxt);
	$id_escuela = pg_fetch_result($resultado,0,0);
	if (pg_numrows($resultado) == 1) {
		return $id_escuela;
	} else {
		return "";
	};	
};

function unidad_usuario($id_usuario) {
	$SQL_unidad = "SELECT id_unidad AS id FROM usuarios WHERE id=$id_usuario;";
	$unidad = consulta_sql($SQL_unidad);
	if (count($unidad) == 1) {
		return $unidad[0]['id'];
	} else {
		return "";
	};	
};

function ids_carreras_escuela($id_escuela) {
	global $authbd;
	$filas = 0;
	$bdcon = pg_connect("dbname=regacad" . $authbd);
	$SQLtxt = "SELECT id FROM carreras WHERE id_escuela=$id_escuela;";
	$resultado = pg_query($bdcon, $SQLtxt);
	$filas = pg_numrows($resultado);
	if ($filas > 0) {
		$carreras = pg_fetch_all($resultado);
		$ids_carreras = array();
		for ($x=0;$x<$filas;$x++) {
			$ids_carreras = array_merge($ids_carreras, array($carreras[$x]['id']));
		};
		return implode(",",$ids_carreras);
	} else {
		return "";
	};	
};


function permiso_ejecutar($id_usuario,$modulo) {
	$SQL_permisos = "SELECT * FROM permisos_apps AS pa 
	                 LEFT JOIN aplicaciones AS a ON pa.id_aplicacion=a.id
	                 WHERE a.activa AND pa.id_usuario=$id_usuario AND a.ejecutable='$modulo'";
	$permisos = consulta_sql($SQL_permisos);                 

	if (count($permisos) > 0) {
		return true;
	} else {
		echo(msje_js("Usted NO tiene permiso para ejecutar este módulo o bien no está activo."));
		echo(js("history.back();"));
		exit;
	}
}

function perm_ejec_modulo($id_usuario,$modulo) {
	$SQL_permisos = "SELECT * FROM permisos_apps AS pa 
	                 LEFT JOIN aplicaciones AS a ON pa.id_aplicacion=a.id
	                 WHERE a.activa AND pa.id_usuario=$id_usuario AND a.ejecutable='$modulo'";
	$permisos = consulta_sql($SQL_permisos);                 

	if (count($permisos) > 0) {
		return true;
	} else {
		return false;
	}
	exit;
}

function modulos($modulo) {
	if (isset($modulo)) {
		$SQL_modulo = "SELECT nombre,descripcion,ejecutable FROM aplicaciones WHERE ejecutable='$modulo'";
		$modulo = consulta_sql($SQL_modulo);
		return $modulo[0];
	} else {
		$SQL_modulos = "SELECT nombre,descripcion,ejecutable FROM aplicaciones";
		$modulos = consulta_sql($SQL_modulos);		
		return $modulos;
	};
};

function enlace_volver() {
	$enlace_volver = strstr($_SERVER['HTTP_REFERER'],"=");
	$enlace_volver = substr($enlace_volver,1,strlen($enlace_volver));
	return $enlace_volver;
};

function sql_regexp($cadena) {
	global $voc_regexp;
	$cadena = mb_strtolower($cadena,"UTF-8");
	$sql_regexp = "";
	$largo = strlen($cadena);
	for ($x=0;$x<$largo;$x++) {
		$caracter = mb_substr($cadena,$x,1,"UTF-8");
		if (array_key_exists($caracter,$voc_regexp)) {
			$caracter = $voc_regexp[$caracter];
		};
		$sql_regexp .= $caracter;
	};
	return $sql_regexp;
};

function consulta_sql($SQLtxt) {
	global $bdcon;
	$resultado = array();
	$res = pg_query($bdcon, $SQLtxt);
	if (pg_numrows($res) > 0) {
		return pg_fetch_all($res);
	} else {
		return $resultado;
	}
}

function consulta_dml($SQLtxt) {
	global $bdcon;
	$res = pg_query($bdcon, $SQLtxt);
	if (pg_affected_rows($res) > 0) {
		return pg_affected_rows($res);
	} else {
		return 0;
	}
}

function meses($numeromes) {
	$mes = "";
	if ($numeromes > 0 && $numeromes < 13) {
		$mes = strftime("%B", mktime(0,0,0,$numeromes,1,2000));
	}
	return $mes;
};

function requeridos($aRequeridos,$aCampos) {
	$requeridos = array();
	for($x=0;$x<count($aRequeridos);$x++) {
		$requeridos = array_merge($requeridos,array("'".$aCampos[$aRequeridos[$x]]."'"));
	}
	return implode("," , $requeridos);
}

function tabla_encabezado($aDatosValores) {
	$HTML_tabla_encabezado = "";
	foreach ($aDatosValores as $nombre_campo => $valor_campo) {
		$HTML_tabla_encabezado .= "<tr>\n"
		                        . "  <td class='celdaNombreAttr'>$nombre_campo:</td>\n"
	                           . "  <td class='celdaValorAttr'>$valor_campo</td>\n"
	                           . "</tr>\n";
	}
	
	return $HTML_tabla_encabezado;
}

function html_paginador($tot_reg,$reg_inicio,$cant_reg,$enlace_nav) {
	$reg_ini_sgte = $reg_inicio + $cant_reg;
	$reg_ini_ante = $reg_inicio - $cant_reg;

	if ($reg_ini_ante < 0) {
		$reg_ini_ante = 0;
	}

	if ($reg_ini_sgte >= $tot_reg) {
		$reg_ini_sgte = 0;
	}
	
	$tot_pag = ceil($tot_reg/$cant_reg);
	
	$html_paginador = "<a class='boton' href='$enlace_nav=$reg_ini_ante' style='font-size: 8pt'>◄ Anterior</a> "
	                . "<select name='r_inicio' onChange='submitform()' class='filtro'>";

  	for($pag=1;$pag<=$tot_pag;$pag++) {
		
		$pag_selected = "";
		if ($cant_reg*($pag-1) == $reg_inicio) { $pag_selected = "selected"; }
		$reg_ini_pag = ($pag - 1) * $cant_reg;
		$html_paginador .= "<option value='$reg_ini_pag' $pag_selected>$pag/$tot_pag</option>\n";
		
		/*
  		if ($cant_reg*($pag-1) == $reg_inicio) {
  				$html_paginador .= " <b>$pag</b> |";
  		} else {
  			$reg_ini_pag = ($pag - 1) * $cant_reg;
  			$html_paginador .= "<a class='enlaces' href='$enlace_nav=$reg_ini_pag'> $pag</a> | ";
  		}
  		*/  			
  	}
  	$html_paginador .= "</select> "
  	                .  "<a class='boton' href='$enlace_nav=$reg_ini_sgte' style='font-size: 8pt'>Siguiente ►</a>";
  	return $html_paginador;
}

function calc_recuperativa ($s1,$nc,$s2) {
	$recuperativa = false;
	if ($s1<>"" && $nc<>"" && $s2<>"") {

		$promedio=calc_promedio($s1,$nc,$s2);

		if ($s1<>-1 && $nc<>-1 && $s2<>-1 && $promedio>=3.7 && $promedio<=3.9) {
			$recuperativa = true;
		}

		if ($s1<>-1 && $nc<>-1 && $s2<=2.9 && $promedio>=3.7) {
			$recuperativa = true;
		}
		
		if ($s1<>-1 && $nc==-1 && $s2<>-1) {
			$recuperativa = true;
		}

		if ($s1<>-1 && $nc==-1 && $s2<>-1) {
			$recuperativa = true;
		}

		if ($s1<>-1 && $nc<>-1 && $s2==-1) {
			$recuperativa = true;
		}
			 
	}
	return $recuperativa;
}

function calc_promedio($s1,$nc,$s2) {
	$promedio = null;
	if ($s1<>"" && $nc<>"" && $s2<>"") {
		$promedio = (abs($s1)*0.3) + (abs($nc)*0.3) + (abs($s2)*0.3);
	}
	return $promedio;
}

function datos_personales_alumno() {
	global $alumno, $enlbase;
	extract($alumno[0]);
	$HTML = "  <tr>".$LF
	      . "    <td class='celdaNombreAttr'>Código Interno:</td>".$LF
	      . "    <td class='celdaValorAttr'>$id</td>".$LF
	      . "    <td class='celdaNombreAttr'>RUT:</td>".$LF
	      . "    <td class='celdaValorAttr'>$rut</td>".$LF
	      . "  </tr>".$LF
	      . "  <tr>".$LF
	      . "    <td class='celdaNombreAttr'>Nombre:</td>".$LF
	      . "    <td class='celdaValorAttr' colspan='3'>$nombre</td>".$LF
	      . "  </tr>".$LF
	      . "  <tr>".$LF
	      . "    <td class='celdaNombreAttr'>Género:</td>".$LF
	      . "    <td class='celdaValorAttr'>$genero</td>".$LF
	      . "    <td class='celdaNombreAttr' nowrap>Fecha de nacimiento:</td>".$LF
	      . "    <td class='celdaValorAttr'>$fec_nac</td>".$LF
	      . "  </tr>".$LF
	      . "  <tr>".$LF
	      . "    <td class='celdaNombreAttr'>Nacionalidad:</td>".$LF
	      . "    <td class='celdaValorAttr'>$nacionalidad</td>".$LF
	      . "    <td class='celdaNombreAttr'>Pasaporte:</td>".$LF
	      . "    <td class='celdaValorAttr'>$pasaporte</td>".$LF
	      . "  </tr>".$LF
	      . "  <tr>".$LF
	      . "    <td class='celdaNombreAttr'>Dirección:</td>".$LF
	      . "    <td class='celdaValorAttr' colspan='3'>$direccion</td>".$LF
	      . "  </tr>".$LF
	      . "  <tr>".$LF
	      . "    <td class='celdaNombreAttr'>Comuna:</td>".$LF
	      . "    <td class='celdaValorAttr'>$comuna</td>".$LF
	      . "    <td class='celdaNombreAttr'>Región:</td>".$LF
	      . "    <td class='celdaValorAttr' nowrap>$region</td>".$LF
	      . "  </tr>".$LF
	      . "  <tr>".$LF
	      . "    <td class='celdaNombreAttr'>Telefóno fijo:</td>".$LF
	      . "    <td class='celdaValorAttr'>$telefono</td>".$LF
	      . "    <td class='celdaNombreAttr'>Telefóno móvil:</td>".$LF
	      . "    <td class='celdaValorAttr'>$tel_movil</td>".$LF
	      . "  </tr>".$LF
	      . "  <tr>".$LF
	      . "    <td class='celdaNombreAttr'>e-mail UMC:</td>".$LF
	      . "    <td class='celdaValorAttr' colspan='3'>$email</td>".$LF
	      . "  </tr>".$LF
	      . "<!-- <tr>".$LF
	      . "    <td class='celdaNombreAttr'>e-mail externo:</td>".$LF
	      . "    <td class='celdaValorAttr'>$email_externo</td>".$LF
	      . "  </tr> -->".$LF
	      . "  <tr>".$LF
	      . "    <td class='celdaNombreAttr'>Admisión:</td>".$LF
	      . "    <td class='celdaValorAttr'>$admision</td>".$LF
	      . "    <td class='celdaNombreAttr'>Cohorte:</td>".$LF
	      . "    <td class='celdaValorAttr'>$cohorte-$semestre_cohorte</td>".$LF
  	      . "  </tr>".$LF
	      . "  <tr>".$LF
	      . "    <td class='celdaNombreAttr'>Carrera Actual:</td>".$LF
	      . "    <td class='celdaValorAttr'>$carrera</td>".$LF
	      . "    <td class='celdaNombreAttr'>Jornada:</td>".$LF
	      . "    <td class='celdaValorAttr'>$jornada</td>".$LF
	      . "  </tr>".$LF
	      . "  <tr>".$LF
	      . "    <td class='celdaNombreAttr'>Año Malla Actual:</td>".$LF
	      . "    <td class='celdaValorAttr'><a class='enlaces' href='$enlbase=ver_malla&id_malla=$id_malla_actual'>$malla_actual</a></td>".$LF
	      . "    <td class='celdaNombreAttr'>Estado<br>Académico:<br>Financiero:</td>".$LF
	      . "    <td class='celdaValorAttr'><br>$estado<br>$moroso_financiero</td>".$LF
	      . "  </tr>";
	return $HTML;
}


function avance_cronologico() {
	global $id_alumno,$enlbase,$cohorte,$semestre_cohorte,$ANO,$SEMESTRE;
	$SQL_alumno_ca = "SELECT vac.id_curso, vac.id_pa, vac.id_pa_homo, vac.id_estado,a.moroso_financiero, 
	        CASE WHEN vac.id_curso IS NOT NULL THEN coalesce(vac.ano,'0')||'-'||coalesce(vac.semestre,'0')
	             WHEN vac.id_pa IS NOT NULL AND vac.convalidado THEN '$cohorte-0'
	             WHEN vac.id_pa_homo IS NOT NULL AND vac.homologada THEN extract(YEAR FROM vac.fec_mod)||'-'||CASE WHEN extract(MONTH from vac.fec_mod) <= 7 THEN 1 ELSE 2 END
	             WHEN vac.id_pa IS NOT NULL AND vac.examen_con_rel THEN extract(YEAR FROM vac.fec_mod)||'-'||CASE WHEN extract(MONTH from vac.fec_mod) <= 7 THEN 1 ELSE 2 END
	        END AS periodo, vac.asignatura, vac.s1, vac.nc, vac.s2, vac.recuperativa AS rec,vac.nf,vac.situacion,
	        vac.fecha_mod,a.estado
	 FROM vista_alumnos_cursos AS vac
	 JOIN alumnos AS a ON a.id=vac.id_alumno
	 WHERE vac.id_alumno=$id_alumno 
	 ORDER BY periodo,vac.asignatura;";
	$alumno_ca = consulta_sql($SQL_alumno_ca);

	$_azul = "color: #000099";
	$_rojo = "color: #ff0000";
	$HTML_alumno_ca = "  <tr class='filaTituloTabla'>"
	                . "    <td class='tituloTabla' colspan='9'>Rendimiento acad&eacute;mico del alumno</td>"
	                . "  </tr>"
	                . "  <tr class='filaTituloTabla'>"
	                . "    <td class='tituloTabla'>Periodo</td>"
	                . "    <td class='tituloTabla'>Asignatura</td>"
	                . "    <td class='tituloTabla'>S1</td>"
	                . "    <td class='tituloTabla'>NC</td>"
	                . "    <td class='tituloTabla'>S2</td>"
	                . "    <td class='tituloTabla'>Recup.</td>"
	                . "    <td class='tituloTabla'>NF</td>"
	                . "    <td class='tituloTabla'>Situaci&oacute;n</td>"
	                . "  </tr>";
	                
	$periodo_aux = $alumno_ca[0]['periodo'];
	
	$periodo_actual = $ANO."-".$SEMESTRE;
	
	for($x=0;$x<count($alumno_ca);$x++) {
		extract($alumno_ca[$x]);
		
		if ($periodo_aux <> $periodo) {
			$HTML_alumno_ca .= "<tr class='filaTabla'><td colspan='8' class='textoTabla'>&nbsp;</td></tr>";
		}
		$periodo_aux = $periodo;

		$estilo_s1 = $estilo_nc = $estilo_s2 = $estilo_rec = $estilo_nf = $estilo_sit = "color: #000000";

		if ($periodo_actual == $periodo && ($estado == 2 || $moroso_financiero == "t") && $SESSION['tipo'] > 3) {
			$s1 = $nc = $s2 = $rec = $nf = $situacion = "N/D";
		}
		
		if ($s1>=1 && $s1<4) { $estilo_s1 = $_rojo; } elseif ($s1>=4) { $estilo_s1 = $_azul; }   
		
		if ($nc>=1 && $nc<4) { $estilo_nc = $_rojo; } elseif ($nc>=4) { $estilo_nc = $_azul; }   
		
		if ($s2>=1 && $s2<4) { $estilo_s2 = $_rojo; } elseif ($s2>=4) { $estilo_s2 = $_azul; }   
		
		if ($rec>=1 && $rec<4) { $estilo_rec = $_rojo; } elseif ($rec>=4) { $estilo_rec = $_azul; }   
		
		if ($nf>=1 && $nf<4 ) { $estilo_nf = $_rojo; } elseif ($nf>=4 || $nf=="APC" || $nf=="APH" || $nf=="APECR") { $estilo_nf = $_azul; }   
		
		if ($situacion == "Reprobado") { $estilo_sit = $_rojo; } elseif ($situacion <> "Suspendido" && $situacion <> "Condicional" && $situacion <> "N/D") { $estilo_sit = $_azul; }

		if ($id_curso <> "") {
			$enl = "$enlbase=ver_curso&id_curso=$id_curso";
			$js_onClick = "\"window.location='$enl';\"";
			$asignatura = "<a class='enlitem' href='$enl'>$asignatura</a>";
		}
		
		$HTML_alumno_ca .= "<tr class='filaTabla' onClick=$js_onClick>\n"
		                .  "  <td class='textoTabla'> $periodo</td>\n"
		                .  "  <td class='textoTabla' nowrap><div title='header=[Propiedades] fade=[on] body=[Fecha de Ingreso: $fecha_mod]'>$asignatura</div></td>\n"
		                .  "  <td class='textoTabla' style='$estilo_s1'> $s1</td>\n"
		                .  "  <td class='textoTabla' style='$estilo_nc'> $nc</td>\n"
		                .  "  <td class='textoTabla' style='$estilo_s2'> $s2</td>\n"
		                .  "  <td class='textoTabla' style='$estilo_rec'> $rec</td>\n"
		                .  "  <td class='textoTabla' style='$estilo_nf'> $nf</td>\n"
		                .  "  <td class='textoTabla' style='$estilo_sit' nowrap> $situacion</td>\n"
		                .  "</tr>\n";
	}	
	return $HTML_alumno_ca;
}

function avance_malla() {
	global $id_alumno;
	
	$SQL_alumno = "SELECT id_malla_actual,cohorte,semestre_cohorte FROM vista_alumnos WHERE id='$id_alumno'";
	$alumno = consulta_sql($SQL_alumno);
	extract($alumno[0]);
	
	$SQL_detalle_malla = "SELECT id_prog_asig,cod_asignatura,asignatura,nivel,caracter
	     FROM vista_detalle_malla
	     WHERE id_malla=$id_malla_actual";
	
	$SQL_alumno_ca = "SELECT CASE WHEN ca.id_curso IS NOT NULL THEN c.id_prog_asig
	             WHEN ca.id_pa IS NOT NULL AND ca.convalidado THEN ca.id_pa
	             WHEN ca.id_pa_homo IS NOT NULL AND ca.homologada THEN ca.id_pa_homo
	             WHEN ca.id_pa IS NOT NULL AND ca.examen_con_rel THEN ca.id_pa
	        END AS id_prog_asig,
	        CASE WHEN ca.id_curso IS NOT NULL THEN c.ano||'-'||c.semestre
	             WHEN ca.id_pa IS NOT NULL AND ca.convalidado THEN '$cohorte-$semestre_cohorte'
	             WHEN ca.id_pa_homo IS NOT NULL AND ca.homologada THEN extract(YEAR FROM ca.fecha_mod)||'-'||CASE WHEN extract(MONTH from ca.fecha_mod) <= 7 THEN 1 ELSE 2 END
	             WHEN ca.id_pa IS NOT NULL AND ca.examen_con_rel THEN extract(YEAR FROM ca.fecha_mod)||'-'||CASE WHEN extract(MONTH from ca.fecha_mod) <= 7 THEN 1 ELSE 2 END
	        END AS periodo,
	  	     CASE WHEN ca.id_curso IS NOT NULL THEN coalesce(ca.nota_final::numeric(2,1)::text,'Cursando')||' '||coalesce(cae.nombre,'')
	             WHEN ca.id_pa IS NOT NULL AND ca.convalidado THEN 'APC'
	             WHEN ca.id_pa_homo IS NOT NULL AND ca.homologada THEN 'APH'
	             WHEN ca.id_pa IS NOT NULL AND ca.examen_con_rel THEN 'APECR'
	        END AS nf,ca.id_estado,ca.nota_final
	 FROM cargas_academicas AS ca
	 LEFT JOIN cursos AS c ON c.id=ca.id_curso
	 LEFT JOIN ca_estados AS cae ON cae.id=ca.id_estado
	 WHERE ca.id_alumno=$id_alumno
	 ORDER BY periodo DESC";

	$SQL_avance_malla = "SELECT dm.cod_asignatura||' '||dm.asignatura AS asignatura,dm.nivel,dm.caracter,
	                            char_comma_sum(coalesce(aca.nf,'No cursado')) AS estado,
	                            char_comma_sum(aca.periodo) AS periodo,
	                            char_comma_sum(text(aca.id_estado)) AS ids_estados
	                     FROM ($SQL_detalle_malla) AS dm
	                     LEFT JOIN ($SQL_alumno_ca) AS aca ON aca.id_prog_asig=dm.id_prog_asig
	                     GROUP BY dm.cod_asignatura,dm.asignatura,dm.nivel,dm.caracter
	                     ORDER BY dm.nivel,asignatura;";
	$avance_malla = consulta_sql($SQL_avance_malla);

	$SQL_alumno_prom_aprob = "SELECT avg(nota_final)::numeric(2,1) AS prom_aprob,count(id) AS cant_asig_aprob
	                          FROM cargas_academicas 
	                          WHERE id_alumno=$id_alumno AND id_estado=1 
	                          AND id_curso IN (SELECT id FROM cursos 
	                                           WHERE id_prog_asig IN (SELECT id_prog_asig 
	                                           FROM ($SQL_detalle_malla) AS dm));";
	$alumno_prom_aprob = consulta_sql($SQL_alumno_prom_aprob);
	$prom_aprob      = $alumno_prom_aprob[0]['prom_aprob'];
	$cant_asig_aprob = $alumno_prom_aprob[0]['cant_asig_aprob'];

	$SQL_alumno_prom_gen = "SELECT avg(nota_final)::numeric(2,1) AS prom_gen,count(id) AS cant_asig_gen
	                        FROM cargas_academicas 
	                        WHERE id_alumno=$id_alumno AND id_estado in (1,2)
	                          AND id_curso IN (SELECT id FROM cursos 
	                                           WHERE id_prog_asig IN (SELECT id_prog_asig 
	                                           FROM ($SQL_detalle_malla) AS dm));";
	$alumno_prom_gen = consulta_sql($SQL_alumno_prom_gen);
	$prom_gen        = $alumno_prom_gen[0]['prom_gen'];
	$cant_asig_gen   = $alumno_prom_gen[0]['cant_asig_gen'];

	
	$HTML = "  <tr class='filaTituloTabla'>"
	      . "    <td class='tituloTabla' colspan='5'>Rendimiento académico con respecto al Plan de estudio (malla)</td>"
	      . "  </tr>"
	      . "  <tr class='filaTituloTabla'>"
	      . "    <td class='tituloTabla'>Nivel</td>"
	      . "    <td class='tituloTabla'>Asignatura</td>"
	      . "    <td class='tituloTabla'>Carácter</td>"
	      . "    <td class='tituloTabla'>Situación</td>"
	      . "    <td class='tituloTabla'>Periodo</td>"
	      . "  </tr>";

	$nivel_aux = $avance_malla[0]['nivel'];
	for($x=0;$x<count($avance_malla);$x++) {
		extract($avance_malla[$x]);
	
		if ($nivel_aux <> $nivel) {
			$HTML .= "<tr class='filaTabla'><td colspan='5' class='textoTabla'>&nbsp;</td></tr>";
		}
		
		$nivel_aux = $nivel;
		
		$estado = explode(',',$estado);
		$ids_estados = explode(',',$ids_estados);
		$periodo = explode(",",$periodo);
				
		$color_estilo = "color: #000000; background: #FFA500;";
		for ($z=0;$z<count($estado);$z++) {
			switch ($ids_estados[$z]) {
				case 1:
				case 3:
				case 4:
				case 5:
					$color_estilo = "color: #000099";
					break;
				case 2:
					$color_estilo = "color: #ff0000";
					break;
			}
			/*
			if ($ids_estados[$z] == "2") {
				$color_estilo = "color: #ff0000";
			} elseif ($ids_estados[$z] == "1") {
				$color_estilo = "color: #000099";
			}
			*/
			$estado[$z] = "<div style='$color_estilo'>".trim($estado[$z])."</div>";
			$periodo[$z] = "<div style='$color_estilo'>".trim($periodo[$z])."</div>";
		}
		$estado = implode("",$estado);		
		$periodo = implode("",$periodo);		


		$HTML .= "<tr class='filaTabla'>\n"
		      .  "  <td class='textoTabla' align='center'> $nivel</td>\n"
		      .  "  <td class='textoTabla' nowrap> $asignatura</td>\n"
		      .  "  <td class='textoTabla' nowrap> $caracter</td>\n"
		      .  "  <td class='textoTabla' nowrap> $estado</td>\n"
		      .  "  <td class='textoTabla' nowrap> $periodo</td>\n"
		      .  "</tr>\n";
	}
	
	$HTML .= "<tr class='filaTabla'>\n"
	      .  "  <td class='textoTabla' colspan='5' align='center'>"
	      .  "    En promedio general un <b>$prom_gen</b> con $cant_asig_gen asignaturas<br>"
	      .  "    En promedio un <b>$prom_aprob</b> con $cant_asig_aprob asignaturas aprobadas"
	      .  "  </td>\n"
	      .  "</tr>\n";

	return $HTML;
}

function vista_homologaciones() {
	global $id_alumno, $id_malla_actual,$cohorte,$semestre_cohorte,$enlbase;
	
	$SQL_nf = "SELECT max(nota_final)::numeric(3,1) FROM cargas_academicas WHERE id_estado=1 AND id_alumno=$id_alumno AND id_curso IN (SELECT id FROM cursos WHERE id_prog_asig=vpa.id)";
	
	$SQL_malla_alumno = "SELECT malla_actual FROM alumnos WHERE id=$id_alumno";

	$SQL_malla = "SELECT coalesce(m.cant_asig_oblig,0)+coalesce(m.cant_asig_elect,0)+coalesce(m.cant_asig_efp,0) AS cant_asig_malla FROM mallas AS m WHERE id=($SQL_malla_alumno)";
	$malla = consulta_sql($SQL_malla);
	$cant_asig_malla = $malla[0]['cant_asig_malla'];

	$SQL_homologaciones = "SELECT vac.id,vac.asignatura,vpa.cod_asignatura||' '||vpa.asignatura AS homologada_por,
	                              extract(YEAR from fec_mod)||'-'||CASE WHEN extract(MONTH from fec_mod) < 8 THEN '1' ELSE '2' END AS periodo,
	                              ($SQL_nf) AS nf
	                       FROM vista_alumnos_cursos AS vac
	                       LEFT JOIN vista_prog_asig AS vpa ON vpa.id=vac.id_pa
	                       WHERE vac.id_alumno=$id_alumno AND homologada
	                       ORDER BY vac.asignatura";

	$homologaciones = consulta_sql($SQL_homologaciones);
	$cantidad = count($homologaciones);

	$porc_homo = round($cantidad * 100 / $cant_asig_malla,0);

	$HTML = "  <tr class='filaTituloTabla'>"
	      . "    <td class='tituloTabla' colspan='5'>Homologaciones ($cantidad asignatura(s), $porc_homo%)</td>"
	      . "  </tr>"
	      . "  <tr class='filaTituloTabla'>"
	      . "    <td class='tituloTabla'>ID</td>"
	      . "    <td class='tituloTabla'>Asignatura homologada</td>"
	      . "    <td class='tituloTabla'>Asignatura aprobada</td>"
	      . "    <td class='tituloTabla'>Periodo</td>"
	      . "    <td class='tituloTabla'>Nota</td>"
	      . "  </tr>";

	if (count($homologaciones) == 0) {
		$HTML .= "<tr><td colspan='4' class='tituloTabla'>** Es alumno no registra Homologaciones **</td></tr>";
	} else {
		$promedio = 0;
		for($x=0;$x<count($homologaciones);$x++) {
			extract($homologaciones[$x]);
		
			$onclick = "onClick=\"window.location='$enlbase=editar_homologacion&id_ca=$id';\"";
			
			$HTML .= "<tr class='filaTabla' $onclick>\n"
			      .  "  <td class='textoTabla' nowrap> $id</td>\n"
			      .  "  <td class='textoTabla' nowrap> $asignatura</td>\n"
			      .  "  <td class='textoTabla' nowrap> $homologada_por</td>\n"
			      .  "  <td class='textoTabla' nowrap> $periodo</td>\n"
			      .  "  <td class='textoTabla' nowrap style='text-align: center'> $nf</td>\n"
			      .  "</tr>\n";
			$promedio += $nf;
		}
		$promedio = round($promedio/$cantidad,1);
		
		$HTML .= "<tr class='filaTabla'>"
		      .  "  <td colspan='4' class='celdaNombreAttr'>Promedio de Notas:</td>"
		      .  "  <td class='textoTabla' style='text-align: center'><b>$promedio</b></td>"
		      .  "</tr>";
	}
	return $HTML;	
}

function vista_convalidaciones() {
	global $id_alumno, $id_malla_actual,$cohorte,$semestre_cohorte,$enlbase;

	$SQL_malla_alumno = "SELECT malla_actual FROM alumnos WHERE id=$id_alumno";

	$SQL_malla = "SELECT coalesce(m.cant_asig_oblig,0)+coalesce(m.cant_asig_elect,0)+coalesce(m.cant_asig_efp,0) AS cant_asig_malla FROM mallas AS m WHERE id=($SQL_malla_alumno)";
	$malla = consulta_sql($SQL_malla);
	$cant_asig_malla = $malla[0]['cant_asig_malla'];
	
	$SQL_convalidaciones = "SELECT ca.id,vpa.cod_asignatura||' '||vpa.asignatura AS asignatura,'$cohorte-0' AS periodo,
	                               conv.asignatura AS asignatura_ext,conv.alias||'/'||conv.ano AS inst_ed_sup	                              
	                        FROM cargas_academicas AS ca
	                        LEFT JOIN vista_convalidaciones AS conv ON conv.id=ca.id_convalida
	                        LEFT JOIN vista_prog_asig AS vpa ON vpa.id=ca.id_pa
	                        WHERE ca.id_alumno=$id_alumno AND convalidado AND id_pa IN (SELECT id_prog_asig FROM detalle_mallas WHERE id_malla=($SQL_malla_alumno))
	                        ORDER BY asignatura";

	$convalidaciones = consulta_sql($SQL_convalidaciones);

	$cant_convalidaciones = count($convalidaciones);

	$porc_conv = round($cant_convalidaciones * 100 / $cant_asig_malla,0);
	
	$HTML = "  <tr class='filaTituloTabla'>"
	      . "    <td class='tituloTabla' colspan='5'>Convalidaciones ($cant_convalidaciones asignatura(s), $porc_conv%)</td>"
	      . "  </tr>"
	      . "  <tr class='filaTituloTabla'>"
	      . "    <td class='tituloTabla'>ID</td>"
	      . "    <td class='tituloTabla'>Asignatura convalidada</td>"
	      . "    <td class='tituloTabla'>Asignatura aprobada</td>"
	      . "    <td class='tituloTabla'>Cursada en</td>"
	      . "    <td class='tituloTabla'>Periodo</td>"
	      . "  </tr>";

	if ($cant_convalidaciones == 0) {
		$HTML .= "<tr><td colspan='5' class='tituloTabla'>** Es alumno no registra Convalidaciones **</td></tr>";
	} else {
		for($x=0;$x<count($convalidaciones);$x++) {
			extract($convalidaciones[$x]);
		
			//$onclick = "onClick=\"window.location='$enlbase=editar_convalidacion&id_ca=$id';\"";
			
			$HTML .= "<tr class='filaTabla' $onclick>\n"
			      .  "  <td class='textoTabla' nowrap> $id</td>\n"
			      .  "  <td class='textoTabla' nowrap> $asignatura</td>\n"
			      .  "  <td class='textoTabla' nowrap> $asignatura_ext</td>\n"
			      .  "  <td class='textoTabla' nowrap> $inst_ed_sup</td>\n"
			      .  "  <td class='textoTabla' nowrap> $periodo</td>\n"
			      .  "</tr>\n";
		}
		$HTML .= "<tr class='filaTabla' $onclick>\n"
		      .  "  <td class='textoTabla' colspan='5' align='center'>"
		      .  "    En total, <b>$cant_convalidaciones</b> asignaturas convalidadas"
		      .  "  </td>\n"
		      .  "</tr>\n";
	}
	return $HTML;	
}

function vista_examenes_con_rel() {
	global $id_alumno, $id_malla_actual,$cohorte,$semestre_cohorte,$enlbase;
	
	$SQL_examenes_con_rel = "SELECT vac.id,vac.asignatura,
	                                extract(YEAR from fec_mod)||'-'||CASE WHEN extract(MONTH from fec_mod) < 8 THEN '1' ELSE '2' END AS periodo
	                         FROM vista_alumnos_cursos AS vac
	                         WHERE vac.id_alumno=$id_alumno AND examen_con_rel
	                         ORDER BY vac.asignatura";

	$examenes_con_rel = consulta_sql($SQL_examenes_con_rel);

	$HTML = "  <tr class='filaTituloTabla'>"
	      . "    <td class='tituloTabla' colspan='5'>Exámenes de Conocimientos Relevantes</td>"
	      . "  </tr>"
	      . "  <tr class='filaTituloTabla'>"
	      . "    <td class='tituloTabla'>ID</td>"
	      . "    <td class='tituloTabla'>Asignatura</td>"
	      . "    <td class='tituloTabla'>Periodo</td>"
	      . "  </tr>";

	if (count($examenes_con_rel) == 0) {
		$HTML .= "<tr><td colspan='3' class='tituloTabla'>** Es alumno no registra Exámenes de Conocimientos Relevantes **</td></tr>";
	} else {
		for($x=0;$x<count($examenes_con_rel);$x++) {
			extract($examenes_con_rel[$x]);
	
			$onclick = "onClick=\"window.location='$enlbase=editar_examen_con_rel&id_ca=$id';\"";
			
			$HTML .= "<tr class='filaTabla' $onclick>\n"
			      .  "  <td class='textoTabla' nowrap> $id</td>\n"
			      .  "  <td class='textoTabla' nowrap> $asignatura</td>\n"
			      .  "  <td class='textoTabla' nowrap> $periodo</td>\n"
			      .  "</tr>\n";
		}
	}
	return $HTML;	
}

function vista_anotaciones() {
	global $id_alumno,$id_malla_actual,$cohorte,$semestre_cohorte,$enlbase;
	
	$anotaciones = consulta_sql("SELECT anotaciones FROM alumnos WHERE id=$id_alumno;");

	$HTML = "  <tr class='filaTituloTabla'>"
	      . "    <td class='tituloTabla'>Anotaciones</td>"
	      . "  </tr>"
	      . "  <tr>"
	      . "    <td class='tituloTabla'>";
	
	if ($anotaciones[0]['anotaciones'] == "") {
		$HTML .= "** Es alumno no registra Anotaciones **";
	} else {
		$anotaciones = $anotaciones[0]['anotaciones'];
		$HTML .= nl2br($anotaciones);
	}

	$HTML .= "      <div align='center'>"
	      .  "        <a href='$enlbase=agregar_anotacion&id_alumno=$id_alumno' class='boton'>Agregar anotación</a>"
	      .  "      </div>"
	      .  "    </td>"
	      .  "  </tr>";
		
	return $HTML;	
}

function enc_est_conteo_ranking($matriz) {
	if (!is_array($matriz)) { return false; }

	$conteo = array();
	$y = $x = 0;	
	while ($x<count($matriz)) {
		$id_profesor = $matriz[$x]['id_profesor'];
		$conteo_profe = array();
		$casos = 0;
		$conteo_profe['id_profesor'] = $id_profesor;
		if ($matriz[$x]['periodo'] <> "") { $conteo_profe['periodo'] = $matriz[$x]['periodo']; }
		$conteo_profe['total'] = $conteo_profe['casos'] = $sub_total = 0;
		while ($id_profesor == $matriz[$x]['id_profesor']) {
			$nitem = 1;
			$suma_item = array();
			for($np=1;$np<=21;$np++) {
				$campo = "p$np";
				$suma_item[$nitem] += (100/3) * (4 - $matriz[$x][$campo]); 
				if ($np==5 || $np==17) { $nitem++; }
			}
			$x++;
			$casos++;
			$sub_total += (($suma_item[1]/5*0.25) + ($suma_item[2]/12*0.50) + ($suma_item[3]/4*0.25));
		}
		$conteo_profe['total'] = $sub_total/$casos;
		$conteo_profe['casos'] = $casos;
		$conteo = array_merge($conteo,array($y => $conteo_profe));		
		$y++;
	}
	return $conteo;
}		

function enc_autoev_conteo_ranking($matriz) {
	if (!is_array($matriz)) { return false; }

	$conteo = array();
	$y = $x = 0;	
	while ($x<count($matriz)) {
		$id_profesor = $matriz[$x]['id_profesor'];
		$conteo_profe = array();
		$casos = 0;
		$conteo_profe['id_profesor'] = $id_profesor;
		if ($matriz[$x]['periodo'] <> "") { $conteo_profe['periodo'] = $matriz[$x]['periodo']; }
		$conteo_profe['total'] = $conteo_profe['casos'] = $sub_total = 0;
		while ($id_profesor == $matriz[$x]['id_profesor']) {
			$nitem = 1;
			$suma_item = array();
			for($np=1;$np<=30;$np++) {
				$campo = "p$np";
				$suma_item[$nitem] += (100/3) * (4 - $matriz[$x][$campo]); 
				if ($np==13 || $np==26) { $nitem++; }
			}
			$x++;
			$casos++;
			$sub_total += (($suma_item[1]/13*0.25) + ($suma_item[2]/13*0.50) + ($suma_item[3]/4*0.25));
		}
		$conteo_profe['total'] = $sub_total/$casos;
		$conteo_profe['casos'] = $casos;
		$conteo = array_merge($conteo,array($y => $conteo_profe));		
		$y++;
	}
	return $conteo;
}		

function enc_autoev_conteo_ranking2($matriz) {

	if (!is_array($matriz)) { return false; }

	$conteo = array();
	$y = $x = 0;	

	while ($x<count($matriz)) {
		$id_profesor = $matriz[$x]['id_profesor'];
		$conteo_profe = array();
		$casos = 0;
		$conteo_profe['id_profesor'] = $id_profesor;
		if ($matriz[$x]['periodo'] <> "") { $conteo_profe['periodo'] = $matriz[$x]['periodo']; }
		$conteo_profe['total'] = $conteo_profe['casos'] = $sub_total = 0;
		while ($id_profesor == $matriz[$x]['id_profesor']) {
			$nitem = 1;
			$suma_item = array();
			for($np=1;$np<=30;$np++) {
				if (!(($np>23 && $np<26) || $np==30)) {
					$campo = "p$np";
					$suma_item[$nitem] += (100/3) * (4 - $matriz[$x][$campo]); 
					if ($np==13 || $np==26) { $nitem++; }
				}
			}
			$x++;
			$casos++;
			$sub_total += (($suma_item[1]/13*0.25) + ($suma_item[2]/13*0.50) + ($suma_item[3]/4*0.25));
		}
		$conteo_profe['total'] = $sub_total/$casos;
		$conteo_profe['casos'] = $casos;
		$conteo = array_merge($conteo,array($y => $conteo_profe));		
		$y++;
	}
	return $conteo;
}

function enc_evdoc_conteo_ranking($matriz) {
	if (!is_array($matriz)) { return false; }

	$conteo = array();
	$y = $x = 0;	
	while ($x<count($matriz)) {
		$id_profesor = $matriz[$x]['id_profesor'];
		$conteo_profe = array();
		$casos = 0;
		$conteo_profe['id_profesor'] = $id_profesor;
		if ($matriz[$x]['periodo'] <> "") { $conteo_profe['periodo'] = $matriz[$x]['periodo']; }
		$conteo_profe['total'] = $conteo_profe['casos'] = $sub_total = 0;
		while ($id_profesor == $matriz[$x]['id_profesor']) {
			$nitem = 1;
			$suma_item = array();
			for($np=1;$np<=19;$np++) {
				$campo = "p$np";
				if ($matriz[$x][$campo] <5) {
					$suma_item[$nitem] += (100/3) * (4 - $matriz[$x][$campo]);
				} 
				if ($np==13 || $np==15) { $nitem++; }
			}
			$x++;
			$casos++;
			$sub_total += (($suma_item[1]/13*0.25) + ($suma_item[2]/2*0.50) + ($suma_item[3]/4*0.25));
		}
		$conteo_profe['total'] = $sub_total/$casos;
		$conteo_profe['casos'] = $casos;
		$conteo = array_merge($conteo,array($y => $conteo_profe));		
		$y++;
	}
	return $conteo;
}		

function lista_control_asist_2011($id_curso) {
	
	$SQL_asist_curso = "SELECT fecha_hora::date AS fecha,to_char(fecha_hora,'YYYY-MM-DD HH24:MI:SS') AS fec_hora
	                    FROM asist_cursos
	                    WHERE id_curso=$id_curso ORDER BY fecha_hora";
	$asist_curso     = consulta_sql($SQL_asist_curso);
	
	$lista_control_asist_2011 = array();
	$x = $y = 0;
	while ($x < count($asist_curso)) {
		$hora_entrada = $hora_salida = "";
		$lista_control_asist_2011[$y]['fecha'] = $fec = $asist_curso[$x]['fecha'];
		$hora_entrada = strtotime($asist_curso[$x]['fec_hora']);
		$lista_control_asist_2011[$y]['hora_entrada'] = strftime("%T",$hora_entrada);
		$x++;
		if ($fec == $asist_curso[$x]['fecha']) {
			$hora_salida  = strtotime($asist_curso[$x]['fec_hora']);
			$lista_control_asist_2011[$y]['hora_salida']  = strftime("%T",$hora_salida);
			$x++;
		}
		$horas_dia_ped = $horas_dia = 0;
		if ($hora_entrada <> "" && $hora_salida <> "") {
			$horas_dia = $hora_salida - $hora_entrada; // esto devuelva la diferencia en segundos
			// Modulos de 1 hora y 30 mins: 2400s = 40m; 4500s = 1h 15m; 9900s = 2h 45m
			/*
			if ($horas_dia >= 2400 && $horas_dia < 4500) { $horas_dia_ped = 1; }
			if ($horas_dia >= 4500 && $horas_dia < 9900) { $horas_dia_ped = 2; }
			if ($horas_dia >= 9900)                      { $horas_dia_ped = 4; }
			*/
			// Modulos de 1 hora y 20 mins: 2100s = 35m; 4500s = 1h 15m; 9600s = 2h 40m
			if ($horas_dia >= 2100 && $horas_dia < 4500) { $horas_dia_ped = 1; }
			if ($horas_dia >= 4500 && $horas_dia < 9600) { $horas_dia_ped = 2; }
			if ($horas_dia >= 9600)                      { $horas_dia_ped = 4; }
			$horas_dia = date("H:i", strtotime("00:00") + $hora_salida - $hora_entrada);
		}
		$lista_control_asist_2011[$y]['horas_dia_pedag']  = $horas_dia_ped;
		$lista_control_asist_2011[$y]['horas_dia_reloj']  = $horas_dia;
		$y++;
	}
	return $lista_control_asist_2011;
}

function total_horas_control_asist_2011($id_curso) {
	$asist_curso = lista_control_asist_2011($id_curso);
	$horas = 0;
	for ($x=0;$x<count($asist_curso);$x++) { $horas += $asist_curso[$x]['horas_dia_pedag']; }
	return $horas;
}

function horas_programdas_curso($id_curso) {
	$SQL_curso = "SELECT dia1,dia2,dia3 FROM cursos WHERE id=$id_curso";
	$SQL_feriados_curso = "SELECT count(sc.fecha) FROM susp_clases sc LEFT JOIN cursos c ON c.id=$id_curso
	                       WHERE sc.ano=c.ano AND sc.semestre=c.semestre AND date_part('dow',sc.fecha) IN (c.dia1,c.dia2,c.dia3)) AS feriados";
}

function generar_cobros ($id_contrato,$id_glosa,$cant_cuotas,$monto_cuota,$monto_total,$diap,$mesp,$anop) {
	$SQL_cobros = "";
	for ($x=1;$x<=$cant_cuotas;$x++) {					
		if ($x == $cant_cuotas) {
			$monto_cuota = $monto_total - ($monto_cuota * ($x-1));
		}
		
		if ($mesp > 12) { $mesp=1; $anop++; }
		
		$diap_aux = $diap;
		if ($mesp == 2 && $diap_aux > 28) { $diap_aux = 28; }
		
		$fecha_venc = "$anop-$mesp-$diap_aux";
		
		$SQL_cobros .= "INSERT INTO finanzas.cobros (id_contrato,id_glosa,nro_cuota,monto,fecha_venc)
						 VALUES ($id_contrato,$id_glosa,$x,$monto_cuota,'$fecha_venc');";
		$mesp++;
	}
	return $SQL_cobros;
}

function generar_cobros_ci ($id_convenio,$id_glosa,$cant_cuotas,$monto_cuota,$monto_cuota_uf,$monto_total,$monto_total_uf,$diap,$mesp,$anop) {
	setlocale(LC_MONETARY,"en_US.UTF-8");
	setlocale(LC_NUMERIC,"en_US.UTF-8");
	$SQL_cobros = "";

	for ($x=1;$x<=$cant_cuotas;$x++) {
		if ($x == $cant_cuotas) {
			if ($monto_cuota <> "null") {
				$monto_cuota = $monto_total - ($monto_cuota * ($x-1));
			}
			
			if ($monto_cuota_uf <> "null") {
				$monto_cuota_uf = $monto_total_uf - ($monto_cuota_uf * ($x-1));
			}	
		}
		if ($mesp > 12) { $mesp=1; $anop++; }
		
		$diap_aux = $diap;
		if ($mesp == 2 && $diap_aux > 28) { $diap_aux = 28; }
		
		$fecha_venc = "$anop-$mesp-$diap_aux";
				
		$SQL_cobros .= "INSERT INTO finanzas.cobros (id_convenio_ci,id_glosa,nro_cuota,monto,monto_uf,fecha_venc)
						 VALUES ($id_convenio,$id_glosa,$x,$monto_cuota,$monto_cuota_uf,'$fecha_venc');";
		$mesp++;
	}
	return $SQL_cobros;
}

function tabla_malla_ponderaciones($actividades,$promgen_pond,$ramos_ponderados) {
	$HTML = "<table class='tabla' cellspacing='0' cellpadding='2' align='center'>"
	      . " <tr class='filaTituloTabla'><td class='tituloTabla' style='text-align: center'></td><td class='tituloTabla' style='text-align: center'><small>Ponderaciones</small></td></tr>"
	      . " <tr><td class='celdaValorAttr'><small>Promedio General:</small></td><td class='celdaValorAttr' style='text-align: center'><small>$promgen_pond%</small></td></tr>";
	$actividades = array_merge($ramos_ponderados,$actividades);
	for ($x=0;$x<count($actividades);$x++) {
		$HTML .= " <tr>"
		      .  "   <td class='celdaValorAttr'><small>{$actividades[$x]['nombre']}:</small></td>"
		      .  "   <td class='celdaValorAttr' style='text-align: center'><small>{$actividades[$x]['pond']}%</small></td>"
		      .  " </tr>";
	}
	
	$HTML .= "</table>";
	return $HTML;
}

function tabla_malla_otorga_titulos_grados($malla) {
	extract($malla);
	
	$HTML = "";
	if ($tns_nombre <> "No aplica" && $tns_sem_req > 0) { 
		$actividades = array();
		if (!is_null($tns_actividad_nombre) && !is_null($tns_actividad_pond)) {
			$tns_actividad_nombre = explode(",",str_replace("\"","",substr($tns_actividad_nombre,1,-1))); 
			$tns_actividad_pond   = explode(",",substr($tns_actividad_pond,1,-1)); 
			if (count($tns_actividad_nombre) == count($tns_actividad_pond)) {
				for ($x=0;$x<count($tns_actividad_nombre);$x++) {
					if ($tns_actividad_nombre[$x] <> "") {
						$actividades[$x]['nombre'] = $tns_actividad_nombre[$x];
						$actividades[$x]['pond']   = $tns_actividad_pond[$x]*100;
					}
				}
			}
		}		
		$ramos_ponderados = consulta_sql("SELECT cod_asignatura||' '||asignatura AS nombre,pond_tns*100 AS pond FROM vista_detalle_malla vdm LEFT JOIN detalle_mallas dm USING(id) WHERE dm.id_malla=$id AND pond_tns > 0");

		$HTML .= "<tr>"
		      .  "  <td class='celdaNombreAttr'>Técnico de Nivel Superior (TNS):</td>"
		      .  "  <td class='celdaValorAttr' colspan='3'>$tns_nombre <b><i>aprobando</i></b> $tns_sem_req semestre(s) ". tabla_malla_ponderaciones($actividades,$tns_promgen_pond,$ramos_ponderados) . "</td>"
		      .  "</tr>";
	}
	if ($ga_nombre <> "No aplica" && $ga_sem_req > 0) {
		$actividades = array();
		if (!is_null($ga_actividad_nombre) && !is_null($ga_actividad_pond)) {
			$ga_actividad_nombre = explode(",",str_replace("\"","",substr($ga_actividad_nombre,1,-1))); 
			$ga_actividad_pond   = explode(",",substr($ga_actividad_pond,1,-1)); 
			if (count($ga_actividad_nombre) == count($ga_actividad_pond)) {
				for ($x=0;$x<count($ga_actividad_nombre);$x++) { 
					if ($ga_actividad_nombre[$x] <> "") {
						$actividades[$x]['nombre'] = $ga_actividad_nombre[$x];
						$actividades[$x]['pond']   = $ga_actividad_pond[$x]*100;
					}
				}
			}
		}
		$ramos_ponderados = consulta_sql("SELECT cod_asignatura||' '||asignatura AS nombre,pond_ga*100 AS pond FROM vista_detalle_malla vdm LEFT JOIN detalle_mallas dm USING(id) WHERE dm.id_malla=$id AND pond_ga > 0");
		
		$HTML .= "<tr>"
		      .  "  <td class='celdaNombreAttr'>Grado Académico (GA):</td>"
		      .  "  <td class='celdaValorAttr' colspan='3'>$ga_nombre <b><i>aprobando</i></b> $ga_sem_req semestre(s) ". tabla_malla_ponderaciones($actividades,$ga_promgen_pond,$ramos_ponderados) . "</td>"
		      .  "</tr>";
	}
	if ($tp_nombre <> "No aplica" && $tp_sem_req > 0) {
		$actividades = array();
		if (!is_null($tp_actividad_nombre) && !is_null($tp_actividad_pond)) {
			$tp_actividad_nombre = explode(",",str_replace("\"","",substr($tp_actividad_nombre,1,-1))); 
			$tp_actividad_pond   = explode(",",substr($tp_actividad_pond,1,-1)); 
			if (count($tp_actividad_nombre) == count($tp_actividad_pond)) {
				for ($x=0;$x<count($tp_actividad_nombre);$x++) {
					if ($tp_actividad_nombre[$x] <> "") {
						$actividades[$x]['nombre'] = $tp_actividad_nombre[$x];
						$actividades[$x]['pond']   = $tp_actividad_pond[$x]*100;
					}
				}
			}
		}		
		$ramos_ponderados = consulta_sql("SELECT cod_asignatura||' '||asignatura AS nombre,pond_tp*100 AS pond FROM vista_detalle_malla vdm LEFT JOIN detalle_mallas dm USING(id) WHERE dm.id_malla=$id AND pond_tp > 0");

		$HTML .= "<tr>"
		      .  "  <td class='celdaNombreAttr'>Titulo Profesional (TP):</td>"
		      .  "  <td class='celdaValorAttr' colspan='3'>$tp_nombre <b><i>aprobando</i></b> $tp_sem_req semestre(s) ". tabla_malla_ponderaciones($actividades,$tp_promgen_pond,$ramos_ponderados) . "</td>"
		      .  "</tr>";
	}
	if ($otros_nombre <> "No aplica" && $otros_sem_req > 0) {
		$actividades = array();
		if (!is_null($otros_actividad_nombre) && !is_null($otros_actividad_pond)) {
			$otros_actividad_nombre = explode(",",str_replace("\"","",substr($otros_actividad_nombre,1,-1))); 
			$otros_actividad_pond   = explode(",",substr($otros_actividad_pond,1,-1)); 
			if (count($otros_actividad_nombre) == count($otros_actividad_pond)) {
				for ($x=0;$x<count($otros_actividad_nombre);$x++) {
					if ($otros_actividad_nombre[$x] <> "") {
						$actividades[$x]['nombre'] = $otros_actividad_nombre[$x];
						$actividades[$x]['pond']   = $otros_actividad_pond[$x]*100;
					}
				}
			}
		}
		$ramos_ponderados = consulta_sql("SELECT cod_asignatura||' '||asignatura AS nombre,pond_otros*100 AS pond FROM vista_detalle_malla vdm LEFT JOIN detalle_mallas dm USING(id) WHERE dm.id_malla=$id AND pond_otros > 0");

		$HTML .= "<tr>"
		      .  "  <td class='celdaNombreAttr'>Otro:</td>"
		      .  "  <td class='celdaValorAttr' colspan='3'>$otros_nombre <b><i>aprobando</i></b> $otros_sem_req semestre(s) ". tabla_malla_ponderaciones($actividades,$otros_promgen_pond,$ramos_ponderados) . "</td>"
		      .  "</tr>";
	}
	if ($HTML <> "") {
		$HTML = "<tr><td class='celdaNombreAttr' colspan='4' style='text-align: center'>Nombre de Títulos y/o Grados que otorga</td></tr>" . $HTML;
	}
	return $HTML;
}

function CategoriasFlujosIngresos($ano,$id_tipo,$id_tipo_excluir) {
	$Categorias[] = array('tipo',             'nombre',                                   'ano_origen_min','ano_origen_max','ano_venc_min','ano_venc_max','totalizador');
	$Categorias[] = array("Matrículas",       "del año",                                             $ano ,           $ano ,            0 ,         9999 ,'Ingresos Presupuestarios');
	$Categorias[] = array("Matrículas",       "Anticipadas",                                       $ano+1 ,           9999 ,            0 ,         9999 ,'Ingresos Presupuestarios');
	$Categorias[] = array("Aranceles",        "Periodo anterior que vencen el año actual",         $ano-1 ,         $ano-1 ,         $ano ,         9999 ,'Ingresos Presupuestarios');
	$Categorias[] = array("Aranceles",        "del año",                                             $ano ,           $ano ,            0 ,         9999 ,'Ingresos Presupuestarios');
	$Categorias[] = array("Cuotas Sociales",  "del año",                                             $ano ,           $ano ,            0 ,         9999 ,'Ingresos Presupuestarios');
	$Categorias[] = array("Matrículas",       "Anteriores",                                             0 ,         $ano-1 ,            0 ,         9999 ,'Ingresos Extrapresupuestarios');
	$Categorias[] = array("Aranceles",        "Rezagados (dos o más periodos anteriores)",              0 ,         $ano-2 ,            0 ,         9999 ,'Ingresos Extrapresupuestarios');
	$Categorias[] = array("Aranceles",        "Periodo anterior vencidos el año anterior",         $ano-1 ,         $ano-1 ,            0 ,       $ano-1 ,'Ingresos Extrapresupuestarios');
	$Categorias[] = array("Aranceles",        "Anticipados",                                       $ano+1 ,           9999 ,            0 ,         9999 ,'Ingresos Extrapresupuestarios');
	$Categorias[] = array("Cuotas Sociales",  "Anticipadas",                                       $ano+1 ,           9999 ,            0 ,         9999 ,'Ingresos Extrapresupuestarios');
	$Categorias[] = array("Cuotas Sociales",  "Atrasadas",                                              0 ,         $ano-1 ,            0 ,         9999 ,'Ingresos Extrapresupuestarios');
	$Categorias[] = array("Créditos Internos","",                                                       0 ,           9999 ,            0 ,         9999 ,'Ingresos Extrapresupuestarios');
	$Categorias[] = array("Donaciones",       "del año",                                                0 ,           9999 ,            0 ,         9999 ,'Ingresos Extrapresupuestarios');
	$Categorias[] = array("Arriendo de Salas","del año",                                                0 ,           9999 ,            0 ,         9999 ,'Ingresos Extrapresupuestarios');
	$Categorias[] = array("Proyectos de VCM" ,"del año",                                                0 ,           9999 ,            0 ,         9999 ,'Ingresos Extrapresupuestarios');
	$Categorias[] = array("Intereses Financieros Ganados","del año",                                    0 ,           9999 ,            0 ,         9999 ,'Ingresos Extrapresupuestarios');
	$Categorias[] = array("Devoluciones",     "",                                                       0 ,           9999 ,            0 ,         9999 ,'Ingresos Extrapresupuestarios');
	$Categorias[] = array("Otros Ingresos",   "",                                                       0 ,           9999 ,            0 ,         9999 ,'Ingresos Extrapresupuestarios');
	$Cat = array();
	for ($x=1;$x<count($Categorias);$x++) { $Cat[$x-1] = array_combine($Categorias[0],$Categorias[$x]); }
	$Categorias = $Cat;

	$aCat = array();
	$y = 0;
	if ($id_tipo <> "") {
		for ($x=0;$x<count($Categorias);$x++) {
			if ($Categorias[$x]['tipo'] == $id_tipo) {
				$aCat[$y] = $Categorias[$x];
				$y++;
			}
		}
		$Categorias = $aCat;
	} elseif ($id_tipo_excluir <> "") {
		for ($x=0;$x<count($Categorias);$x++) {
			if ($Categorias[$x]['tipo'] <> $id_tipo_excluir) {
				$aCat[$y] = $Categorias[$x];
				$y++;
			}
		}
		$Categorias = $aCat;		
	}
	
	return $Categorias;
}

/**
* Sums the values of the arrays be there keys (PHP 4, PHP 5)
* array array_sum_values ( array array1 [, array array2 [, array ...]] )
*/
function array_sum_values() {
	$return = array();
	$intArgs = func_num_args();
	$arrArgs = func_get_args();
	if($intArgs < 1) trigger_error('Warning: Wrong parameter count for array_sum_values()', E_USER_WARNING);

	foreach($arrArgs as $arrItem) {
		if(!is_array($arrItem)) trigger_error('Warning: Wrong parameter values for array_sum_values()', E_USER_WARNING);
			foreach($arrItem as $k => $v) {
				$return[$k] += $v;
			}
		}
	return $return;
} 

/*
function array_column($_array,$columna) {
	$column = array();
	for($x=0;$x<count($_array);$x++) { $column[$x] = $_array[$x][$columna]; }
	return $column;
}
*/

function comp_pago_email($id_pago) {	
	$SQL_pago = "SELECT p.id,coalesce(p.nro_boleta,p.nro_boleta_e) as nro_docto,
	                    CASE WHEN p.nro_boleta IS NOT NULL THEN 'B'
	                         WHEN p.nro_boleta_e IS NOT NULL THEN 'BE'
	                    END AS tipo_docto,
	                    to_char(p.fecha,'DD-tmMon-YYYY') AS fecha,u.nombre_usuario AS cajero,
						efectivo,cheque,transferencia,tarj_credito,tarj_debito,cheque_afecha,
						CASE WHEN id_arqueo IS NULL THEN 'No' ELSE 'Si' END AS rendida,p.nulo,p.nulo_motivo,to_char(p.nulo_fecha,'DD tmMonth YYYY') AS nulo_fecha,
						coalesce(a.rut,pap.rut,a3.rut,a2.rut) AS rut_alumno,
						coalesce(a.apellidos||' '||a.nombres,pap.apellidos||' '||pap.nombres,a3.apellidos||' '||a3.nombres,a2.apellidos||' '||a2.nombres) AS nombre_alumno,
						coalesce(car.nombre,car3.nombre,car2.nombre) AS carrera_alumno,
						coalesce(car.regimen,car3.regimen,car2.regimen) AS regimen_alumno,
						coalesce(c.jornada,a3.jornada,a2.jornada) AS jornada_alumno,
						coalesce(a.email,pap.email,a3.email,a2.email) as email_alumno,
						coalesce(a.nombre_usuario,a3.nombre_usuario,a2.nombre_usuario)||'@al.umcervantes.cl' as email_alumno_inst,
						coalesce(a.nombre_usuario,a3.nombre_usuario,a2.nombre_usuario)||'@alumni.umc.cl' as email_alumno_gsuite,
						to_char(p.fecha_reg,'DD-tmMon-YYYY HH24:MI') as fecha_reg
				 FROM finanzas.pagos AS p
				 LEFT JOIN vista_usuarios AS u          ON u.id=id_cajero
				 LEFT JOIN finanzas.pagos_detalle AS pd ON pd.id_pago=p.id 
				 LEFT JOIN finanzas.cobros AS cob       ON cob.id=id_cobro 
				 LEFT JOIN finanzas.contratos AS c      ON c.id=cob.id_contrato
				 LEFT JOIN finanzas.convenios_ci AS cci ON cci.id=cob.id_convenio_ci 
				 LEFT JOIN alumnos AS a                 ON a.id=c.id_alumno
				 LEFT JOIN alumnos AS a2                ON a2.id=cob.id_alumno
				 LEFT JOIN alumnos AS a3                ON a3.id=cci.id_alumno
				 LEFT JOIN pap                          ON pap.id=c.id_pap
				 LEFT JOIN carreras AS car			    ON car.id=c.id_carrera
				 LEFT JOIN carreras AS car2			    ON car2.id=a2.carrera_actual
				 LEFT JOIN carreras AS car3			    ON car3.id=a3.carrera_actual
				 WHERE p.id=$id_pago";
	$pago     = consulta_sql($SQL_pago);

	if (count($pago) == 0) { exit; }
	
	if ($pago[0]['regimen_alumno'] == "POST-TD" || $pago[0]['regimen_alumno'] == "POST-GD" || $pago[0]['regimen_alumno'] == "DIP-D") { return false; exit; }

	$efectivo      = number_format($pago[0]['efectivo'],0,",",".");
	$cheque        = number_format($pago[0]['cheque'],0,",",".");
	$cheque_afecha = number_format($pago[0]['cheque_afecha'],0,",",".");
	$transferencia = number_format($pago[0]['transferencia'],0,",",".");
	$tarj_credito  = number_format($pago[0]['tarj_credito'],0,",",".");
	$tarj_debito   = number_format($pago[0]['tarj_debito'],0,",",".");

	$SQL_pago_detalle = "SELECT c.id AS id_cobro,g.nombre AS glosa,pd.monto_pagado,c.monto,
								to_char(c.fecha_venc,'DD-tmMon-YYYY') AS fecha_venc,nro_cuota,
								coalesce(id_contrato,id_convenio_ci) AS nro_docto,
								coalesce(con.ano,date_part('year',cci.fecha)) AS ano_docto,
								g.agrupador
						 FROM finanzas.pagos_detalle     AS pd
						 LEFT JOIN finanzas.cobros       AS c   ON c.id=pd.id_cobro
						 LEFT JOIN finanzas.glosas       AS g   ON g.id=c.id_glosa
						 LEFT JOIN finanzas.contratos    AS con ON con.id=c.id_contrato
						 LEFT JOIN finanzas.convenios_ci AS cci ON cci.id=c.id_convenio_ci
						 WHERE pd.id_pago=$id_pago
						 ORDER BY c.fecha_venc";
	$pago_detalle     = consulta_sql($SQL_pago_detalle);
	$monto_total = 0;
	$HTML = "";
	$pago_certif = false;
	for ($x=0;$x<count($pago_detalle);$x++) {
		extract($pago_detalle[$x]);
		$accion = "Pagó";
		if ($monto_pagado < $monto) { $accion = "Abonó"; } 
		if ($agrupador == "Certificaciones") { $pago_certif = true; }
		$monto_total += $monto_pagado;
		$monto_pagado = number_format($monto_pagado,0,",",".");
		$id_contrato =  number_format($id_contrato,0,",",".");
		$HTML .= "<tr>\n"
			  .  "  <td class='textoTabla' style='vertical-align: middle'>$glosa</td>\n"
			  .  "  <td class='textoTabla' style='vertical-align: middle'>$fecha_venc</td>\n"
			  .  "  <td class='textoTabla' style='vertical-align: middle; text-align: center'>$nro_cuota</td>\n"
			  .  "  <td class='textoTabla' style='vertical-align: middle; text-align: center'>$nro_docto</td>\n"
			  .  "  <td class='textoTabla' style='vertical-align: middle; text-align: center'>$ano_docto</td>\n"
			  .  "  <td class='textoTabla' style='vertical-align: middle; text-align: center'>$accion</td>\n"
			  .  "  <td class='textoTabla' style='vertical-align: middle; text-align: right'>$$monto_pagado</td>\n"
			  .  "</tr>\n";
	}
	$monto_total = number_format($monto_total,0,",",".");
	if ($pago[0]['nulo'] == "t") { $nulo = " <b style='color: red'>NULO</b>"; }
	$jornada_alumno = "";
	if ($pago[0]['jornada_alumno'] == "D") { $jornada_alumno = "Diurna"; } else { $jornada_alumno = "Vespertina"; }

	$HTML = "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional //EN'>
	         <html>
	         <head>
	           <meta content='text/html; charset=UTF-8' http-equiv='content-type'>
	           <style type='text/css'>\n".file_get_contents("sgu.css")."\n</style>
	         </head>
	         <body>
	         <table bgcolor='#FFFFFF' border='0' cellspacing='1' cellpadding='2' class='tabla' style='margin-top: 5px'>
			   <tr><td bgcolor='#BBCAD6' colspan='4' style='text-align: center; '>Antecedentes de la Boleta</td></tr>
			   <tr>
				 <td bgcolor='#BBCAD6' style='color: #7F7F7F'>ID:</td><td bgcolor='#F5F5F5' style='color: #7F7F7F'>{$pago[0]['id']}</td>
				 <td bgcolor='#BBCAD6'>Nº:</td><td bgcolor='#F5F5F5'><b>{$pago[0]['tipo_docto']}/".number_format($pago[0]['nro_docto'],0,',','.').$nulo."</b></td>
			   </tr>
			   <tr>
				 <td bgcolor='#BBCAD6'>Fecha:</td><td bgcolor='#F5F5F5' colspan='3'>{$pago[0]['fecha']}</td>
			   </tr>
			   <tr><td bgcolor='#BBCAD6' colspan='4' style='text-align: center; '>Antecedentes del Estudiante</td></tr>
			   <tr>
				 <td bgcolor='#BBCAD6'>Nombre:</td><td bgcolor='#F5F5F5'>{$pago[0]['nombre_alumno']}</td>
				 <td bgcolor='#BBCAD6'>RUT:</td><td bgcolor='#F5F5F5'>{$pago[0]['rut_alumno']}</td>
			   </tr>          
			   <tr>
				 <td bgcolor='#BBCAD6'>Carrera:</td><td bgcolor='#F5F5F5'>{$pago[0]['carrera_alumno']}</td>
				 <td bgcolor='#BBCAD6'>Jornada:</td><td bgcolor='#F5F5F5'>$jornada_alumno</td>
			   </tr>
			 </table>
			 <table bgcolor='#F5F5F5' border='0' cellspacing='1' cellpadding='2' class='tabla' style='margin-top: 5px'>
			   <tr bgcolor='#DEF1FF'><td colspan='7' class='tituloTabla' style='text-align: center; '>Detalle</td></tr>
			   <tr bgcolor='#DEF1FF'>
				 <td align='center' rowspan='2'>Glosa</td>
				 <td align='center' rowspan='2'>Vencimiento</td>
				 <td align='center' rowspan='2'>Nº<br> Cuota</td>
				 <td align='center' colspan='2'>Documento</td>
				 <td align='center' rowspan='2'>Acción</td>
				 <td align='center' rowspan='2'>Monto</td>
			   </tr>
			   <tr bgcolor='#DEF1FF'>
				 <td align='center'>N°</td>
				 <td align='center'>Año</td>
			   </tr>
			   $HTML
			   <tr><td colspan='7' class='celdaValorAttr'>&nbsp;</td></tr>
			   <tr bgcolor='#DEF1FF'>
				 <td colspan='4'>&nbsp;</td>
				 <td class='celdaNombreAttr' colspan='2' style='text-align: right; '>Monto Boleta:</td>
				 <td bgcolor='#F5F5F5' style='text-align: right;'><b>$ $monto_total</b></td>
			   </tr>
			   <tr>
				 <td colspan='4'>&nbsp;</td>
				 <td bgcolor='#DEF1FF' colspan='3' style='text-align: center;'>Forma de Pago</td>
			   </tr>
			   <tr>
				 <td colspan='4'>&nbsp;</td><td bgcolor='#DEF1FF' colspan='2'>Efectivo</td><td class='celdaValorAttr' style='text-align: right;'>$ $efectivo</td>
			   </tr>
			   <tr>
				 <td colspan='4'>&nbsp;</td><td bgcolor='#DEF1FF' colspan='2'>Cheque al día</td><td class='celdaValorAttr' style='text-align: right;'>$ $cheque</td>
			   </tr>
			   <tr>
				 <td colspan='4'>&nbsp;</td><td bgcolor='#DEF1FF' colspan='2'>Cheque a fecha</td><td class='celdaValorAttr' style='text-align: right;'>$ $cheque_afecha</td>
			   </tr>
			   <tr>
				 <td colspan='4'>&nbsp;</td><td  bgcolor='#DEF1FF' colspan='2'>Transferencia</td><td class='celdaValorAttr' style='text-align: right;'>$ $transferencia</td>
			   </tr>
			   <tr>
				 <td colspan='4'>&nbsp;</td><td  bgcolor='#DEF1FF' colspan='2'>Tarjeta de Crédito</td><td class='celdaValorAttr' style='text-align: right;'>$ $tarj_credito</td>
			   </tr>
			   <tr>
				 <td colspan='4'>&nbsp;</td><td  bgcolor='#DEF1FF' colspan='2'>Tarjeta de Débito</td><td class='celdaValorAttr' style='text-align: right;'>$ $tarj_debito</td>
			   </tr>  
			 </table>
			 <p class='texto'>ATENCIÓN: Por favor no responder este mensaje</p>
			 </body>
			 </html>";

	$CR = "\r\n";
			
	$cabeceras = "From: UMC - SGU <no-responder@umcervantes.cl>" . $CR
	           . "Content-Type: text/html;charset=utf-8" . $CR;
	           
	$destinatarios = "$email_alumno,$email_alumno_inst,$email_alumno_gsuite";
	           
	mail($pago[0]['email_alumno'],"Comprobante de Pago $id_pago",$HTML,$cabeceras);
	mail($pago[0]['email_alumno_inst'],"Comprobante de Pago $id_pago",$HTML,$cabeceras);
	mail($pago[0]['email_alumno_gsuite'],"Comprobante de Pago $id_pago",$HTML,$cabeceras);
	
	if ($pago_certif) {
		$glosas = implode(", ",array_column($pago_detalle,'glosa'));
		mail("aaranela@umcervantes.cl","{$pago[0]['rut_alumno']} $glosas Comprobante de Pago $id_pago",$HTML,$cabeceras);
		mail("ppineiro@umcervantes.cl","$pago[0]['rut_alumno']} $glosas Comprobante de Pago $id_pago",$HTML,$cabeceras);
	}
//	mail("jeugenio@umcervantes.cl,jeugenio.martinez@gmail.com","Comprobante de Pago $id_pago",$HTML,$cabeceras);
}

function certificado_email($folio) {
	$SQL_certificado = "SELECT cod,ac.folio,c.nombre AS tipo,a.email,
	                           CASE a.genero WHEN 'f' THEN 'Estimada' WHEN 'm' THEN 'Estimado' END AS vocativo,
	                           split_part(initcap(a.nombres),' ',1) as nombre_pila,
	                           nombre_usuario||'@alumni.umc.cl' as email_gsuite,
	                           nombre_usuario||'@al.umcervates.cl' as email_inst,
							   c.email_cc
	                    FROM alumnos_certificados AS ac
	                    LEFT JOIN vista_alumnos_certificados_codbarras AS vaccb USING (folio)
	                    LEFT JOIN alumnos AS a ON a.id=ac.id_alumno
	                    LEFT JOIN certificados AS c ON c.id=ac.id_certificado
	                    WHERE ac.folio=$folio";
	$certificado = consulta_sql($SQL_certificado);
	
	if (count($certificado) == 0) { return false; exit; }
	
	extract($certificado[0]);
	
	$CR = "\r\n";
			
	$cabeceras = "From: Registro Académico UMC - Certificaciones <certificaciones@umcervantes.cl>" . $CR
	           . "Content-Type: text/plain;charset=utf-8" . $CR;
	           
	$asunto = "Emisión de $tipo";
	
	$mensaje = "$vocativo $nombre_pila,\n" . $CR
	         . "Para obtener tu certificado pincha en el enlace siguiente:\n" . $CR
	         . "https://sgu.umc.cl/sgu/certificado_noimp.php?cod=$cod \n" . $CR
	         . "Si no se abre el certificado, copie la URL anterior, abra su navegador, pegue la URL y presiona ENTER\n" . $CR
	         . "Para cualquier duda o consulta pueden contactarnos en la dirección certificaciones@corp.umc.cl \n" . $CR
	         . "Unidad de Registro Académico, Títulos y Grados" . $CR
	         . "Universidad Miguel de Cervantes\n" . $CR
	         . "$email, $email_gsuite, $email_inst" . $CR;
	         
	mail($email,$asunto,$mensaje,$cabeceras);
	mail($email_gsuite,$asunto,$mensaje,$cabeceras);
	mail($email_inst,$asunto,$mensaje,$cabeceras);
	mail($email_cc,$asunto,$mensaje,$cabeceras);
	//mail("jeugenio@umcervantes.cl",$asunto,$mensaje,$cabeceras);
}

function calificacion_palabras($calif) {
	list($entero,$decimal) = explode(",",$calif);
	$entero  = num2palabras($entero);
	$decimal = num2palabras($decimal);
	if ($decimal == "un") { $decimal = "uno"; } elseif ($decimal == "") { $decimal = "cero"; }
	return "$entero coma $decimal";
}

function apelativo_aprobacion($calif) {
	$calif = floatval(str_replace(",",".",$calif));
	$apelativo = "Reprobado con Distinción";
	if ($calif>=4 && $calif<5) { $apelativo = "Aprobado"; }
	elseif ($calif>=5 && $calif<6) { $apelativo = "Aprobado con Distinción"; }
	elseif ($calif>=6 && $calif<=7) { $apelativo = "Aprobado con Distinción Máxima"; }
	return $apelativo;
}

function verif_estado_carpeta_doctos($rut) {
	$postulante = consulta_sql("SELECT id,regimen,admision,notif_creacion FROM pap WHERE rut='$rut'");
	extract($postulante[0]);
	
	$doctos_obligatorios = consulta_sql("SELECT * FROM doctos_obligatorios WHERE regimen='$regimen' AND admision_tipo='$admision'");
	//var_dump($doctos_obligatorios);

	$SQL_cant_doctos = "SELECT dd.id 
	                    FROM doctos_obligatorios AS docob
	                    LEFT JOIN doctos_digitalizados AS dd ON (dd.rut='$rut' AND NOT dd.eliminado AND dd.id_tipo IN (docob.id_tipo_docto,coalesce(docob.id_tipo_docto_comp,0))) 
	                    WHERE docob.regimen='$regimen' AND docob.admision_tipo='$admision' AND dd.id IS NOT NULL";		
	$cant_doctos = count(consulta_sql($SQL_cant_doctos));

	$estado_carpeta_doctos = $notif_creacion = "";
	if ($cant_doctos == 0 || count($doctos_obligatorios) == 0) { 
		$estado_carpeta_doctos = "Vacía"; 
		$notif_creacion = "notif_creacion"; //no modifica la fecha de notificacion, si la hubiera.
	} else {
		if (count($doctos_obligatorios) <= $cant_doctos) { 
			$estado_carpeta_doctos = "Completa"; 
			$notif_creacion = "null"; //elimina la fecha notificacion, para renotificar.
		}
		if (count($doctos_obligatorios) > $cant_doctos) { 
			$estado_carpeta_doctos = "Incompleta"; 
			$notif_creacion = "notif_creacion";//no modifica la fecha de notificacion, si la hubiera.
		}  
	}
	consulta_dml("UPDATE pap SET estado_carpeta_doctos='$estado_carpeta_doctos',estado_carpeta_doctos_fecha=now(),notif_creacion=$notif_creacion WHERE id=$id");

	if (count($doctos_obligatorios) <= $cant_doctos && $notif_creacion == "") { email_memorandum_alumnos_nuevos($rut); }

}

function email_memorandum_alumnos_nuevos($rut) {
	$postulante = consulta_sql("SELECT id,apellidos||' '||nombres AS pap_nombre,regimen FROM pap WHERE rut='$rut' AND notif_creacion IS NULL");	
	if (count($postulante) > 0) {
		extract($postulante[0]);
	
		$CR = "\r\n";
				
		$cabeceras = "From: Unidad de Admisión UMC <admision@umcervantes.cl>" . $CR
				   . "Content-Type: text/plain;charset=utf-8" . $CR;
				   
		$asunto = "+Alumni $regimen $rut $pap_nombre";
		
		$mensaje = "Se ha completado la matrícula y documentación de un(a) postulante que debe verificar "
				 . "y habilitar como Alumno/a Regular." . $CR. $CR
				 . "Saluda atentamente," . $CR . $CR
				 . "Unidad de Admisión" . $CR
				 . "Universidad Miguel de Cervantes" . $CR;
				 
		$SQL_resp_unidad = "SELECT coalesce(email,nombre_usuario||'@umcervantes.cl') AS email 
							FROM usuarios
							WHERE activo AND id_unidad IN (SELECT id FROM gestion.unidades WHERE alias='RATG')";
		$resp_unidad = consulta_sql($SQL_resp_unidad);
		for ($x=0;$x<count($resp_unidad);$x++)	{
			mail($resp_unidad[$x]['email'],$asunto,$mensaje,$cabeceras);
		}
		consulta_dml("UPDATE pap SET notif_creacion=now() WHERE id=$id");
		echo(msje_js("Se ha notificado a Registro Académico que la carpeta "
		            ."del/la estudiante se encuentra Completa, para su "
					."revisión y habilitación como Alumno/a Regular."));
		//mail("jeugenio@umcervantes.cl",$asunto,$mensaje,$cabeceras);	
	} else {
		echo(msje_js("ATENCIÓN: Este/a postulante ya se encuentra notificada a Registro Académico"));
	}
}

include("evdem_funciones.php")

?>
