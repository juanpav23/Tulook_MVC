// ===============================
// Archivo: ver.js (versión FINAL corregida)
// ===============================
document.addEventListener("DOMContentLoaded", () => {
  const { usuarioLogueado, variantes, baseTallas, productoBase, baseUrl, esFavorito } = PRODUCTO_DATA;

  // === Elementos DOM ===
  const colorChips = document.querySelectorAll('#color-container .chip');
  const tallaContainer = document.getElementById('talla-container');
  const mainImg = document.getElementById('main-img');
  const precioText = document.getElementById('precio-text');
  const nombreProd = document.getElementById('nombre-producto');
  const cantidadInput = document.getElementById('cantidad');
  const stockInfo = document.getElementById('stock-info');
  const formTipo = document.getElementById('form-tipo');
  const formIdProducto = document.getElementById('form-id-producto');
  const formIdColor = document.getElementById('form-id-color');
  const formNColor = document.getElementById('form-n-color');
  const formCodigoHex = document.getElementById('form-codigo-hex');
  const formIdTalla = document.getElementById('form-id-talla');
  const formCantidad = document.getElementById('form-cantidad');
  const favForm = document.getElementById('fav-form');
  const favIdProd = document.getElementById('fav-id-prod');
  const btnAddCart = document.getElementById('btn-add-cart');
  const btnFavorito = document.querySelector('#fav-form button[type="submit"]');

  // Botones + / -
  const btnPlus = document.getElementById('qty-plus');
  const btnMinus = document.getElementById('qty-minus');

  // === Estado actual ===
  let seleccionActual = {
    tallaSeleccionada: false,
    stockDisponible: 0,
    colorId: productoBase.idColor,
    productoId: productoBase.id,
    tipoActual: 'base', // 'base' o 'variante'
    esFavorito: esFavorito || false // Sincronizar estado de favoritos
  };

  // ---------- SISTEMA DE FAVORITOS DINÁMICO ----------
  function actualizarEstadoFavorito(esFavorito) {
    seleccionActual.esFavorito = esFavorito;
    
    if (btnFavorito) {
      if (esFavorito) {
        btnFavorito.classList.remove('btn-outline-danger');
        btnFavorito.classList.add('btn-danger');
        btnFavorito.innerHTML = '<i class="fa fa-heart"></i> Quitar de Me Gusta';
      } else {
        btnFavorito.classList.remove('btn-danger');
        btnFavorito.classList.add('btn-outline-danger');
        btnFavorito.innerHTML = '<i class="fa fa-heart"></i> Añadir a Me Gusta';
      }
    }
  }

  // Verificar estado actual del favorito
  async function verificarEstadoFavorito() {
    if (!usuarioLogueado) return;

    try {
      const formData = new FormData();
      const idProductoActual = formIdProducto.value || productoBase.id;
      
      if (seleccionActual.tipoActual === 'variante') {
        formData.append('id_producto', idProductoActual);
      } else {
        formData.append('id_articulo', productoBase.id);
      }

      const response = await fetch(`${baseUrl}?c=Favorito&a=verificarEstado`, {
        method: 'POST',
        body: formData
      });
      
      const data = await response.json();
      
      if (data.success) {
        actualizarEstadoFavorito(data.esFavorito);
      }
    } catch (error) {
      console.error('Error verificando favorito:', error);
    }
  }

  // Manejar envío del formulario de favoritos con AJAX
  function setupFavoritosAJAX() {
    if (!favForm || !usuarioLogueado) return;

    favForm.addEventListener('submit', async function(e) {
      e.preventDefault();

      try {
        const formData = new FormData(this);
        const btnOriginal = btnFavorito.innerHTML;
        
        // Mostrar loading
        btnFavorito.disabled = true;
        btnFavorito.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Procesando...';

        const response = await fetch(this.action, {
          method: 'POST',
          body: formData
        });

        if (response.ok) {
          // Invertir el estado localmente
          const nuevoEstado = !seleccionActual.esFavorito;
          actualizarEstadoFavorito(nuevoEstado);
          
          // Mostrar mensaje de confirmación
          const mensaje = nuevoEstado ? 
            '❤️ Agregado a favoritos' : 
            '❌ Eliminado de favoritos';
          
          Swal.fire({
            icon: 'success',
            title: mensaje,
            showConfirmButton: false,
            timer: 1500
          });
        } else {
          throw new Error('Error en la respuesta del servidor');
        }
      } catch (error) {
        console.error('Error en favoritos:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se pudo procesar la acción',
          timer: 2000
        });
        // Re-verificar estado real
        setTimeout(verificarEstadoFavorito, 500);
      } finally {
        btnFavorito.disabled = false;
      }
    });
  }

  // ---------- Helpers ----------
  function setCantidad(val) {
    const max = parseInt(cantidadInput.max || seleccionActual.stockDisponible || Infinity, 10);
    let n = parseInt(String(val).replace(/^0+/, ''), 10);
    if (isNaN(n) || n < 1) n = 1;
    if (isFinite(max) && n > max) n = max;
    cantidadInput.value = n;
    formCantidad.value = n;
    actualizarBotonCarrito();
  }

  function actualizarBotonCarrito() {
    if (!seleccionActual.tallaSeleccionada) {
      btnAddCart.disabled = true;
      btnAddCart.classList.add('btn-cart-disabled');
      btnAddCart.innerHTML = '<i class="fa fa-shopping-cart"></i> Selecciona una talla';
      return;
    }

    if (seleccionActual.stockDisponible === 0) {
      btnAddCart.disabled = true;
      btnAddCart.classList.add('btn-cart-disabled');
      btnAddCart.innerHTML = '<i class="fa fa-times"></i> Sin stock disponible';
      return;
    }

    const cantidad = parseInt(cantidadInput.value, 10) || 1;
    if (cantidad > seleccionActual.stockDisponible) {
      btnAddCart.disabled = true;
      btnAddCart.classList.add('btn-cart-disabled');
      btnAddCart.innerHTML = '<i class="fa fa-times"></i> Stock insuficiente';
      return;
    }

    btnAddCart.disabled = false;
    btnAddCart.classList.remove('btn-cart-disabled');
    btnAddCart.innerHTML = '<i class="fa fa-shopping-cart"></i> Agregar al carrito';
  }

  function actualizarInfoStock(stock) {
    if (stock > 0) {
      stockInfo.textContent = `${stock} unidades disponibles`;
      stockInfo.className = 'stock-info text-success';
      cantidadInput.max = stock;
    } else {
      stockInfo.textContent = 'Sin stock disponible';
      stockInfo.className = 'stock-info text-danger';
      cantidadInput.removeAttribute('max');
    }
    setCantidad(cantidadInput.value);
  }

  // ---------- Render tallas ----------
  function renderTallasBase() {
    tallaContainer.innerHTML = '';
    limpiarSeleccionTalla();

    const tallasBase = baseTallas.filter(t => t.Tipo === 'base');
    
    if (!tallasBase || tallasBase.length === 0) {
      tallaContainer.innerHTML = '<div class="text-muted">No hay tallas disponibles para este producto base.</div>';
      return;
    }

    tallasBase.forEach(t => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'chip';
      btn.textContent = t.N_Talla || 'Talla';
      
      btn.dataset.idTalla = t.ID_Talla;
      btn.dataset.idProducto = t.ID_Producto;
      btn.dataset.tipo = 'base';
      btn.dataset.cantidad = t.Cantidad || 0;

      if ((t.Cantidad || 0) <= 0) {
        btn.classList.add('disabled');
        btn.disabled = true;
        btn.innerHTML += ' <small class="ms-1 text-muted">(Agotado)</small>';
      } else {
        btn.addEventListener('click', (ev) => seleccionarTallaBase(ev, t));
      }

      tallaContainer.appendChild(btn);
    });
  }

  function renderTallasVariantes(colorId) {
    tallaContainer.innerHTML = '';
    limpiarSeleccionTalla();

    const opciones = variantes.filter(v => String(v.ID_Color) === String(colorId));
    
    if (!opciones.length) {
      tallaContainer.innerHTML = '<div class="text-muted">No hay tallas disponibles para este color.</div>';
      return;
    }

    const ejemplo = opciones[0];
    nombreProd.textContent = ejemplo.Nombre_Producto || `${productoBase.nombre} - ${ejemplo.N_Color}`;
    mainImg.src = ejemplo.Foto || mainImg.src;
    precioText.textContent = `$${new Intl.NumberFormat('es-CO').format(ejemplo.Precio_Final)}`;

    opciones.forEach(opt => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'chip';
      btn.textContent = `${opt.N_Talla}`;
      
      btn.dataset.idProducto = opt.ID_Producto;
      btn.dataset.idTalla = opt.ID_Talla;
      btn.dataset.tipo = 'variante';
      btn.dataset.cantidad = opt.Cantidad || 0;

      if ((opt.Cantidad || 0) <= 0) {
        btn.classList.add('disabled');
        btn.disabled = true;
        btn.innerHTML += ' <small class="ms-1 text-muted">(Agotado)</small>';
      } else {
        btn.addEventListener('click', (ev) => seleccionarTallaVariante(ev, opt, colorId));
      }

      tallaContainer.appendChild(btn);
    });
  }

  // ---------- Selecciones ----------
  function limpiarSeleccionTalla() {
    formIdTalla.value = '';
    formIdProducto.value = productoBase.id;
    seleccionActual.tallaSeleccionada = false;
    seleccionActual.stockDisponible = 0;
    stockInfo.textContent = '';
    cantidadInput.removeAttribute('max');
    actualizarBotonCarrito();
  }

  function seleccionarTallaBase(ev, talla) {
    document.querySelectorAll('#talla-container .chip').forEach(c => c.classList.remove('active'));
    const btn = ev.currentTarget;
    btn.classList.add('active');

    formIdProducto.value = talla.ID_Producto;
    formIdColor.value = 'base';
    formIdTalla.value = talla.ID_Talla;
    formTipo.value = 'base';
    
    if (favIdProd) {
      favIdProd.value = talla.ID_Producto;
    }

    seleccionActual.tallaSeleccionada = true;
    seleccionActual.stockDisponible = parseInt(talla.Cantidad || 0, 10);
    seleccionActual.tipoActual = 'base';

    actualizarInfoStock(seleccionActual.stockDisponible);
    actualizarBotonCarrito();
    
    // Verificar estado de favoritos cuando cambia la selección
    verificarEstadoFavorito();
  }

  function seleccionarTallaVariante(ev, opcion, colorId) {
    document.querySelectorAll('#talla-container .chip').forEach(c => c.classList.remove('active'));
    const btn = ev.currentTarget;
    btn.classList.add('active');

    formIdProducto.value = opcion.ID_Producto;
    formIdColor.value = colorId;
    formIdTalla.value = opcion.ID_Talla;
    formTipo.value = 'variante';
    
    if (favIdProd) {
      favIdProd.value = opcion.ID_Producto;
    }

    mainImg.src = opcion.Foto || mainImg.src;
    precioText.textContent = `$${new Intl.NumberFormat('es-CO').format(opcion.Precio_Final)}`;
    nombreProd.textContent = opcion.Nombre_Producto || `${productoBase.nombre} - ${opcion.N_Color} ${opcion.N_Talla}`;

    seleccionActual.tallaSeleccionada = true;
    seleccionActual.stockDisponible = parseInt(opcion.Cantidad || 0, 10);
    seleccionActual.tipoActual = 'variante';

    actualizarInfoStock(seleccionActual.stockDisponible);
    actualizarBotonCarrito();
    
    // Verificar estado de favoritos cuando cambia la selección
    verificarEstadoFavorito();
  }

  // ---------- Eventos colores ----------
  colorChips.forEach(chip => {
    chip.addEventListener('click', () => {
      colorChips.forEach(c => c.classList.remove('active'));
      chip.classList.add('active');

      const id = chip.dataset.id;
      seleccionActual.colorId = id;

      const colorDot = chip.querySelector('.color-dot');
      const nColor = chip.textContent.trim();
      const codigoHex = colorDot ? colorDot.style.backgroundColor : '#cccccc';

      formIdColor.value = id;
      if (formNColor) formNColor.value = nColor;
      if (formCodigoHex) formCodigoHex.value = codigoHex;

      mainImg.src = chip.dataset.foto || mainImg.src;
      nombreProd.textContent = chip.dataset.nombre || nombreProd.textContent;

      limpiarSeleccionTalla();

      if (chip.dataset.base === "1" || id === "base") {
        precioText.textContent = `$${new Intl.NumberFormat('es-CO').format(productoBase.precio)}`;
        formTipo.value = 'base';
        renderTallasBase();
      } else {
        formTipo.value = 'variante';
        renderTallasVariantes(id);
      }
      
      // Verificar estado de favoritos cuando cambia el color
      setTimeout(verificarEstadoFavorito, 100);
    });
  });

  // ---------- Control cantidad ----------
  cantidadInput.addEventListener('keydown', (e) => {
    const allow = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab'];
    if (allow.includes(e.key)) return;
    if (!/^[0-9]$/.test(e.key)) e.preventDefault();
    if (cantidadInput.value.length === 0 && e.key === '0') e.preventDefault();
  });

  cantidadInput.addEventListener('input', () => {
    const raw = String(cantidadInput.value).replace(/\D+/g, '');
    setCantidad(raw || '1');
  });

  btnPlus?.addEventListener('click', () => {
    const max = parseInt(cantidadInput.max || seleccionActual.stockDisponible || Infinity, 10);
    let val = parseInt(cantidadInput.value || '1', 10);
    if (isNaN(val)) val = 1;
    if (val < max) val++;
    setCantidad(val);
  });

  btnMinus?.addEventListener('click', () => {
    let val = parseInt(cantidadInput.value || '1', 10);
    if (isNaN(val) || val <= 1) val = 1;
    else val--;
    setCantidad(val);
  });

  // ---------- Agregar al carrito ----------
  btnAddCart.addEventListener('click', (e) => {
    if (!usuarioLogueado) {
      Swal.fire({
        icon: 'info',
        title: 'Inicia sesión',
        text: 'Debes iniciar sesión para agregar productos al carrito.',
        confirmButtonText: 'Ir al login'
      }).then(() => window.location.href = `${baseUrl}?c=Usuario&a=login`);
      return;
    }

    if (!seleccionActual.tallaSeleccionada) {
      Swal.fire({ 
        icon: 'warning', 
        title: 'Selecciona talla', 
        text: 'Debes elegir una talla antes de agregar al carrito.' 
      });
      return;
    }

    const qty = parseInt(cantidadInput.value.trim(), 10);
    const max = seleccionActual.stockDisponible;

    if (isNaN(qty) || qty <= 0) {
      Swal.fire({ 
        icon: 'warning', 
        title: 'Cantidad inválida', 
        text: 'Solo se permiten números positivos.', 
        timer: 2000 
      });
      setCantidad(1);
      return;
    }

    if (qty > max) {
      Swal.fire({ 
        icon: 'error', 
        title: 'Stock insuficiente', 
        text: `Solo hay ${max} unidades disponibles.` 
      });
      setCantidad(max);
      return;
    }

    formCantidad.value = qty;
    
    Swal.fire({
      icon: 'success',
      title: 'Producto agregado 🛒',
      text: 'El producto se agregó correctamente al carrito.',
      showConfirmButton: false,
      timer: 900
    }).then(() => {
      document.getElementById('add-cart-form').submit();
    });
  });

  // ---------- Inicialización ----------
  function inicializar() {
    // Configurar favoritos
    actualizarEstadoFavorito(seleccionActual.esFavorito);
    setupFavoritosAJAX();
    
    setCantidad(cantidadInput.value || 1);
    
    // Inicializar con artículo base por defecto
    const chipBase = document.querySelector('#color-container .chip[data-base="1"]');
    if (chipBase) {
      const colorDot = chipBase.querySelector('.color-dot');
      const nColor = chipBase.textContent.trim();
      const codigoHex = colorDot ? colorDot.style.backgroundColor : '#cccccc';
      
      if (formNColor) formNColor.value = nColor;
      if (formCodigoHex) formCodigoHex.value = codigoHex;
      
      chipBase.click();
    } else if (colorChips.length > 0) {
      colorChips[0].click();
    } else {
      renderTallasBase();
    }

    // Verificar estado de favoritos al cargar la página
    setTimeout(verificarEstadoFavorito, 500);
  }

  // Iniciar la aplicación
  inicializar();
});