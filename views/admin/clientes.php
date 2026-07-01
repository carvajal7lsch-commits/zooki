<?php
require_once '../config/Database.php';
require_once '../models/Usuario.php';
require_once '../models/Mascota.php';

$database = new Database();
$db = $database->getConnection();
$usuarioModel = new Usuario($db);
$mascotaModel = new Mascota($db);

// Obtener todos los propietarios (rol 4)
$propietarios = $usuarioModel->getAllOwners();

// Para cada propietario, obtener sus mascotas
foreach ($propietarios as &$propietario) {
    $mascotas = $mascotaModel->getByPropietario($propietario['documento']);
    $propietario['mascotas'] = $mascotas;
    $propietario['num_mascotas'] = count($mascotas);
}
unset($propietario);
?>

<div class="section-card">
    <div class="section-header">
        <h2>
            <i class="fas fa-users text-primary mr-1"></i>
            Clientes Registrados
        </h2>
        <div class="header-actions">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" id="searchClientes" placeholder="Buscar cliente...">
            </div>
        </div>
    </div>

    <div class="clients-grid" id="clientesGrid">
        <?php if (empty($propietarios)): ?>
            <div class="empty-state">
                <i class="fas fa-users-slash"></i>
                <p>No hay clientes registrados</p>
            </div>
        <?php else: ?>
            <?php foreach ($propietarios as $propietario): ?>
                <div class="client-card" data-search="<?= strtolower($propietario['nombre_completo'] . ' ' . $propietario['documento']) ?>">
                    <div class="client-header">
                        <div class="client-avatar">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($propietario['nombre_completo']) ?>&background=5560FF&color=fff" alt="<?= htmlspecialchars($propietario['nombre_completo']) ?>">
                        </div>
                        <div class="client-info">
                            <h3><?= htmlspecialchars($propietario['nombre_completo']) ?></h3>
                            <p class="client-doc"><?= htmlspecialchars($propietario['documento']) ?></p>
                            <div class="client-meta">
                                <span class="badge badge-<?= $propietario['estado'] == 1 ? 'success' : 'danger' ?>">
                                    <?= $propietario['estado'] == 1 ? 'Activo' : 'Inactivo' ?>
                                </span>
                                <span class="badge badge-info">
                                    <i class="fas fa-paw"></i> <?= $propietario['num_mascotas'] ?> mascota(s)
                                </span>
                            </div>
                        </div>
                        <div class="client-actions">
                            <button class="btn-icon" onclick="toggleClientMascotas('<?= $propietario['documento'] ?>')" title="Ver mascotas">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="client-contact">
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span><?= htmlspecialchars($propietario['telefono'] ?? 'No registrado') ?></span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span><?= htmlspecialchars($propietario['email'] ?? 'No registrado') ?></span>
                        </div>
                    </div>

                    <div class="client-mascotas d-none" id="mascotas-<?= $propietario['documento'] ?>">
                        <?php if (empty($propietario['mascotas'])): ?>
                            <p class="no-mascotas">Este cliente no tiene mascotas registradas</p>
                        <?php else: ?>
                            <div class="mascotas-list">
                                <?php foreach ($propietario['mascotas'] as $mascota): ?>
                                    <div class="mascota-mini-card">
                                        <div class="mascota-mini-info">
                                            <strong><?= htmlspecialchars($mascota['nombre']) ?></strong>
                                            <span class="mascota-especie"><?= htmlspecialchars($mascota['nombre_especie'] ?? $mascota['especie'] ?? 'No especificada') ?></span>
                                            <span class="mascota-raza"><?= htmlspecialchars($mascota['nombre_raza'] ?? $mascota['raza'] ?? 'No especificada') ?></span>
                                        </div>
                                        <div class="mascota-mini-status">
                                            <span class="badge badge-<?= $mascota['estado'] == 1 ? 'success' : 'danger' ?>">
                                                <?= $mascota['estado'] == 1 ? 'Activo' : 'Inactivo' ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>



<script>
function toggleClientMascotas(doc) {
    const mascotasDiv = document.getElementById('mascotas-' + doc);
    mascotasDiv.classList.toggle('d-none');
}

// Búsqueda de clientes
document.getElementById('searchClientes').addEventListener('keyup', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const cards = document.querySelectorAll('.client-card');
    
    cards.forEach(card => {
        const searchData = card.getAttribute('data-search');
        if (searchData.includes(searchTerm)) {
            card.classList.remove('d-none');
        } else {
            card.classList.add('d-none');
        }
    });
});
</script>
