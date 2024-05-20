<?php
//include("funciones.php");

$AF5_Niveles = array(array('nombre' => "Alto",       'centil_min' => 85, 'centil_max' => 99, 'clase' => "Superior"),
                     array('nombre' => "Medio alto", 'centil_min' => 65, 'centil_max' => 80, 'clase' => "Superior"),
                     array('nombre' => "Medio",      'centil_min' => 45, 'centil_max' => 60, 'clase' => "Promedio"),
                     array('nombre' => "Medio bajo", 'centil_min' => 25, 'centil_max' => 40, 'clase' => "Promedio"),
                     array('nombre' => "Bajo",       'centil_min' => 1,  'centil_max' => 20, 'clase' => "Inferior"),
                     array('nombre' => "Descartados",'centil_min' => 0,  'centil_max' => 0));
				     
$AF5_Dimensiones = array(array('alias' => "Académico/Laboral",'nombre' => "Académico/Laboral", 'preguntas' => "p1,p6,p11,p16,p21,p26"),
                         array('alias' => "Social"           ,'nombre' => "Social"           , 'preguntas' => "p2,p7,p12,p17,p22,p27"),
                         array('alias' => "Emocional"        ,'nombre' => "Emocional"        , 'preguntas' => "p3,p8,p13,p18,p23,p28"),
                         array('alias' => "Familiar"         ,'nombre' => "Familiar"         , 'preguntas' => "p4,p9,p14,p19,p24,p29"));

$ACRA_Niveles = array(array('nombre' => "Alto",       'centil_min' => 85, 'centil_max' => 99, 'clase' => "Superior"),
                      array('nombre' => "Medio alto", 'centil_min' => 65, 'centil_max' => 80, 'clase' => "Superior"),
                      array('nombre' => "Medio",      'centil_min' => 45, 'centil_max' => 60, 'clase' => "Promedio"),
                      array('nombre' => "Medio bajo", 'centil_min' => 25, 'centil_max' => 40, 'clase' => "Promedio"),
                      array('nombre' => "Bajo",       'centil_min' => 1,  'centil_max' => 20, 'clase' => "Inferior"),
                      array('nombre' => "Descartados",'centil_min' => 0,  'centil_max' => 0));
                     
$ACRA_Escalas = array(array('alias'=>"Adquisición",  'nombre'=>"Estrategias de Adquisición de Información",  'preguntas'=>"p1,p2,p3,p4,p5,p6,p7,p8,p9,p10,p11,p12,p13,p14,p15,p16,p17,p18,p19,p20"),
                      array('alias'=>"Codificación", 'nombre'=>"Estrategias de Codificación de Información", 'preguntas'=>"p21,p22,p23,p24,p25,p26,p27,p28,p29,p30,p31,p32,p33,p34,p35,p36,p37,p38,p39,p40,p41,p42,p43,p44,p45,p46,p47,p48,p49,p50,p51,p52,p53,p54,p55,p56,p57,p58,p59,p60,p61,p62,p63,p64,p65,p66"),
                      array('alias'=>"Recuperación", 'nombre'=>"Estrategias de Recuperación de Información", 'preguntas'=>"p67,p68,p69,p70,p71,p72,p73,p74,p75,p76,p77,p78,p79,p80,p81,p82,p83,p84"),
                      array('alias'=>"Procesamiento",'nombre'=>"Estrategias de Apoyo al Procesamiento",      'preguntas'=>"p85,p86,p87,p88,p89,p90,p91,p92,p93,p94,p95,p96,p97,p98,p99,p100,p101,p102,p103,p104,p105,p106,p107,p108,p109,p110,p111,p112,p113,p114,p115,p116,p117,p118,p119"));

$OTIS_Niveles = array(array('nombre' => "Muy superior",  'centil_min' => 99, 'centil_max' => 99, 'clase' => "Superior"),
                      array('nombre' => "Superior",      'centil_min' => 90, 'centil_max' => 97, 'clase' => "Superior"),
                      array('nombre' => "Promedio alto", 'centil_min' => 77, 'centil_max' => 89, 'clase' => "Media"),
                      array('nombre' => "Promedio",      'centil_min' => 25, 'centil_max' => 75, 'clase' => "Media"),
                      array('nombre' => "Promedio bajo", 'centil_min' => 10, 'centil_max' => 23, 'clase' => "Descendido"),
                      array('nombre' => "Límite",        'centil_min' => 4,  'centil_max' => 5,  'clase' => "Descendido"),
                      array('nombre' => "Muy bajo",      'centil_min' => 1,  'centil_max' => 1,  'clase' => "Descendido"),
                      array('nombre' => "Descartados",   'centil_min' => 0,  'centil_max' => 0));

