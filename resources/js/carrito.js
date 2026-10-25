
document.addEventListener('DOMContentLoaded', () => {

  // Enlace mediante ById (requisito)
  const formPago = document.getElementById('formPago');
  if (!formPago) {
    console.warn('formEffects: No se encontró #formPago');
    return;
  }

  const campos = formPago.getElementsByClassName('form-input');

  if (!campos || campos.length === 0) {
    console.warn('formEffects: No se encontraron elementos con la clase .form-input dentro de #formPago');
    return;
  }

 
  function aplicarRing(elem) {
    if (!elem) return;
    elem.classList.add('ring-2', 'ring-blue-400', 'ring-offset-1', 'transition-all', 'duration-150');
  }


  function quitarRing(elem) {
    if (!elem) return;
    elem.classList.remove('ring-2', 'ring-blue-400', 'ring-offset-1', 'transition-all', 'duration-150');
  }

  // Aux: decide si mantener ring cuando hay valor
  function tieneValor(elem) {
    try {
      return ('value' in elem) && String(elem.value).trim().length > 0;
    } catch (e) {
      return false;
    }
  }

  // Añadir eventos a cada campo
  for (let i = 0; i < campos.length; i++) {
    const campo = campos[i];

    // MOUSE events
    campo.addEventListener('mouseover', () => aplicarRing(campo));
    campo.addEventListener('mouseout', () => {
      // si tiene foco o valor, no quitar
      const focused = (document.activeElement === campo);
      if (!focused && !tieneValor(campo)) quitarRing(campo);
    });

    // FOCUS / BLUR
    campo.addEventListener('focus', () => aplicarRing(campo));
    campo.addEventListener('blur', () => {
      if (!tieneValor(campo)) quitarRing(campo);
    });

    // Escribir en input (para inputs y textarea)
    campo.addEventListener('input', () => {
      if (tieneValor(campo)) {
        aplicarRing(campo);
      } else {
        // si no tiene focus, quitar
        if (document.activeElement !== campo) quitarRing(campo);
      }
    });

    // Para selects: usar change (se dispara al cambiar opción)
    campo.addEventListener('change', () => {
      if (tieneValor(campo)) aplicarRing(campo);
      else if (document.activeElement !== campo) quitarRing(campo);
    });
  }

});

