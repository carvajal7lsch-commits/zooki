/* Portal del propietario — Zooki */

function escapeHtml(str) {
    if (str == null) return '';
    const div = document.createElement('div');
    div.textContent = String(str);
    return div.innerHTML;
}

function formatFecha(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr.includes('T') ? dateStr : dateStr + 'T12:00:00');
    return d.toLocaleDateString('es-CO', { day: 'numeric', month: 'short', year: 'numeric' });
}

function formatFechaHora(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    return d.toLocaleDateString('es-CO', {
        weekday: 'short',
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function badgeEstado(estado) {
    const e = (estado || 'pendiente').toLowerCase();
    return `<span class="portal-badge portal-badge--${escapeHtml(e)}">${escapeHtml(estado || 'pendiente')}</span>`;
}

function openDrawer() {
    document.getElementById('portalDrawerOverlay').classList.add('is-open');
    document.getElementById('portalDrawer').classList.add('is-open');
    document.getElementById('portalDrawerOverlay').setAttribute('aria-hidden', 'false');
    document.getElementById('portalDrawer').setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
}

function cerrarDrawer() {
    document.getElementById('portalDrawerOverlay').classList.remove('is-open');
    document.getElementById('portalDrawer').classList.remove('is-open');
    document.getElementById('portalDrawerOverlay').setAttribute('aria-hidden', 'true');
    document.getElementById('portalDrawer').setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
}

function showTab(tabId, btn) {
    document.querySelectorAll('.portal-tab').forEach(t => {
        t.classList.remove('active');
        t.setAttribute('aria-selected', 'false');
    });
    document.querySelectorAll('.portal-tab-panel').forEach(p => p.classList.remove('active'));

    const tab = btn || document.querySelector(`.portal-tab[data-tab="${tabId}"]`);
    if (tab) {
        tab.classList.add('active');
        tab.setAttribute('aria-selected', 'true');
    }
    const panel = document.getElementById(`tab-${tabId}`);
    if (panel) panel.classList.add('active');
}

document.querySelectorAll('.portal-tab').forEach(btn => {
    btn.addEventListener('click', () => showTab(btn.dataset.tab, btn));
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') cerrarDrawer();
});

function renderSummary(mascota) {
    const especie = mascota.nombre_especie || mascota.especie || '—';
    const raza = mascota.nombre_raza || mascota.raza || '—';
    const edad = mascota.fecha_nacimiento
        ? formatFecha(mascota.fecha_nacimiento)
        : 'No registrada';

    document.getElementById('drawerPetSummary').innerHTML = `
        <div class="portal-summary-grid">
            <div class="portal-summary-item">
                <span>Especie / Raza</span>
                <strong>${escapeHtml(especie)} · ${escapeHtml(raza)}</strong>
            </div>
            <div class="portal-summary-item">
                <span>Historia clínica</span>
                <strong>${escapeHtml(mascota.numero_historia_clinica || '—')}</strong>
            </div>
            <div class="portal-summary-item">
                <span>Sexo</span>
                <strong>${escapeHtml(mascota.sexo || '—')}</strong>
            </div>
            <div class="portal-summary-item">
                <span>Peso</span>
                <strong>${mascota.peso ? escapeHtml(mascota.peso) + ' kg' : '—'}</strong>
            </div>
            <div class="portal-summary-item" style="grid-column: 1 / -1;">
                <span>Nacimiento</span>
                <strong>${escapeHtml(edad)}</strong>
            </div>
        </div>
    `;
}

async function verDetalle(id) {
    openDrawer();
    showTab('historial', document.querySelector('.portal-tab[data-tab="historial"]'));

    document.getElementById('drawerPetTitle').textContent = 'Cargando…';
    document.getElementById('drawerPetSubtitle').textContent = '';
    document.getElementById('drawerPetSummary').innerHTML = '';
    document.getElementById('historialContent').innerHTML = '<div class="portal-loading">Cargando historial…</div>';
    document.getElementById('citasContent').innerHTML = '';
    document.getElementById('vacunasContent').innerHTML = '';

    try {
        const res = await (await fetch(`index.php?action=ver_detalle_mascota_propietario_ajax&id_mascota=${id}`)).json();

        if (!res.success) {
            alert(res.message || 'No se pudo cargar la información');
            cerrarDrawer();
            return;
        }

        const m = res.mascota;
        const especie = m.nombre_especie || m.especie || '';
        const raza = m.nombre_raza || m.raza || '';

        document.getElementById('drawerPetTitle').textContent = m.nombre;
        document.getElementById('drawerPetSubtitle').textContent = `${especie} · ${raza}`;
        renderSummary(m);

        // Historial
        if (!res.historial.length) {
            document.getElementById('historialContent').innerHTML = `
                <div class="portal-empty-inline">
                    <i class="fas fa-stethoscope"></i>
                    <p>Aún no hay consultas registradas para ${escapeHtml(m.nombre)}.</p>
                </div>`;
        } else {
            let html = '<div class="portal-timeline">';
            res.historial.forEach(h => {
                html += `
                <div class="portal-timeline-item">
                    <div class="portal-timeline-date">${formatFechaHora(h.fecha_hora)}</div>
                    <div class="portal-timeline-card">
                        <h4>${escapeHtml(h.motivo_consulta)}</h4>
                        <p><strong>Diagnóstico:</strong> ${escapeHtml(h.diagnostico)}</p>
                        <p><strong>Tratamiento:</strong> ${escapeHtml(h.plan_tratamiento)}</p>
                        <p><strong>Veterinario:</strong> ${escapeHtml(h.veterinario)}</p>
                    </div>
                </div>`;
            });
            html += '</div>';
            document.getElementById('historialContent').innerHTML = html;
        }

        // Citas
        if (!res.citas.length) {
            document.getElementById('citasContent').innerHTML = `
                <div class="portal-empty-inline">
                    <i class="far fa-calendar"></i>
                    <p>No hay citas programadas.</p>
                </div>`;
        } else {
            let html = '';
            res.citas.forEach(c => {
                html += `
                <div class="portal-list-item">
                    <div class="portal-list-icon portal-list-icon--cita"><i class="far fa-calendar-check"></i></div>
                    <div class="portal-list-body">
                        <strong>${formatFecha(c.fecha)} · ${escapeHtml((c.hora || '').substring(0, 5))}</strong>
                        <p>${escapeHtml(c.motivo)}</p>
                        ${c.veterinario_nombre ? `<p>Veterinario: ${escapeHtml(c.veterinario_nombre)}</p>` : ''}
                        ${badgeEstado(c.estado)}
                    </div>
                </div>`;
            });
            document.getElementById('citasContent').innerHTML = html;
        }

        // Vacunas
        if (!res.vacunas.length) {
            document.getElementById('vacunasContent').innerHTML = `
                <div class="portal-empty-inline">
                    <i class="fas fa-syringe"></i>
                    <p>No hay vacunas registradas.</p>
                </div>`;
        } else {
            let html = '';
            res.vacunas.forEach(v => {
                html += `
                <div class="portal-list-item">
                    <div class="portal-list-icon portal-list-icon--vacuna"><i class="fas fa-syringe"></i></div>
                    <div class="portal-list-body">
                        <strong>${escapeHtml(v.nombre_vacuna)}</strong>
                        <p>Aplicada: ${formatFecha(v.fecha_aplicacion)}</p>
                        ${v.fecha_proxima_dosis ? `<p style="color:var(--z-primary);font-weight:600;">Próxima dosis: ${formatFecha(v.fecha_proxima_dosis)}</p>` : ''}
                        ${v.laboratorio ? `<p>Laboratorio: ${escapeHtml(v.laboratorio)}</p>` : ''}
                    </div>
                </div>`;
            });
            document.getElementById('vacunasContent').innerHTML = html;
        }

    } catch (e) {
        console.error(e);
        alert('Error al cargar los datos de la mascota');
        cerrarDrawer();
    }
}

/* Lógica de Agendamiento desde el Portal (HU-26) */
document.addEventListener('DOMContentLoaded', () => {
    const bookingModal = document.getElementById('portalBookingModal');
    const openBtn = document.getElementById('btnOpenBookingModal');
    const closeBtn = document.getElementById('btnCloseBookingModal');
    const form = document.getElementById('portalBookingForm');
    const tipoCitaSelect = document.getElementById('booking_tipo_cita');
    const vetSelect = document.getElementById('booking_veterinario');
    const dateInput = document.getElementById('booking_fecha');
    const horaSelect = document.getElementById('booking_hora');

    if (!bookingModal) return;

    // Abrir modal y cargar catálogos iniciales
    openBtn.addEventListener('click', async () => {
        bookingModal.style.display = 'flex';
        bookingModal.classList.remove('d-none');
        
        // Limpiar formulario
        form.reset();
        horaSelect.innerHTML = '<option value="">Elige fecha...</option>';

        try {
            // Cargar tipos de cita
            const resTipos = await (await fetch('index.php?action=portal_get_tipos_cita_ajax')).json();
            if (resTipos.success) {
                tipoCitaSelect.innerHTML = '<option value="">Selecciona...</option>';
                resTipos.tipos.forEach(t => {
                    tipoCitaSelect.innerHTML += `<option value="${t.id_tipo_cita}" data-duracion="${t.duracion_minutos}">${escapeHtml(t.nombre_tipo)} (${t.duracion_minutos} min)</option>`;
                });
            }

            // Cargar veterinarios
            const resVets = await (await fetch('index.php?action=portal_get_vets_ajax')).json();
            vetSelect.innerHTML = '<option value="">Selecciona...</option>';
            resVets.forEach(v => {
                vetSelect.innerHTML += `<option value="${v.documento}">Dr(a). ${escapeHtml(v.nombre_completo)}</option>`;
            });

        } catch (e) {
            console.error('Error al cargar catálogos:', e);
        }
    });

    // Cerrar modal
    closeBtn.addEventListener('click', () => {
        bookingModal.style.display = 'none';
        bookingModal.classList.add('d-none');
    });

    // Consultar disponibilidad al cambiar fecha o veterinario
    async function actualizarDisponibilidad() {
        const vet = vetSelect.value;
        const fecha = dateInput.value;
        const tipoCitaOpt = tipoCitaSelect.options[tipoCitaSelect.selectedIndex];
        
        if (!vet || !fecha || !tipoCitaOpt || !tipoCitaOpt.value) {
            horaSelect.innerHTML = '<option value="">Selecciona campos...</option>';
            return;
        }

        const duracion = tipoCitaOpt.dataset.duracion || 30;
        horaSelect.innerHTML = '<option value="">Buscando horas...</option>';

        try {
            const url = `index.php?action=portal_get_horas_ajax&doc_veterinario=${vet}&fecha=${fecha}&duracion_minutos=${duracion}`;
            const res = await (await fetch(url)).json();
            if (res.success && res.sugerencias) {
                if (res.sugerencias.length === 0) {
                    horaSelect.innerHTML = '<option value="">Sin horas disponibles</option>';
                } else {
                    horaSelect.innerHTML = '<option value="">Selecciona hora...</option>';
                    res.sugerencias.forEach(h => {
                        horaSelect.innerHTML += `<option value="${h}">${h}</option>`;
                    });
                }
            } else {
                horaSelect.innerHTML = '<option value="">Error al cargar horas</option>';
            }
        } catch (e) {
            console.error('Error obteniendo disponibilidad:', e);
            horaSelect.innerHTML = '<option value="">Error de conexión</option>';
        }
    }

    vetSelect.addEventListener('change', actualizarDisponibilidad);
    dateInput.addEventListener('change', actualizarDisponibilidad);
    tipoCitaSelect.addEventListener('change', actualizarDisponibilidad);

    // Enviar formulario de agendamiento
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.querySelector('span').textContent = 'Agendando...';

        const fd = new FormData(form);

        try {
            const res = await (await fetch('index.php?action=portal_agendar_cita_ajax', {
                method: 'POST',
                body: fd,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })).json();

            if (res.success) {
                // Si la cita requiere confirmación asíncrona por correo
                if (res.id_cita) {
                    const mailFd = new FormData();
                    mailFd.append('id_cita', res.id_cita);
                    mailFd.append('tipo', 'confirmacion_nueva');
                    fetch('index.php?action=enviar_email_ajax', {
                        method: 'POST',
                        body: mailFd
                    }).catch(console.error);
                }

                Swal.fire({
                    icon: 'success',
                    title: '¡Cita Reservada!',
                    text: res.message || 'Tu cita ha sido agendada y confirmada con éxito.',
                    confirmButtonColor: '#5560FF'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo agendar',
                    text: res.message || 'Inténtalo nuevamente.',
                    confirmButtonColor: '#5560FF'
                });
                submitBtn.disabled = false;
                submitBtn.querySelector('span').textContent = 'Confirmar Cita';
            }
        } catch (error) {
            console.error('Error al agendar cita:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error de Red',
                text: 'No pudimos conectarnos con el servidor. Inténtalo más tarde.',
                confirmButtonColor: '#5560FF'
            });
            submitBtn.disabled = false;
            submitBtn.querySelector('span').textContent = 'Confirmar Cita';
        }
    });
});