$OTIS_Areas = array(array('alias' => "General",   'nombre' => "General",    'preguntas' => "p1,p2,p3,p4,p5,p6,p7,p8,p9,p10,p11,p12,p13,p14,p15,p16,p17,p18,p19,p20,p21,p22,p23,p24,p25,p26,p27,p28,p29,p30,p31,p32,p33,p34,p35,p36,p37,p38,p39,p40,p41,p42,p43,p44,p45,p46,p47,p48,p49,p50,p51,p52,p53,p54,p55,p56,p57,p58,p59,p60,p61,p62,p63,p64,p65,p66,p67,p68,p69,p70,p71,p72,p73,p74,p75"),
                    array('alias' => "Ejecución", 'nombre' => "Ejecución",  'preguntas' => "p11,p21,p35,p42,p44,p45,p48,p56,p60,p63,p67,p70,p71,p72,p75"),
                    array('alias' => "Relacionar",'nombre' => "Relacionar", 'preguntas' => "p1,p3,p4,p6,p9,p18,p22,p23,p25,p26,p30,p33,p34,p37,p57"),
                    array('alias' => "Mensajes",  'nombre' => "Mensajes",   'preguntas' => "p2,p7,p8,p12,p19,p28,p29,p32,p38,p40,p46,p50,p55,p59,p65"),
                    array('alias' => "Léxico",    'nombre' => "Léxico",     'preguntas' => "p14,p15,p16,p20,p24,p27,p39,p41,p51,p52,p54,p62,p66,p68,p69"),
                    array('alias' => "Aritmética",'nombre' => "Aritmética", 'preguntas' =>  "p5,p10,p13,p17,p31,p36,p43,p47,p49,p53,p58,p61,p64,p73,p74"));

function sinergia_respuestas($prueba, $ano_prueba, $semestre_prueba, $SQL_filtros_alumno) {
	
	$SQL_respuestas = "SELECT sr.*,a.apellidos AS nombre,date_part('year',age(a.fec_nac)) AS edad,c.alias AS carrera,a.jornada,c.regimen,
	                          CASE a.genero WHEN 'f' THEN 'mujeres' WHEN 'm' THEN 'varones' END AS genero,
	                          CASE WHEN a.admision IN (1,10) THEN 'edmedia' ELSE 'universidad' END AS nivel_estudios
	                   FROM sinergia.respuestas   AS sr
	                   LEFT JOIN alumnos          AS a ON a.rut=sr.rut_alumno
	                   LEFT JOIN carreras         AS c ON c.id=a.carrera_actual
	                   LEFT JOIN sinergia.pruebas AS p ON p.id=sr.id_prueba
	                   $SQL_filtros_alumno AND ano=$ano_prueba AND semestre=$semestre_prueba 
	                     AND p.alias='$prueba'
	                   ORDER BY a.apellidos";
	$respuestas     = consulta_sql($SQL_respuestas);
	
	for ($x=0;$x<count($respuestas);$x++) {
		$resp = explode(",",str_replace(array("{","}"),"",$respuestas[$x]['resp']));
		$aResp = array();
		for ($y=0;$y<count($resp);$y++) {
			$ind = $y+1;
			$aResp["p$ind"] = $resp[$y];
		}
		$respuestas[$x] = array_merge($respuestas[$x],$aResp);
	}
	return $respuestas;
}

function sinergia_AF5($respuestas) {
	global $AF5_Dimensiones;
	
	$dimensiones = $AF5_Dimensiones;
	                     
	$baremo['mujeres_edmedia']      = sinergia_baremo_cargar("af5","mujeres_edmedia");
	$baremo['mujeres_bachillerato'] = sinergia_baremo_cargar("af5","mujeres_bachillerato");
	$baremo['mujeres_universidad']  = sinergia_baremo_cargar("af5","mujeres_universidad");
	$baremo['varones_edmedia']      = sinergia_baremo_cargar("af5","varones_edmedia");
	$baremo['varones_bachillerato'] = sinergia_baremo_cargar("af5","varones_bachillerato");
	$baremo['varones_universidad']  = sinergia_baremo_cargar("af5","varones_universidad");
	
	for ($x=0;$x<count($respuestas);$x++) {
		$genero         = $respuestas[$x]['genero'];
		$nivel_estudios = $respuestas[$x]['nivel_estudios'];
		$punt_dim = array();
		for ($y=0;$y<count($dimensiones);$y++) {
			$preguntas_dim = explode(",",$dimensiones[$y]["preguntas"]);
			$suma_punt = 0;
			for ($z=0;$z<count($preguntas_dim);$z++) {
				$preg = $preguntas_dim[$z];
				$puntaje = $respuestas[$x][$preg];
				if ($preg == "p4" || $preg == "p12" || $preg == "p14" || $preg == "p22") { $puntaje = 100 - $puntaje; }
				$suma_punt += $puntaje;
			}
			$nombre_dim = $dimensiones[$y]["nombre"];
			if ($nombre_dim == "Emocional") { $suma_punt = 600 - $suma_punt; }
			$punt_dim["puntaje_$nombre_dim"] = round($suma_punt/60,2);
			$tipo_baremo = $genero."_".$nivel_estudios;
			$punt_dim["centil_$nombre_dim"] = sinergia_AF5_baremo_buscar_centil($baremo[$tipo_baremo],$nombre_dim,$punt_dim["puntaje_$nombre_dim"]);
			$punt_dim["nivel_$nombre_dim"]  = sinergia_AF5_buscar_nivel($punt_dim["centil_$nombre_dim"]);
			$punt_dim["clase_$nombre_dim"]  = sinergia_AF5_buscar_clase($punt_dim["nivel_$nombre_dim"]);
		}
		$respuestas[$x] = array_merge($respuestas[$x],$punt_dim);
	}
	return $respuestas;
}

