  <table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla'>
    <tr>
      <td class='celdaNombreAttr'>RUT:</td>
      <td class='celdaValorAttr'>
        <input type='text' size="12" name="rut" id="rut" onChange="var valor=this.value;this.value=valor.toUpperCase();" tabindex="1">
        <script>formulario.rut.focus();</script>
        <script>document.getElementById("rut").focus();</script>
        <input type="submit" name="validar" value="Validar" onClick="return valida_rut(formulario.rut);">
      </td>
    </tr>
  </table>
