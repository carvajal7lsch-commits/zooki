<?php
// Cargar datos de la mascota (el controlador ya nos provee la variable $mascota)
$m = $mascota; 
?>

<div class="section-card">
    <div class="section-header">
        <h2><i class="fas fa-edit"></i> Editar Ficha de Mascota</h2>
        <a href="index.php?action=dashboard" class="btn-outline">Cancelar</a>
    </div>

    <form action="index.php?action=actualizar_mascota" method="POST" enctype="multipart/form-data" class="modern-form">
        <input type="hidden" name="id_mascota" value="<?php echo $m['id_mascota']; ?>">
        
        <div class="form-container-split">
            <!-- Columna Izquierda: Foto -->
            <div class="form-column-photo">
                <div class="photo-preview-container">
                    <img id="preview" src="<?php echo $m['url_foto'] ? 'uploads/mascotas/'.$m['url_foto'] : 'https://via.placeholder.com/250?text=Sin+Foto'; ?>" alt="Foto mascota">
                </div>
                <div class="input-group">
                    <label for="foto">Cambiar Fotografía</label>
                    <input type="file" name="foto" id="foto" accept="image/*">
                    <small>JPG/PNG, máx. 5MB</small>
                </div>
            </div>

            <!-- Columna Derecha: Datos -->
            <div class="form-column-data">
                <div class="input-row">
                    <div class="input-group">
                        <label>Historia Clínica (No editable)</label>
                        <input type="text" value="<?php echo $m['numero_historia_clinica']; ?>" disabled class="input-disabled">
                    </div>
                    <div class="input-group">
                        <label for="nombre">Nombre de la Mascota *</label>
                        <input type="text" name="nombre" id="nombre" required value="<?php echo $m['nombre']; ?>">
                    </div>
                </div>

                <div class="input-row">
                    <div class="input-group">
                        <label for="especie">Especie *</label>
                        <input type="text" name="especie" id="especie" required value="<?php echo $m['especie']; ?>">
                    </div>
                    <div class="input-group">
                        <label for="raza">Raza</label>
                        <input type="text" name="raza" id="raza" value="<?php echo $m['raza']; ?>">
                    </div>
                </div>

                <div class="input-row">
                    <div class="input-group">
                        <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" value="<?php echo $m['fecha_nacimiento']; ?>">
                    </div>
                    <div class="input-group">
                        <label for="peso">Peso (Kg) *</label>
                        <input type="number" step="0.01" name="peso" id="peso" required value="<?php echo $m['peso']; ?>">
                    </div>
                </div>

                <div class="input-row">
                    <div class="input-group">
                        <label for="sexo">Sexo *</label>
                        <select name="sexo" id="sexo" required>
                            <option value="Macho" <?php echo ($m['sexo'] == 'Macho') ? 'selected' : ''; ?>>Macho</option>
                            <option value="Hembra" <?php echo ($m['sexo'] == 'Hembra') ? 'selected' : ''; ?>>Hembra</option>
                            <option value="Desconocido" <?php echo ($m['sexo'] == 'Desconocido') ? 'selected' : ''; ?>>Desconocido</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label for="color">Color *</label>
                        <input type="text" name="color" id="color" required value="<?php echo $m['color']; ?>">
                    </div>
                </div>

                <div class="input-row">
                    <div class="input-group">
                        <label for="estado">Estado de la Mascota *</label>
                        <select name="estado" id="estado" required>
                            <option value="1" <?php echo ($m['estado'] == 1) ? 'selected' : ''; ?>>Activa (En atención)</option>
                            <option value="0" <?php echo ($m['estado'] == 0) ? 'selected' : ''; ?>>Inactiva (Archivada)</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Propietario</label>
                        <input type="text" value="<?php echo $m['propietario_nombre']; ?>" disabled class="input-disabled">
                    </div>
                </div>

                <div class="form-actions mt-4">
                    <button type="submit" class="btn-primary w-100">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    // Previsualización de imagen
    document.getElementById('foto').onchange = function (evt) {
        var tgt = evt.target || window.event.srcElement,
            files = tgt.files;
        
        if (FileReader && files && files.length) {
            var fr = new FileReader();
            fr.onload = function () {
                document.getElementById('preview').src = fr.result;
            }
            fr.readAsDataURL(files[0]);
        }
    }
</script>
