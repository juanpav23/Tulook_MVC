// ==========================
//  Archivo: ver.js
// ==========================

// Variables globales pasadas desde la vista PHP
const usuarioLogueado = window.usuarioLogueado || false;
const variantes = window.variantes || [];
const baseTallas = window.baseTallas || [];
const productoBase = window.productoBase || {};

const colorChips = document.querySelectorAll('#color-container .chip');
const tallaContainer = document.getElementById('talla-container');
const mainImg = document.getElementById('main-img');
const precioText = document.getElementById('precio-text');
const nombreProd = document.getElementById('nombre-producto');
const cantidadInput = document.getElementById('cantidad');
const formTipo = document.getElementById('form-tipo');
const formIdProducto = document.getElementById('form-id-producto');
const formIdColor = document.getElementById('form-id-color');
const formIdTalla = document.getElementById('form-id-talla');
const formCantidad = document.getElementById('form-cantidad');
const favIdProd = document.getElementById('fav-id-prod');
const btnAddCart = document.getElementById('btn-add-cart');

let colorActivo = "base"; // Guarda el color actual
let tallaSeleccionada = null; // Guarda la talla seleccionada

// ==========================
//  Render tallas base
// ==========================
function renderBaseTallas() {
  tallaContainer.innerHTML = '';
  if (!baseTallas.length) {
    tallaContainer.innerHTML = '<div class="text-muted">No hay tallas disponibles para este producto base.</div>';
    return;
  }

  baseTallas.forEach(t => {
    const btn = document.createElement('div');
    btn.className = 'chip';
    btn.textContent = `${t.N_Talla} - $${new Intl.NumberFormat('es-CO').format(productoBase.precio)}`;

    btn.onclick = () => {
      document.querySelectorAll('#talla-container .chip').forEach(c => c.classList.remove('active'));
      btn.classList.add('active');
      formIdProducto.value = t.ID_Producto || productoBase.id;
      formIdColor.value = 'base';
      formIdTalla.value = t.ID_Talla;
      favIdProd.value = t.ID_Producto || productoBase.id;
      cantidadInput.max = t.Cantidad || 10;
      formTipo.value = 'base';
      tallaSeleccionada = t.ID_Talla;
    };
    tallaContainer.appendChild(btn);
  });
}

// ==========================
//  Render tallas por color
// ==========================
function renderTallasForColor(colorId) {
  tallaContainer.innerHTML = '';
  const options = variantes.filter(v => String(v.ID_Color) === String(colorId));

  if (options.length === 0) {
    tallaContainer.innerHTML = '<div class="text-muted">No hay tallas para este color</div>';
    return;
  }

  const ejemplo = options[0];
  nombreProd.textContent = ejemplo.Nombre_Producto || `${productoBase.nombre} - ${ejemplo.N_Color}`;
  mainImg.src = ejemplo.Foto || mainImg.src;
  precioText.textContent = '$' + new Intl.NumberFormat('es-CO').format(ejemplo.Precio_Final);

  options.forEach(opt => {
    const btn = document.createElement('div');
    btn.className = 'chip';
    btn.textContent = `${opt.N_Talla} - $${new Intl.NumberFormat('es-CO').format(opt.Precio_Final)}`;

    btn.onclick = () => {
      document.querySelectorAll('#talla-container .chip').forEach(c => c.classList.remove('active'));
      btn.classList.add('active');
      formIdProducto.value = opt.ID_Producto;
      formIdColor.value = colorId;
      formIdTalla.value = opt.ID_Talla;
      favIdProd.value = opt.ID_Producto;
      mainImg.src = opt.Foto || mainImg.src;
      precioText.textContent = '$' + new Intl.NumberFormat('es-CO').format(opt.Precio_Final);
      nombreProd.textContent = opt.Nombre_Producto || `${productoBase.nombre} - ${opt.N_Color} ${opt.N_Talla}`;
      cantidadInput.max = opt.Cantidad;
      formTipo.value = 'variante';
      tallaSeleccionada = opt.ID_Talla;
    };
    tallaContainer.appendChild(btn);
  });
}

// ==========================
//  Cambio de color
// ==========================
colorChips.forEach(chip => {
  chip.addEventListener('click', () => {
    colorChips.forEach(c => c.classList.remove('active'));
    chip.classList.add('active');
    const id = chip.dataset.id;
    colorActivo = id;
    tallaSeleccionada = null; // Resetear talla al cambiar de color

    mainImg.src = chip.dataset.foto;
    nombreProd.textContent = chip.dataset.nombre;

    if (chip.dataset.base === "1" || id === "base") {
      precioText.textContent = '$' + new Intl.NumberFormat('es-CO').format(productoBase.precio);
      renderBaseTallas();
      return;
    }

    renderTallasForColor(id);
  });
});

// ==========================
//  Agregar al carrito
// ==========================
btnAddCart.addEventListener('click', function () {
  if (!usuarioLogueado) {
    Swal.fire({
      icon: 'info',
      title: 'Inicia sesión',
      text: 'Debes iniciar sesión para agregar productos al carrito.',
      confirmButtonText: 'Ir al login'
    }).then(() => window.location.href = "?c=Usuario&a=login");
    return;
  }

  // Validar cantidad
  const raw = cantidadInput.value.trim();
  if (!/^[1-9]\d*$/.test(raw)) {
    Swal.fire({
      icon: 'warning',
      title: 'Cantidad inválida',
      text: 'Solo se permiten números positivos.',
      timer: 2000,
      showConfirmButton: false
    });
    cantidadInput.value = 1;
    return;
  }

  const qty = parseInt(raw);
  const max = parseInt(cantidadInput.max || 0);
  if (max && qty > max) {
    Swal.fire({
      icon: 'error',
      title: 'Stock insuficiente',
      text: `Solo hay ${max} unidades disponibles.`,
    });
    cantidadInput.value = max;
    return;
  }

  // Validar que haya seleccionado talla válida del color actual
  if (!tallaSeleccionada || formIdColor.value !== colorActivo) {
    Swal.fire({
      icon: 'warning',
      title: 'Selecciona una talla',
      text: 'Debes elegir una talla del color que estás viendo antes de comprar.',
    });
    return;
  }

  // Enviar al carrito
  formCantidad.value = qty;

  Swal.fire({
    icon: 'success',
    title: 'Producto agregado 🛒',
    text: 'El producto se agregó correctamente al carrito.',
    showConfirmButton: false,
    timer: 1800
  }).then(() => {
    document.getElementById('add-cart-form').submit();
    setTimeout(() => window.location.href = "?c=Carrito&a=carrito", 300);
  });
});

// ==========================
//  Inicialización
// ==========================
renderBaseTallas();
