function enblanco2() {
	var problemas=false,campo="", campos=enblanco2.arguments;	
	for(x=0; x<campos.length; x++) {
		if (document.formulario.elements[campos[x]].value == "") {
			problemas = true;
			campo = campos[x];
			break;
		}
	}
	if (problemas) {
		alert("No ha ingresado campos que son requeridos\n(Estos se encuentran subrayados)");
		document.formulario.elements[campo].focus();
		return false;
	} else {
		return true;
	}
}

function submitform() {
	document.formulario.submit();
}

function valida_rut(rut_validar) {
	var campo=rut_validar.name,rut=rut_validar.value,valido=true,dvr=0,resto=0,suma=0,factor=2,rut_aux=0,dv=0,x=0;
	var largo=rut.length;
	rut = rut.toUpperCase();
	if (rut == "") {
		return true;
	}
	if (largo <=2) {
		valido = false;
	} else {
		dv = rut.charAt(largo - 1);
		if (dv != "K" && (dv < 0 && dv > 9)) {
			valido = false;
		} else {
			rut_aux = rut.substring(0,largo-2);
			for(x=rut_aux.length-1;x>=0;x--) {
				suma = suma + (factor * rut_aux.charAt(x));
				factor++;
				if (factor == 8) {
					factor = 2;
				}
			}
			resto = suma % 11;
			dvr = 11 - resto;
			if (dvr == 10) { dvr = "K"; }
			if (dvr == 11) { dvr = "0"; }
			if (dvr != dv) {
				valido = false;
			}
		}
	}
	if (!valido) {
		alert("Este RUT es inválido");
		rut_validar.value="";
		rut_validar.focus();
		rut_validar.select();
		return false;
	} else {
		return true;
	}
}

function val_entrada() {
	var problemas=false,campo="", campos=val_entrada.arguments;	
	for(x=0; x<campos.length; x++) {
		if (document.formulario.elements[campos[x]].value == "") {
			problemas = true;
			campo = campos[x];
			break;
		}
	}
	if (problemas) {
		alert("No ha ingresado todos los datos de autentificacion requeridos");
		document.formulario.elements[campo].focus();
		return false;
	} else {
		return true;
	}
}

var cambios=false;

function cambiado() {
	cambios=true;
}	

function confirmar_guardar() {
	if (cambios) {
		if (!confirm("Esta seguro de guardar los cambios?")) {
			return false;
		}
	}		
	return true;
}

function cancelar_guardar() {
	if (cambios) {
		if (!confirm("Esta seguro de perder los cambios?")) {
			return false;
		}
	}		
	history.back();
}

function confirmar_borrar(enlace,elemento) {
	if (!confirm("Esta seguro de borrar este elemento\n" + elemento + "?")) {
		return false;
	}		
	window.location=enlace;
}

function menu_leyenda(leyenda) {
	document.write(leyenda);
}

function val_nota() {
	var problemas=false,campo="",campos=val_nota.arguments,nota=0;
	for(x=0; x<campos.length; x++) {
		nota=document.formulario.elements[campos[x]].value;
		if ((nota < 4 || nota > 7) && nota != "") {		
			problemas = true;
			campo = campos[x];
			break;
		}
	}
	if (problemas) {
		alert("Valor de calificación (nota) fuera de rango para este campo (1..7)");
		document.formulario.elements[campo].focus();
//		document.formulario.elements[campo].select();
		return false;
	} else {
		return true;
	}	
}

function val_psu(campo) {
	var puntaje=document.formulario.elements[campo].value;
	if (puntaje != "" && (puntaje <= 0 || puntaje >= 900)) {
		alert("Valor de puntaje PSU fuera de rango para este campo (1..900)");
		document.formulario.elements[campo].focus();
//		document.formulario.elements[campo].select();
		return false;
	} else {
		return true;
	}
}

function validar_nota() {
	var problemas=false,campo="",campos=validar_nota.arguments,nota=0;
	for(x=0; x<campos.length; x++) {
		nota=document.formulario.elements[campos[x]].value;
		if (nota == "NSP" || (nota >= 1 && nota <= 7)) {
			problemas = false;
		} else {
			problemas = true;
			campo = campos[x];
			break;
		}
	}
	if (problemas) {
		alert("Valor de calificación (nota) fuera de rango para este campo (1..7 ó NSP) "+campo);
		document.formulario.elements[campo].focus();
		//document.formulario.elements[campo].select();
		return false;
	} else {
		return true;
	}
}

function validar_fecha(fecha) {
	var dia=0, mes=0, ano=0, fecha_aux='', x=0, valido=true;
	var largo=fecha.length;

	if (fecha == "") {
		return true;
	}
	
	if (largo != 10) {
		valido = false;
	} else {
		dia = fecha.substring(0,2);
		mes = fecha.substring(3,2);
		ano = fecha.substring(6,4);
		if (dia<0 || dia>31) { valido=false; }
		if (mes<0 || mes>12) { valido=false; }
		if (ano<2009 || ano>2009) { valido=false; }
	}
	if (!valido) {
		alert('Esta fecha es inválida.'
		     +'\n'
		     +'El formato correcto es DD-MM-AAAA');		
	}
	return valido;
}

function daysInMonth(humanMonth, year) {
	return new Date(year || new Date().getFullYear(), humanMonth, 0).getDate();
}