function sinergia_AF5_resumen_json($respuestas) {
	global $AF5_Niveles, $AF5_Dimensiones;
	
	$niveles = array();
	for ($x=0;$x<count($AF5_Dimensiones);$x++) {
		$nombre_dim = $AF5_Dimensiones[$x]['nombre'];
		$niveles[$x]['dimension'] = $nombre_dim;
		$freq_niveles = array_count_values(array_column($respuestas,"nivel_$nombre_dim"));
		$aNiveles = array();
		for($y=count($AF5_Niveles)-1;$y>=0;$y--) {
			$nombre_nivel = $AF5_Niveles[$y]['nombre'];
			$aNiveles[$nombre_nivel] = $freq_niveles[$nombre_nivel];
		}
		$niveles[$x] = array_merge($niveles[$x],$aNiveles);
	}
	return json_encode($niveles);
}

function sinergia_AF5_resumen($respuestas) {
	global $AF5_Niveles, $AF5_Dimensiones;
	
	$niveles = array();
	for ($x=0;$x<count($AF5_Dimensiones);$x++) {
		$nombre_dim = $AF5_Dimensiones[$x]['nombre'];
		$niveles[$nombre_dim] = array_count_values(array_column($respuestas,"nivel_$nombre_dim"));
		$niveles[$nombre_dim."_clase"] = array_count_values(array_column($respuestas,"clase_$nombre_dim"));
		$aNiveles = array();
		for($y=0;$y<count($AF5_Niveles);$y++) {
			$nombre_nivel = $AF5_Niveles[$y]['nombre'];			
			$aNiveles[$nombre_nivel] = $niveles[$nombre_dim][$nombre_nivel];
		}
		$niveles[$nombre_dim] = $aNiveles;
	}
	return $niveles;
}

function sinergia_baremo_cargar($prueba,$nombre) {
	// Transformar CSV a matriz, al estilo de pg_fetch_all()
	$baremo       = file("sinergia/".$prueba.'_baremo_'.$nombre.'.csv',FILE_IGNORE_NEW_LINES);
	$aKeys_baremo = str_getcsv($baremo[0]);
	$aBaremo      = array();
	for ($x=1;$x<count($baremo);$x++) {
		$aBaremo[$x-1] = array_combine($aKeys_baremo,str_getcsv($baremo[$x]));
	}
	return $aBaremo;
}

function sinergia_correccion_cargar($prueba) {
	// Transformar CSV a matriz, al estilo de pg_fetch_all()
	$correccion       = file("sinergia/".$prueba.'_correccion.csv',FILE_IGNORE_NEW_LINES);
	$aKeys_correccion = str_getcsv($correccion[0]);
	$aCorreccion      = array();
	for ($x=1;$x<count($correccion);$x++) {
		$aCorreccion[$x-1] = array_combine($aKeys_correccion,str_getcsv($correccion[$x]));
	}
	return $aCorreccion;
}

function sinergia_AF5_baremo_buscar_centil($baremo,$nombre_dimension,$puntaje) {
	$centil = 0;
	for ($x=0;$x<count($baremo);$x++) {
		if ($baremo[$x][$nombre_dimension.'-min'] <= $puntaje && $baremo[$x][$nombre_dimension.'-max'] >= $puntaje) {
			$centil = $baremo[$x]['Centil'];
			$x=count($baremo);
		}
	}
	return $centil;
}

