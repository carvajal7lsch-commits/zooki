<?php
$total_mascotas = count($mascotas);
$con_cita = 0;
foreach ($mascotas as $m) {
    if (!empty($m['proxima_cita'])) $con_cita++;
}
?>

<section class="portal-home">
    <div class="portal-hero">
        <div class="portal-hero-text">
            <p class="portal-hero-kicker">Tu espacio en Zooki</p>
            <h1>Hola, <?php echo htmlspecialchars($primer_nombre); ?> 👋</h1>
            <p class="portal-hero-desc">
                Aquí puedes ver el historial clínico, las citas y las vacunas de tus compañeros.
            </p>
            <div style="margin-top: 1rem;">
                <button type="button" class="portal-btn-book" id="btnOpenBookingModal" style="background-color: var(--primary); color: #ffffff; border: none; border-radius: 12px; padding: 0.85rem 1.5rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; font-family: inherit; font-size: 0.95rem; box-shadow: 0 4px 14px rgba(85, 96, 255, 0.25); transition: transform 0.2s, box-shadow 0.2s;">
                    <i class="far fa-calendar-plus"></i> Agendar una Cita
                </button>
            </div>
        </div>
        <?php if ($total_mascotas > 0): ?>
        <div class="portal-hero-stats">
            <div class="portal-stat">
                <span class="portal-stat-value"><?php echo $total_mascotas; ?></span>
                <span class="portal-stat-label"><?php echo $total_mascotas === 1 ? 'Mascota' : 'Mascotas'; ?></span>
            </div>
            <?php if ($con_cita > 0): ?>
            <div class="portal-stat portal-stat--accent">
                <span class="portal-stat-value"><?php echo $con_cita; ?></span>
                <span class="portal-stat-label">Con cita próxima</span>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php if (empty($mascotas)): ?>
        <div class="portal-empty">
            <div class="portal-empty-icon"><i class="fas fa-paw"></i></div>
            <h2>Aún no hay mascotas registradas</h2>
            <p>Cuando la clínica registre a tu compañero, aparecerá aquí con su historial y citas.</p>
        </div>
    <?php else: ?>
        <div class="portal-section-head">
            <h2>Mis mascotas</h2>
            <p>Toca una tarjeta para ver todo el detalle</p>
        </div>

        <div class="portal-pet-grid">
            <?php foreach ($mascotas as $m): ?>
                <?php
                    $foto = $m['url_foto'] ? 'uploads/mascotas/' . htmlspecialchars($m['url_foto']) : null;
                    $cita = $m['proxima_cita'] ?? null;
                ?>
                <article class="portal-pet-card" role="button" tabindex="0"
                         onclick="verDetalle(<?php echo (int)$m['id_mascota']; ?>)"
                         onkeydown="if(event.key==='Enter')verDetalle(<?php echo (int)$m['id_mascota']; ?>)">
                    <div class="portal-pet-card-photo">
                        <?php if ($foto): ?>
                            <img src="<?php echo $foto; ?>" alt="<?php echo htmlspecialchars($m['nombre']); ?>">
                        <?php else: ?>
                            <div class="portal-pet-card-placeholder">
                                <i class="fas fa-dog"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="portal-pet-card-body">
                        <h3><?php echo htmlspecialchars($m['nombre']); ?></h3>
                        <p class="portal-pet-meta"><?php echo htmlspecialchars($m['especie']); ?> · <?php echo htmlspecialchars($m['raza']); ?></p>
                        <span class="portal-pet-hc">HC <?php echo htmlspecialchars($m['numero_historia_clinica']); ?></span>

                        <?php if ($cita): ?>
                            <div class="portal-pet-next">
                                <i class="far fa-calendar-check"></i>
                                <span>
                                    Próxima cita:
                                    <strong><?php echo date('d/m/Y', strtotime($cita['fecha'])); ?></strong>
                                    a las <?php echo substr($cita['hora'], 0, 5); ?>
                                </span>
                            </div>
                        <?php else: ?>
                            <div class="portal-pet-next portal-pet-next--muted">
                                <i class="far fa-calendar"></i>
                                <span>Sin citas programadas</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="portal-pet-card-action">
                        Ver detalle <i class="fas fa-arrow-right"></i>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- Panel lateral — detalle mascota -->
