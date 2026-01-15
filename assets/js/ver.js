// ===============================
// Archivo: ver.js (SISTEMA DE ATRIBUTOS Y DESCUENTOS CON LÍMITES)
// ===============================
document.addEventListener("DOMContentLoaded", () => {
  const { usuarioLogueado, variantes, variantesAgrupadas, productoBase, baseUrl, infoDescuento, todosDescuentos, atributosRequeridos } = PRODUCTO_DATA;

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
  const btnAddCart = document.getElementById('btn-add-cart');
  const btnPlus = document.getElementById('qty-plus');
  const btnMinus = document.getElementById('qty-minus');
  
  // Elementos para descuentos
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
    precioBase: 0,
    precioFinal: 0,
    descuentoAplicado: 0,
    descuentoSeleccionado: {
      codigo: '',
      tipo: 'ninguno',
      valor: 0,
      idDescuento: null,
      puedeUsar: true,
      mensajeError: ''
    }
  };

  // ---------- SISTEMA DE VALIDACIÓN DE DESCUENTOS CON LÍMITES ----------
  async function validarCodigoDescuento(codigo) {
    try {
      console.log('🔍 Validando código de descuento:', codigo);
      
      if (!codigo || codigo.trim() === '') {
        return {
          valido: false,
          mensaje: '❌ Por favor ingresa un código',
          descuento: null
        };
      }
      
      // 1. Buscar en descuentos locales para ver información
      let descuentoLocal = null;
      if (todosDescuentos && Array.isArray(todosDescuentos)) {
        for (const descuento of todosDescuentos) {
          if (descuento.Codigo === codigo) {
            descuentoLocal = descuento;
            break;
          }
        }
      }
      
      // 2. Validar con el servidor (verifica límites y si usuario lo ganó)
      const url = `${baseUrl}?c=Descuento&a=validar`;
      console.log('📤 Enviando validación a:', `${url}&codigo=${encodeURIComponent(codigo)}`);
      
      const response = await fetch(`${url}&codigo=${encodeURIComponent(codigo)}`, {
        method: 'GET',
        credentials: 'include'
      });
      
      const responseText = await response.text();
      console.log('📥 Respuesta servidor:', responseText);
      
      let data;
      try {
        data = JSON.parse(responseText);
      } catch (parseError) {
        console.error('❌ Error parseando JSON:', parseError);
        return {
          valido: false,
          mensaje: 'Error del servidor',
          descuento: null
        };
      }
      
      console.log('✅ JSON parseado:', data);
      
      // 3. Procesar respuesta
      if (data.valido === true || data.success === true) {
        const descuento = data.descuento || data.data || descuentoLocal;
        
        // Calcular usos restantes
        const usosUsuario = descuento.usos_usuario || descuento.Usos || 0;
        const maxUsosUsuario = descuento.Max_Usos_Usuario || 0;
        const usosRestantesUsuario = maxUsosUsuario > 0 ? maxUsosUsuario - usosUsuario : 'Ilimitado';
        
        const usosGlobales = descuento.Usos_Globales || 0;
        const maxUsosGlobal = descuento.Max_Usos_Global || 0;
        const usosRestantesGlobal = maxUsosGlobal > 0 ? maxUsosGlobal - usosGlobales : 'Ilimitado';
        
        console.log('📊 Límites del descuento:', {
          usosUsuario,
          maxUsosUsuario,
          usosRestantesUsuario,
          usosGlobales,
          maxUsosGlobal,
          usosRestantesGlobal
        });
        
        
        // Preparar mensaje informativo
        let mensajeExtra = '';
        if (maxUsosUsuario > 0) {
          mensajeExtra = ` Puedes usarlo ${usosRestantesUsuario} vez(es) más.`;
        }
        
        return {
          valido: true,
          mensaje: (data.mensaje || '✅ Código válido') + mensajeExtra,
          descuento: descuento,
          tieneLimites: maxUsosUsuario > 0 || maxUsosGlobal > 0,
          usosRestantesUsuario: usosRestantesUsuario,
          usosRestantesGlobal: usosRestantesGlobal
        };
        
      } else {
        // Código inválido
        return {
          valido: false,
          mensaje: data.mensaje || '❌ Código no válido',
          descuento: descuentoLocal
        };
      }
      
    } catch (error) {
      console.error('❌ Error validando código:', error);
      return {
        valido: false,
        mensaje: '❌ Error de conexión. Verifica tu internet.',
        descuento: null
      };
    }
  }

  function aplicarDescuentoManual(precioBase, tipoDescuento, valorDescuento) {
    let precioFinal = precioBase;
    let descuentoAplicado = 0;

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
    
    // Mostrar "GRATIS" solo cuando el precio final sea 0
    if (precioFinal === 0 || precioFinal === '0' || precioFinal === 0.00) {
      if (precioOriginal === 0 || precioOriginal === '0' || precioOriginal === 0.00) {
        precioFinalElement.innerHTML = '<span class="text-success fw-bold">GRATIS</span>';
        precioFinalElement.className = 'precio-final';
        
        precioOriginalElement.style.display = 'none';
        descuentoBadgeElement.style.display = 'none';
        ahorroInfoElement.style.display = 'none';
      } else {
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

  function inicializarSistemaDescuentos() {
    if (!usuarioLogueado) {
      console.log('⚠️ Usuario no logueado, descuentos deshabilitados');
      if (inputCodigoDescuento) inputCodigoDescuento.disabled = true;
      if (btnAplicarDescuento) btnAplicarDescuento.disabled = true;
      return;
    }
    
    // Cargar descuento de localStorage si existe
    const descuentoGuardado = localStorage.getItem('descuentoActual');
    if (descuentoGuardado && seleccionActual.productoId) {
      try {
        const descuento = JSON.parse(descuentoGuardado);
        if (validarDescuentoParaProducto(descuento, seleccionActual.productoId)) {
          aplicarDescuentoDesdeStorage(descuento);
        }
      } catch (e) {
        console.error('Error al cargar descuento de localStorage:', e);
        localStorage.removeItem('descuentoActual');
      }
    }
    
    if (btnAplicarDescuento) {
      btnAplicarDescuento.addEventListener('click', async function() {
        const codigo = inputCodigoDescuento ? inputCodigoDescuento.value.trim().toUpperCase() : '';
        
        if (!codigo) {
          mostrarMensajeDescuento('Por favor ingresa un código de descuento', 'warning');
          return;
        }
        
        // Mostrar loading
        const originalText = btnAplicarDescuento.innerHTML;
        btnAplicarDescuento.disabled = true;
        btnAplicarDescuento.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Validando...';
        
        try {
          const resultadoValidacion = await validarCodigoDescuento(codigo);
          console.log('Resultado validación:', resultadoValidacion);
          
          if (resultadoValidacion.valido) {
            // Verificar si aplica al producto actual
            if (validarDescuentoParaProducto(resultadoValidacion.descuento, seleccionActual.productoId)) {
              // ✅ Actualizar estado del descuento
              seleccionActual.descuentoSeleccionado = {
                codigo: resultadoValidacion.descuento.Codigo || '',
                tipo: resultadoValidacion.descuento.Tipo || 'ninguno',
                valor: parseFloat(resultadoValidacion.descuento.Valor) || 0,
                idDescuento: resultadoValidacion.descuento.ID_Descuento || null,
                puedeUsar: true,
                mensajeError: ''
              };

              // Actualizar campos hidden
              if (formCodigoDescuento) formCodigoDescuento.value = resultadoValidacion.descuento.Codigo || '';
              if (formTipoDescuento) formTipoDescuento.value = resultadoValidacion.descuento.Tipo || '';
              if (formValorDescuento) formValorDescuento.value = resultadoValidacion.descuento.Valor || 0;
              if (formIdDescuento) formIdDescuento.value = resultadoValidacion.descuento.ID_Descuento || '';

              // Recalcular precio
              recalcularPrecioConDescuento();
              
              // Guardar en localStorage
              guardarDescuentoEnStorage(resultadoValidacion.descuento);
              
              // Mostrar información del descuento
              mostrarDescuentoActivo(resultadoValidacion.descuento, resultadoValidacion.usosRestantesUsuario);
              
              mostrarMensajeDescuento(`✅ ${resultadoValidacion.mensaje}`, 'success');
              if (inputCodigoDescuento) inputCodigoDescuento.value = '';
            } else {
              mostrarMensajeDescuento('❌ Este descuento no aplica para el producto seleccionado', 'danger');
            }
          } else {
            mostrarMensajeDescuento(`❌ ${resultadoValidacion.mensaje}`, 'danger');
          }
        } catch (error) {
          console.error('Error validando descuento:', error);
          mostrarMensajeDescuento('❌ Error al validar el código. Intenta nuevamente.', 'danger');
        } finally {
          btnAplicarDescuento.disabled = false;
          btnAplicarDescuento.innerHTML = originalText;
        }
      });
    }
    
    // Permitir presionar Enter
    if (inputCodigoDescuento) {
      inputCodigoDescuento.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
          if (btnAplicarDescuento) btnAplicarDescuento.click();
        }
      });
    }
    
    // Botón para remover descuento
    if (btnRemoverDescuento) {
      btnRemoverDescuento.addEventListener('click', function() {
        removerDescuento();
        localStorage.removeItem('descuentoActual');
        if (descuentoActualDiv) descuentoActualDiv.style.display = 'none';
        mostrarMensajeDescuento('Descuento removido', 'info');
      });
    }
  }

  function validarDescuentoParaProducto(descuento, productoId) {
    if (!descuento) return false;
    
    // Si no hay producto seleccionado aún
    if (!productoId) {
      return !descuento.ID_Articulo && !descuento.ID_Producto && !descuento.ID_Categoria;
    }
    
    // Descuento general aplica a todos
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

  function aplicarDescuentoDesdeStorage(descuento) {
    // Aplicar descuento desde localStorage
    seleccionActual.descuentoSeleccionado = {
      codigo: descuento.Codigo || '',
      tipo: descuento.Tipo || 'ninguno',
      valor: parseFloat(descuento.Valor) || 0,
      idDescuento: descuento.ID_Descuento || null,
      puedeUsar: true,
      mensajeError: ''
    };

    // Actualizar campos hidden
    if (formCodigoDescuento) formCodigoDescuento.value = descuento.Codigo || '';
    if (formTipoDescuento) formTipoDescuento.value = descuento.Tipo || '';
    if (formValorDescuento) formValorDescuento.value = descuento.Valor || 0;
    if (formIdDescuento) formIdDescuento.value = descuento.ID_Descuento || '';

    recalcularPrecioConDescuento();
    mostrarDescuentoActivo(descuento);
  }

  function mostrarDescuentoActivo(descuento, usosRestantes = null) {
    if (!descuentoActualDiv || !textoDescuentoActivo || !descuento) return;
    
    const valorMostrar = descuento.Tipo === 'Porcentaje' 
      ? `${descuento.Valor}%` 
      : `$${new Intl.NumberFormat('es-CO').format(descuento.Valor)}`;
    
    let textoExtra = '';
    if (usosRestantes !== null && usosRestantes !== 'Ilimitado' && usosRestantes > 0) {
      textoExtra = ` (Te quedan ${usosRestantes} uso(s))`;
    }
    
    textoDescuentoActivo.textContent = 
      `Descuento "${descuento.Codigo || ''}" aplicado: -${valorMostrar}${textoExtra}`;
    
    descuentoActualDiv.style.display = 'block';
  }

  function mostrarMensajeDescuento(mensaje, tipo = 'info') {
    if (!mensajeDescuento) return;
    
    const clases = {
      success: 'alert-success',
      danger: 'alert-danger',
      warning: 'alert-warning',
      info: 'alert-info'
    };
    
    const iconos = {
      success: 'check-circle',
      danger: 'exclamation-circle',
      warning: 'exclamation-triangle',
      info: 'info-circle'
    };
    
    mensajeDescuento.innerHTML = `
      <div class="alert ${clases[tipo]} alert-dismissible fade show py-2" role="alert">
        <div class="d-flex align-items-center">
          <i class="fas fa-${iconos[tipo]} me-2"></i>
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
      idDescuento: null,
      puedeUsar: true,
      mensajeError: ''
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
    try {
      localStorage.setItem('descuentoActual', JSON.stringify(descuento));
    } catch (e) {
      console.error('Error guardando en localStorage:', e);
    }
  }

  function recalcularPrecioConDescuento() {
    const precioBase = seleccionActual.precioBase;
    const { tipo, valor, puedeUsar } = seleccionActual.descuentoSeleccionado;

    if (tipo === 'ninguno' || valor === 0 || !puedeUsar) {
      actualizarPrecioVisual(precioBase, precioBase, 0);
    } else {
      const infoPrecio = aplicarDescuentoManual(precioBase, tipo, valor);
      actualizarPrecioVisual(infoPrecio.precioFinal, infoPrecio.precioOriginal, infoPrecio.descuento);
    }
  }

  // ---------- SISTEMA DE ATRIBUTOS DINÁMICOS ----------
  function inicializarSelectoresAtributos() {
    console.log('🔄 Inicializando sistema de atributos dependientes...');
    
    const chips = document.querySelectorAll('.chip');
    console.log('🎯 Chips encontrados:', chips.length);
    
    chips.forEach(chip => {
      chip.addEventListener('click', function() {
        if (this.classList.contains('disabled')) {
          return;
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
        mensaje.innerHTML = `<small class="text-muted"><i class="fas fa-info-circle"></i> Opciones disponibles para la selección actual</small>`;
      }
    }
  }

  function verificarCombinacionDisponible(combinacion) {
    if (!variantes || !Array.isArray(variantes)) return false;
    
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
        return true;
      }
    }
    
    return false;
  }

  function verificarCombinacionAtributos() {
    console.log('Verificando combinación de atributos...');
    
    if (!atributosRequeridos || !Array.isArray(atributosRequeridos)) {
      console.error('atributosRequeridos no está definido o no es un array');
      return;
    }
    
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
        console.log('✅ Variante encontrada:', varianteEncontrada);
        seleccionarVariante(varianteEncontrada);
      } else {
        console.log('❌ No se encontró variante para esta combinación');
        limpiarSeleccionVariante();
        if (stockInfo) {
          stockInfo.textContent = 'Combinación no disponible';
          stockInfo.className = 'text-danger';
        }
        if (btnAddCart) {
          btnAddCart.disabled = true;
          btnAddCart.innerHTML = '<i class="fa fa-times"></i> Combinación no disponible';
        }
      }
    } else {
      console.log('⚠️ Selecciona todos los atributos...');
      limpiarSeleccionVariante();
      if (stockInfo) {
        stockInfo.textContent = 'Selecciona todas las opciones';
        stockInfo.className = 'text-muted';
      }
      if (btnAddCart) {
        btnAddCart.disabled = true;
        btnAddCart.innerHTML = '<i class="fa fa-shopping-cart"></i> Selecciona todas las opciones';
      }
    }
  }

  function buscarVariantePorAtributos() {
    console.log('🔍 Buscando variante con atributos:', seleccionActual.atributosSeleccionados);
    
    // Buscar en las variantes agrupadas primero
    if (variantesAgrupadas && typeof variantesAgrupadas === 'object') {
      for (const grupo of Object.values(variantesAgrupadas)) {
        if (grupo.atributos && Array.isArray(grupo.atributos)) {
          const coincide = grupo.atributos.every(atributo => {
            const valorSeleccionado = seleccionActual.atributosSeleccionados[atributo.id];
            console.log(`  - Comparando: ${atributo.id}: ${atributo.valor} vs ${valorSeleccionado}`);
            return valorSeleccionado === atributo.valor;
          });
          
          if (coincide && grupo.variantes && grupo.variantes.length > 0) {
            console.log('✅ Variante encontrada en grupo:', grupo.variantes[0]);
            return grupo.variantes[0];
          }
        }
      }
    }
    
    // Si no se encuentra en agrupadas, buscar en todas las variantes
    if (variantes && Array.isArray(variantes)) {
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

    // Actualizar campo hidden del formulario
    const formIdProducto = document.getElementById('form-id-producto');
    if (formIdProducto) {
      formIdProducto.value = variante.ID_Producto;
      console.log('✅ form-id-producto actualizado:', formIdProducto.value);
    }

    // Actualizar formulario
    if (formTipo) formTipo.value = 'variante';

    // Actualizar imagen si tiene una específica
    if (variante.Foto && variante.Foto !== productoBase.Foto && mainImg) {
      mainImg.src = variante.Foto;
    }

    // Obtener precio base para esta variante
    let precioBaseVariante = productoBase.precio || 0;

    // Buscar el precio en las variantes
    if (variantes && Array.isArray(variantes)) {
      for (const v of variantes) {
        if (v.ID_Producto === variante.ID_Producto) {
          precioBaseVariante = v.Precio_Final || v.Precio || productoBase.precio || 0;
          break;
        }
      }
    }

    console.log('💰 Precio base de la variante:', precioBaseVariante);

    // Aplicar descuento automático
    const info = await aplicarDescuentoAutomatico(variante.ID_Producto, precioBaseVariante);
    console.log('💰 Info descuento automático:', info);
    
    // Actualizar precio base en el estado
    seleccionActual.precioBase = info.precioOriginal || precioBaseVariante;
    
    // Luego aplicar descuento seleccionado por el usuario si existe
    if (seleccionActual.descuentoSeleccionado.valor > 0 && seleccionActual.descuentoSeleccionado.puedeUsar) {
      const infoConDescuentoManual = aplicarDescuentoManual(
        info.precioOriginal, 
        seleccionActual.descuentoSeleccionado.tipo, 
        seleccionActual.descuentoSeleccionado.valor
      );
      actualizarPrecioVisual(infoConDescuentoManual.precioFinal, 
                            infoConDescuentoManual.precioOriginal, 
                            infoConDescuentoManual.descuento);
    } else {
      actualizarPrecioVisual(info.precioFinal, info.precioOriginal, info.descuento);
    }

    // Actualizar información de stock
    actualizarInfoStock(seleccionActual.stockDisponible);
    actualizarBotonCarrito();

    console.log('✅ Variante seleccionada correctamente. ID_Producto:', seleccionActual.productoId);
  }

  function limpiarSeleccionVariante() {
    seleccionActual.varianteSeleccionada = null;
    seleccionActual.productoId = null;
    seleccionActual.stockDisponible = 0;
    if (formIdProducto) formIdProducto.value = '';
    if (stockInfo) {
      stockInfo.textContent = '';
      stockInfo.className = '';
    }
    if (cantidadInput) cantidadInput.removeAttribute('max');
    actualizarBotonCarrito();
  }

  // ---------- HELPERS ----------
  function setCantidad(val) {
    if (!cantidadInput) return;
    
    const max = parseInt(cantidadInput.max || seleccionActual.stockDisponible || 999, 10);
    let n = parseInt(String(val).replace(/^0+/, ''), 10);
    if (isNaN(n) || n < 1) n = 1;
    if (n > max) n = max;
    cantidadInput.value = n;
    if (formCantidad) formCantidad.value = n;
    actualizarBotonCarrito();
  }

  function actualizarBotonCarrito() {
    if (!btnAddCart) return;
    
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

    const cantidad = parseInt(cantidadInput ? cantidadInput.value : 1, 10) || 1;
    if (cantidad > seleccionActual.stockDisponible) {
      btnAddCart.disabled = true;
      btnAddCart.innerHTML = '<i class="fa fa-times"></i> Stock insuficiente';
      return;
    }

    btnAddCart.disabled = false;
    btnAddCart.innerHTML = '<i class="fa fa-shopping-cart"></i> Agregar al carrito';
  }

  function actualizarInfoStock(stock) {
    if (!stockInfo) return;
    
    if (stock > 0) {
      stockInfo.textContent = `${stock} unidades disponibles`;
      stockInfo.className = 'text-success';
      if (cantidadInput) cantidadInput.max = stock;
    } else {
      stockInfo.textContent = 'Sin stock disponible';
      stockInfo.className = 'text-danger';
      if (cantidadInput) cantidadInput.removeAttribute('max');
    }
    setCantidad(cantidadInput ? cantidadInput.value : 1);
  }

  // ---------- CANTIDAD ----------
  if (cantidadInput) {
    cantidadInput.addEventListener('input', () => {
      const val = cantidadInput.value.replace(/\D+/g, '');
      setCantidad(val || 1);
    });
  }

  if (btnPlus) {
    btnPlus.addEventListener('click', () => {
      const max = seleccionActual.stockDisponible;
      let n = parseInt(cantidadInput ? cantidadInput.value : 1, 10);
      if (n < max) setCantidad(n + 1);
    });
  }

  if (btnMinus) {
    btnMinus.addEventListener('click', () => {
      let n = parseInt(cantidadInput ? cantidadInput.value : 1, 10);
      if (n > 1) setCantidad(n - 1);
    });
  }

  // ---------- CARRITO CON AJAX ----------
  if (btnAddCart) {
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

      const qty = parseInt(cantidadInput ? cantidadInput.value : 1, 10);
      if (qty > seleccionActual.stockDisponible) {
        return Swal.fire({
          icon: 'error',
          title: 'Stock insuficiente',
          text: `Solo hay ${seleccionActual.stockDisponible} unidades disponibles.`,
          confirmButtonText: 'Entendido'
        });
      }

      // Verificar si el descuento sigue siendo válido
      if (seleccionActual.descuentoSeleccionado.codigo && 
          !seleccionActual.descuentoSeleccionado.puedeUsar) {
        return Swal.fire({
          icon: 'warning',
          title: 'Descuento no disponible',
          html: `El código <strong>${seleccionActual.descuentoSeleccionado.codigo}</strong> ya no puede ser usado.<br>
                 <small>${seleccionActual.descuentoSeleccionado.mensajeError}</small>`,
          confirmButtonText: 'Remover descuento'
        }).then(() => {
          removerDescuento();
          if (descuentoActualDiv) descuentoActualDiv.style.display = 'none';
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
          esGratis: seleccionActual.precioFinal === 0,
          descuento: seleccionActual.descuentoSeleccionado
        });

        // Preparar parámetros
        const params = new URLSearchParams();
        params.append('id_producto', seleccionActual.productoId);
        
        const idArticuloInput = document.querySelector('input[name="id_articulo"]');
        if (idArticuloInput) {
          params.append('id_articulo', idArticuloInput.value);
        } else {
          params.append('id_articulo', productoBase.id || '');
        }
        
        params.append('cantidad', qty);
        params.append('precio_final', seleccionActual.precioFinal);
        params.append('tipo', 'variante');

        // Datos del descuento
        params.append('codigo_descuento', seleccionActual.descuentoSeleccionado.codigo || '');
        params.append('tipo_descuento', seleccionActual.descuentoSeleccionado.tipo || '');
        params.append('valor_descuento', seleccionActual.descuentoSeleccionado.valor || 0);
        params.append('id_descuento', seleccionActual.descuentoSeleccionado.idDescuento || '');

        const response = await fetch(`${baseUrl}?c=Carrito&a=agregarAjax&${params.toString()}`, {
          method: 'GET'
        });

        const result = await response.json();

        if (result.success) {
          // ✅ ÉXITO
          await Swal.fire({
            icon: 'success',
            title: '✅ ¡Agregado al carrito!',
            html: `
              <div class="text-start">
                <p><strong>${productoBase.nombre || 'Producto'}</strong></p>
                <p>Cantidad: <strong>${qty}</strong></p>
                <p>Precio: <strong>$${new Intl.NumberFormat('es-CO').format(seleccionActual.precioFinal)}</strong></p>
                ${seleccionActual.descuentoSeleccionado.codigo ? 
                  `<p>Descuento aplicado: <strong>${seleccionActual.descuentoSeleccionado.codigo}</strong></p>` : ''}
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
          throw new Error(result.message || 'Error al agregar al carrito');
        }

      } catch (error) {
        console.error('Error al agregar al carrito:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.message || 'No se pudo agregar el producto al carrito'
        });
      } finally {
        btnAddCart.disabled = false;
        btnAddCart.innerHTML = originalText;
      }
    });
  }

  // ---------- INICIALIZACIÓN ----------
  async function inicializar() {
    inicializarSistemaDescuentos();
    inicializarSelectoresAtributos();
  
    setCantidad(1);

    // Mostrar mensaje inicial
    if (stockInfo) {
      stockInfo.textContent = 'Selecciona todas las opciones';
      stockInfo.className = 'text-muted';
    }
    
    if (btnAddCart) {
      btnAddCart.disabled = true;
      btnAddCart.innerHTML = '<i class="fa fa-shopping-cart"></i> Selecciona todas las opciones';
    }
  }

  // 🔍 DEBUG: Verificar datos del producto
  function debugProducto() {
    console.log('=== DEBUG PRODUCTO ===');
    console.log('Precio base del producto:', productoBase?.precio);
    console.log('Variantes disponibles:', variantes?.length || 0);
    console.log('Primera variante:', variantes?.[0]);
    console.log('Info descuento:', infoDescuento);
    console.log('Todos descuentos:', todosDescuentos);
    console.log('Atributos requeridos:', atributosRequeridos);
    console.log('======================');
  }

  // Llamar al debug después de inicializar
  setTimeout(debugProducto, 1000);

  inicializar();
});