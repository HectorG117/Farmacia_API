
document.addEventListener("DOMContentLoaded", () => {
    // Enlace mediante ById (requisito)
    const formPoliticas = document.getElementById("formPoliticas");
    if (!formPoliticas) {
        console.warn("politicas.js: No se encontró #formPoliticas");
        return;
    }

    // Enlace a divisiones mediante ClassName (requisito)
    const cards = formPoliticas.getElementsByClassName("policy-card");

    // --- Funciones explícitas (requisito) ---
    function efectoMouseOver(card) {
        card.style.backgroundColor = "#f0f7ff";
        card.style.boxShadow = "0 6px 18px rgba(0, 120, 255, 0.25)";
        card.style.borderColor = "#82b7ff";
    }

    function efectoMouseOut(card) {
        card.style.backgroundColor = "white";
        card.style.boxShadow = "0 2px 6px rgba(0,0,0,0.1)";
        card.style.borderColor = "#e5e7eb";
    }

    // --- Agregar eventos mediante addEventListener (requisito) ---
    for (let i = 0; i < cards.length; i++) {
        let card = cards[i];

        // Transición aplicada por JS
        card.style.transition = "all 0.25s ease";

        card.addEventListener("mouseover", () => {
            efectoMouseOver(card);
        });

        card.addEventListener("mouseout", () => {
            efectoMouseOut(card);
        });
    }
});