<div id="portalDrawerOverlay" class="portal-drawer-overlay" onclick="cerrarDrawer()" aria-hidden="true"></div>
<aside id="portalDrawer" class="portal-drawer" aria-hidden="true" aria-labelledby="drawerPetTitle">
    <div class="portal-drawer-header">
        <button type="button" class="portal-drawer-back" onclick="cerrarDrawer()" aria-label="Volver">
            <i class="fas fa-arrow-left"></i>
        </button>
        <div class="portal-drawer-title-wrap">
            <h2 id="drawerPetTitle">—</h2>
            <p id="drawerPetSubtitle"></p>
        </div>
        <button type="button" class="portal-drawer-close" onclick="cerrarDrawer()" aria-label="Cerrar">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div id="drawerPetSummary" class="portal-drawer-summary"></div>

    <nav class="portal-drawer-tabs" role="tablist">
        <button type="button" class="portal-tab active" data-tab="historial" role="tab" aria-selected="true">
            <i class="fas fa-history"></i> Historial
        </button>
        <button type="button" class="portal-tab" data-tab="citas" role="tab" aria-selected="false">
            <i class="far fa-calendar-alt"></i> Citas
        </button>
        <button type="button" class="portal-tab" data-tab="vacunas" role="tab" aria-selected="false">
            <i class="fas fa-syringe"></i> Vacunas
        </button>
    </nav>

    <div class="portal-drawer-body">
        <div id="tab-historial" class="portal-tab-panel active" role="tabpanel">
            <div id="historialContent" class="portal-loading">Cargando historial…</div>
        </div>
        <div id="tab-citas" class="portal-tab-panel" role="tabpanel">
            <div id="citasContent"></div>
        </div>
        <div id="tab-vacunas" class="portal-tab-panel" role="tabpanel">
            <div id="vacunasContent"></div>
        </div>
    </div>
</aside>

