// ===============================
// Archivo: ver.js (CORREGIDO PARA VALORFIJO)
// ===============================
document.addEventListener("DOMContentLoaded", () => {
  const { usuarioLogueado, variantes, baseTallas, productoBase, baseUrl, esFavorito, infoDescuento, todosDescuentos } = PRODUCTO_DATA;

  // === Elementos DOM ===
  const colorChips = document.querySelectorAll('#color-container .chip');
  const tallaContainer = document.getElementById('talla-container');
  const mainImg = document.getElementById('main-img');
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
  const formPrecioFinal = document.getElementById('form-precio-final');
  const favForm = document.getElementById('fav-form');
  const favIdProd = document.getElementById('fav-id-prod');
  const btnAddCart = document.getElementById('btn-add-cart');
  const btnFavorito = document.querySelector('#fav-form button[type="submit"]');
  const btnPlus = document.getElementById('qty-plus');
  const btnMinus = document.getElementById('qty-minus');
  
  // Elementos para descuentos
  const opcionesDescuento = document.querySelectorAll('.opcion-descuento');
  const formCodigoDescuento = document.getElementById('form-codigo-descuento-final');
  const formTipoDescuento = document.getElementById('form-tipo-descuento-final');
  const formValorDescuento = document.getElementById('form-valor-descuento-final');
  const formIdDescuento = document.getElementById('form-id-descuento-final');

  // === Estado actual ===
  let seleccionActual = {
    tallaSeleccionada: false,
    stockDisponible: 0,
    colorId: productoBase.idColor,
    productoId: productoBase.id,
    tipoActual: 'base',
    esFavorito: esFavorito || false,
    precioBase: productoBase.precio,
    precioFinal: productoBase.precio,
    descuentoAplicado: 0,
    descuentoSeleccionado: {
      codigo: '',
      tipo: 'ninguno',
      valor: 0,
      idDescuento: null
    }
  };

  // ---------- SISTEMA DE DESCUENTOS CORREGIDO ----------
  function aplicarDescuentoManual(precioBase, tipoDescuento, valorDescuento) {
    let precioFinal = precioBase;
    let descuentoAplicado = 0;

    console.log('Aplicando descuento manual:', { precioBase, tipoDescuento, valorDescuento });

    if (tipoDescuento === 'Porcentaje' && valorDescuento > 0) {
      // Descuento porcentual
      descuentoAplicado = (precioBase * valorDescuento) / 100;
      precioFinal = precioBase - descuentoAplicado;
    } else if ((tipoDescuento === 'Fijo' || tipoDescuento === 'ValorFijo') && valorDescuento > 0) {
      // Descuento fijo - CORREGIDO: acepta tanto 'Fijo' como 'ValorFijo'
      descuentoAplicado = Math.min(valorDescuento, precioBase);
      precioFinal = precioBase - descuentoAplicado;
    }

    const porcentajeDescuento = (tipoDescuento === 'Porcentaje') 
        ? valorDescuento 
        : (descuentoAplicado / precioBase) * 100;

    console.log('Resultado descuento:', { precioFinal, descuentoAplicado, porcentajeDescuento });

    return {
      precioFinal: Math.max(precioFinal, 0),
      descuento: porcentajeDescuento,
      tieneDescuento: valorDescuento > 0,
      precioOriginal: precioBase,
      descuentoAplicado: descuentoAplicado
    };
  }

  async function aplicarDescuentoAutomatico(productoId, precioBase) {
    try {
      if (!productoId || !precioBase) {
        return getPrecioFallback(precioBase);
      }

      const formData = new FormData();
      formData.append('id_producto', productoId);
      formData.append('precio_base', precioBase);

      const response = await fetch(`${baseUrl}?c=Producto&a=obtenerPrecioConDescuento`, {
        method: 'POST',
        body: formData
      });
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      const text = await response.text();
      let data;
      
      try {
        data = JSON.parse(text);
      } catch (parseError) {
        console.warn('Respuesta no es JSON válido');
        return getPrecioFallback(precioBase);
      }
      
      if (data.success) {
        return {
          precioFinal: parseFloat(data.precioFinal) || precioBase,
          descuento: parseFloat(data.descuentoPorcentaje) || 0,
          tieneDescuento: data.tieneDescuento || false,
          precioOriginal: parseFloat(data.precioOriginal) || precioBase
        };
      } else {
        return getPrecioFallback(precioBase);
      }
    } catch (error) {
      console.error('Error aplicando descuento automático:', error);
      return getPrecioFallback(precioBase);
    }
  }

  function getPrecioFallback(precioBase) {
    return {
      precioFinal: precioBase,
      descuento: 0,
      tieneDescuento: false,
      precioOriginal: precioBase
    };
  }

  function actualizarPrecioVisual(precioFinal, precioOriginal, descuento) {
    const precioFinalElement = document.getElementById('precio-final');
    const precioOriginalElement = document.getElementById('precio-original');
    const descuentoBadgeElement = document.getElementById('descuento-badge');
    const ahorroInfoElement = document.getElementById('ahorro-info');
    
    // Formatear números
    const precioFinalFormateado = new Intl.NumberFormat('es-CO').format(precioFinal);
    const precioOriginalFormateado = new Intl.NumberFormat('es-CO').format(precioOriginal);
    const ahorro = precioOriginal - precioFinal;
    const porcentajeAhorro = ahorro > 0 ? ((ahorro / precioOriginal) * 100).toFixed(1) : 0;
    
    console.log('Actualizando precio visual:', { precioFinal, precioOriginal, descuento, ahorro });
    
    if (descuento > 0 && ahorro > 0) {
        precioFinalElement.textContent = `$${precioFinalFormateado}`;
        precioFinalElement.className = 'precio-final fw-bold';
        
        precioOriginalElement.textContent = `$${precioOriginalFormateado}`;
        precioOriginalElement.style.display = 'inline';
        
        descuentoBadgeElement.innerHTML = `<i class="fas fa-tag me-1"></i>-${descuento.toFixed(1)}%`;
        descuentoBadgeElement.style.display = 'inline-block';
        
        ahorroInfoElement.innerHTML = `<i class="fas fa-bolt me-1"></i>Ahorras $${new Intl.NumberFormat('es-CO').format(ahorro)} (${porcentajeAhorro}%)`;
        ahorroInfoElement.style.display = 'block';
    } else {
        precioFinalElement.textContent = `$${precioFinalFormateado}`;
        precioFinalElement.className = 'precio-final fw-bold';
        
        precioOriginalElement.style.display = 'none';
        descuentoBadgeElement.style.display = 'none';
        ahorroInfoElement.style.display = 'none';
    }

    if (formPrecioFinal) {
      formPrecioFinal.value = precioFinal;
    }

    seleccionActual.precioBase = precioOriginal;
    seleccionActual.precioFinal = precioFinal;
    seleccionActual.descuentoAplicado = descuento;
  }

  // ---------- SISTEMA DE SELECCIÓN DE DESCUENTOS ----------
  function inicializarSelectorDescuentos() {
    if (!usuarioLogueado || opcionesDescuento.length === 0) return;

    opcionesDescuento.forEach(opcion => {
      opcion.addEventListener('click', function() {
        // Remover clase activa de todas las opciones
        opcionesDescuento.forEach(o => {
          o.classList.remove('activa');
          const badge = o.querySelector('.badge');
          const button = o.querySelector('button');
          if (badge) {
            badge.textContent = '';
            badge.className = 'badge bg-secondary px-2';
          }
          if (button) {
            button.textContent = 'Seleccionar';
            button.className = 'btn btn-outline-success btn-sm btn-descuento';
          }
        });

        // Agregar clase activa a la opción seleccionada
        this.classList.add('activa');
        
        // Actualizar botones
        const badge = this.querySelector('.badge');
        const button = this.querySelector('button');
        if (badge) {
          badge.textContent = 'Seleccionado';
          badge.className = 'badge bg-success px-2';
        }
        if (button) {
          button.textContent = 'Seleccionado';
          button.className = 'btn btn-success btn-sm btn-descuento';
        }

        // Obtener datos del descuento
        const codigo = this.dataset.codigo || '';
        const tipo = this.dataset.tipo || 'ninguno';
        const valor = parseFloat(this.dataset.valor) || 0;
        const idDescuento = this.dataset.idDescuento || null;

        console.log('Descuento seleccionado:', { codigo, tipo, valor, idDescuento });

        // Actualizar estado
        seleccionActual.descuentoSeleccionado = {
          codigo,
          tipo,
          valor,
          idDescuento
        };

        // Actualizar campos ocultos del formulario
        if (formCodigoDescuento) formCodigoDescuento.value = codigo;
        if (formTipoDescuento) formTipoDescuento.value = tipo;
        if (formValorDescuento) formValorDescuento.value = valor;
        if (formIdDescuento) formIdDescuento.value = idDescuento;

        // Recalcular precio con el descuento seleccionado
        recalcularPrecioConDescuento();
      });
    });
  }

  function recalcularPrecioConDescuento() {
    const precioBase = seleccionActual.precioBase;
    const { tipo, valor } = seleccionActual.descuentoSeleccionado;

    console.log('Recalculando precio con descuento:', { precioBase, tipo, valor });

    if (tipo === 'ninguno' || valor === 0) {
      // Mostrar precio base sin descuento
      console.log('Sin descuento aplicado');
      actualizarPrecioVisual(precioBase, precioBase, 0);
    } else {
      // Aplicar descuento manual (corregido)
      const infoPrecio = aplicarDescuentoManual(precioBase, tipo, valor);
      console.log('Precio con descuento aplicado:', infoPrecio);
      actualizarPrecioVisual(infoPrecio.precioFinal, infoPrecio.precioOriginal, infoPrecio.descuento);
    }
  }

  // ---------- SISTEMA DE FAVORITOS ----------
  function actualizarEstadoFavorito(esFav) {
    seleccionActual.esFavorito = esFav;
  
    if (btnFavorito) {
      if (esFav) {
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
      
      if (!response.ok) return;
      
      const texto = await response.text();
      let data;
      
      try {
        data = JSON.parse(texto);
      } catch (parseError) {
        return;
      }
      
      if (data.success) {
        actualizarEstadoFavorito(data.esFavorito);
      }
    } catch (error) {
      console.warn('Error verificando favorito:', error.message);
    }
  }

  function setupFavoritosAJAX() {
    if (!favForm || !usuarioLogueado) return;

    favForm.addEventListener('submit', async function (e) {
      e.preventDefault();

      try {
        const formData = new FormData(this);
        btnFavorito.disabled = true;
        btnFavorito.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Procesando...';

        const response = await fetch(`${baseUrl}?c=Favorito&a=toggleAjax`, {
          method: 'POST',
          body: formData
        });

        const data = await response.json();

        if (data.success) {
          actualizarEstadoFavorito(data.esFavorito);

          Swal.fire({
            icon: 'success',
            title: data.action === 'added' ? '❤️ Agregado a favoritos' : '❌ Eliminado de favoritos',
            showConfirmButton: false,
            timer: 1000
          });
        }
      } catch (error) {
        console.error('Error en favoritos:', error);
      } finally {
        btnFavorito.disabled = false;
        btnFavorito.innerHTML = seleccionActual.esFavorito ?
          '<i class="fa fa-heart"></i> Quitar de Me Gusta' :
          '<i class="fa fa-heart"></i> Añadir a Me Gusta';
      }
    });
  }

  // ---------- HELPERS ----------
  function setCantidad(val) {
    const max = parseInt(cantidadInput.max || seleccionActual.stockDisponible || 999, 10);
    let n = parseInt(String(val).replace(/^0+/, ''), 10);
    if (isNaN(n) || n < 1) n = 1;
    if (n > max) n = max;
    cantidadInput.value = n;
    formCantidad.value = n;
    actualizarBotonCarrito();
  }

  function actualizarBotonCarrito() {
    if (!seleccionActual.tallaSeleccionada) {
      btnAddCart.disabled = true;
      btnAddCart.innerHTML = '<i class="fa fa-shopping-cart"></i> Selecciona una talla';
      return;
    }

    if (seleccionActual.stockDisponible === 0) {
      btnAddCart.disabled = true;
      btnAddCart.innerHTML = '<i class="fa fa-times"></i> Sin stock disponible';
      return;
    }

    const cantidad = parseInt(cantidadInput.value, 10) || 1;
    if (cantidad > seleccionActual.stockDisponible) {
      btnAddCart.disabled = true;
      btnAddCart.innerHTML = '<i class="fa fa-times"></i> Stock insuficiente';
      return;
    }

    btnAddCart.disabled = false;
    btnAddCart.innerHTML = '<i class="fa fa-shopping-cart"></i> Agregar al carrito';
  }

  function actualizarInfoStock(stock) {
    if (stock > 0) {
      stockInfo.textContent = `${stock} unidades disponibles`;
      stockInfo.className = 'text-success';
      cantidadInput.max = stock;
    } else {
      stockInfo.textContent = 'Sin stock disponible';
      stockInfo.className = 'text-danger';
      cantidadInput.removeAttribute('max');
    }
    setCantidad(cantidadInput.value);
  }

  // ---------- TALLAS ----------
  function renderTallasBase() {
    tallaContainer.innerHTML = '';
    limpiarSeleccionTalla();

    const tallasBase = baseTallas.filter(t => t.Tipo === 'base');

    if (!tallasBase.length) {
      tallaContainer.innerHTML = '<div class="text-muted">No hay tallas disponibles</div>';
      return;
    }

    tallasBase.forEach(t => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'chip';
      btn.textContent = t.N_Talla;

      btn.dataset.idTalla = t.ID_Talla;
      btn.dataset.idProducto = t.ID_Producto;
      btn.dataset.tipo = 'base';
      btn.dataset.cantidad = t.Cantidad || 0;

      if (t.Cantidad <= 0) {
        btn.classList.add('disabled');
        btn.disabled = true;
        btn.innerHTML += ' <small class="ms-1 text-muted">(Agotado)</small>';
      } else {
        btn.addEventListener('click', (ev) => seleccionarTallaBase(ev, t));
      }

      tallaContainer.appendChild(btn);
    });
  }

  async function renderTallasVariantes(colorId) {
    tallaContainer.innerHTML = '';
    limpiarSeleccionTalla();

    const opciones = variantes.filter(v => String(v.ID_Color) === String(colorId));

    if (!opciones.length) {
      tallaContainer.innerHTML = '<div class="text-muted">No hay tallas disponibles</div>';
      return;
    }

    const ejemplo = opciones[0];
    nombreProd.textContent = ejemplo.Nombre_Producto;
    mainImg.src = ejemplo.Foto;

    // Obtener precio base para este producto
    const precioBaseVariante = ejemplo.Precio_Final;
    
    // Aplicar descuento automático inicial
    const info = await aplicarDescuentoAutomatico(ejemplo.ID_Producto, precioBaseVariante);
    
    // Luego aplicar descuento seleccionado por el usuario si existe
    if (seleccionActual.descuentoSeleccionado.valor > 0) {
      const infoConDescuentoManual = aplicarDescuentoManual(
        info.precioOriginal, 
        seleccionActual.descuentoSeleccionado.tipo, 
        seleccionActual.descuentoSeleccionado.valor
      );
      actualizarPrecioVisual(infoConDescuentoManual.precioFinal, infoConDescuentoManual.precioOriginal, infoConDescuentoManual.descuento);
    } else {
      actualizarPrecioVisual(info.precioFinal, info.precioOriginal, info.descuento);
    }

    opciones.forEach(opt => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'chip';
      btn.textContent = opt.N_Talla;

      btn.dataset.idProducto = opt.ID_Producto;
      btn.dataset.idTalla = opt.ID_Talla;
      btn.dataset.tipo = 'variante';
      btn.dataset.cantidad = opt.Cantidad || 0;

      if (opt.Cantidad <= 0) {
        btn.classList.add('disabled');
        btn.disabled = true;
        btn.innerHTML += ' <small class="ms-1 text-muted">(Agotado)</small>';
      } else {
        btn.addEventListener('click', (ev) => seleccionarTallaVariante(ev, opt, colorId));
      }

      tallaContainer.appendChild(btn);
    });
  }

  function limpiarSeleccionTalla() {
    formIdTalla.value = '';
    formIdProducto.value = productoBase.id;
    seleccionActual.tallaSeleccionada = false;
    seleccionActual.stockDisponible = 0;
    stockInfo.textContent = '';
    cantidadInput.removeAttribute('max');
    actualizarBotonCarrito();
  }

  async function seleccionarTallaBase(ev, talla) {
    document.querySelectorAll('#talla-container .chip').forEach(c => c.classList.remove('active'));
    ev.currentTarget.classList.add('active');

    formIdProducto.value = talla.ID_Producto;
    formIdColor.value = 'base';
    formIdTalla.value = talla.ID_Talla;
    formTipo.value = 'base';
  
    if (favIdProd) favIdProd.value = talla.ID_Producto;

    seleccionActual.tallaSeleccionada = true;
    seleccionActual.stockDisponible = parseInt(talla.Cantidad, 10);
    seleccionActual.tipoActual = 'base';

    // Obtener precio base
    const precioBase = productoBase.precio;
    
    // Aplicar descuento automático
    const info = await aplicarDescuentoAutomatico(talla.ID_Producto, precioBase);
    
    // Luego aplicar descuento seleccionado por el usuario si existe
    if (seleccionActual.descuentoSeleccionado.valor > 0) {
      const infoConDescuentoManual = aplicarDescuentoManual(
        info.precioOriginal, 
        seleccionActual.descuentoSeleccionado.tipo, 
        seleccionActual.descuentoSeleccionado.valor
      );
      actualizarPrecioVisual(infoConDescuentoManual.precioFinal, infoConDescuentoManual.precioOriginal, infoConDescuentoManual.descuento);
    } else {
      actualizarPrecioVisual(info.precioFinal, info.precioOriginal, info.descuento);
    }

    actualizarInfoStock(seleccionActual.stockDisponible);
    actualizarBotonCarrito();
    verificarEstadoFavorito();
  }

  async function seleccionarTallaVariante(ev, opcion, colorId) {
    document.querySelectorAll('#talla-container .chip').forEach(c => c.classList.remove('active'));
    ev.currentTarget.classList.add('active');

    formIdProducto.value = opcion.ID_Producto;
    formIdColor.value = colorId;
    formIdTalla.value = opcion.ID_Talla;
    formTipo.value = 'variante';
  
    if (favIdProd) favIdProd.value = opcion.ID_Producto;

    mainImg.src = opcion.Foto;
    nombreProd.textContent = `${opcion.Nombre_Producto}`;

    seleccionActual.tallaSeleccionada = true;
    seleccionActual.stockDisponible = parseInt(opcion.Cantidad, 10);
    seleccionActual.tipoActual = 'variante';

    // Obtener precio base para esta variante
    const precioBaseVariante = opcion.Precio_Final;
    
    // Aplicar descuento automático
    const info = await aplicarDescuentoAutomatico(opcion.ID_Producto, precioBaseVariante);
    
    // Luego aplicar descuento seleccionado por el usuario si existe
    if (seleccionActual.descuentoSeleccionado.valor > 0) {
      const infoConDescuentoManual = aplicarDescuentoManual(
        info.precioOriginal, 
        seleccionActual.descuentoSeleccionado.tipo, 
        seleccionActual.descuentoSeleccionado.valor
      );
      actualizarPrecioVisual(infoConDescuentoManual.precioFinal, infoConDescuentoManual.precioOriginal, infoConDescuentoManual.descuento);
    } else {
      actualizarPrecioVisual(info.precioFinal, info.precioOriginal, info.descuento);
    }

    actualizarInfoStock(seleccionActual.stockDisponible);
    actualizarBotonCarrito();
    verificarEstadoFavorito();
  }

  // ---------- COLORES ----------
  colorChips.forEach(chip => {
    chip.addEventListener('click', async () => {
      colorChips.forEach(c => c.classList.remove('active'));
      chip.classList.add('active');

      const id = chip.dataset.id;
      seleccionActual.colorId = id;

      const colorDot = chip.querySelector('.color-dot');
      const nColor = chip.dataset.nombre;
      const codigoHex = chip.dataset.hex || '#ccc';

      formIdColor.value = id;
      formNColor.value = nColor;
      formCodigoHex.value = codigoHex;

      mainImg.src = chip.dataset.foto;
      nombreProd.textContent = chip.dataset.nombre;

      limpiarSeleccionTalla();

      if (chip.dataset.base === "1") {
        const precioBase = productoBase.precio;
        const info = await aplicarDescuentoAutomatico(productoBase.id, precioBase);
        
        // Aplicar descuento seleccionado si existe
        if (seleccionActual.descuentoSeleccionado.valor > 0) {
          const infoConDescuentoManual = aplicarDescuentoManual(
            info.precioOriginal, 
            seleccionActual.descuentoSeleccionado.tipo, 
            seleccionActual.descuentoSeleccionado.valor
          );
          actualizarPrecioVisual(infoConDescuentoManual.precioFinal, infoConDescuentoManual.precioOriginal, infoConDescuentoManual.descuento);
        } else {
          actualizarPrecioVisual(info.precioFinal, info.precioOriginal, info.descuento);
        }
        
        renderTallasBase();
      } else {
        await renderTallasVariantes(id);
      }

      setTimeout(verificarEstadoFavorito, 100);
    });
  });

  // ---------- CANTIDAD ----------
  cantidadInput.addEventListener('input', () => {
    const val = cantidadInput.value.replace(/\D+/g, '');
    setCantidad(val || 1);
  });

  btnPlus.addEventListener('click', () => {
    const max = seleccionActual.stockDisponible;
    let n = parseInt(cantidadInput.value, 10);
    if (n < max) setCantidad(n + 1);
  });

  btnMinus.addEventListener('click', () => {
    let n = parseInt(cantidadInput.value, 10);
    if (n > 1) setCantidad(n - 1);
  });

  // ---------- CARRITO ----------
  btnAddCart.addEventListener('click', () => {
    if (!usuarioLogueado) {
      return Swal.fire({
        icon: 'info',
        title: 'Inicia sesión',
        text: 'Debes iniciar sesión para agregar productos al carrito.',
        confirmButtonText: 'Ir al login'
      }).then(() => window.location.href = `${baseUrl}?c=Usuario&a=login`);
    }

    if (!seleccionActual.tallaSeleccionada) {
      return Swal.fire({
        icon: 'warning',
        title: 'Selecciona talla',
        text: 'Debes elegir una talla antes de agregar al carrito.'
      });
    }

    const qty = parseInt(cantidadInput.value, 10);
    if (qty > seleccionActual.stockDisponible) {
      return Swal.fire({
        icon: 'error',
        title: 'Stock insuficiente',
        text: `Solo hay ${seleccionActual.stockDisponible} unidades disponibles.`
      });
    }

    formCantidad.value = qty;
    formPrecioFinal.value = seleccionActual.precioFinal;

    // Mostrar resumen del descuento aplicado
    let mensajeDescuento = '';
    if (seleccionActual.descuentoSeleccionado.valor > 0) {
      const valorMostrar = seleccionActual.descuentoSeleccionado.tipo === 'Porcentaje' 
        ? seleccionActual.descuentoSeleccionado.valor + '%' 
        : '$' + new Intl.NumberFormat('es-CO').format(seleccionActual.descuentoSeleccionado.valor);
      
      mensajeDescuento = `<br><small>Descuento aplicado: <strong>${seleccionActual.descuentoSeleccionado.codigo}</strong> (${valorMostrar})</small>`;
    }

    Swal.fire({
      icon: 'success',
      title: 'Producto agregado 🛒',
      html: `Precio final: <strong>$${new Intl.NumberFormat('es-CO').format(seleccionActual.precioFinal)}</strong>${mensajeDescuento}`,
      showConfirmButton: false,
      timer: 1500
    }).then(() => {
      document.getElementById('add-cart-form').submit();
    });
  });

  // ---------- INICIALIZACIÓN ----------
  async function inicializar() {
    actualizarEstadoFavorito(seleccionActual.esFavorito);
    setupFavoritosAJAX();
    inicializarSelectorDescuentos();
  
    setCantidad(1);

    // Aplicar descuento inicial
    const infoInicial = await aplicarDescuentoAutomatico(productoBase.id, productoBase.precio);
    actualizarPrecioVisual(infoInicial.precioFinal, infoInicial.precioOriginal, infoInicial.descuento);

    const chipBase = document.querySelector('#color-container .chip[data-base="1"]');
    if (chipBase) {
      await chipBase.click();
    } else if (colorChips.length > 0) {
      await colorChips[0].click();
    } else {
      renderTallasBase();
    }

    setTimeout(verificarEstadoFavorito, 300);
  }

  inicializar();
});