function sinergia_AF5_buscar_nivel($centil) {
	global $AF5_Niveles;
	
	$nivel = "Descartados";
	for ($x=0;$x<count($AF5_Niveles);$x++) {
		if ($AF5_Niveles[$x]['centil_min'] <= $centil && $AF5_Niveles[$x]['centil_max'] >= $centil) {
			$nivel = $AF5_Niveles[$x]['nombre'];
		}
	}
	return $nivel;	
}

function sinergia_AF5_buscar_clase($nivel) {
	global $AF5_Niveles;
	
	for ($x=0;$x<count($AF5_Niveles);$x++) {
		if ($AF5_Niveles[$x]['nombre'] == $nivel) { $clase = $AF5_Niveles[$x]['clase']; }
	}
	return $clase;
}

function sinergia_ACRA($respuestas) {
	global $ACRA_Escalas;
	
	$escalas = $ACRA_Escalas;
	
	$baremo['Adquisición']   = sinergia_baremo_cargar("acra","escala_adquisicion");
	$baremo['Codificación']  = sinergia_baremo_cargar("acra","escala_codificacion");
	$baremo['Recuperación']  = sinergia_baremo_cargar("acra","escala_recuperacion");
	$baremo['Procesamiento'] = sinergia_baremo_cargar("acra","escala_apoyo_al_procesamiento");

	for ($x=0;$x<count($respuestas);$x++) {
		$punt_escala = array();
		for ($y=0;$y<count($escalas);$y++) {
			$preguntas_escala = explode(",",$escalas[$y]["preguntas"]);
			$suma_punt = 0;
			for ($z=0;$z<count($preguntas_escala);$z++) {
				$preg = $preguntas_escala[$z];
				$suma_punt += $respuestas[$x][$preg];
			}
			$nombre_escala = $escalas[$y]["nombre"];
			$tipo_baremo = $escalas[$y]["alias"];
			$punt_escala["puntaje_$tipo_baremo"] = $suma_punt;
			$punt_escala["centil_$tipo_baremo"]  = sinergia_ACRA_baremo_buscar_centil($baremo[$tipo_baremo],$punt_escala["puntaje_$tipo_baremo"]);
			$punt_escala["nivel_$tipo_baremo"]   = sinergia_ACRA_buscar_nivel($punt_escala["centil_$tipo_baremo"]);
			$punt_escala["clase_$tipo_baremo"]   = sinergia_ACRA_buscar_clase($punt_escala["nivel_$tipo_baremo"]);
		}
		$respuestas[$x] = array_merge($respuestas[$x],$punt_escala);
	}
	return $respuestas;
}

function sinergia_ACRA_resumen($respuestas) {
	global $ACRA_Niveles, $ACRA_Escalas;
	
	$niveles = array();
	for ($x=0;$x<count($ACRA_Escalas);$x++) {
		//$nombre_escala       = $ACRA_Escalas[$x]['nombre'];
		$nombre_escala_alias = $ACRA_Escalas[$x]['alias'];
		$nombre_escala       = ucfirst($ACRA_Escalas[$x]['alias']);
		$niveles[$nombre_escala] = array_count_values(array_column($respuestas,"nivel_$nombre_escala_alias"));
		$niveles[$nombre_escala."_clase"] = array_count_values(array_column($respuestas,"clase_$nombre_escala_alias"));
		$aNiveles = array();
		for($y=0;$y<count($ACRA_Niveles);$y++) {
			$nombre_nivel = $ACRA_Niveles[$y]['nombre'];
			$aNiveles[$nombre_nivel] = $niveles[$nombre_escala][$nombre_nivel];
		}
		$niveles[$nombre_escala] = $aNiveles;
	}
	return $niveles;
}

function sinergia_ACRA_resumen_json($respuestas) {
	global $ACRA_Niveles, $ACRA_Escalas;
	
	$niveles = array();
	for ($x=0;$x<count($ACRA_Escalas);$x++) {
		$nombre_escala       = $ACRA_Escalas[$x]['nombre'];
		$nombre_escala_alias = $ACRA_Escalas[$x]['alias'];
		$niveles[$x]['escala'] = ucfirst($nombre_escala_alias);
		$freq_niveles = array_count_values(array_column($respuestas,"nivel_$nombre_escala_alias"));
		$aNiveles = array();
		for($y=count($ACRA_Niveles)-1;$y>=0;$y--) {
			$nombre_nivel = $ACRA_Niveles[$y]['nombre'];
			$aNiveles[$nombre_nivel] = $freq_niveles[$nombre_nivel];
		}
		$niveles[$x] = array_merge($niveles[$x],$aNiveles);
	}
	return json_encode($niveles);
}