<!-- Modal para Agendar Cita desde el Portal (Sin estilos CSS inline prohibidos, usando clases predefinidas de Zooki o estilos del portal) -->
<div id="portalBookingModal" class="portal-drawer-overlay d-none" style="display: none; position: fixed; inset: 0; background: rgba(9, 10, 32, 0.55); backdrop-filter: blur(6px); z-index: 1000; align-items: center; justify-content: center; padding: 1.5rem;">
    <div class="portal-booking-card" style="background: #ffffff; border-radius: 20px; padding: 2rem; width: min(480px, 100%); box-shadow: 0 20px 50px rgba(15, 23, 42, 0.15); position: relative;">
        <button type="button" id="btnCloseBookingModal" style="position: absolute; top: 1.25rem; right: 1.25rem; background: none; border: none; font-size: 1.25rem; color: #64748b; cursor: pointer;"><i class="fas fa-times"></i></button>
        <h3 style="margin-top: 0; color: #0f172a; font-size: 1.35rem; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;"><i class="far fa-calendar-plus" style="color: var(--primary);"></i> Agendar Nueva Cita</h3>
        <p style="color: #64748b; margin-bottom: 1.5rem; font-size: 0.9rem;">Elige a tu compañero y el horario de tu preferencia.</p>
        
        <form id="portalBookingForm">
            <div class="input-group" style="margin-bottom: 1rem;">
                <label style="font-weight: 600; color: #0f172a; margin-bottom: 0.5rem; display: block; font-size: 0.85rem;">Seleccionar Mascota</label>
                <div class="input-wrapper" style="display: flex; align-items: center; border: 1px solid rgba(85,96,255,0.25); border-radius: 12px; padding: 0.65rem 0.85rem;">
                    <i class="fas fa-paw" style="color: #64748b; margin-right: 0.5rem;"></i>
                    <select name="id_mascota" id="booking_mascota" required style="border: none; background: transparent; width: 100%; outline: none; font-family: inherit;">
                        <option value="">Selecciona...</option>
                        <?php foreach ($mascotas as $m): ?>
                            <option value="<?php echo (int)$m['id_mascota']; ?>"><?php echo htmlspecialchars($m['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="input-group" style="margin-bottom: 1rem;">
                <label style="font-weight: 600; color: #0f172a; margin-bottom: 0.5rem; display: block; font-size: 0.85rem;">Tipo de Cita</label>
                <div class="input-wrapper" style="display: flex; align-items: center; border: 1px solid rgba(85,96,255,0.25); border-radius: 12px; padding: 0.65rem 0.85rem;">
                    <i class="fas fa-notes-medical" style="color: #64748b; margin-right: 0.5rem;"></i>
                    <select name="id_tipo_cita" id="booking_tipo_cita" required style="border: none; background: transparent; width: 100%; outline: none; font-family: inherit;">
                        <option value="">Cargando tipos de cita...</option>
                    </select>
                </div>
            </div>

            <div class="input-group" style="margin-bottom: 1rem;">
                <label style="font-weight: 600; color: #0f172a; margin-bottom: 0.5rem; display: block; font-size: 0.85rem;">Seleccionar Veterinario</label>
                <div class="input-wrapper" style="display: flex; align-items: center; border: 1px solid rgba(85,96,255,0.25); border-radius: 12px; padding: 0.65rem 0.85rem;">
                    <i class="fas fa-user-md" style="color: #64748b; margin-right: 0.5rem;"></i>
                    <select name="doc_veterinario" id="booking_veterinario" required style="border: none; background: transparent; width: 100%; outline: none; font-family: inherit;">
                        <option value="">Selecciona veterinario...</option>
                    </select>
                </div>
            </div>

            <div class="form-grid-2-gap" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                <div class="input-group" style="margin-bottom: 0;">
                    <label style="font-weight: 600; color: #0f172a; margin-bottom: 0.5rem; display: block; font-size: 0.85rem;">Fecha</label>
                    <div class="input-wrapper" style="display: flex; align-items: center; border: 1px solid rgba(85,96,255,0.25); border-radius: 12px; padding: 0.65rem 0.85rem;">
                        <i class="far fa-calendar-alt" style="color: #64748b; margin-right: 0.5rem;"></i>
                        <input type="date" name="fecha" id="booking_fecha" required style="border: none; background: transparent; width: 100%; outline: none; font-family: inherit;">
                    </div>
                </div>

                <div class="input-group" style="margin-bottom: 0;">
                    <label style="font-weight: 600; color: #0f172a; margin-bottom: 0.5rem; display: block; font-size: 0.85rem;">Hora</label>
                    <div class="input-wrapper" style="display: flex; align-items: center; border: 1px solid rgba(85,96,255,0.25); border-radius: 12px; padding: 0.65rem 0.85rem;">
                        <i class="far fa-clock" style="color: #64748b; margin-right: 0.5rem;"></i>
                        <select name="hora" id="booking_hora" required style="border: none; background: transparent; width: 100%; outline: none; font-family: inherit;">
                            <option value="">Elige fecha...</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="input-group" style="margin-bottom: 1.5rem;">
                <label style="font-weight: 600; color: #0f172a; margin-bottom: 0.5rem; display: block; font-size: 0.85rem;">Motivo de la Cita</label>
                <div class="input-wrapper" style="display: flex; align-items: center; border: 1px solid rgba(85,96,255,0.25); border-radius: 12px; padding: 0.65rem 0.85rem;">
                    <textarea name="motivo" id="booking_motivo" required placeholder="Describe brevemente el motivo..." style="border: none; background: transparent; width: 100%; outline: none; font-family: inherit; resize: none; height: 60px;"></textarea>
                </div>
            </div>

            <button type="submit" class="btn-primary" style="background-color: var(--primary); color: #ffffff; width: 100%; border: none; border-radius: 12px; padding: 0.85rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem; font-size: 1rem; box-shadow: 0 4px 12px rgba(85,96,255,0.2);">
                <span>Confirmar Cita</span>
                <i class="far fa-calendar-check"></i>
            </button>
        </form>
    </div>
</div>
