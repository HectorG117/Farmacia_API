document.addEventListener("DOMContentLoaded", () => {
    // seleccionamos productos y botones con tolerancia a varias clases posibles
    const productos = Array.from(document.querySelectorAll('.producto'));
    const botonSelectores = ['.filtrar-categoria', '.categoria-btn', '.filtro-btn', '.categoria'];
    const botones = Array.from(document.querySelectorAll(botonSelectores.join(',')));
    const buscador = document.getElementById('buscador');

    // --- Configuración de efectos (tontito) ---
    const HOVER_SCALE = 'scale(1.04)';
    const NORMAL_SCALE = 'scale(1)';
    const HOVER_BG = '#dbeafe';          // azul claro
    const NORMAL_BG = '';               // dejar que el CSS original gobierne
    const HOVER_SHADOW = '0 10px 20px rgba(0,0,0,0.25)';
    const NORMAL_SHADOW = '';           // dejar que el CSS original gobierne
    const TRANSITION = 'transform 180ms ease, box-shadow 180ms ease, background-color 180ms ease';

    // Aplicar listeners a cada producto (hover, click y accesibilidad keyboard)
    productos.forEach(prod => {
        // aseguramos estilos iniciales
        prod.style.transition = TRANSITION;
        // hover in
        prod.addEventListener('mouseenter', () => {
            prod.style.transform = HOVER_SCALE;
            prod.style.backgroundColor = HOVER_BG;
            prod.style.boxShadow = HOVER_SHADOW;
        });
        // hover out
        prod.addEventListener('mouseleave', () => {
            prod.style.transform = NORMAL_SCALE;
            prod.style.backgroundColor = NORMAL_BG;
            prod.style.boxShadow = NORMAL_SHADOW;
        });

        // hacer foco navegable y reaccionar a Enter (accesibilidad)
        prod.setAttribute('tabindex', prod.getAttribute('tabindex') ?? '0');
        prod.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                prod.click();
            }
        });

        // Buscar producto por nombre
    function buscarProducto() {
        const texto = buscador.value.toLowerCase();
        for (let producto of productos) {
            const nombre = producto.textContent.toLowerCase();
            if (nombre.includes(texto)) {
                producto.style.display = 'flex';
            } else {
                producto.style.display = 'none';
            }
        }
    }

     // Evento de búsqueda
     buscador.addEventListener('keyup', buscarProducto);

        // click: construir URL hacia /producto
        prod.addEventListener('click', () => {
            let nombre = prod.dataset.nombre ?? '';
            let precio = prod.dataset.precio ?? '';
            let descripcion = prod.dataset.descripcion ?? '';
            let imagen = prod.dataset.imagen ?? '';

            // normalizamos la ruta de la imagen
            // Si imagen ya parece una ruta (empieza con '/', 'http' o contiene 'pictures/'), la usamos tal cual.
            // Si es solo el nombre del archivo (ej: paracetamol.webp), construimos la ruta en public/pictures/Productos/
            imagen = imagen.trim();
            const looksLikePath = imagen.startsWith('/') || imagen.startsWith('http') || imagen.toLowerCase().includes('pictures/');
            const imagenParaUrl = looksLikePath ? imagen : `pictures/Productos/${imagen}`;

            const params = new URLSearchParams();
            params.set('nombre', nombre);
            params.set('precio', precio);
            params.set('descripcion', descripcion);
            params.set('imagen', imagenParaUrl);

            window.location.href = `/producto?${params.toString()}`;
        });
    });

    // --- Función para filtrar productos (tontito) ---
    function filterByCategory(cat) {
        const wanted = (cat || 'todos').toString().toLowerCase();

        productos.forEach(prod => {
            const pcat = (prod.dataset.categoria || '').toString().toLowerCase();

            if (wanted === 'todos' || pcat === wanted) {
                // restaurar display dejándolo vacío para que el grid/CSS controle el layout
                prod.style.display = '';
            } else {
                prod.style.display = 'none';
            }
        });
    }

    // conectamos los botones (tontito)
    botones.forEach(btn => {
        // prevenir comportamiento inesperado si es <a>
        btn.addEventListener('click', (e) => {
            if (e) e.preventDefault();
            // soportar data-cat o data-categoria según como estén definidos
            const cat = btn.dataset.cat ?? btn.dataset.categoria ?? btn.getAttribute('data-cat') ?? btn.getAttribute('data-categoria') ?? btn.textContent.trim();
            filterByCategory(cat.toString().toLowerCase());
            // añadir clase activa visual (opcional)
            botones.forEach(b => b.classList.remove('opacity-90', 'ring-2', 'ring-offset-2'));
            btn.classList.add('opacity-90', 'ring-2', 'ring-offset-2');
        });
        // permitir activar con Enter si es foco
        btn.setAttribute('tabindex', btn.getAttribute('tabindex') ?? '0');
        btn.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                btn.click();
            }
        });
    });

    // por defecto mostramos 'todos'
    filterByCategory('todos');
});