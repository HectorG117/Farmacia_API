    function mostrarOcultar1() {
      const respuesta1 = document.getElementById('respuesta1');
      respuesta1.classList.toggle('hidden');
    }
    
    function mostrarOcultar2() {
      const respuesta2 = document.getElementById('respuesta2');
      respuesta2.classList.toggle('hidden');
    }

    function mostrarOcultar3() {
      const respuesta3 = document.getElementById('respuesta3');
      respuesta3.classList.toggle('hidden');
    }

    function mostrarOcultar4() {
      const respuesta4 = document.getElementById('respuesta4');
      respuesta4.classList.toggle('hidden');
    }

    function mostrarOcultar5() {
      const respuesta5 = document.getElementById('respuesta5');
      respuesta5.classList.toggle('hidden');
    }

    const pregunta1 = document.getElementById('pregunta1');
    const pregunta2 = document.getElementById('pregunta2');
    const pregunta3 = document.getElementById('pregunta3');
    const pregunta4 = document.getElementById('pregunta4');
    const pregunta5 = document.getElementById('pregunta5');
    
    pregunta1.addEventListener('click', mostrarOcultar1);
    pregunta2.addEventListener('click', mostrarOcultar2);
    pregunta3.addEventListener('click', mostrarOcultar3);
    pregunta4.addEventListener('click', mostrarOcultar4);
    pregunta5.addEventListener('click', mostrarOcultar5);