function sinergia_ACRA_baremo_buscar_centil($baremo,$puntaje) {
	$centil = 0;
	for ($x=0;$x<count($baremo);$x++) {
		if ($baremo[$x]['Puntaje_directo-min'] <= $puntaje && $baremo[$x]['Puntaje_directo-max'] >= $puntaje) {
			$centil = $baremo[$x]['Centil'];
			$x=count($baremo);
		}
	}
	return $centil;
}

function sinergia_ACRA_buscar_nivel($centil) {
	global $ACRA_Niveles;
	
	$nivel = "Descartados";
	for ($x=0;$x<count($ACRA_Niveles);$x++) {
		if ($ACRA_Niveles[$x]['centil_min'] <= $centil && $ACRA_Niveles[$x]['centil_max'] >= $centil) {
			$nivel = $ACRA_Niveles[$x]['nombre'];
		}
	}
	return $nivel;	
}

function sinergia_ACRA_buscar_clase($nivel) {
	global $ACRA_Niveles;
	
	for ($x=0;$x<count($ACRA_Niveles);$x++) {
		if ($ACRA_Niveles[$x]['nombre'] == $nivel) { $clase = $ACRA_Niveles[$x]['clase']; }
	}
	return $clase;
}

function sinergia_OTIS($respuestas) {
	global $OTIS_Areas;
	
	$areas = $OTIS_Areas;
	
	$baremo['mujeres'] = sinergia_baremo_cargar("otis","mujeres_administrativas");
	$baremo['varones'] = sinergia_baremo_cargar("otis","varones_subalternos");
	
	$correccion = sinergia_correccion_cargar("OTIS");

	for ($x=0;$x<count($respuestas);$x++) {
		$genero    = $respuestas[$x]['genero'];
		$punt_area = array();
		for ($y=0;$y<count($areas);$y++) {
			$preguntas_area = explode(",",$areas[$y]["preguntas"]);
			$suma_punt = 0;
			for ($z=0;$z<count($preguntas_area);$z++) {
				$preg = $preguntas_area[$z];
				$puntaje = sinergia_OTIS_corregir_pregunta($correccion,$preg,$respuestas[$x][$preg]);
				$suma_punt += $puntaje;
			}
			$nombre_area = $areas[$y]["nombre"];
			$punt_area["puntaje_$nombre_area"] = $suma_punt;
			if ($nombre_area == "General") {
				$punt_area["centil_$nombre_area"] = sinergia_OTIS_baremo_buscar_centil($baremo[$genero],$punt_area["puntaje_$nombre_area"]);
			} else {
				$punt_area["centil_$nombre_area"] = round(($punt_area["puntaje_$nombre_area"]/15)*100,0);
			}
			$punt_area["nivel_$nombre_area"]  = sinergia_OTIS_buscar_nivel($punt_area["centil_$nombre_area"]);
			$punt_area["clase_$nombre_area"]  = sinergia_OTIS_buscar_clase($punt_area["nivel_$nombre_area"]);
		}
		$respuestas[$x] = array_merge($respuestas[$x],$punt_area);
	}
	return $respuestas;
}

function sinergia_OTIS_resumen($respuestas) {
	global $OTIS_Niveles, $OTIS_Areas;
	
	$niveles = array();
	for ($x=0;$x<count($OTIS_Areas);$x++) {
		$nombre_area           = $OTIS_Areas[$x]['nombre'];
		$niveles[$nombre_area] = array_count_values(array_column($respuestas,"nivel_$nombre_area"));
		$niveles[$nombre_area."_clase"] = array_count_values(array_column($respuestas,"clase_$nombre_area"));
		$aNiveles = array();
		for($y=0;$y<count($OTIS_Niveles);$y++) {
			$nombre_nivel = $OTIS_Niveles[$y]['nombre'];
			$aNiveles[$nombre_nivel] = $niveles[$nombre_area][$nombre_nivel];
		}
		$niveles[$nombre_area] = $aNiveles;
	}
	return $niveles;
}

function sinergia_OTIS_resumen_json($respuestas) {
	global $OTIS_Niveles, $OTIS_Areas;
	
	$niveles = array();
	for ($x=0;$x<count($OTIS_Areas);$x++) {
		$nombre_area         = $OTIS_Areas[$x]['nombre'];
		$niveles[$x]['area'] = $nombre_area;
		$freq_niveles        = array_count_values(array_column($respuestas,"nivel_$nombre_area"));
		$aNiveles = array();
		for($y=count($OTIS_Niveles)-1;$y>=0;$y--) {
			$nombre_nivel = $OTIS_Niveles[$y]['nombre'];
			$aNiveles[$nombre_nivel] = $freq_niveles[$nombre_nivel];
		}
		$niveles[$x] = array_merge($niveles[$x],$aNiveles);
	}
	return json_encode($niveles);
}

