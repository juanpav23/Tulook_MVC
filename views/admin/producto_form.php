<div class="container mt-4">
    <h2><?= isset($articulo) ? 'Editar Producto Base' : 'Nuevo Producto Base' ?></h2>

    <form action="<?= BASE_URL ?>?c=Admin&a=saveProducto" method="post" enctype="multipart/form-data">
        <?php if (!empty($articulo['ID_Articulo'])): ?>
            <input type="hidden" name="ID_Articulo" value="<?= $articulo['ID_Articulo'] ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label for="N_Articulo" class="form-label">Nombre del Producto</label>
            <input type="text" name="N_Articulo" id="N_Articulo" class="form-control" required
                value="<?= htmlspecialchars($articulo['N_Articulo'] ?? '') ?>">
        </div>

        <div class="row">
            <div class="col-md-3 mb-3">
                <label class="form-label">Categoría</label>
                <select name="ID_Categoria" class="form-select" required>
                    <option value="">Seleccionar...</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['ID_Categoria'] ?>" 
                            <?= ($articulo['ID_Categoria'] ?? '') == $cat['ID_Categoria'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['N_Categoria']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label">Subcategoría</label>
                <select name="ID_SubCategoria" class="form-select">
                    <option value="">Seleccionar...</option>
                    <?php foreach ($subcats as $sub): ?>
                        <option value="<?= $sub['ID_SubCategoria'] ?>" 
                            <?= ($articulo['ID_SubCategoria'] ?? '') == $sub['ID_SubCategoria'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sub['SubCategoria']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label">Género</label>
                <select name="ID_Genero" class="form-select">
                    <option value="">Seleccionar...</option>
                    <?php foreach ($generos as $g): ?>
                        <option value="<?= $g['ID_Genero'] ?>" 
                            <?= ($articulo['ID_Genero'] ?? '') == $g['ID_Genero'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($g['N_Genero']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label">Precio Base</label>
                <select name="ID_Precio" class="form-select" required>
                    <option value="">Seleccionar...</option>
                    <?php foreach ($precios as $p): ?>
                        <option value="<?= $p['ID_precio'] ?>" 
                            <?= ($articulo['ID_Precio'] ?? '') == $p['ID_precio'] ? 'selected' : '' ?>>
                            <?= $p['ID_precio'] ?> - $<?= number_format($p['Valor'], 2) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Color Base</label>
                <select name="ID_Color" class="form-select">
                    <option value="">Seleccionar...</option>
                    <?php foreach ($colors as $c): ?>
                        <option value="<?= $c['ID_Color'] ?>" 
                            <?= ($articulo['ID_Color'] ?? '') == $c['ID_Color'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['N_Color']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Talla Base</label>
                <select name="ID_Talla" class="form-select">
                    <option value="">Seleccionar...</option>
                    <?php foreach ($tallas as $t): ?>
                        <option value="<?= $t['ID_Talla'] ?>" 
                            <?= ($articulo['ID_Talla'] ?? '') == $t['ID_Talla'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['N_Talla']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Cantidad en Stock</label>
                <input type="number" name="Cantidad" class="form-control" min="0"
                    value="<?= htmlspecialchars($articulo['Cantidad'] ?? '') ?>" placeholder="Ej: 20">
            </div>
        </div>

        <!-- Imagen actual -->
        <?php if (!empty($articulo['Foto'])): ?>
            <div class="mb-3">
                <label class="form-label">Imagen actual:</label><br>
                <img src="<?= BASE_URL . htmlspecialchars($articulo['Foto']); ?>" 
                     alt="Imagen actual"
                     style="width:150px; border-radius:8px; margin-bottom:8px;">
                <input type="hidden" name="foto_actual" value="<?= htmlspecialchars($articulo['Foto']); ?>">
            </div>
        <?php endif; ?>

        <!-- NUEVO: Sistema de selección de rutas -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-folder"></i> Selección de Ruta de Imagen
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Categoría *</label>
                        <select name="categoria_ruta" class="form-control" id="categoria_ruta" required>
                            <option value="">Seleccionar categoría</option>
                            <option value="Ropa">Ropa</option>
                            <option value="Calzado">Calzado</option>
                            <option value="Accesorios">Accesorios</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Género *</label>
                        <select name="genero_ruta" class="form-control" id="genero_ruta" required>
                            <option value="">Seleccionar género</option>
                            <option value="Hombre">Hombre</option>
                            <option value="Mujer">Mujer</option>
                            <option value="Niños">Niños</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Subcategoría *</label>
                        <select name="subcategoria_ruta" class="form-control" id="subcategoria_ruta" required>
                            <option value="">Seleccionar subcategoría</option>
                            <!-- Las opciones se cargarán dinámicamente -->
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Nombre del archivo *</label>
                        <input type="text" name="nombre_archivo" class="form-control" id="nombre_archivo" 
                               placeholder="Ejemplo: camisa_azul.jpg" required>
                        <small class="form-text text-muted">Incluye la extensión (.jpg, .png, etc.)</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <label class="form-label">Ruta generada automáticamente</label>
                        <input type="text" name="Ruta_Imagen" class="form-control bg-light" id="ruta_generada" 
                               value="<?= htmlspecialchars($articulo['Foto'] ?? '') ?>" readonly>
                        <small class="form-text text-muted">Esta ruta se usará para guardar la imagen</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">O subir nueva imagen</label>
            <input type="file" name="foto" class="form-control">
            <small class="form-text text-muted">Si subes una imagen, se guardará en la ruta generada arriba</small>
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="Activo" class="form-check-input" id="activo"
                <?= !empty($articulo['Activo']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="activo">Activo</label>
        </div>

        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="<?= BASE_URL ?>?c=Admin&a=productos" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<!-- JavaScript para el sistema de rutas -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categoriaSelect = document.getElementById('categoria_ruta');
    const generoSelect = document.getElementById('genero_ruta');
    const subcategoriaSelect = document.getElementById('subcategoria_ruta');
    const nombreArchivoInput = document.getElementById('nombre_archivo');
    const rutaGeneradaInput = document.getElementById('ruta_generada');
    
    // Definir las subcategorías por categoría
    const subcategorias = {
        'Ropa': {
            'Hombre': ['Boxer', 'Camisa', 'Camiseta', 'Chaqueta', 'Jeans', 'Pantaloneta', 'Sudadera'],
            'Mujer': ['Camisa', 'Camiseta', 'Chaqueta', 'Jeans', 'Lenceria', 'Sudadera'],
            'Niños': ['Camisa', 'Camiseta', 'Jeans', 'Sudadera']
        },
        'Calzado': {
            'Hombre': ['Botas', 'Chanclas', 'Crocs', 'Sandalias', 'Tenis', 'Zapatos'],
            'Mujer': ['Botas', 'Chanclas', 'Crocs', 'Sandalias', 'Tenis', 'Zapatos'],
            'Niños': ['Botas', 'Chanclas', 'Crocs', 'Sandalias', 'Tenis', 'Zapatos']
        },
        'Accesorios': {
            'Hombre': ['Billeteras', 'Cinturon', 'Gafas', 'Gorras', 'Llaveros', 'Morrales', 'Perfumes', 'Relojes', 'Sombreros'],
            'Mujer': ['Billeteras', 'Cinturon', 'Gafas', 'Gorras', 'Llaveros', 'Morrales', 'Perfumes', 'Relojes', 'Sombreros'],
            'Niños': ['Billeteras', 'Cinturon', 'Gafas', 'Gorras', 'Llaveros', 'Morrales', 'Perfumes', 'Relojes', 'Sombreros']
        }
    };
    
    // Función para actualizar las subcategorías
    function actualizarSubcategorias() {
        const categoria = categoriaSelect.value;
        const genero = generoSelect.value;
        
        // Limpiar subcategorías
        subcategoriaSelect.innerHTML = '<option value="">Seleccionar subcategoría</option>';
        
        if (categoria && genero && subcategorias[categoria] && subcategorias[categoria][genero]) {
            subcategorias[categoria][genero].forEach(subcat => {
                const option = document.createElement('option');
                option.value = subcat;
                option.textContent = subcat;
                subcategoriaSelect.appendChild(option);
            });
        }
        
        generarRuta();
    }
    
    // Función para generar la ruta automáticamente
    function generarRuta() {
        const categoria = categoriaSelect.value;
        const genero = generoSelect.value;
        const subcategoria = subcategoriaSelect.value;
        const nombreArchivo = nombreArchivoInput.value;
        
        if (categoria && genero && subcategoria && nombreArchivo) {
            const ruta = `ImgProducto/${categoria}/${genero}/${subcategoria}/${nombreArchivo}`;
            rutaGeneradaInput.value = ruta;
        } else {
            rutaGeneradaInput.value = '';
        }
    }
    
    // Event listeners
    categoriaSelect.addEventListener('change', actualizarSubcategorias);
    generoSelect.addEventListener('change', actualizarSubcategorias);
    subcategoriaSelect.addEventListener('change', generarRuta);
    nombreArchivoInput.addEventListener('input', generarRuta);
    
    // Si hay una ruta existente, intentar pre-cargar los valores
    const rutaExistente = "<?= htmlspecialchars($articulo['Foto'] ?? '') ?>";
    if (rutaExistente) {
        preCargarRutaExistente(rutaExistente);
    }
    
    function preCargarRutaExistente(ruta) {
        const partes = ruta.split('/');
        if (partes.length >= 4) {
            // Asignar valores a los selects
            categoriaSelect.value = partes[1] || '';
            generoSelect.value = partes[2] || '';
            
            // Actualizar subcategorías y luego asignar valor
            actualizarSubcategorias();
            setTimeout(() => {
                subcategoriaSelect.value = partes[3] || '';
                nombreArchivoInput.value = partes.slice(4).join('/') || '';
                generarRuta();
            }, 100);
        }
    }

    // Inicializar si hay valores en los selects de categoría y género
    const categoriaForm = document.querySelector('select[name="ID_Categoria"]');
    const generoForm = document.querySelector('select[name="ID_Genero"]');
    
    if (categoriaForm && generoForm) {
        // Sincronizar con los selects principales si es posible
        categoriaForm.addEventListener('change', function() {
            const mapping = {
                '1': 'Ropa',
                '2': 'Accesorios', 
                '3': 'Calzado'
            };
            categoriaSelect.value = mapping[this.value] || '';
            actualizarSubcategorias();
        });
        
        generoForm.addEventListener('change', function() {
            const mapping = {
                '1': 'Hombre',
                '2': 'Mujer',
                '3': 'Niños'
            };
            generoSelect.value = mapping[this.value] || '';
            actualizarSubcategorias();
        });
        
        // Intentar sincronizar valores iniciales
        if (categoriaForm.value) {
            const mappingCat = {
                '1': 'Ropa',
                '2': 'Accesorios',
                '3': 'Calzado'
            };
            categoriaSelect.value = mappingCat[categoriaForm.value] || '';
        }
        
        if (generoForm.value) {
            const mappingGen = {
                '1': 'Hombre',
                '2': 'Mujer', 
                '3': 'Niños'
            };
            generoSelect.value = mappingGen[generoForm.value] || '';
        }
        
        actualizarSubcategorias();
    }
});
</script>



