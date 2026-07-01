<?php
require_once '../config/Database.php';

$database = new Database();
$db = $database->getConnection();

// Obtener configuración actual (si existe la tabla)
$config = [];
try {
    $config = $db->query("SELECT * FROM configuracion WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // La tabla no existe, usar valores por defecto
}

if (!$config) {
    $config = [
        'nombre_clinica' => '',
        'direccion' => '',
        'telefono' => '',
        'email' => '',
        'logo' => '',
        'horarios' => '',
        'dias_recordatorio' => 3
    ];
}
?>

<div class="compact-config">
    <div class="config-header-inline">
        <div>
            <h2><i class="fas fa-cog"></i> Configuración del Sistema</h2>
            <p>Parámetros principales de la clínica</p>
        </div>
        <button type="submit" form="formConfiguracion" class="btn-save-compact" id="btnSubmit">
            <i class="fas fa-save"></i> Guardar Cambios
        </button>
    </div>

    <form id="formConfiguracion" onsubmit="saveConfiguracion(event)" novalidate>
        <div class="compact-grid">
            
            <div class="form-group">
                <label>Nombre de la Clínica</label>
                <div class="input-container">
                    <i class="fas fa-clinic-medical icon-input"></i>
                    <input type="text" name="nombre_clinica" id="config_nombre_clinica" class="dense-input" value="<?= htmlspecialchars($config['nombre_clinica'] ?? '') ?>" placeholder="Ej: VetCare" required minlength="3" maxlength="100">
                </div>
                <span class="error-msg"></span>
            </div>

            <div class="form-group">
                <label>Teléfono</label>
                <div class="input-container">
                    <i class="fas fa-phone icon-input"></i>
                    <input type="text" name="telefono" id="config_telefono" class="dense-input" value="<?= htmlspecialchars($config['telefono'] ?? '') ?>" placeholder="Ej: 5551234567" required minlength="7" maxlength="15" pattern="[0-9]+">
                </div>
                <span class="error-msg"></span>
            </div>

            <div class="form-group">
                <label>Correo Electrónico</label>
                <div class="input-container">
                    <i class="fas fa-envelope icon-input"></i>
                    <input type="email" name="email" id="config_email" class="dense-input" value="<?= htmlspecialchars($config['email'] ?? '') ?>" placeholder="contacto@clinica.com" required maxlength="100">
                </div>
                <span class="error-msg"></span>
            </div>

            <div class="form-group">
                <label>Logotipo (URL)</label>
                <div class="input-container">
                    <i class="fas fa-image icon-input"></i>
                    <input type="url" name="logo" id="config_logo" class="dense-input" value="<?= htmlspecialchars($config['logo'] ?? '') ?>" placeholder="https://..." maxlength="255">
                </div>
                <span class="error-msg"></span>
            </div>

            <div class="form-group full-width">
                <label>Dirección Completa</label>
                <div class="input-container">
                    <i class="fas fa-map-marker-alt icon-input"></i>
                    <input type="text" name="direccion" id="config_direccion" class="dense-input" value="<?= htmlspecialchars($config['direccion'] ?? '') ?>" placeholder="Ej: Av. Principal 123, Ciudad" required minlength="5" maxlength="200">
                </div>
                <span class="error-msg"></span>
            </div>

            <div class="form-group">
                <label>Horarios de Atención</label>
                <div class="input-container textarea-container">
                    <i class="fas fa-clock icon-input"></i>
                    <textarea name="horarios" id="config_horarios" class="dense-input" placeholder="Ej: Lun-Vie 8am-6pm" maxlength="500"><?= htmlspecialchars($config['horarios'] ?? '') ?></textarea>
                </div>
                <span class="error-msg"></span>
            </div>

            <div class="form-group">
                <label>Anticipación Recordatorios (Días)</label>
                <div class="input-container">
                    <i class="fas fa-bell icon-input"></i>
                    <input type="number" name="dias_recordatorio" id="config_dias_recordatorio" class="dense-input" value="<?= htmlspecialchars($config['dias_recordatorio'] ?? 3) ?>" min="1" max="30" required>
                </div>
                <span class="error-msg"></span>
            </div>

        </div>
    </form>
</div>


<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formConfiguracion');
    const inputs = form.querySelectorAll('.dense-input');
    const btnSubmit = document.getElementById('btnSubmit');

    const validateInput = (input) => {
        const errorSpan = input.closest('.form-group').querySelector('.error-msg');
        let isValid = true;
        let errorMessage = '';

        if (input.required && !input.value.trim()) {
            isValid = false;
            errorMessage = 'Requerido';
        } else if (input.minLength > 0 && input.value.length < input.minLength) {
            isValid = false;
            errorMessage = `Mín. ${input.minLength} caracteres`;
        } else if (input.type === 'email' && input.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(input.value)) {
                isValid = false;
                errorMessage = 'Email inválido';
            }
        } else if (input.type === 'url' && input.value) {
            try {
                new URL(input.value);
            } catch (_) {
                isValid = false;
                errorMessage = 'URL inválida';
            }
        } else if (input.pattern && input.value) {
            const regex = new RegExp(`^${input.pattern}$`);
            if (!regex.test(input.value)) {
                isValid = false;
                errorMessage = 'Formato inválido';
            }
        } else if (input.type === 'number' && input.value) {
            if (Number(input.value) < Number(input.min) || Number(input.value) > Number(input.max)) {
                isValid = false;
                errorMessage = `Entre ${input.min} y ${input.max}`;
            }
        }

        if (input.name === 'telefono' && input.value) {
            input.value = input.value.replace(/[^0-9]/g, '');
            if(input.value.length > 0 && input.value.length < 7) {
                 isValid = false;
                 errorMessage = 'Mín. 7 dígitos';
            }
        }

        if (!isValid && input.value.length > 0 || (input.required && !input.value.trim() && document.activeElement !== input)) {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
            if(errorSpan) errorSpan.textContent = errorMessage;
        } else {
            input.classList.remove('is-invalid');
            if (input.value && isValid) input.classList.add('is-valid');
            else input.classList.remove('is-valid');
            if(errorSpan) errorSpan.textContent = '';
        }

        checkFormValidity();
    };

    const checkFormValidity = () => {
        let isFormValid = true;
        inputs.forEach(input => {
            if (input.classList.contains('is-invalid') || (input.required && !input.value.trim())) {
                isFormValid = false;
            }
        });
        btnSubmit.disabled = !isFormValid;
    };

    inputs.forEach(input => {
        input.addEventListener('input', () => validateInput(input));
        input.addEventListener('blur', () => validateInput(input));
    });

    checkFormValidity();
});

function saveConfiguracion(event) {
    event.preventDefault();
    
    Swal.fire({
        title: 'Guardando...',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Guardado',
                    text: 'Cambios aplicados correctamente',
                    confirmButtonColor: '#2563eb',
                    timer: 1500
                });
            }, 800);
        }
    });
}
</script>
