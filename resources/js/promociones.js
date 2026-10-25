// Evita error si menú no existe
const menu = document.getElementById("menu");
if (menu) menu.addEventListener("change", e => redirigir(e.target.value));

const menuMobile = document.getElementById("menuMobile");
if (menuMobile) menuMobile.addEventListener("change", e => redirigir(e.target.value));

// Lista final de promociones
const promociones = [
  {
    nombre: "Vitamina C 300mg",
    precioNormal: 400,
    precioPromo: 299,
    descuento: "-25% Descuento",
    imagen: window.RUTAS_PROMOCIONES.vitaminaC
  },
  {
    nombre: "Termómetro Digital",
    precioNormal: 220,
    precioPromo: 160,
    descuento: "Ahorra $60",
    imagen: window.RUTAS_PROMOCIONES.termometro
  },
  {
    nombre: "Omeprazol 20mg",
    precioNormal: 130,
    precioPromo: 99,
    descuento: "Descuento especial",
    imagen: window.RUTAS_PROMOCIONES.omeprazol
  }
];

// Crear tarjetas
const contenedor = document.getElementById("promociones");
if (contenedor) {
  promociones.forEach(p => {
    const card = document.createElement("div");
    card.className =
      "bg-white shadow-lg rounded-lg p-4 flex flex-col items-center text-center hover:scale-105 transition transform cursor-pointer w-72";
    card.innerHTML = `
      <img src="${p.imagen}" class="w-48 h-48 object-contain mb-3 rounded">
      <h3 class="font-semibold text-lg text-blue-600">${p.nombre}</h3>
      <p class="text-gray-600 line-through">$${p.precioNormal}</p>
      <p class="text-green-600 text-xl font-bold mb-2">$${p.precioPromo}</p>
      <span class="bg-yellow-400 text-white px-3 py-1 rounded-full font-semibold">${p.descuento}</span>
    `;
    card.addEventListener("click", () => {
      localStorage.setItem("productoSeleccionado", JSON.stringify(p));
      window.location.href = "/producto";
    });
    contenedor.appendChild(card);
  });
}
