document.addEventListener("DOMContentLoaded", () => {
    
    // Obtener todas las secciones
    const cards = document.getElementsByClassName("servicio-card");

    // Función explícita: aplicar efecto
    function aplicarEfectos(card) {
        card.classList.add(
            "scale-103",      // zoom 3%
            "shadow-2xl",     // sombra fuerte
            "rounded-2xl",    // más redondeado
            "border", 
            "border-blue-300"
        );
    }

    function quitarEfectos(card) {
        card.classList.remove(
            "scale-103",
            "shadow-2xl",
            "rounded-2xl",
            "border",
            "border-blue-300"
        );
    }

    // Aplicar transición global a cada card
    Array.from(cards).forEach(card => {
        card.classList.add("transition", "duration-300");

        card.addEventListener("mouseenter", () => aplicarEfectos(card));
        card.addEventListener("mouseleave", () => quitarEfectos(card));
    });

});