function sinergia_OTIS_corregir_pregunta($correccion,$pregunta,$respuesta) {	
	$puntaje = 0;
	for ($x=0;$x<count($correccion);$x++) {
		if ($respuesta == $correccion[$x]["respuesta"] && $pregunta == $correccion[$x]["pregunta"]) {
			$puntaje = 1;
			$x = count($correccion);
		}
	}
	return $puntaje;
}

function sinergia_OTIS_baremo_buscar_centil($baremo,$puntaje) {
	$centil = 0;
	for ($x=0;$x<count($baremo);$x++) {
		if ($baremo[$x]["Puntaje_min"] <= $puntaje && $baremo[$x]["Puntaje_max"] >= $puntaje) {
			$centil = $baremo[$x]['Centil'];
			$x=count($baremo);
		}
	}
	return $centil;
}

function sinergia_OTIS_buscar_nivel($centil) {
	global $OTIS_Niveles;
	
	$nivel = "Descartados";
	for ($x=0;$x<count($OTIS_Niveles);$x++) {
		if ($OTIS_Niveles[$x]['centil_min'] <= $centil && $OTIS_Niveles[$x]['centil_max'] >= $centil) {
			$nivel = $OTIS_Niveles[$x]['nombre'];
		}
	}
	return $nivel;	
}

function sinergia_OTIS_buscar_clase($nivel) {
	global $OTIS_Niveles;
	
	for ($x=0;$x<count($OTIS_Niveles);$x++) {
		if ($OTIS_Niveles[$x]['nombre'] == $nivel) { $clase = $OTIS_Niveles[$x]['clase']; }
	}
	return $clase;
}

function flipDiagonally($arr) {
    $out = array();
    foreach ($arr as $key => $subarr) {
    	foreach ($subarr as $subkey => $subvalue) {
    		$out[$subkey][$key] = $subvalue;
    	}
    }
    return $out;
}

function sinergia_HTML_cuadro_resumen($prueba,$aResumen_prueba,$nombre_categorias,$titulo_cuadro,&$tot_cat = array()) {
	global $enl_nav, $enlbase, $enlbase_sm, $modulo;
	
	$cant_cat = 0;
	
	$HTML = "<table bgcolor='#ffffff' cellspacing='0' cellpadding='2' class='tabla' style='margin-top: 5px;'>\n";
	
	
	$HTML_cab = "  <tr class='filaTituloTabla'>\n";
	foreach ($aResumen_prueba AS $categoria => $niveles) {
		if (substr($categoria,-6) == "_clase") { 
			unset($aResumen_prueba[$categoria]); 
		} else {
			$categoria = str_replace("/","/<br>",$categoria);
			$HTML_cab .= "    <td class='tituloTabla' style='min-width: 70px'>$categoria</td>\n";
			$cant_cat++;
		}
	}
	$HTML_cab .= "  </tr>\n";
	$HTML_cab = "  <tr class='filaTituloTabla'>\n"
	          . "    <td class='tituloTabla' rowspan='2'>Nivel</td>\n"
	          . "    <td class='tituloTabla' colspan='$cant_cat'>$nombre_categorias</td>\n"
	          . "  </tr>\n"
	          . $HTML_cab;
	
	$aResumen_prueba = flipDiagonally($aResumen_prueba);

	$cols = $cant_cat + 1;
	$HTML .= "  <tr class='filaTituloTabla'>\n"
	      .  "    <td class='tituloTabla' colspan='$cols' style='text-align: left'>$titulo_cuadro</td>\n"
	      .  "  </tr>\n"
	      . $HTML_cab; 

	$HTML .= "  <tr class='filaTabla'>\n";

	foreach ($aResumen_prueba AS $nombre_nivel => $categorias) {
		$HTML .= "  <tr class='filaTabla'>\n"
		      .  "    <td class='celdaRamoMalla' style='font-size: 9pt; text-align: left; min-width: 80px;'>$nombre_nivel</td>\n";
		foreach ($categorias AS $nombre => $casos) { 
			$cant_casos = $casos;
			if ($nombre_nivel <> "Descartados") {
				$tot_cat[$nombre] += $casos; 
				if ($cant_casos > 0) { 
					//$casos = "<a id='sgu_fancybox' href='$enlbase_sm=sinergia_resultados_individual&$enl_nav&prueba=$prueba&nivel=$nombre_nivel&categoria=$nombre&mod_ant=$modulo' class='enlaces'>$cant_casos</a>";
				}
			}
			if ($nombre_nivel == "Descartados" && $casos > 0) { $casos = "($casos)"; }			
			
			$HTML .= "    <td class='celdaRamoMalla' style='font-size: 9pt'>$casos</td>\n";
		}
		$HTML .= "  </tr>\n";
	}
	$HTML .= "  <tr class='filaTabla'>\n"
	      .  "    <td class='celdaNombreAttr' style='vertical-align: middle'><small>Total alumnos:</small></td>\n";
	foreach ($tot_cat AS $total) { $HTML .= "    <td class='celdaNombreAttr' style='text-align: center'>$total</td>\n"; }
	$cant_cat++;
	$HTML .= "  </tr>\n"
	      .  "  <tr>"
	      .  "    <td class='celdaRamoMalla' colspan='$cant_cat' style='text-align: left'>\n"
	      .  "      <!-- <b>Pinche en cada número (cantidad de alumnos) para obtener el listado respectivo</b> -->\n"
	      .  "    </td>\n"
	      .  "  </tr>\n"
	      .  "</table>\n"
	      .  "";
	return $HTML;
}

