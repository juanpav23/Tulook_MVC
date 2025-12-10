// ===============================
// Archivo: ver.js (SISTEMA DE ATRIBUTOS DINÁMICOS CON CÓDIGO DE DESCUENTO)
// ===============================
document.addEventListener("DOMContentLoaded", () => {
  const { usuarioLogueado, variantes, variantesAgrupadas, productoBase, baseUrl, esFavorito, infoDescuento, todosDescuentos, atributosRequeridos } = PRODUCTO_DATA;

  // === Elementos DOM ===
  const atributoContainers = document.querySelectorAll('.atributo-container');
  const variantesContainer = document.getElementById('variantes-container');
  const mainImg = document.getElementById('main-img');
  const nombreProd = document.getElementById('nombre-producto');
  const cantidadInput = document.getElementById('cantidad');
  const stockInfo = document.getElementById('stock-info');
  const formTipo = document.getElementById('form-tipo');
  const formIdProducto = document.getElementById('form-id-producto');
  const formCantidad = document.getElementById('form-cantidad');
  const formPrecioFinal = document.getElementById('form-precio-final');
  const favForm = document.getElementById('fav-form');
  const favIdProd = document.getElementById('fav-id-prod');
  const btnAddCart = document.getElementById('btn-add-cart');
  const btnFavorito = document.querySelector('#fav-form button[type="submit"]');
  const btnPlus = document.getElementById('qty-plus');
  const btnMinus = document.getElementById('qty-minus');
  
  // Elementos para descuentos - NUEVOS
  const inputCodigoDescuento = document.getElementById('input-codigo-descuento');
  const btnAplicarDescuento = document.getElementById('btn-aplicar-descuento');
  const mensajeDescuento = document.getElementById('mensaje-descuento');
  const descuentoActualDiv = document.getElementById('descuento-actual');
  const btnRemoverDescuento = document.getElementById('btn-remover-descuento');
  const textoDescuentoActivo = document.getElementById('texto-descuento-activo');
  
  // Elementos hidden del formulario
  const formCodigoDescuento = document.getElementById('form-codigo-descuento-final');
  const formTipoDescuento = document.getElementById('form-tipo-descuento-final');
  const formValorDescuento = document.getElementById('form-valor-descuento-final');
  const formIdDescuento = document.getElementById('form-id-descuento-final');

  // === Estado actual ===
  let seleccionActual = {
    atributosSeleccionados: {},
    varianteSeleccionada: null,
    stockDisponible: 0,
    productoId: null,
    esFavorito: esFavorito || false,
    precioBase: 0,
    precioFinal: 0,
    descuentoAplicado: 0,
    descuentoSeleccionado: {
      codigo: '',
      tipo: 'ninguno',
      valor: 0,
      idDescuento: null
    }
  };

  // ---------- SISTEMA DE DESCUENTOS ----------
  function aplicarDescuentoManual(precioBase, tipoDescuento, valorDescuento) {
    let precioFinal = precioBase;
    let descuentoAplicado = 0;

    console.log('Aplicando descuento manual:', { precioBase, tipoDescuento, valorDescuento });

    if (tipoDescuento === 'Porcentaje' && valorDescuento > 0) {
      descuentoAplicado = (precioBase * valorDescuento) / 100;
      precioFinal = precioBase - descuentoAplicado;
    } else if ((tipoDescuento === 'Fijo' || tipoDescuento === 'ValorFijo') && valorDescuento > 0) {
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
    
    const precioFinalFormateado = new Intl.NumberFormat('es-CO').format(precioFinal);
    const precioOriginalFormateado = new Intl.NumberFormat('es-CO').format(precioOriginal);
    const ahorro = precioOriginal - precioFinal;
    const porcentajeAhorro = ahorro > 0 ? ((ahorro / precioOriginal) * 100).toFixed(1) : 0;
    
    console.log('Actualizando precio visual:', { precioFinal, precioOriginal, descuento, ahorro });
    
    // ✅ CORRECCIÓN: Solo mostrar "GRATIS" cuando el precio final sea 0
    if (precioFinal === 0 || precioFinal === '0' || precioFinal === 0.00) {
      // Si el precio original también era 0, es gratis de verdad
      if (precioOriginal === 0 || precioOriginal === '0' || precioOriginal === 0.00) {
        precioFinalElement.innerHTML = '<span class="text-success fw-bold">GRATIS</span>';
        precioFinalElement.className = 'precio-final';
        
        precioOriginalElement.style.display = 'none';
        descuentoBadgeElement.style.display = 'none';
        ahorroInfoElement.style.display = 'none';
      } else {
        // Si el precio original > 0 pero ahora es 0 por descuento
        precioFinalElement.innerHTML = '<span class="text-success fw-bold">GRATIS</span>';
        precioFinalElement.className = 'precio-final';
        
        precioOriginalElement.textContent = `$${precioOriginalFormateado}`;
        precioOriginalElement.style.display = 'inline';
        
        if (descuento > 0) {
          descuentoBadgeElement.innerHTML = `<i class="fas fa-tag me-1"></i>-${descuento.toFixed(1)}%`;
          descuentoBadgeElement.style.display = 'inline-block';
        }
        
        ahorroInfoElement.innerHTML = `<i class="fas fa-bolt me-1"></i>Ahorras $${new Intl.NumberFormat('es-CO').format(ahorro)} (100%)`;
        ahorroInfoElement.style.display = 'block';
      }
    } else if (descuento > 0 && ahorro > 0) {
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

  // ---------- SISTEMA DE INGRESO DE CÓDIGO DE DESCUENTO ----------
  function inicializarSistemaDescuentos() {
    if (!usuarioLogueado) return;
    
    // Cargar descuento de localStorage si existe
    const descuentoGuardado = localStorage.getItem('descuentoActual');
    if (descuentoGuardado && seleccionActual.productoId) {
      const descuento = JSON.parse(descuentoGuardado);
      if (validarDescuentoParaProducto(descuento, seleccionActual.productoId)) {
        aplicarDescuentoDesdeStorage(descuento);
      }
    }
    
    btnAplicarDescuento.addEventListener('click', async function() {
      const codigo = inputCodigoDescuento.value.trim().toUpperCase();
      
      if (!codigo) {
        mostrarMensajeDescuento('Por favor ingresa un código de descuento', 'warning');
        return;
      }
      
      // Mostrar loading
      btnAplicarDescuento.disabled = true;
      btnAplicarDescuento.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Validando...';
      
      try {
        const descuentoValido = await validarCodigoDescuento(codigo);
        
        if (descuentoValido) {
          // Verificar si aplica al producto actual
          if (validarDescuentoParaProducto(descuentoValido, seleccionActual.productoId)) {
            aplicarDescuentoManualDesdeCodigo(descuentoValido);
            guardarDescuentoEnStorage(descuentoValido);
            mostrarDescuentoActivo(descuentoValido);
            mostrarMensajeDescuento('✅ ¡Descuento aplicado correctamente!', 'success');
            inputCodigoDescuento.value = '';
          } else {
            mostrarMensajeDescuento('❌ Este descuento no aplica para el producto seleccionado', 'danger');
          }
        } else {
          mostrarMensajeDescuento('❌ Código de descuento inválido o expirado', 'danger');
        }
      } catch (error) {
        console.error('Error validando descuento:', error);
        mostrarMensajeDescuento('❌ Error al validar el código. Intenta nuevamente.', 'danger');
      } finally {
        btnAplicarDescuento.disabled = false;
        btnAplicarDescuento.innerHTML = '<i class="fas fa-check me-1"></i>Aplicar';
      }
    });
    
    // Permitir presionar Enter
    inputCodigoDescuento.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        btnAplicarDescuento.click();
      }
    });
    
    // Botón para remover descuento
    if (btnRemoverDescuento) {
      btnRemoverDescuento.addEventListener('click', function() {
        removerDescuento();
        localStorage.removeItem('descuentoActual');
        descuentoActualDiv.style.display = 'none';
        mostrarMensajeDescuento('Descuento removido', 'info');
      });
    }
  }

  async function validarCodigoDescuento(codigo) {
    try {
      // Buscar en los descuentos vigentes que ya tenemos cargados
      for (const descuento of todosDescuentos) {
        if (descuento.Codigo === codigo) {
          // Verificar vigencia
          const ahora = new Date().toISOString();
          if (descuento.Activo === 1 && 
              descuento.FechaInicio <= ahora && 
              descuento.FechaFin >= ahora) {
            return descuento;
          }
        }
      }
      
      // Si no se encuentra localmente, consultar al servidor
      const formData = new FormData();
      formData.append('codigo', codigo);
      
      const response = await fetch(`${baseUrl}?c=Descuento&a=validarCodigo`, {
        method: 'POST',
        body: formData
      });
      
      if (response.ok) {
        const data = await response.json();
        if (data.success) {
          return data.descuento;
        }
      }
      
      return null;
    } catch (error) {
      console.error('Error validando código:', error);
      return null;
    }
  }

  function validarDescuentoParaProducto(descuento, productoId) {
    // Si el descuento es general (sin ID específico), aplica a todos
    if (!descuento.ID_Articulo && !descuento.ID_Producto && !descuento.ID_Categoria) {
      return true;
    }
    
    // Verificar si aplica por artículo
    if (descuento.ID_Articulo && descuento.ID_Articulo == productoBase.id) {
      return true;
    }
    
    // Verificar si aplica por producto específico
    if (descuento.ID_Producto && descuento.ID_Producto == productoId) {
      return true;
    }
    
    // Verificar si aplica por categoría
    if (descuento.ID_Categoria && descuento.ID_Categoria == productoBase.id_categoria) {
      return true;
    }
    
    return false;
  }

  function aplicarDescuentoManualDesdeCodigo(descuento) {
    seleccionActual.descuentoSeleccionado = {
      codigo: descuento.Codigo,
      tipo: descuento.Tipo,
      valor: parseFloat(descuento.Valor),
      idDescuento: descuento.ID_Descuento
    };

    // Actualizar campos hidden
    if (formCodigoDescuento) formCodigoDescuento.value = descuento.Codigo;
    if (formTipoDescuento) formTipoDescuento.value = descuento.Tipo;
    if (formValorDescuento) formValorDescuento.value = descuento.Valor;
    if (formIdDescuento) formIdDescuento.value = descuento.ID_Descuento;

    // Recalcular precio
    recalcularPrecioConDescuento();
  }

  function aplicarDescuentoDesdeStorage(descuento) {
    aplicarDescuentoManualDesdeCodigo(descuento);
    mostrarDescuentoActivo(descuento);
  }

  function mostrarDescuentoActivo(descuento) {
    if (descuentoActualDiv && textoDescuentoActivo) {
      const valorMostrar = descuento.Tipo === 'Porcentaje' 
        ? `${descuento.Valor}%` 
        : `$${new Intl.NumberFormat('es-CO').format(descuento.Valor)}`;
      
      textoDescuentoActivo.textContent = 
        `Descuento "${descuento.Codigo}" aplicado: -${valorMostrar}`;
      
      descuentoActualDiv.style.display = 'block';
    }
  }

  function mostrarMensajeDescuento(mensaje, tipo = 'info') {
    if (!mensajeDescuento) return;
    
    const clases = {
      success: 'alert-success',
      danger: 'alert-danger',
      warning: 'alert-warning',
      info: 'alert-info'
    };
    
    mensajeDescuento.innerHTML = `
      <div class="alert ${clases[tipo]} alert-dismissible fade show py-2" role="alert">
        <div class="d-flex align-items-center">
          <i class="fas fa-${tipo === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>
          <span>${mensaje}</span>
        </div>
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
      </div>
    `;
    mensajeDescuento.style.display = 'block';
    
    // Auto-ocultar después de 5 segundos
    setTimeout(() => {
      mensajeDescuento.style.display = 'none';
    }, 5000);
  }

  function removerDescuento() {
    seleccionActual.descuentoSeleccionado = {
      codigo: '',
      tipo: 'ninguno',
      valor: 0,
      idDescuento: null
    };

    // Limpiar campos hidden
    if (formCodigoDescuento) formCodigoDescuento.value = '';
    if (formTipoDescuento) formTipoDescuento.value = '';
    if (formValorDescuento) formValorDescuento.value = 0;
    if (formIdDescuento) formIdDescuento.value = '';

    // Recalcular precio sin descuento
    recalcularPrecioConDescuento();
  }

  function guardarDescuentoEnStorage(descuento) {
    localStorage.setItem('descuentoActual', JSON.stringify(descuento));
  }

  function recalcularPrecioConDescuento() {
    const precioBase = seleccionActual.precioBase;
    const { tipo, valor } = seleccionActual.descuentoSeleccionado;

    console.log('Recalculando precio con descuento:', { precioBase, tipo, valor });

    if (tipo === 'ninguno' || valor === 0) {
      console.log('Sin descuento aplicado');
      actualizarPrecioVisual(precioBase, precioBase, 0);
    } else {
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
      const idProductoActual = formIdProducto.value;
      
      formData.append('id_producto', idProductoActual);

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

  // ---------- SISTEMA DE ATRIBUTOS DINÁMICOS ----------
  function inicializarSelectoresAtributos() {
    console.log('🔄 Inicializando sistema de atributos dependientes...');
    
    const chips = document.querySelectorAll('.chip');
    console.log('🎯 Chips encontrados:', chips.length);
    
    chips.forEach(chip => {
      chip.addEventListener('click', function() {
        if (this.classList.contains('disabled')) {
          return; // No hacer nada si está deshabilitado
        }
        
        const atributoId = this.dataset.atributoId;
        const valor = this.dataset.valor;
        const step = parseInt(this.dataset.step);
        
        console.log(`📍 Seleccionado: ${valor} (Atributo: ${atributoId}, Paso: ${step})`);
        
        // Remover selección de otros chips del mismo atributo
        document.querySelectorAll(`.chip[data-atributo-id="${atributoId}"]`).forEach(c => {
          c.classList.remove('active');
        });
        
        // Seleccionar este chip
        this.classList.add('active');
        
        // Actualizar selección
        seleccionActual.atributosSeleccionados[atributoId] = valor;
        
        console.log('📋 Selección actual:', seleccionActual.atributosSeleccionados);
        
        // Procesar selección en cadena
        procesarSeleccionEnCadena(step);
      });
    });
  }

  function procesarSeleccionEnCadena(stepSeleccionado) {
    const gruposAtributos = document.querySelectorAll('.atributo-group');
    
    // Resetear atributos siguientes
    for (let i = stepSeleccionado; i < gruposAtributos.length; i++) {
      const grupo = gruposAtributos[i];
      const atributoId = grupo.dataset.atributoId;
      const step = parseInt(grupo.dataset.step);
      
      if (step > stepSeleccionado) {
        // Limpiar selección de atributos siguientes
        delete seleccionActual.atributosSeleccionados[atributoId];
        
        // Deseleccionar chips
        grupo.querySelectorAll('.chip').forEach(chip => {
          chip.classList.remove('active');
        });
        
        // Actualizar disponibilidad
        actualizarDisponibilidadAtributos(step);
      }
    }
    
    // Actualizar el siguiente atributo inmediato
    if (stepSeleccionado < gruposAtributos.length) {
      actualizarDisponibilidadAtributos(stepSeleccionado + 1);
    }
    
    // Verificar combinación completa
    verificarCombinacionAtributos();
  }

  function actualizarDisponibilidadAtributos(stepTarget) {
    const grupo = document.querySelector(`.atributo-group[data-step="${stepTarget}"]`);
    if (!grupo) return;
    
    const atributoId = grupo.dataset.atributoId;
    const chips = grupo.querySelectorAll('.chip');
    
    // Obtener selección actual de atributos anteriores
    const seleccionAnterior = {};
    for (let i = 1; i < stepTarget; i++) {
      const grupoAnterior = document.querySelector(`.atributo-group[data-step="${i}"]`);
      if (grupoAnterior) {
        const atributoIdAnterior = grupoAnterior.dataset.atributoId;
        seleccionAnterior[atributoIdAnterior] = seleccionActual.atributosSeleccionados[atributoIdAnterior];
      }
    }
    
    // Verificar disponibilidad para cada opción
    chips.forEach(chip => {
      const valor = chip.dataset.valor;
      
      // Combinación temporal para verificar
      const combinacionTemp = {...seleccionAnterior, [atributoId]: valor};
      const disponible = verificarCombinacionDisponible(combinacionTemp);
      
      if (disponible) {
        chip.classList.remove('disabled');
        chip.style.opacity = '1';
      } else {
        chip.classList.add('disabled');
        chip.style.opacity = '0.5';
      }
    });
    
    // Actualizar mensaje
    const mensaje = grupo.querySelector('.atributo-message');
    if (mensaje) {
      const todosDeshabilitados = Array.from(chips).every(chip => chip.classList.contains('disabled'));
      if (todosDeshabilitados) {
        mensaje.innerHTML = '<small class="text-danger">No hay productos disponibles con la selección actual</small>';
      } else {
        const atributoAnterior = document.querySelector(`.atributo-group[data-step="${stepTarget - 1}"] h6`)?.textContent || 'las opciones anteriores';
        mensaje.innerHTML = `<small class="text-muted"><i class="fas fa-info-circle"></i> Opciones disponibles para la selección actual</small>`;
      }
    }
  }

  function verificarCombinacionDisponible(combinacion) {
    // Buscar en las variantes si existe esta combinación
    for (const variante of variantes) {
      let coincide = true;
      
      for (const [atributoId, valor] of Object.entries(combinacion)) {
        let encontrado = false;
        
        // Buscar en los 3 posibles atributos de la variante
        for (let i = 1; i <= 3; i++) {
          const idAtributoVariante = variante[`ID_Atributo${i}`];
          const valorAtributoVariante = variante[`ValorAtributo${i}`];
          
          if (idAtributoVariante == atributoId && valorAtributoVariante == valor) {
            encontrado = true;
            break;
          }
        }
        
        if (!encontrado) {
          coincide = false;
          break;
        }
      }
      
      if (coincide && (variante.Cantidad > 0 || variante.Cantidad === null)) {
        return true; // Combinación disponible
      }
    }
    
    return false; // Combinación no disponible
  }

  function verificarCombinacionAtributos() {
    console.log('Verificando combinación de atributos...');
    
    // Verificar si tenemos todos los atributos requeridos seleccionados
    const todosAtributosSeleccionados = atributosRequeridos.every(id => {
      const seleccionado = seleccionActual.atributosSeleccionados[id];
      console.log(`Atributo ${id}: ${seleccionado ? 'seleccionado' : 'no seleccionado'}`);
      return seleccionado;
    });

    console.log('Todos los atributos seleccionados:', todosAtributosSeleccionados);

    if (todosAtributosSeleccionados) {
      // Buscar la variante que coincida con la combinación seleccionada
      const varianteEncontrada = buscarVariantePorAtributos();
      
      if (varianteEncontrada) {
        console.log('Variante encontrada:', varianteEncontrada);
        seleccionarVariante(varianteEncontrada);
      } else {
        // No se encontró variante para esta combinación
        console.log('No se encontró variante para esta combinación');
        limpiarSeleccionVariante();
        stockInfo.textContent = 'Combinación no disponible';
        stockInfo.className = 'text-danger';
        btnAddCart.disabled = true;
        btnAddCart.innerHTML = '<i class="fa fa-times"></i> Combinación no disponible';
      }
    } else {
      // Aún no se han seleccionado todos los atributos
      console.log('Selecciona todos los atributos...');
      limpiarSeleccionVariante();
      stockInfo.textContent = 'Selecciona todas las opciones';
      stockInfo.className = 'text-muted';
      btnAddCart.disabled = true;
      btnAddCart.innerHTML = '<i class="fa fa-shopping-cart"></i> Selecciona todas las opciones';
    }
  }

  function buscarVariantePorAtributos() {
    console.log('🔍 Buscando variante con atributos:', seleccionActual.atributosSeleccionados);
    
    // Buscar en las variantes agrupadas primero
    for (const grupo of Object.values(variantesAgrupadas)) {
      const coincide = grupo.atributos.every(atributo => {
        const valorSeleccionado = seleccionActual.atributosSeleccionados[atributo.id];
        console.log(`  - Comparando: ${atributo.id}: ${atributo.valor} vs ${valorSeleccionado}`);
        return valorSeleccionado === atributo.valor;
      });
      
      if (coincide && grupo.variantes.length > 0) {
        console.log('✅ Variante encontrada en grupo:', grupo.variantes[0]);
        return grupo.variantes[0];
      }
    }
    
    // Si no se encuentra en agrupadas, buscar en todas las variantes
    for (const variante of variantes) {
      let coincide = true;
      
      for (let i = 1; i <= 3; i++) {
        const idAtributo = variante[`ID_Atributo${i}`];
        const valorAtributo = variante[`ValorAtributo${i}`];
        
        if (idAtributo && valorAtributo) {
          const valorSeleccionado = seleccionActual.atributosSeleccionados[idAtributo];
          if (valorSeleccionado !== valorAtributo) {
            coincide = false;
            break;
          }
        }
      }
      
      if (coincide) {
        console.log('✅ Variante encontrada en lista completa:', variante);
        return variante;
      }
    }
    
    console.log('❌ No se encontró variante para los atributos seleccionados');
    return null;
  }

  async function seleccionarVariante(variante) {
    console.log('🎯 Seleccionando variante:', variante);
    
    if (!variante || !variante.ID_Producto) {
      console.error('❌ Variante inválida:', variante);
      return;
    }

    seleccionActual.varianteSeleccionada = variante;
    seleccionActual.productoId = variante.ID_Producto;
    seleccionActual.stockDisponible = variante.Cantidad || 0;

    // ✅ CORRECCIÓN: Actualizar el campo hidden del formulario
    const formIdProducto = document.getElementById('form-id-producto');
    if (formIdProducto) {
      formIdProducto.value = variante.ID_Producto;
      console.log('✅ form-id-producto actualizado:', formIdProducto.value);
    }

    // Actualizar formulario
    formTipo.value = 'variante';
    
    if (favIdProd) {
      favIdProd.value = variante.ID_Producto;
    }

    // Actualizar imagen si tiene una específica
    if (variante.Foto && variante.Foto !== productoBase.Foto) {
      mainImg.src = variante.Foto;
    }

    // Obtener precio base para esta variante
    let precioBaseVariante = productoBase.precio; // Precio por defecto del artículo

    // Buscar el precio en las variantes
    for (const v of variantes) {
        if (v.ID_Producto === variante.ID_Producto) {
            precioBaseVariante = v.Precio_Final || productoBase.precio;
            break;
        }
    }

    console.log('💰 Precio base de la variante:', precioBaseVariante);

    // Aplicar descuento automático
    const info = await aplicarDescuentoAutomatico(variante.ID_Producto, precioBaseVariante);
    console.log('💰 Info descuento automático:', info);
    
    // Actualizar precio base en el estado
    seleccionActual.precioBase = info.precioOriginal;
    
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

    // Actualizar información de stock
    actualizarInfoStock(seleccionActual.stockDisponible);
    actualizarBotonCarrito();
    verificarEstadoFavorito();

    console.log('✅ Variante seleccionada correctamente. ID_Producto:', seleccionActual.productoId);
  }

  function limpiarSeleccionVariante() {
    seleccionActual.varianteSeleccionada = null;
    seleccionActual.productoId = null;
    seleccionActual.stockDisponible = 0;
    formIdProducto.value = '';
    stockInfo.textContent = '';
    cantidadInput.removeAttribute('max');
    actualizarBotonCarrito();
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
    if (!seleccionActual.varianteSeleccionada) {
      btnAddCart.disabled = true;
      btnAddCart.innerHTML = '<i class="fa fa-shopping-cart"></i> Selecciona todas las opciones';
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

  // ---------- CARRITO CON AJAX ----------
  btnAddCart.addEventListener('click', async () => {
    if (!usuarioLogueado) {
      return Swal.fire({
        icon: 'info',
        title: 'Inicia sesión',
        text: 'Debes iniciar sesión para agregar productos al carrito.',
        confirmButtonText: 'Ir al login'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = `${baseUrl}?c=Usuario&a=login`;
        }
      });
    }

    if (!seleccionActual.varianteSeleccionada) {
      return Swal.fire({
        icon: 'warning',
        title: 'Selecciona opciones',
        text: 'Debes elegir todas las opciones antes de agregar al carrito.',
        confirmButtonText: 'Entendido'
      });
    }

    const qty = parseInt(cantidadInput.value, 10);
    if (qty > seleccionActual.stockDisponible) {
      return Swal.fire({
        icon: 'error',
        title: 'Stock insuficiente',
        text: `Solo hay ${seleccionActual.stockDisponible} unidades disponibles.`,
        confirmButtonText: 'Entendido'
      });
    }

    // Loading
    const originalText = btnAddCart.innerHTML;
    btnAddCart.disabled = true;
    btnAddCart.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Agregando...';

    try {
      console.log('🛒 Enviando al carrito:', {
        productoId: seleccionActual.productoId,
        precioFinal: seleccionActual.precioFinal,
        esGratis: seleccionActual.precioFinal === 0
      });

      // ✅ ENVÍO SIMPLE QUE FUNCIONA
      const params = new URLSearchParams();
      params.append('id_producto', seleccionActual.productoId);
      params.append('id_articulo', document.querySelector('input[name="id_articulo"]').value);
      params.append('cantidad', qty);
      params.append('precio_final', seleccionActual.precioFinal);
      params.append('tipo', 'variante');

      // ✅ Asegurar que también se pasen los datos del descuento
      params.append('codigo_descuento', seleccionActual.descuentoSeleccionado.codigo || '');
      params.append('tipo_descuento', seleccionActual.descuentoSeleccionado.tipo || '');
      params.append('valor_descuento', seleccionActual.descuentoSeleccionado.valor || 0);
      params.append('id_descuento', seleccionActual.descuentoSeleccionado.idDescuento || '');

      const response = await fetch(`${baseUrl}?c=Carrito&a=agregarAjax&${params.toString()}`, {
        method: 'GET' // ✅ USAR GET QUE SÍ FUNCIONA
      });

      const result = await response.json();

      if (result.success) {
        // ✅ ÉXITO
        await Swal.fire({
          icon: 'success',
          title: '✅ ¡Agregado al carrito!',
          html: `
            <div class="text-start">
              <p><strong>${productoBase.nombre}</strong></p>
              <p>Cantidad: <strong>${qty}</strong></p>
              <p>Precio: <strong>$${new Intl.NumberFormat('es-CO').format(seleccionActual.precioFinal)}</strong></p>
            </div>
          `,
          showConfirmButton: true,
          confirmButtonText: 'Seguir comprando',
          showCancelButton: true,
          cancelButtonText: 'Ver carrito'
        }).then((result) => {
          if (result.dismiss === Swal.DismissReason.cancel) {
            window.location.href = `${baseUrl}?c=Carrito&a=carrito`;
          }
        });

      } else {
        throw new Error(result.message);
      }

    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.message
      });
    } finally {
      btnAddCart.disabled = false;
      btnAddCart.innerHTML = originalText;
    }
  });

  // ---------- INICIALIZACIÓN ----------
  async function inicializar() {
    actualizarEstadoFavorito(seleccionActual.esFavorito);
    setupFavoritosAJAX();
    inicializarSistemaDescuentos();  // Cambiado de inicializarSelectorDescuentos()
    inicializarSelectoresAtributos();
  
    setCantidad(1);

    // Mostrar mensaje inicial
    stockInfo.textContent = 'Selecciona todas las opciones';
    stockInfo.className = 'text-muted';
    btnAddCart.disabled = true;
    btnAddCart.innerHTML = '<i class="fa fa-shopping-cart"></i> Selecciona todas las opciones';

    setTimeout(verificarEstadoFavorito, 300);
  }

  // 🔍 DEBUG: Verificar datos del producto
  function debugProducto() {
      console.log('=== DEBUG PRODUCTO ===');
      console.log('Precio base del producto:', productoBase.precio);
      console.log('Variantes disponibles:', variantes.length);
      console.log('Primera variante:', variantes[0]);
      console.log('Info descuento:', infoDescuento);
      console.log('Todos descuentos:', todosDescuentos);
      console.log('======================');
  }

  // Llamar al debug después de inicializar
  setTimeout(debugProducto, 1000);

  inicializar();
});