function sinergia_HTML_cuadro_resumen_porc($prueba,$aResumen_prueba,$nombre_categorias,$titulo_cuadro,$tot_cat) {
	global $enl_nav, $enlbase, $enlbase_sm;
	
	$cant_cat = 0;
	
	$HTML = "<table bgcolor='#ffffff' cellspacing='0' cellpadding='2' class='tabla' style='margin-top: 5px;'>\n";
	
	$HTML_cab = "  <tr class='filaTituloTabla'>\n";
	foreach ($aResumen_prueba AS $categoria => $niveles) {
		if (substr($categoria,-6) == "_clase") { 
			unset($aResumen_prueba[$categoria]); 
		} else {			$categoria = str_replace("/","/<br>",$categoria);
			$HTML_cab .= "    <td class='tituloTabla' style='min-width: 70px'>$categoria</td>\n";
			$cant_cat++;
		}
	}
	$HTML_cab .= "  </tr>\n";
	$HTML_cab = "  <tr class='filaTituloTabla'>\n"
	          . "    <td class='tituloTabla' rowspan='2'>Nivel</td>\n"
	          . "    <td class='tituloTabla' colspan='$cant_cat'>$nombre_categorias</td>\n"
	          . "  </tr>\n"
	          . $HTML_cab;
	
	$aResumen_prueba = flipDiagonally($aResumen_prueba);
	
	$cols = $cant_cat + 1;
	$HTML .= "  <tr class='filaTituloTabla'>\n"
	      .  "    <td class='tituloTabla' colspan='$cols' style='text-align: left'>$titulo_cuadro</td>\n"
	      .  "  </tr>\n"
	      . $HTML_cab;

	foreach ($aResumen_prueba AS $nombre_nivel => $categorias) {
		if ($nombre_nivel <> "Descartados") {
			$HTML .= "  <tr class='filaTabla'>\n"
				  .  "    <td class='celdaRamoMalla' style='font-size: 9pt; text-align: left; min-width: 80px'>$nombre_nivel</td>\n";
			foreach ($categorias AS $nombre => $casos) { 
				$porc = number_format(round(($casos/$tot_cat[$nombre])*100,1),1,",",".")."%";
				if ($casos > 0) { 
					$porc = "<a id='sgu_fancybox_small' href='$enlbase_sm=sinergia_interpretacion_prueba_nivel_categoria&$enl_nav&prueba=$prueba&nivel=$nombre_nivel&categoria=$nombre' class='enlaces'>$porc</a>";
				} else {
					$porc = "";
				}
				$HTML .= "    <td class='celdaRamoMalla' style='font-size: 9pt'>$porc</td>";
			}
			$HTML .= "  </tr>\n";
		}
	}
	$cant_cat++;
	$HTML .= "  <tr class='filaTabla'>"
	      .  "    <td class='celdaNombreAttr' colspan='$cant_cat' style='text-align: left'>\n"
	      .  "      <sup>Pinche en cada porcentaje para obtener descripción y sugerencias de ".strtolower($nombre_categorias)." y niveles</sup>\n"
	      .  "    </td>\n"
	      .  "  </tr>\n"
	      .  "</table>\n"
	      .  "";
	return $HTML;
}

function sinergia_amchart_graphs_json($aResumen_prueba_json,$nombre_categoria) {
	$aResumen_prueba = json_decode($aResumen_prueba_json,true);

	$colores5 = array("Alto"       => '#00BC00',
	                  "Medio alto" => '#B1FF00',
	                  "Medio"      => '#FFFF00',
	                  "Medio bajo" => '#FF9F3E',
	                  "Bajo"       => '#FF0000');

	$colores7 = array("Muy superior"  => '#028302',
	                  "Superior"      => '#8AC700',
	                  "Promedio alto" => '#B1FF00',
	                  "Promedio"      => '#FFFF00',
	                  "Promedio bajo" => '#FF9F3E',
	                  "Límite"        => '#E99907',
	                  "Muy bajo"      => '#FF0000');
	
	$graphs_base = array("balloonText"        => "Nivel [[title]] de [[category]]: [[value]] alumno(s)",
	                     "columnWidth"        => 0.5,
	                     "fillAlphas"         => 1,
	                     "labelText"          => "[[percents]]%",
	                     "lineThickness"      => 0,
	                     "type"               => "column");
	$graphs = array();
	foreach ($aResumen_prueba[0] AS $nombre_campo => $valor) {
		if ($nombre_campo <> $nombre_categoria && $nombre_campo <> "Descartados") {
			$color = $colores5[$nombre_campo];
			if ($color == "") { $color = $colores7[$nombre_campo]; }
			
			$graph = array("fillColors"    => "$color",
			               "title"         => "$nombre_campo",
	                       "id"            => "$nombre_campo",
	                       "valueField"    => "$nombre_campo");
			$graph = array_merge($graphs_base,$graph);
			$graphs = array_merge($graphs,array($graph));
		}
	}
	return json_encode($graphs);
}

function sinergia_resumen_porc($aResumen_prueba) {
	$totales = array();
	foreach ($aResumen_prueba AS $categoria => $niveles) {
		$totales[$categoria] = array_sum($niveles);
	}
	
	$aNiveles = array();
	foreach ($aResumen_prueba AS $categoria => $niveles) {
		foreach ($niveles AS $nivel => $valor) {
			if ($nivel <> "Descartados") { $aNiveles[$categoria][$nivel] = round($valor/$totales[$categoria]*100,1); }
		}
	}
	
	return $aNiveles;
}

function sinergia_HTML_informe_analitico($aResumen_prueba_porc,$prueba) {
	//var_dump($aResumen_prueba_porc);
	$mayorias = array();
	foreach ($aResumen_prueba_porc AS $categoria => $niveles) {	
		if (substr($categoria,-6) == "_clase") {
			$cat = substr($categoria,0,-6);
			//var_dump($niveles);
			//$niveles = arsort($niveles);
			arsort($niveles);			
			$mayorias[$cat]["nivel"] = key($niveles);
			$mayorias[$cat]["porc"]  = current($niveles);
		}
	}
	//var_dump($mayorias);
	
	$SQL_interpretaciones = "SELECT i.* 
	                         FROM sinergia.interpretaciones AS i
	                         LEFT JOIN sinergia.pruebas AS p ON p.id=i.id_prueba
	                         WHERE p.alias='$prueba' AND tipo='analítico'";
	$interpretaciones = consulta_sql($SQL_interpretaciones);
	
	$HTML = "";
	foreach ($mayorias AS $categoria => $clase_mayor) {
		extract($clase_mayor);
		for ($x=0;$x<count($interpretaciones);$x++) {
			extract($interpretaciones[$x],EXTR_PREFIX_ALL,"i");
			if ($categoria == $i_categoria && $nivel == $i_nivel) {
				$porc_mayoria = "$porc";
				eval("\$i_descripcion = \"$i_descripcion\";");
				$i_descripcion = nl2br($i_descripcion);
				$HTML .= "<b>$i_categoria</b><br>$i_descripcion<br><br>";
				$x = count($interpretaciones);
			}
		}
	}
	return $HTML;
}

//print_r(sinergia_ACRA(sinergia_respuestas('ACRA',2014,1,'')));
//print_r(sinergia_AF5(sinergia_respuestas('AF5',2014,1,'')));
//echo(var_dump(array_count_values(array_column(sinergia_AF5(sinergia_respuestas('AF5',2014,1,'')),'nivel_Emocional'))));
//print_r(sinergia_AF5_resumen(sinergia_AF5(sinergia_respuestas('AF5',2014,1,''))));
//print_r(sinergia_ACRA_resumen(sinergia_ACRA(sinergia_respuestas('ACRA',2014,1,''))));
//echo(var_dump(sinergia_AF5_baremo_cargar("mujeres_edmedia")));
//echo(var_dump(sinergia_AF5_baremo_buscar(sinergia_AF5_baremo_cargar("mujeres_edmedia"),'Emocional',5,1166666666667)));
//print_r(sinergia_OTIS(sinergia_respuestas('OTIS_sencillo',2014,1,'')));
//print_r(sinergia_respuestas('OTIS_sencillo',2014,1,''));
//print_r(sinergia_OTIS_corregir_pregunta(sinergia_correccion_cargar('OTIS'),"p1",4));
//print_r(sinergia_OTIS_resumen(sinergia_OTIS(sinergia_respuestas('OTIS_sencillo',2014,1,''))));

?>
