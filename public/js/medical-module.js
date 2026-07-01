/* 
    Zooki Medical Module - Comprehensive External JS 
    Logic for Pets, Owners, and Consultations.
*/

// --- GLOBAL EVENT LISTENERS (ZOOKI_REGLAS COMPLIANCE) ---
document.addEventListener('DOMContentLoaded', () => {
    // 1. Modales - Cierre con botón X y Cancelar
    document.querySelectorAll('.close-modal-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            closeModal(e.currentTarget.dataset.modal);
        });
    });

    // 2. Modales - Cierre al hacer click en el fondo (backdrop)
    document.querySelectorAll('.close-modal-backdrop').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal(modal.dataset.modal);
            }
        });
    });

    // 3. Tabs en modales
    document.querySelectorAll('.modal-tab-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            if(typeof switchModalTab === 'function') {
                switchModalTab(e, e.currentTarget.dataset.targetTab);
            }
        });
    });

    // 4. Toggle Switches (Estado Activo/Inactivo)
    document.querySelectorAll('.status-toggle-input').forEach(toggle => {
        toggle.addEventListener('change', (e) => {
            const isChecked = e.target.checked;
            const targetInput = document.getElementById(e.target.dataset.target);
            const textTarget = document.getElementById(e.target.dataset.textTarget);
            
            if (targetInput) targetInput.value = isChecked ? 1 : 0;
            if (textTarget) {
                textTarget.textContent = isChecked ? e.target.dataset.textActive : e.target.dataset.textInactive;
                textTarget.className = isChecked ? 'text-success' : 'text-danger';
            }
        });
    });

    // 5. Image Upload - Previews y Clear buttons
    document.querySelectorAll('.image-upload-input').forEach(input => {
        input.addEventListener('change', (e) => {
            if(typeof previewImg === 'function') {
                previewImg(e.target, e.target.dataset.preview, e.target.dataset.clearBtn);
            }
        });
    });
    document.querySelectorAll('.clear-preview-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            if(typeof clearPreview === 'function') {
                clearPreview(e, e.currentTarget.dataset.input, e.currentTarget.dataset.preview, e.currentTarget.id);
            }
        });
    });

    // 6. Validaciones en tiempo real
    const validations = [
        { selector: '.validate-select', func: 'validarSelect' },
        { selector: '.validate-doc', func: 'validarDocumento', event: 'input' },
        { selector: '.validate-name', func: 'validarNombre', event: 'input' },
        { selector: '.validate-tel', func: 'validarTelefono', event: 'input' },
        { selector: '.validate-email', func: 'validarEmail', event: 'input' }
    ];
    validations.forEach(val => {
        document.querySelectorAll(val.selector).forEach(el => {
            const ev = val.event || 'change';
            el.addEventListener(ev, (e) => {
                if(typeof window[val.func] === 'function') {
                    window[val.func](e.target);
                }
            });
        });
    });

    // 7. Eventos específicos de edición (Razas, etc)
    const loadBreedsSelects = document.querySelectorAll('.load-breeds-select');
    loadBreedsSelects.forEach(select => {
        select.addEventListener('change', (e) => {
            if(typeof loadBreeds === 'function') {
                loadBreeds(e.target.value, e.target.dataset.target);
            }
        });
    });

    const checkOtherBreedSelects = document.querySelectorAll('.check-other-breed');
    checkOtherBreedSelects.forEach(select => {
        select.addEventListener('change', (e) => {
            if(typeof checkOtherBreed === 'function') {
                checkOtherBreed(e.target, e.target.dataset.target);
            }
        });
    });

    // 8. Búsqueda de propietario
    const ownerSearchInputs = document.querySelectorAll('.owner-search-input');
    ownerSearchInputs.forEach(input => {
        input.addEventListener('keyup', (e) => {
            if(typeof searchOwnersForEdit === 'function') {
                searchOwnersForEdit(e.target.value);
            }
        });
    });

    const closeOwnerSelections = document.querySelectorAll('.close-selected-owner');
    closeOwnerSelections.forEach(btn => {
        btn.addEventListener('click', () => {
            if(typeof clearEditOwnerSelection === 'function') {
                clearEditOwnerSelection();
            }
        });
    });

    // 9. Forms de Edición (Submits)
    const formEditMascota = document.getElementById('formEditMascota');
    if(formEditMascota) {
        formEditMascota.addEventListener('submit', (e) => {
            if(typeof updatePet === 'function') {
                updatePet(e);
            }
        });
    }

    const formEditPropietario = document.getElementById('formEditPropietario');
    if(formEditPropietario) {
        formEditPropietario.addEventListener('submit', (e) => {
            if(typeof updateOwner === 'function') {
                updateOwner(e);
            }
        });
    }
});

// --- UTILITIES ---
function openModal(id) {
    const m = document.getElementById(id);
    if (m) {
        m.classList.remove('d-none');
        m.style.display = 'flex';
    }
}

function closeModal(id) {
    const m = document.getElementById(id);
    if (m) {
        m.classList.add('d-none');
        m.style.display = 'none';
    }
}

function abrirModalRegistro(tipo) {
    const m = document.getElementById('modalNuevoRegistro');
    if (m) {
        openModal('modalNuevoRegistro');
        const tabProp = document.querySelector('#modalNuevoRegistro .modal-tab-btn[data-target-tab="tabNuevoPropietario"]');
        const tabMasc = document.querySelector('#modalNuevoRegistro .modal-tab-btn[data-target-tab="tabNuevaMascota"]');
        if (tipo === 'propietario' && tabProp) {
            tabProp.click();
        } else if (tipo === 'mascota' && tabMasc) {
            tabMasc.click();
        }
    } else {
        // Fallback para vistas antiguas que no tienen modalNuevoRegistro
        if (tipo === 'propietario') openModal('modalPropietario');
        else openModal('modalMascota');
    }
}

function previewImg(input, previewId, btnId = null) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => { 
            const img = document.getElementById(previewId);
            img.src = e.target.result;
            img.style.display = 'block';
            if (btnId) {
                const btn = document.getElementById(btnId);
                if (btn) btn.style.display = 'flex';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function clearPreview(e, inputId, previewId, btnId) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    document.getElementById(inputId).value = '';
    const img = document.getElementById(previewId);
    img.src = '';
    img.style.display = 'none';
    const btn = document.getElementById(btnId);
    if (btn) btn.style.display = 'none';
}

function viewImage(src) {
    const lb = document.getElementById('lightboxVisor');
    if (lb) { lb.querySelector('img').src = src; lb.style.display = 'flex'; }
}

function closeLightbox() {
    const lb = document.getElementById('lightboxVisor');
    if (lb) lb.style.display = 'none';
}

function switchModalTab(event, tabId) {
    const modal = event.target.closest('.modal, .users-modal, .modal-content');
    if (!modal) return;
    modal.querySelectorAll('.modal-tab-btn').forEach(b => b.classList.remove('active'));
    modal.querySelectorAll('.modal-tab-content').forEach(c => c.classList.remove('active'));
    event.currentTarget.classList.add('active');
    document.getElementById(tabId).classList.add('active');
}

function switchModule(module) {
    const globalHeader = document.querySelector('.section-header');
    if (globalHeader) globalHeader.style.display = 'flex';
    
    const dView = document.getElementById('dossierView');
    if (dView) { dView.style.display = 'none'; dView.classList.add('d-none'); }
    
    const directory = document.getElementById('ownersDirectory');
    if (directory) directory.style.display = 'block';

    const petsSearch = document.getElementById('searchRowPets');
    const ownersSearch = document.getElementById('searchRowOwners');
    if (petsSearch && ownersSearch) {
        petsSearch.classList.toggle('is-active', module === 'pets');
        ownersSearch.classList.toggle('is-active', module === 'owners');
        petsSearch.style.display = module === 'pets' ? '' : 'none';
        ownersSearch.style.display = module === 'owners' ? '' : 'none';
    }

    const petViewToggle = document.getElementById('petViewToggle');
    const ownerViewToggle = document.getElementById('ownerViewToggle');
    if (petViewToggle && ownerViewToggle) {
        petViewToggle.style.display = module === 'pets' ? 'flex' : 'none';
        ownerViewToggle.style.display = module === 'owners' ? 'flex' : 'none';
    }

    document.getElementById('tabPets').classList.toggle('active', module === 'pets');
    document.getElementById('tabOwners').classList.toggle('active', module === 'owners');
    
    document.getElementById('modulePets').classList.toggle('active', module === 'pets');
    document.getElementById('moduleOwners').classList.toggle('active', module === 'owners');

    if (module === 'owners') {
        loadOwners();
    }
}


// --- PROPIETARIOS ---
async function loadOwners() {
    try {
        const res = await fetch('index.php?action=listar_propietarios_ajax');
        const owners = await res.json();
        const tbody = document.getElementById('ownersTableBody');
        const grid = document.getElementById('ownersGrid');
        if (tbody) tbody.innerHTML = '';
        if (grid) grid.innerHTML = '';

        owners.forEach(o => {
            if (tbody) {
                const tr = document.createElement('tr');
                tr.setAttribute('data-estado', o.estado);
                tr.innerHTML = `
                    <td><b>${o.documento}</b></td>
                    <td>${o.nombre_completo}</td>
                    <td><i class="fas fa-phone-alt"></i> ${o.telefono}</td>
                    <td>${o.email}</td>
                    <td><span class="hc-badge" style="background:var(--primary-soft); color:var(--primary);">${o.total_mascotas} Mascotas</span></td>
                    <td><span class="status-badge ${o.estado == 1 ? 'active' : 'inactive'}">${o.estado == 1 ? 'Activo' : 'Inactivo'}</span></td>
                    <td><div class="action-buttons"><button onclick="openOwnerDossier('${o.documento}')" class="btn-icon history" title="Ver Expediente"><i class="fas fa-folder-open"></i></button></div></td>
                `;
                tbody.appendChild(tr);
            }
            if (grid) {
                const card = document.createElement('div');
                card.className = 'client-card person-card';
                card.setAttribute('data-estado', o.estado);
                card.innerHTML = `
                    <div class="card-header-mini">
                        <div class="avatar-mini cursor-pointer" onclick="openOwnerDossier('${o.documento}')">
                            <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(o.nombre_completo)}&background=5560FF&color=fff&size=128" alt="${o.nombre_completo}">
                        </div>
                        <div class="status-indicator">
                            <label class="toggle-switch" onclick="event.stopPropagation()" title="${o.estado == 1 ? 'Cliente Activo (Clic para desactivar)' : 'Cliente Inactivo (Clic para activar)'}">
                                <input type="checkbox" ${o.estado == 1 ? 'checked' : ''} onchange="toggleUserStatus('${o.documento}', this.checked ? 1 : 0)">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                    <div class="card-body-mini cursor-pointer" onclick="openOwnerDossier('${o.documento}')">
                        <h3 class="card-title-mini">${o.nombre_completo}</h3>
                        <div class="card-tags-mini">
                            ${o.estado == 0 ? '<span class="tag-mini"><i class="bi bi-moon-stars"></i> Inactivo</span>' : ''}
                            <span class="tag-mini"><i class="bi bi-phone"></i> ${o.telefono || 'N/A'}</span>
                            <span class="tag-mini"><i class="fas fa-paw"></i> ${o.total_mascotas} Paciente(s)</span>
                        </div>
                        <div class="card-contact-mini">
                            <span class="contact-text-mini"><i class="bi bi-person-badge"></i> ${o.documento}</span>
                        </div>
                    </div>
                    <div class="card-footer-mini">
                        <button class="action-btn-mini" onclick="editOwner('${o.documento}')" title="Editar">
                            <i class="bi bi-pencil-fill"></i>
                        </button>
                        <button class="action-btn-mini" onclick="openOwnerDossier('${o.documento}')" title="Ver detalles de mascotas">
                            <i class="bi bi-eye-fill"></i>
                        </button>
                    </div>
                `;
                grid.appendChild(card);
            }
        });
    } catch (e) { console.error(e); }
}

function toggleUserStatus(doc, newStatus, isCurrentUser = false) {
    if (isCurrentUser) {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true
        });
        
        Toast.fire({
            icon: 'warning',
            title: 'No puedes desactivarte a ti mismo'
        });
        return;
    }
    
    const actionWord = newStatus === 1 ? 'activado' : 'desactivado';
    
    fetch('index.php?action=cambiar_estado_usuario_ajax', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `documento=${encodeURIComponent(doc)}&estado=${newStatus}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });
            
            Toast.fire({
                icon: 'success',
                title: `Usuario ${actionWord}`
            });
            loadOwners();
        } else {
            const toggle = document.querySelector(`input[onchange*="${doc}"]`);
            if (toggle) {
                toggle.checked = !toggle.checked;
            }
            Swal.fire('Error', data.message || 'No se pudo actualizar el estado', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const toggle = document.querySelector(`input[onchange*="${doc}"]`);
        if (toggle) {
            toggle.checked = !toggle.checked;
        }
        Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
    });
}

function togglePetStatus(id, newStatus) {
    const actionWord = newStatus === 1 ? 'activada' : 'desactivada';
    
    fetch('index.php?action=cambiar_estado_mascota_ajax', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id_mascota=${id}&estado=${newStatus}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });
            
            Toast.fire({
                icon: 'success',
                title: `Mascota ${actionWord}`
            });
            
            const gridCard = document.querySelector(`.pet-card[data-id="${id}"]`);
            if (gridCard) gridCard.setAttribute('data-estado', newStatus);
            const tableRow = document.querySelector(`#petsTable tr[data-id="${id}"]`);
            if (tableRow) tableRow.setAttribute('data-estado', newStatus);
        } else {
            const toggle = document.querySelector(`input[onchange*="togglePetStatus(${id}"]`);
            if (toggle) {
                toggle.checked = !toggle.checked;
            }
            Swal.fire('Error', data.message || 'No se pudo actualizar el estado', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const toggle = document.querySelector(`input[onchange*="togglePetStatus(${id}"]`);
        if (toggle) {
            toggle.checked = !toggle.checked;
        }
        Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
    });
}

let currentDossierDoc = '';

async function viewPetInDossier(doc, petId, petName) {
    switchModule('owners');
    await openOwnerDossier(doc);
    setTimeout(() => {
        loadPetDashboard(petId, petName);
    }, 100);
}

async function openOwnerDossier(doc) {
    currentDossierDoc = doc;
    try {
        // 1. Obtener datos del dueño
        const owner = await (await fetch(`index.php?action=get_propietario_ajax&doc=${doc}`)).json();
        
        // 2. Obtener sus mascotas
        const pets = await (await fetch(`index.php?action=listar_mascotas_propietario_ajax&doc=${doc}`)).json();

        // 3. Renderizar info del dueño
        document.getElementById('dossierOwnerName').innerText = owner.nombre_completo;
        document.getElementById('dossierOwnerDoc').innerText = owner.documento;
        document.getElementById('dossierOwnerPhone').innerText = owner.telefono || 'Sin teléfono';
        document.getElementById('dossierOwnerEmail').innerText = owner.email || 'Sin email';
        
        const estadoBadge = document.getElementById('dossierOwnerEstado');
        if (estadoBadge) {
            estadoBadge.className = `status-badge ${owner.estado == 1 ? 'active' : 'inactive'}`;
            estadoBadge.innerText = owner.estado == 1 ? 'Activo' : 'Inactivo';
        }

        // Renderizar info del dueño para la tarjeta compacta (Dashboard)
        const nameDash = document.getElementById('dossierOwnerNameDash');
        if (nameDash) nameDash.innerText = owner.nombre_completo;
        const docDash = document.getElementById('dossierOwnerDocDash');
        if (docDash) docDash.innerText = owner.documento;
        const phoneDash = document.getElementById('dossierOwnerPhoneDash');
        if (phoneDash) phoneDash.innerText = owner.telefono || 'Sin teléfono';
        const emailDash = document.getElementById('dossierOwnerEmailDash');
        if (emailDash) emailDash.innerText = owner.email || 'Sin email';

        const estadoBadgeDash = document.getElementById('dossierOwnerEstadoDash');
        if (estadoBadgeDash) {
            estadoBadgeDash.className = `status-badge ${owner.estado == 1 ? 'active' : 'inactive'}`;
            estadoBadgeDash.innerText = owner.estado == 1 ? 'Activo' : 'Inactivo';
        }

        // 4. Renderizar mascotas en cards con scroll horizontal
        const petsScroll = document.getElementById('dossierPetsScroll');
        const petsCount = document.getElementById('dossierPetsCount');
        if (petsCount) petsCount.innerText = `Mostrando ${pets.length} pacientes asociados`;
        petsScroll.innerHTML = '';

        if (pets.length === 0) {
            petsScroll.innerHTML = '<div style="text-align:center; padding:3rem; color:var(--text-muted); width:100%;"><i class="fas fa-paw" style="font-size:3rem; margin-bottom:1rem; opacity:0.3; display:block;"></i>Este cliente aún no tiene mascotas registradas.</div>';
        } else {
            pets.forEach(m => {
                const card = document.createElement('div');
                card.className = 'pet-dossier-card';
                card.style.cursor = 'pointer';
                card.onclick = (e) => {
                    if (!e.target.closest('.btn-sec')) {
                        loadPetDashboard(m.id_mascota, m.nombre);
                    }
                };
                
                // Simulación visual de estado clínico para el mockup (Ajustar con backend real luego)
                let statusClass = 'al-dia';
                let statusText = 'AL DÍA';
                if (!m.numero_historia_clinica) {
                    statusClass = 'desconocido'; statusText = 'SIN HISTORIA';
                } else if (Math.random() > 0.7) {
                    statusClass = 'vacuna-pendiente'; statusText = 'VACUNA PENDIENTE';
                }

                card.innerHTML = `
                    <div class="pet-dossier-card-top">
                        <img src="${m.url_foto ? 'uploads/mascotas/'+m.url_foto : 'img/default-pet.png'}" class="pet-dossier-card-photo" onerror="this.src='https://ui-avatars.com/api/?name=${m.nombre}&background=random'">
                        <div class="pet-dossier-card-info">
                            <h4>${m.nombre}</h4>
                            <p>${m.especie} • ${m.raza || 'Mestizo'}</p>
                        </div>
                    </div>
                    <div class="pet-dossier-card-grid">
                        <div class="data-group">
                            <label>GÉNERO</label>
                            <span>${m.sexo === 'M' ? 'MACHO' : (m.sexo === 'H' ? 'HEMBRA' : 'DESCONOCIDO')}</span>
                        </div>
                        <div class="data-group right-align">
                            <label>HISTORIA</label>
                            <span style="color:var(--primary);">${m.numero_historia_clinica || 'SIN REGISTRO'}</span>
                        </div>
                    </div>
                    <div class="pet-dossier-status">
                        <span class="status-pill ${statusClass}">${statusText}</span>
                    </div>
                    <div class="pet-dossier-card-actions">
                        <button class="btn-ficha" onclick="loadPetDashboard('${m.id_mascota}', '${m.nombre}')">Ver Ficha</button>
                        <button class="btn-sec" onclick="alert('Funcionalidad en construcción')" title="Historial Médico"><i class="fas fa-file-medical"></i></button>
                    </div>
                `;
                petsScroll.appendChild(card);
            });
        }

        // Transición de vistas
        const searchRowPets = document.getElementById('searchRowPets');
        const searchRowOwners = document.getElementById('searchRowOwners');
        if (searchRowPets) searchRowPets.style.display = 'none';
        if (searchRowOwners) searchRowOwners.style.display = 'none';

        document.getElementById('ownersDirectory').style.display = 'none';
        const dView = document.getElementById('dossierView');
        if (dView) { dView.style.display = 'block'; dView.classList.remove('d-none'); }
        
        const toggleHeader = document.querySelector('.head-view-toggle');
        if (toggleHeader) toggleHeader.style.display = 'none';

        const listSection = document.getElementById('dossierListSection');
        if (listSection) listSection.style.display = 'block';
        const dashSection = document.getElementById('dossierPetDashboardSection');
        if (dashSection) dashSection.style.display = 'none';
        
        // Reset local filters when opening dossier
        const searchInput = document.getElementById('dossierPetSearch');
        if (searchInput) searchInput.value = '';
        const speciesFilter = document.getElementById('dossierPetSpeciesFilter');
        if (speciesFilter) speciesFilter.value = '';

        window.scrollTo({ top: 0, behavior: 'smooth' });

    } catch (e) { console.error('Error al abrir dossier:', e); }
}

function hideDossier() {
    const searchRowPets = document.getElementById('searchRowPets');
    const searchRowOwners = document.getElementById('searchRowOwners');
    const activeTab = document.getElementById('tabPets').classList.contains('active') ? 'pets' : 'owners';
    
    if (searchRowPets) searchRowPets.style.display = activeTab === 'pets' ? 'flex' : 'none';
    if (searchRowOwners) searchRowOwners.style.display = activeTab === 'owners' ? 'flex' : 'none';

    const dView = document.getElementById('dossierView');
    if (dView) { dView.style.display = 'none'; dView.classList.add('d-none'); }
    document.getElementById('ownersDirectory').style.display = 'block';
    
    const toggleHeader = document.querySelector('.head-view-toggle');
    if (toggleHeader) toggleHeader.style.display = '';
}

function addNewPetFromDossier() {
    abrirModalRegistro('mascota');
    const ownerInput = document.getElementById('petOwnerDoc');
    if (ownerInput) {
        selectOwner({ documento: currentDossierDoc, nombre_completo: document.getElementById('dossierOwnerName').innerText });
    }
}

function editOwnerFromDossier() {
    editOwner(currentDossierDoc);
}

async function saveOwner(e) {
    e.preventDefault();
    const fd = new FormData(e.target);
    try {
        const res = await (await fetch('index.php?action=guardar_propietario_ajax', { method: 'POST', body: fd })).json();
        if (res.success) {
            alert('¡Propietario guardado!');
            const input = document.getElementById('petOwnerDoc');
            if (input) input.value = fd.get('documento');
            closeModal('modalPropietario');
            e.target.reset();
            loadOwners();
        } else alert(res.message);
    } catch (err) { console.error(err); }
}

async function editOwner(doc) {
    try {
        const o = await (await fetch(`index.php?action=get_propietario_ajax&doc=${doc}`)).json();
        if (o) {
            if (document.getElementById('edit_owner_tipo_doc') && o.tipo_documento) {
                document.getElementById('edit_owner_tipo_doc').value = o.tipo_documento;
            }
            document.getElementById('edit_owner_doc').value = o.documento;
            document.getElementById('edit_owner_doc_orig').value = o.documento;
            document.getElementById('edit_owner_nombre').value = o.nombre_completo;
            document.getElementById('edit_owner_tel').value = o.telefono;
            document.getElementById('edit_owner_email').value = o.email;
            document.getElementById('edit_owner_estado').value = o.estado;
            
            // Sincronizar el toggle visual
            const toggle = document.getElementById('toggle_edit_owner_estado');
            if (toggle) {
                toggle.checked = (o.estado == 1);
                // Disparar evento change manualmente para actualizar textos
                toggle.dispatchEvent(new Event('change'));
            }
            
            openModal('modalEditarPropietario');
        }
    } catch (e) { console.error(e); }
}

async function updateOwner(e) {
    e.preventDefault();
    const fd = new FormData(e.target);
    try {
        const res = await (await fetch('index.php?action=actualizar_propietario_ajax', { method: 'POST', body: fd })).json();
        if (res.success) { 
            loadOwners(); 
            closeModal('modalEditarPropietario'); 
            Swal.fire({
                toast: true, position: 'top-end', showConfirmButton: false, timer: 2000,
                icon: 'success', title: 'Propietario actualizado con éxito'
            });
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    } catch (e) { 
        console.error(e); 
        Swal.fire('Error', 'No se pudo actualizar', 'error');
    }
}

// --- MASCOTAS ---
function switchView(view) {
    const listBtn = document.getElementById('btnViewList');
    if (!listBtn) return;
    listBtn.classList.toggle('active', view === 'list');
    document.getElementById('btnViewGrid').classList.toggle('active', view === 'grid');
    document.getElementById('listView').classList.toggle('active', view === 'list');
    document.getElementById('gridView').classList.toggle('active', view === 'grid');
    localStorage.setItem('petViewPreference', view);
}

function switchOwnerView(view) {
    const listBtn = document.getElementById('btnOwnerViewList');
    if (!listBtn) return;
    listBtn.classList.toggle('active', view === 'list');
    document.getElementById('btnOwnerViewGrid').classList.toggle('active', view === 'grid');
    document.getElementById('ownerListView').classList.toggle('active', view === 'list');
    document.getElementById('ownerGridView').classList.toggle('active', view === 'grid');
    localStorage.setItem('ownerViewPreference', view);
}

async function initSpecies() {
    try {
        const especies = await (await fetch('index.php?action=listar_especies_ajax')).json();
        const sels = [document.getElementById('new_especie'), document.getElementById('edit_especie')];
        sels.forEach(s => {
            if (!s) return;
            s.innerHTML = '<option value="">Seleccione...</option>';
            especies.forEach(e => s.innerHTML += `<option value="${e.id_especie}">${e.nombre_especie}</option>`);
            s.innerHTML += `<option value="Otra">Otra / No listada</option>`;
        });
    } catch (e) { console.error(e); }
}

let allColoresBase = [];
async function initColores() {
    try {
        allColoresBase = await (await fetch('index.php?action=listar_colores_ajax')).json();
        const selects = ['#newSelectedColoresInput', '#editSelectedColoresInput'];
        
        selects.forEach(id => {
            const selectEl = document.querySelector(id);
            if (!selectEl) return;
            selectEl.innerHTML = '';
            
            allColoresBase.forEach(c => {
                const option = document.createElement('option');
                option.value = c.id_color;
                option.textContent = c.nombre_color;
                selectEl.appendChild(option);
            });
            
            // Inicializar Select2 en español y con placeholder
            $(selectEl).select2({
                placeholder: "Seleccione un color base o combinación...",
                allowClear: true,
                width: '100%',
                language: {
                    noResults: function() {
                        return "No se encontraron colores";
                    }
                }
            });
        });
    } catch (e) { console.error('Error cargando colores:', e); }
}


async function loadBreeds(idEspecie, targetId, selectedRaza = null) {
    const sel = document.getElementById(targetId);
    if (!sel) return;
    if (!idEspecie) { sel.innerHTML = '<option value="">Seleccione especie...</option>'; return; }
    try {
        const razas = await (await fetch(`index.php?action=listar_razas_ajax&id_especie=${idEspecie}`)).json();
        sel.innerHTML = '<option value="">Seleccione raza...</option>';
        razas.forEach(r => {
            const s = (selectedRaza && (selectedRaza == r.id_raza || selectedRaza == r.nombre_raza)) ? 'selected' : '';
            sel.innerHTML += `<option value="${r.id_raza}" ${s}>${r.nombre_raza}</option>`;
        });
        sel.innerHTML += `<option value="Otra">Otra / No listada</option>`;
    } catch (e) { console.error(e); }
}

function validatePetForm(form) {
    let valid = true;
    const inputs = form.querySelectorAll('input, select, textarea');
    form.querySelectorAll('.error-msg').forEach(m => m.remove());
    form.querySelectorAll('.input-group').forEach(g => g.classList.remove('error'));
    
    inputs.forEach(f => {
        // Ignorar campos ocultos o no requeridos que esten vacios y sean válidos
        if (!f.willValidate) return;

        if (!f.checkValidity()) {
            valid = false;
            const group = f.closest('.input-group') || f.parentElement;
            if (group && !group.querySelector('.error-msg')) {
                group.classList.add('error');
                const msg = document.createElement('span');
                msg.className = 'error-msg';
                // Mensajes personalizados
                if (f.validity.valueMissing) {
                    msg.innerText = 'Requerido';
                } else if (f.validity.rangeOverflow) {
                    msg.innerText = 'Máximo permitido: ' + f.max;
                } else if (f.validity.rangeUnderflow) {
                    msg.innerText = 'Mínimo permitido: ' + f.min;
                } else if (f.validity.tooLong) {
                    msg.innerText = 'Demasiado largo';
                } else {
                    msg.innerText = 'Valor inválido';
                }
                group.appendChild(msg);
            }
        }
    });
    return valid;
}

async function savePet(e) {
    e.preventDefault();
    if (typeof validatePetForm === 'function' && !validatePetForm(e.target)) return;
    try {
        const response = await fetch('index.php?action=guardar_mascota_ajax', { method: 'POST', body: new FormData(e.target) });
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        const text = await response.text();
        let res;
        try {
            res = JSON.parse(text);
        } catch (jsonErr) {
            console.error('Failed to parse JSON. Raw response:', text);
            throw new Error('La respuesta del servidor no es un JSON válido.');
        }
        if (res.success) {
            location.reload();
        } else {
            alert(res.message || 'Error desconocido al registrar la mascota.');
        }
    } catch (err) {
        console.error(err);
        alert('Ocurrió un error al procesar el registro: ' + err.message);
    }
}

async function editPet(id) {
    try {
        const m = await (await fetch(`index.php?action=get_mascota_ajax&id=${id}`)).json();
        if (m) {
            document.getElementById('edit_id_mascota').value = m.id_mascota;
            document.getElementById('edit_nombre').value = m.nombre;
            document.getElementById('edit_especie').value = m.id_especie;
            await loadBreeds(m.id_especie, 'edit_raza', m.id_raza);
            document.getElementById('edit_peso').value = m.peso;
            document.getElementById('edit_fecha_nac').value = m.fecha_nacimiento;
            document.getElementById('edit_sexo').value = m.sexo;
            document.getElementById('edit_estado').value = m.estado;
            
            // Sincronizar el toggle visual de mascota
            const toggle = document.getElementById('toggle_edit_pet_estado');
            if (toggle) {
                toggle.checked = (m.estado == 1);
                toggle.dispatchEvent(new Event('change'));
            }

            const selectedIds = m.colores_ids ? m.colores_ids.split(',') : [];
            $('#editSelectedColoresInput').val(selectedIds).trigger('change');
            selectEditOwner({ documento: m.doc_propietario, nombre_completo: m.propietario_nombre });
            
            const p = document.getElementById('editPreview');
            if (p) {
                p.src = m.url_foto ? 'uploads/mascotas/' + m.url_foto : 'https://ui-avatars.com/api/?name=' + m.nombre;
                p.classList.remove('d-none');
                const btn = document.getElementById('btnClearEditImg');
                if (btn) btn.classList.remove('d-none');
            }
            openModal('modalEditarMascota');
        }
    } catch (e) { console.error(e); }
}

async function updatePet(e) {
    e.preventDefault();
    if (typeof validatePetForm === 'function' && !validatePetForm(e.target)) return;
    try {
        const response = await fetch('index.php?action=actualizar_mascota_ajax', { method: 'POST', body: new FormData(e.target) });
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        const text = await response.text();
        let res;
        try {
            res = JSON.parse(text);
        } catch (jsonErr) {
            console.error('Failed to parse JSON. Raw response:', text);
            throw new Error('La respuesta del servidor no es un JSON válido.');
        }
        if (res.success) {
            Swal.fire({
                toast: true, position: 'top-end', showConfirmButton: false, timer: 2000,
                icon: 'success', title: 'Mascota actualizada con éxito'
            }).then(() => location.reload());
        } else {
            Swal.fire('Error', res.message || 'Error desconocido al actualizar la mascota.', 'error');
        }
    } catch (err) {
        console.error(err);
        Swal.fire('Error', 'Ocurrió un error al actualizar los datos: ' + err.message, 'error');
    }
}

// --- BUSCADOR DE PROPIETARIOS ---
let allOwnersList = [];

async function searchOwnersForPet(term) {
    if (allOwnersList.length === 0) {
        const res = await fetch('index.php?action=listar_propietarios_ajax');
        allOwnersList = await res.json();
    }
    const suggestions = document.getElementById('ownerSuggestions');
    if (term.length < 2) { suggestions.style.display = 'none'; return; }
    const matches = allOwnersList.filter(o => 
        o.nombre_completo.toLowerCase().includes(term.toLowerCase()) || 
        o.documento.includes(term)
    ).slice(0, 5);
    if (matches.length > 0) {
        suggestions.innerHTML = '';
        matches.forEach(m => {
            const div = document.createElement('div');
            div.className = 'suggestion-item';
            div.innerHTML = `<span class="name">${m.nombre_completo}</span><br><small>${m.documento}</small>`;
            div.onclick = () => selectOwner(m);
            suggestions.appendChild(div);
        });
        suggestions.style.display = 'block';
    } else suggestions.style.display = 'none';
}

function selectOwner(owner) {
    document.getElementById('petOwnerDoc').value = owner.documento;
    const input = document.getElementById('ownerSearchInput');
    const wrapper = input.closest('.input-wrapper');
    if (wrapper) wrapper.style.display = 'none';
    else input.style.display = 'none';
    document.getElementById('ownerSuggestions').style.display = 'none';
    document.getElementById('selectedOwnerName').innerText = owner.nombre_completo;
    document.getElementById('selectedOwnerInfo').classList.remove('d-none');
}

function clearOwnerSelection() {
    document.getElementById('petOwnerDoc').value = '';
    const input = document.getElementById('ownerSearchInput');
    input.value = ''; 
    const wrapper = input.closest('.input-wrapper');
    if (wrapper) wrapper.style.display = '';
    else input.style.display = 'block';
    document.getElementById('selectedOwnerInfo').classList.add('d-none');
}

async function searchOwnersForEdit(term) {
    if (allOwnersList.length === 0) {
        const res = await fetch('index.php?action=listar_propietarios_ajax');
        allOwnersList = await res.json();
    }
    const suggestions = document.getElementById('editOwnerSuggestions');
    if (term.length < 2) { suggestions.style.display = 'none'; return; }
    const matches = allOwnersList.filter(o => o.nombre_completo.toLowerCase().includes(term.toLowerCase()) || o.documento.includes(term)).slice(0, 5);
    if (matches.length > 0) {
        suggestions.innerHTML = '';
        matches.forEach(m => {
            const div = document.createElement('div');
            div.className = 'suggestion-item';
            div.innerHTML = `<span class="name">${m.nombre_completo}</span><br><small>${m.documento}</small>`;
            div.onclick = () => selectEditOwner(m);
            suggestions.appendChild(div);
        });
        suggestions.style.display = 'block';
    } else suggestions.style.display = 'none';
}

function selectEditOwner(owner) {
    document.getElementById('edit_petOwnerDoc').value = owner.documento;
    const input = document.getElementById('editOwnerSearchInput');
    if(input) {
        input.style.display = 'none';
        const wrapper = input.closest('.input-wrapper');
        if (wrapper) wrapper.style.display = 'none';
    }
    document.getElementById('editOwnerSuggestions').style.display = 'none';
    document.getElementById('selectedEditOwnerName').innerText = owner.nombre_completo;
    document.getElementById('selectedEditOwnerInfo').classList.remove('d-none');
    document.getElementById('selectedEditOwnerInfo').style.display = 'flex'; // Fix inline style if present
}

function clearEditOwnerSelection() {
    document.getElementById('edit_petOwnerDoc').value = '';
    const input = document.getElementById('editOwnerSearchInput');
    if(input) {
        input.value = ''; 
        input.style.display = 'block';
        const wrapper = input.closest('.input-wrapper');
        if (wrapper) wrapper.style.display = 'flex';
    }
    document.getElementById('selectedEditOwnerInfo').classList.add('d-none');
    document.getElementById('selectedEditOwnerInfo').style.display = '';
}

function filterTableBySpecies(species) {
    filterTable();
}

function filterTable() {
    const term = document.getElementById('tableSearch') ? document.getElementById('tableSearch').value.toLowerCase() : '';
    const statusRadio = document.querySelector('input[name="estado_mascotas"]:checked');
    const statusVal = statusRadio ? statusRadio.value : '';
    const speciesSelect = document.querySelector('.command-center-filter-select');
    const speciesVal = speciesSelect ? speciesSelect.value.toLowerCase() : '';

    document.querySelectorAll('#petsTable tbody tr').forEach(r => {
        if (r.classList.contains('empty-table')) return;
        const txt = r.innerText.toLowerCase();
        const rStatus = r.getAttribute('data-estado');
        const rSpeciesCell = r.cells[2] ? r.cells[2].innerText.toLowerCase() : '';

        const matchesSearch = txt.includes(term);
        const matchesStatus = (statusVal === '' || rStatus === statusVal);
        const matchesSpecies = (speciesVal === '' || rSpeciesCell.includes(speciesVal));

        r.style.display = (matchesSearch && matchesStatus && matchesSpecies) ? '' : 'none';
    });

    document.querySelectorAll('.pet-card').forEach(c => {
        const txt = c.innerText.toLowerCase();
        const cStatus = c.getAttribute('data-estado');
        const cSpecies = c.getAttribute('data-species') ? c.getAttribute('data-species').toLowerCase() : '';

        const matchesSearch = txt.includes(term);
        const matchesStatus = (statusVal === '' || cStatus === statusVal);
        const matchesSpecies = (speciesVal === '' || cSpecies === speciesVal);

        c.style.display = (matchesSearch && matchesStatus && matchesSpecies) ? '' : 'none';
    });
}

// --- CONSULTAS ---
function openConsultationModal(id, nombre) {
    document.getElementById('consultation_id_mascota').value = id;
    document.getElementById('consultationPetName').innerText = nombre;
    document.getElementById('formConsulta').reset();
    document.getElementById('treatmentsList').innerHTML = '';
    openModal('modalConsulta');
}

async function saveConsultation(e) {
    e.preventDefault();
    const fd = new FormData(e.target);
    if (!fd.get('motivo') || !fd.get('diagnostico')) { alert('Motivo y Diagnóstico son obligatorios.'); return; }
    try {
        const res = await (await fetch('index.php?action=registrar_consulta_ajax', { method: 'POST', body: fd })).json();
        if (res.success) { alert(res.message); closeModal('modalConsulta'); location.reload(); } else alert(res.message);
    } catch (e) { console.error(e); }
}

function addTreatmentRow() {
    const list = document.getElementById('treatmentsList');
    const row = document.createElement('div');
    row.className = 'treatment-row';
    row.innerHTML = `
        <div class="input-group"><label>Medicamento</label><input type="text" name="med_nombre[]" required></div>
        <div class="input-group"><label>Dosis</label><input type="text" name="med_dosis[]" required></div>
        <div class="input-group"><label>Vía</label><select name="med_via[]"><option value="Oral">Oral</option><option value="Subcutánea">Subcutánea</option></select></div>
        <div class="input-group"><label>Duración</label><input type="text" name="med_duracion[]" required></div>
        <button type="button" class="btn-remove-treatment" onclick="this.parentElement.remove()"><i class="fas fa-trash"></i></button>
    `;
    list.appendChild(row);
}

async function viewMedicalHistory(id, nombre) {
    document.getElementById('historyPetName').innerText = nombre;
    const timeline = document.getElementById('historyTimeline');
    const vaccineList = document.getElementById('vaccineList');
    const hcNumber = document.getElementById('historyHCNumber');
    const petSpecie = document.getElementById('historyPetSpecie');
    const petAge = document.getElementById('historyPetAge');

    timeline.innerHTML = '<div class="empty-table"><i class="fas fa-spinner fa-spin"></i><p>Cargando historial...</p></div>';
    vaccineList.innerHTML = '';
    hcNumber.innerText = '---';
    
    openHistorialDrawer();

    try {
        const response = await fetch(`index.php?action=listar_historial_ajax&id_mascota=${id}`);
        const data = await response.json();
        
        // 1. Datos de Mascota
        const m = data.mascota;
        hcNumber.innerText = m.numero_historia_clinica || 'Pte. Asignación';
        petSpecie.innerText = m.nombre_especie;
        petAge.innerText = m.fecha_nacimiento ? calculateAge(m.fecha_nacimiento) : 'Edad desconocida';

        // 2. Resumen de Vacunación
        if (data.vacunas && data.vacunas.length > 0) {
            data.vacunas.forEach(v => {
                const vCard = document.createElement('div');
                vCard.className = 'vaccine-card';
                vCard.innerHTML = `
                    <div class="vaccine-icon"><i class="fas fa-syringe"></i></div>
                    <div class="vaccine-info">
                        <h5>${v.nombre_vacuna}</h5>
                        <span>${new Date(v.fecha_aplicacion).toLocaleDateString()}</span>
                    </div>
                `;
                vaccineList.appendChild(vCard);
            });
        } else {
            vaccineList.innerHTML = '<p class="sub-text">No hay registros de vacunación.</p>';
        }

        // 3. Consultas Clínicas (Línea de Tiempo)
        timeline.innerHTML = '';
        if (data.consultas && data.consultas.length > 0) {
            data.consultas.forEach(c => {
                const item = document.createElement('div');
                item.className = 'history-item';
                
                // Construir HTML de archivos
                let filesHtml = '';
                if (c.archivos && c.archivos.length > 0) {
                    filesHtml = '<div class="clinical-section"><label><i class="fas fa-paperclip"></i> Archivos Adjuntos</label><div style="display:flex; gap:0.5rem; flex-wrap:wrap;">';
                    c.archivos.forEach(file => {
                        const isImg = ['jpg', 'jpeg', 'png'].includes(file.extension);
                        filesHtml += `
                            <a href="ver_archivo.php?id=${file.id_archivo}" target="_blank" class="history-file-link">
                                <i class="fas ${isImg ? 'fa-image' : 'fa-file-pdf'}"></i> ${file.nombre_original}
                            </a>`;
                    });
                    filesHtml += '</div></div>';
                }

                // Construir HTML de tratamientos
                let treatmentsHtml = '';
                if (c.tratamientos && c.tratamientos.length > 0) {
                    treatmentsHtml = `
                        <div class="clinical-section">
                            <label><i class="fas fa-pills"></i> Tratamiento Prescrito</label>
                            <table class="history-treatment-table">
                                <thead><tr><th>Medicamento</th><th>Dosis</th><th>Vía</th><th>Duración</th></tr></thead>
                                <tbody>
                                    ${c.tratamientos.map(t => `<tr><td>${t.medicamento}</td><td>${t.dosis}</td><td>${t.via_administracion}</td><td>${t.duracion}</td></tr>`).join('')}
                                </tbody>
                            </table>
                        </div>`;
                }

                item.innerHTML = `
                    <div class="history-header" onclick="this.parentElement.classList.toggle('active')">
                        <div class="header-main">
                            <h4>${c.motivo_consulta}</h4>
                            <span class="vet-badge"><i class="fas fa-user-md"></i> ${c.veterinario}</span>
                        </div>
                        <div class="header-side">
                            <span class="date">${new Date(c.fecha_hora).toLocaleString()}</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="history-content">
                        <div class="clinical-data-grid">
                            <div class="clinical-field"><label>Peso</label><span>${c.peso || '--'} Kg</span></div>
                            <div class="clinical-field"><label>Temp.</label><span>${c.temperatura || '--'} °C</span></div>
                            <div class="clinical-field"><label>F.C.</label><span>${c.frecuencia_cardiaca || '--'} LPM</span></div>
                        </div>
                        <div class="clinical-section">
                            <label>Anamnesis</label>
                            <p>${c.anamnesis || 'N/A'}</p>
                        </div>
                        <div class="clinical-section">
                            <label>Diagnóstico Definitivo</label>
                            <p><strong>${c.diagnostico}</strong></p>
                        </div>
                        <div class="clinical-section">
                            <label>Plan de Manejo</label>
                            <p>${c.plan_tratamiento || 'N/A'}</p>
                        </div>
                        ${treatmentsHtml}
                        ${filesHtml}
                    </div>
                `;
                timeline.appendChild(item);
            });
        } else {
            timeline.innerHTML = '<div class="empty-table"><i class="fas fa-folder-open"></i><p>No hay consultas registradas para esta mascota.</p></div>';
        }
    } catch (e) {
        console.error(e);
        timeline.innerHTML = '<div class="empty-table" style="color:red;"><i class="fas fa-exclamation-triangle"></i><p>Error al cargar el historial clínico.</p></div>';
    }
}

function openHistorialDrawer() {
    const overlay = document.getElementById('historialDrawerOverlay');
    const drawer = document.getElementById('historialDrawer');
    if (overlay) overlay.style.display = 'block';
    if (drawer) setTimeout(() => { drawer.style.transform = 'translateX(0)'; }, 10);
}

function closeHistorialDrawer() {
    const drawer = document.getElementById('historialDrawer');
    const overlay = document.getElementById('historialDrawerOverlay');
    if (drawer) drawer.style.transform = 'translateX(100%)';
    setTimeout(() => { if (overlay) overlay.style.display = 'none'; }, 400);
}

function calculateAge(fecha) {
    const born = new Date(fecha);
    const now = new Date();
    let years = now.getFullYear() - born.getFullYear();
    let months = now.getMonth() - born.getMonth();
    if (months < 0 || (months === 0 && now.getDate() < born.getDate())) {
        years--;
        months = 12 + months;
    }
    if (years === 0) return `${months} meses`;
    return `${years} años, ${months} meses`;
}

// --- DRAWER FUNCTIONS ---
function openDrawer(drawerId) {
    const drawer = document.getElementById(drawerId);
    const overlay = document.getElementById(drawerId + 'Overlay');
    if (drawer && overlay) {
        drawer.classList.add('is-open');
        overlay.classList.add('is-open');
    }
}

function closeDrawer(drawerId) {
    const drawer = document.getElementById(drawerId);
    const overlay = document.getElementById(drawerId + 'Overlay');
    if (drawer && overlay) {
        drawer.classList.remove('is-open');
        overlay.classList.remove('is-open');
    }
}

// --- VACUNAS ---
async function openVaccineModal(id, nombre) {
    document.getElementById('vacuna_id_mascota').value = id;
    document.getElementById('vacunaPetName').innerText = nombre;
    document.getElementById('formVacuna').reset();
    document.getElementById('formVacuna').elements['fecha_aplicacion'].value = new Date().toISOString().split('T')[0];
    
    // Ocultar campos de nueva vacuna y laboratorio
    document.getElementById('nuevaVacunaContainer').style.display = 'none';
    document.getElementById('nuevaVacunaInput').required = false;
    document.getElementById('nuevoLaboratorioContainer').style.display = 'none';
    document.getElementById('nuevoLaboratorioInput').required = false;
    
    // Cargar vacunas según la especie de la mascota
    try {
        const res = await fetch(`index.php?action=get_vacunas_por_especie_ajax&id_mascota=${id}`);
        const data = await res.json();
        
        const vacunaSelect = document.querySelector('select[name="nombre_vacuna"]');
        if (vacunaSelect && data.success) {
            vacunaSelect.innerHTML = '<option value="">Seleccione una vacuna...</option>';
            data.vacunas.forEach(v => {
                vacunaSelect.innerHTML += `<option value="${v.nombre_vacuna}">${v.nombre_vacuna}</option>`;
            });
            // Agregar opción "Otra" al final
            vacunaSelect.innerHTML += `<option value="Otra">Otra (no está en la lista)</option>`;
        }
    } catch (e) {
        console.error('Error al cargar vacunas:', e);
    }
    
    // Cargar laboratorios
    try {
        const res = await fetch('index.php?action=get_laboratorios_ajax');
        const data = await res.json();
        
        const laboratorioSelect = document.querySelector('select[name="laboratorio"]');
        if (laboratorioSelect && data.success) {
            laboratorioSelect.innerHTML = '<option value="">Seleccione laboratorio...</option>';
            data.laboratorios.forEach(l => {
                laboratorioSelect.innerHTML += `<option value="${l.nombre_laboratorio}">${l.nombre_laboratorio}</option>`;
            });
            // Agregar opción "Otro" al final
            laboratorioSelect.innerHTML += `<option value="Otro">Otro (no está en la lista)</option>`;
        }
    } catch (e) {
        console.error('Error al cargar laboratorios:', e);
    }
    
    openDrawer('drawerVacuna');
}

function toggleNuevaVacuna(select) {
    const nuevaVacunaContainer = document.getElementById('nuevaVacunaContainer');
    const nuevaVacunaInput = document.getElementById('nuevaVacunaInput');
    
    if (select.value === 'Otra') {
        nuevaVacunaContainer.style.display = 'block';
        nuevaVacunaInput.required = true;
    } else {
        nuevaVacunaContainer.style.display = 'none';
        nuevaVacunaInput.required = false;
        nuevaVacunaInput.value = '';
    }
}

function toggleNuevoLaboratorio(select) {
    const nuevoLaboratorioContainer = document.getElementById('nuevoLaboratorioContainer');
    const nuevoLaboratorioInput = document.getElementById('nuevoLaboratorioInput');
    
    if (select.value === 'Otro') {
        nuevoLaboratorioContainer.style.display = 'block';
        nuevoLaboratorioInput.required = true;
    } else {
        nuevoLaboratorioContainer.style.display = 'none';
        nuevoLaboratorioInput.required = false;
        nuevoLaboratorioInput.value = '';
    }
}

async function saveVaccine(e) {
    e.preventDefault();
    const fd = new FormData(e.target);
    const vacunaSelect = document.querySelector('select[name="nombre_vacuna"]');
    const nuevaVacunaInput = document.getElementById('nuevaVacunaInput');
    const laboratorioSelect = document.querySelector('select[name="laboratorio"]');
    const nuevoLaboratorioInput = document.getElementById('nuevoLaboratorioInput');
    
    try {
        // Si se seleccionó "Otra" y hay una nueva vacuna, primero registrarla
        if (vacunaSelect.value === 'Otra' && nuevaVacunaInput.value.trim()) {
            const nuevaVacunaFd = new FormData();
            nuevaVacunaFd.append('nombre_vacuna', nuevaVacunaInput.value.trim());
            nuevaVacunaFd.append('id_mascota', fd.get('id_mascota'));
            
            const resNueva = await (await fetch('index.php?action=registrar_nueva_vacuna_ajax', { method: 'POST', body: nuevaVacunaFd })).json();
            
            if (resNueva.success) {
                // Actualizar el select con la nueva vacuna y seleccionarla
                fd.set('nombre_vacuna', resNueva.nombre_vacuna);
            } else {
                alert(resNueva.message);
                return;
            }
        }
        
        // Si se seleccionó "Otro" y hay un nuevo laboratorio, primero registrarlo
        if (laboratorioSelect.value === 'Otro' && nuevoLaboratorioInput.value.trim()) {
            const nuevoLaboratorioFd = new FormData();
            nuevoLaboratorioFd.append('nombre_laboratorio', nuevoLaboratorioInput.value.trim());
            
            const resLab = await (await fetch('index.php?action=registrar_nuevo_laboratorio_ajax', { method: 'POST', body: nuevoLaboratorioFd })).json();
            
            if (resLab.success) {
                // Actualizar el select con el nuevo laboratorio y seleccionarlo
                fd.set('laboratorio', resLab.nombre_laboratorio);
            } else {
                alert(resLab.message);
                return;
            }
        }
        
        // Registrar la aplicación de la vacuna
        const res = await (await fetch('index.php?action=registrar_vacuna_ajax', { method: 'POST', body: fd })).json();
        if (res.success) { 
            alert(res.message); 
            closeDrawer('drawerVacuna'); 
            location.reload(); 
        } else {
            alert(res.message);
        }
    } catch (e) { 
        console.error(e); 
        alert('Error al registrar la vacuna');
    }
}

// --- DESPARASITACIÓN ---
async function openDewormingModal(id, nombre) {
    document.getElementById('desp_id_mascota').value = id;
    document.getElementById('despPetName').innerText = nombre;
    document.getElementById('formDesparasitacion').reset();
    document.getElementById('formDesparasitacion').elements['fecha_aplicacion'].value = new Date().toISOString().split('T')[0];
    
    // Ocultar campo de nuevo producto
    document.getElementById('nuevoProductoContainer').style.display = 'none';
    document.getElementById('nuevoProductoInput').required = false;
    
    // Cargar productos
    try {
        const res = await fetch('index.php?action=get_productos_desparasitacion_ajax');
        const data = await res.json();
        
        const productoSelect = document.querySelector('select[name="producto"]');
        if (productoSelect && data.success) {
            productoSelect.innerHTML = '<option value="">Seleccione producto...</option>';
            data.productos.forEach(p => {
                productoSelect.innerHTML += `<option value="${p.nombre_producto}">${p.nombre_producto} (${p.tipo})</option>`;
            });
            // Agregar opción "Otro" al final
            productoSelect.innerHTML += `<option value="Otro">Otro (no está en la lista)</option>`;
        }
    } catch (e) {
        console.error('Error al cargar productos:', e);
    }
    
    openDrawer('drawerDesparasitacion');
}

function toggleNuevoProducto(select) {
    const nuevoProductoContainer = document.getElementById('nuevoProductoContainer');
    const nuevoProductoInput = document.getElementById('nuevoProductoInput');
    
    if (select.value === 'Otro') {
        nuevoProductoContainer.style.display = 'block';
        nuevoProductoInput.required = true;
    } else {
        nuevoProductoContainer.style.display = 'none';
        nuevoProductoInput.required = false;
        nuevoProductoInput.value = '';
    }
}

async function saveDeworming(e) {
    e.preventDefault();
    const fd = new FormData(e.target);
    const productoSelect = document.querySelector('select[name="producto"]');
    const nuevoProductoInput = document.getElementById('nuevoProductoInput');
    const tipoSelect = document.querySelector('select[name="tipo"]');
    
    try {
        // Si se seleccionó "Otro" y hay un nuevo producto, primero registrarlo
        if (productoSelect.value === 'Otro' && nuevoProductoInput.value.trim()) {
            const nuevoProductoFd = new FormData();
            nuevoProductoFd.append('nombre_producto', nuevoProductoInput.value.trim());
            nuevoProductoFd.append('tipo', tipoSelect.value);
            
            const resNuevo = await (await fetch('index.php?action=registrar_nuevo_producto_desparasitacion_ajax', { method: 'POST', body: nuevoProductoFd })).json();
            
            if (resNuevo.success) {
                // Actualizar el select con el nuevo producto y seleccionarlo
                fd.set('producto', resNuevo.nombre_producto);
            } else {
                alert(resNuevo.message);
                return;
            }
        }
        
        // Registrar la desparasitación
        const res = await (await fetch('index.php?action=registrar_desparasitacion_ajax', { method: 'POST', body: fd })).json();
        if (res.success) { 
            alert(res.message); 
            closeDrawer('drawerDesparasitacion'); 
            location.reload(); 
        } else {
            alert(res.message);
        }
    } catch (e) { 
        console.error(e); 
        alert('Error al registrar la desparasitación');
    }
}

// --- CITAS / AGENDAMIENTO ---
let vetsLoaded = false;

async function loadVets() {
    if (vetsLoaded) return;
    try {
        const res = await (await fetch('index.php?action=listar_veterinarios_ajax')).json();
        const select = document.getElementById('cita_veterinario');
        select.innerHTML = '<option value="">Seleccione un veterinario...</option>';
        res.forEach(v => {
            select.innerHTML += `<option value="${v.documento}">${v.nombre_completo}</option>`;
        });
        vetsLoaded = true;
    } catch (e) {
        console.error('Error loading vets', e);
        document.getElementById('cita_veterinario').innerHTML = '<option value="">Error al cargar</option>';
    }
}

async function loadVetsForAppointment() {
    const select = document.getElementById('cita_veterinario');
    if (!select) return;

    try {
        const res = await (await fetch('index.php?action=listar_veterinarios_ajax')).json();
        if (res && res.length > 0) {
            select.innerHTML = res.map(v => `<option value="${v.documento}">${v.nombre_completo}</option>`).join('');
        } else {
            select.innerHTML = '<option value="">No hay veterinarios disponibles</option>';
        }
    } catch(e) { 
        console.error('Error loading vets', e); 
        select.innerHTML = '<option value="">Error al cargar veterinarios</option>';
    }
}

function openAppointmentModal(id, nombre) {
    document.getElementById('cita_id_mascota').value = id;
    document.getElementById('citaPetName').innerText = nombre;
    document.getElementById('formCita').reset();
    document.getElementById('cita_fecha').value = new Date().toISOString().split('T')[0];
    loadVetsForAppointment();
    openModal('modalCita');
}

async function saveAppointment(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Agendando...';

    const fd = new FormData(e.target);
    try {
        const res = await (await fetch('index.php?action=registrar_cita_ajax', { method: 'POST', body: fd })).json();
        if (res.success) { 
            alert(res.message); 
            closeModal('modalCita'); 
            location.reload(); 
        } else {
            alert(res.message);
        }
    } catch (e) { 
        console.error(e); 
        alert('Error al agendar la cita. Es posible que el correo haya fallado pero la cita se guardó.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Confirmar Cita';
    }
}

// --- INTERACTIVE PET DASHBOARD (ESTILO OKVET) ---
async function loadPetDashboard(id, nombre) {
    try {
        // Fetch database data
        const response = await fetch(`index.php?action=listar_historial_ajax&id_mascota=${id}`);
        const data = await response.json();
        
        const m = data.mascota;
        
        // 1. Dashboard Header & Tab bindings
        document.getElementById('dashPetName').innerText = m.nombre;
        
        const editBtn = document.getElementById('dashEditPetBtn');
        if (editBtn) {
            editBtn.setAttribute('data-id', id);
            editBtn.onclick = () => editPet(id);
        }
        

        
        // 2. Pet Info Card
        const photo = document.getElementById('dashPetPhoto');
        if (photo) {
            photo.src = m.url_foto ? 'uploads/mascotas/' + m.url_foto : 'img/default-pet.png';
            photo.onerror = () => { photo.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(m.nombre) + '&background=random'; };
        }
        
        const statusDot = document.getElementById('dashPetStatus');
        if (statusDot) {
            statusDot.style.backgroundColor = m.estado == 1 ? 'var(--success)' : 'var(--danger)';
            statusDot.style.boxShadow = m.estado == 1 ? '0 0 10px var(--success)' : '0 0 10px var(--danger)';
        }
        
        document.getElementById('dashPetEspecie').innerText = m.nombre_especie || '---';
        document.getElementById('dashPetRaza').innerText = m.nombre_raza || '---';
        document.getElementById('dashPetSexo').innerText = m.sexo || '---';
        document.getElementById('dashPetPeso').innerText = m.peso ? m.peso + ' Kg' : '---';
        document.getElementById('dashPetHC').innerText = m.numero_historia_clinica || '---';
        document.getElementById('dashPetFechaNac').innerText = m.fecha_nacimiento 
            ? new Date(m.fecha_nacimiento).toLocaleDateString() + ' (' + calculateAge(m.fecha_nacimiento) + ')' 
            : '---';
            
        const colorsDiv = document.getElementById('dashPetColores');
        if (colorsDiv) {
            colorsDiv.innerHTML = '';
            if (m.colores_nombres) {
                m.colores_nombres.split(',').forEach(cName => {
                    colorsDiv.innerHTML += `<span class="status-badge" style="background:#f8fafc; color:var(--text-secondary); border: 1px solid var(--border-color); font-size:0.75rem; padding: 4px 10px; border-radius: 8px; text-transform:none; font-weight:600;">${cName.trim()}</span>`;
                });
            } else {
                colorsDiv.innerHTML = '<span style="color:var(--text-muted); font-size:0.85rem;">Ninguno</span>';
            }
        }
        

        
        // 7. Render Unified Chronological Timeline
        const timelineDiv = document.getElementById('dashHistorialTimeline');
        if (timelineDiv) {
            const timelineEvents = [];
            
            if (data.consultas) {
                data.consultas.forEach(c => {
                    timelineEvents.push({
                        type: 'consulta',
                        date: new Date(c.fecha_hora),
                        title: `Consulta Clínica`,
                        subtitle: `Dr. ${c.veterinario}`,
                        body: `<b>Motivo:</b> ${c.motivo_consulta}<br><b>Diagnóstico:</b> ${c.diagnostico}<br><b>Peso:</b> ${c.peso || '--'} Kg • <b>F.C:</b> ${c.frecuencia_cardiaca || '--'} LPM • <b>Temp:</b> ${c.temperatura || '--'} °C`,
                        meta: c.tratamientos && c.tratamientos.length > 0 ? `<div style="margin-top:0.4rem; font-size:0.75rem; color:var(--primary); font-weight:700;"><i class="fas fa-pills"></i> Tratamiento prescrito (${c.tratamientos.length} meds)</div>` : ''
                    });
                });
            }
            
            if (data.vacunas) {
                data.vacunas.forEach(v => {
                    timelineEvents.push({
                        type: 'vacuna',
                        date: new Date(v.fecha_aplicacion),
                        title: `Vacuna Aplicada`,
                        subtitle: `Vacuna: ${v.nombre_vacuna} ${v.laboratorio ? '(' + v.laboratorio + ')' : ''}`,
                        body: `Aplicación de dosis veterinaria. Lote: ${v.lote || 'N/A'}.<br><b>Próxima dosis programada:</b> ${v.fecha_proxima_dosis ? new Date(v.fecha_proxima_dosis).toLocaleDateString() : 'N/A'}`
                    });
                });
            }
            
            if (data.desparasitaciones) {
                data.desparasitaciones.forEach(d => {
                    timelineEvents.push({
                        type: 'desparasitacion',
                        date: new Date(d.fecha_aplicacion),
                        title: `Control de Parásitos`,
                        subtitle: `Producto: ${d.producto} (${d.tipo})`,
                        body: `Control antiparasitario. ${d.observaciones || 'Sin observaciones.'}<br><b>Próxima dosis recomendada:</b> ${d.fecha_proxima ? new Date(d.fecha_proxima).toLocaleDateString() : 'N/A'}`
                    });
                });
            }
            
            // Chronological Sorting Descending
            timelineEvents.sort((a, b) => b.date - a.date);
            
            timelineDiv.innerHTML = '';
            if (timelineEvents.length > 0) {
                const container = document.createElement('div');
                container.className = 'clinical-timeline';
                timelineEvents.forEach(evt => {
                    const eventDiv = document.createElement('div');
                    eventDiv.className = `timeline-event event-${evt.type}`;
                    
                    let iconClass = 'fa-notes-medical';
                    if (evt.type === 'consulta') iconClass = 'fa-stethoscope';
                    else if (evt.type === 'vacuna') iconClass = 'fa-syringe';
                    else if (evt.type === 'desparasitacion') iconClass = 'fa-bug';
                    
                    eventDiv.innerHTML = `
                        <div class="timeline-header">
                            <div class="timeline-title">
                                <i class="fas ${iconClass}"></i> ${evt.title}
                            </div>
                            <div class="timeline-date">
                                ${evt.date.toLocaleDateString()}
                            </div>
                        </div>
                        <div style="font-size:0.75rem; color:var(--text-muted); font-weight:700; margin-bottom:0.3rem;">${evt.subtitle}</div>
                        <div class="timeline-body">
                            ${evt.body}
                        </div>
                        ${evt.meta || ''}
                    `;
                    container.appendChild(eventDiv);
                });
                timelineDiv.appendChild(container);
            } else {
                timelineDiv.innerHTML = '<div class="empty-table"><i class="fas fa-history"></i><p>No hay eventos registrados en el expediente clínico.</p></div>';
            }
        }
        
        // 8. Toggles visual views
        const listSection = document.getElementById('dossierListSection');
        if (listSection) listSection.style.display = 'none';
        const dashSection = document.getElementById('dossierPetDashboardSection');
        if (dashSection) dashSection.style.display = 'block';
        
        // Reset lateral active tab to General Info
        document.querySelectorAll('.dash-sidebar .dash-tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.dash-content-area .dash-tab-content').forEach(content => content.classList.remove('active'));
        
        const firstTabBtn = document.querySelector('.dash-sidebar .dash-tab-btn');
        if (firstTabBtn) {
            firstTabBtn.classList.add('active');
        }
        const firstTabContent = document.getElementById('dashGeneral');
        if (firstTabContent) {
            firstTabContent.classList.add('active');
        }
        
    } catch (e) {
        console.error('Error loading pet dashboard:', e);
        alert('Error al intentar abrir el panel de control de la mascota.');
    }
}

async function printMedicalHistory(id, nombre) {
    try {
        const response = await fetch(`index.php?action=listar_historial_ajax&id_mascota=${id}`);
        const data = await response.json();
        
        // Crear un iframe oculto para imprimir sin salir de la página
        let printIframe = document.getElementById('printIframe');
        if (!printIframe) {
            printIframe = document.createElement('iframe');
            printIframe.id = 'printIframe';
            printIframe.style.visibility = 'hidden';
            printIframe.style.position = 'absolute';
            printIframe.style.right = '0';
            printIframe.style.bottom = '0';
            document.body.appendChild(printIframe);
        }
        
        let html = `
            <html>
            <head>
                <title>Historia Clínica - ${nombre}</title>
                <style>
                    @page { margin: 15mm; }
                    body { font-family: 'Arial', sans-serif; padding: 0; margin: 0; color: #333; }
                    h1 { border-bottom: 2px solid #0066ff; padding-bottom: 10px; }
                    .section { margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
                    .meta { color: #666; font-size: 0.9em; margin-bottom: 10px; }
                    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 0.9em; }
                    th { background-color: #f8fafc; }
                    
                    /* Ocultar encabezados y pies de página por defecto del navegador al imprimir */
                    @media print {
                        @page { margin: 0; }
                        body { margin: 1.6cm; }
                    }
                </style>
            </head>
            <body>
                <h1>Historia Clínica: ${nombre}</h1>
                <div class="meta">
                    <strong>HC N°:</strong> ${data.mascota.numero_historia_clinica || '---'} <br>
                    <strong>Especie/Raza:</strong> ${data.mascota.nombre_especie} - ${data.mascota.nombre_raza || '---'} <br>
                    <strong>Propietario:</strong> ${data.mascota.propietario_nombre}
                </div>
        `;

        if (data.consultas && data.consultas.length > 0) {
            html += `<div class="section"><h2>Consultas Clínicas</h2>`;
            data.consultas.forEach(c => {
                html += `
                    <h3>Fecha: ${new Date(c.fecha_hora).toLocaleDateString()} - Dr. ${c.veterinario}</h3>
                    <p><strong>Motivo:</strong> ${c.motivo_consulta}</p>
                    <p><strong>Diagnóstico:</strong> ${c.diagnostico}</p>
                    <p><strong>Peso:</strong> ${c.peso || '--'} Kg | <strong>Temp:</strong> ${c.temperatura || '--'} °C</p>
                `;
                if (c.tratamientos && c.tratamientos.length > 0) {
                    html += `<ul>`;
                    c.tratamientos.forEach(t => {
                        html += `<li>${t.medicamento} - ${t.dosis} (${t.frecuencia} por ${t.duracion})</li>`;
                    });
                    html += `</ul>`;
                }
            });
            html += `</div>`;
        }

        if (data.vacunas && data.vacunas.length > 0) {
            html += `<div class="section"><h2>Vacunación</h2><table><tr><th>Fecha</th><th>Vacuna</th><th>Próxima Dosis</th></tr>`;
            data.vacunas.forEach(v => {
                html += `<tr><td>${new Date(v.fecha_aplicacion).toLocaleDateString()}</td><td>${v.nombre_vacuna}</td><td>${v.fecha_proxima_dosis ? new Date(v.fecha_proxima_dosis).toLocaleDateString() : '---'}</td></tr>`;
            });
            html += `</table></div>`;
        }

        if (data.desparasitaciones && data.desparasitaciones.length > 0) {
            html += `<div class="section"><h2>Desparasitaciones</h2><table><tr><th>Fecha</th><th>Producto</th><th>Tipo</th><th>Próxima Dosis</th></tr>`;
            data.desparasitaciones.forEach(d => {
                html += `<tr><td>${new Date(d.fecha_aplicacion).toLocaleDateString()}</td><td>${d.producto}</td><td>${d.tipo}</td><td>${d.fecha_proxima ? new Date(d.fecha_proxima).toLocaleDateString() : '---'}</td></tr>`;
            });
            html += `</table></div>`;
        }

        html += `
                <div style="margin-top: 30px; font-size: 0.8em; text-align: center; color: #999;">
                    Documento generado el ${new Date().toLocaleString()} por Zooki.
                </div>
            </body>
            </html>
        `;

        const printDocument = printIframe.contentWindow.document;
        printDocument.open();
        printDocument.write(html);
        printDocument.close();
        
        // Esperar a que renderice y llamar print
        setTimeout(() => {
            printIframe.contentWindow.focus();
            printIframe.contentWindow.print();
        }, 500);

    } catch (e) {
        console.error('Error generando PDF:', e);
        alert('Error al generar el documento para imprimir.');
    }
}

function switchPetDashTab(event, tabId) {
    const tabsContainer = event.target.closest('.dossier-dashboard-tabs');
    if (tabsContainer) {
        tabsContainer.querySelectorAll('.dossier-dashboard-tab').forEach(b => b.classList.remove('active'));
    } else {
        // Fallback en caso de que el click no capture el contenedor
        document.querySelectorAll('.dossier-dashboard-tab').forEach(b => b.classList.remove('active'));
    }
    
    event.currentTarget.classList.add('active');
    
    document.querySelectorAll('.dossier-dashboard-content .dash-tab-content').forEach(c => {
        c.classList.remove('active');
        c.style.display = 'none';
    });
    
    const targetContent = document.getElementById(tabId);
    if (targetContent) {
        targetContent.classList.add('active');
        targetContent.style.display = 'block';
    }
}

function showPetsListFromDossier() {
    const listSection = document.getElementById('dossierListSection');
    if (listSection) listSection.style.display = 'block';
    
    const dashSection = document.getElementById('dossierPetDashboardSection');
    if (dashSection) dashSection.style.display = 'none';
}

function toggleDashConsultaDetails(index) {
    const detailRow = document.getElementById(`consultaDetails-${index}`);
    const icon = document.getElementById(`toggleIcon-${index}`);
    if (detailRow) {
        if (detailRow.style.display === 'none') {
            detailRow.style.display = 'table-row';
            if (icon) icon.className = 'fas fa-eye-slash';
        } else {
            detailRow.style.display = 'none';
            if (icon) icon.className = 'fas fa-eye';
        }
    }
}

// Bind to window to ensure absolute global availability
window.loadPetDashboard = loadPetDashboard;
window.switchPetDashTab = switchPetDashTab;
window.showPetsListFromDossier = showPetsListFromDossier;
window.toggleDashConsultaDetails = toggleDashConsultaDetails;

// --- INITIALIZATION ---
window.onclick = (e) => {
    if (e.target.classList.contains('modal')) closeModal(e.target.id);
    if (e.target.classList.contains('lightbox')) closeLightbox();
};

function setupPreviewBox(box, input, previewId, btnId = null) {
    if (!box || !input) return;
    
    // Handler para hacer clic
    box.addEventListener('click', (e) => {
        // Evitar bucle si se hace click en elementos internos
        if (e.target !== input && !e.target.closest('.btn-clear-img')) {
            input.click();
        }
    });
    
    // Handlers para arrastrar
    ['dragenter', 'dragover'].forEach(eventName => {
        box.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            box.classList.add('dragging');
        }, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        box.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            box.classList.remove('dragging');
        }, false);
    });
    
    // Handler para soltar
    box.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files && files.length > 0) {
            input.files = files;
            // Actualizar vista previa
            previewImg(input, previewId, btnId);
        }
    }, false);
}

// promptAddNewColor removida para evitar data basura

function initPreviewBoxes() {
    const regBox = document.querySelector('#tabNuevaMascota .preview-box');
    const regInput = document.getElementById('newFoto');
    const editBox = document.querySelector('#modalEditarMascota .preview-box');
    const editInput = document.getElementById('editFoto');
    
    setupPreviewBox(regBox, regInput, 'newPreview', 'btnClearNewImg');
    setupPreviewBox(editBox, editInput, 'editPreview', 'btnClearEditImg');
}

document.addEventListener('DOMContentLoaded', () => {
    // Mover todos los modales al nivel del body para evitar que el sidebar los cubra (z-index fix)
    document.querySelectorAll('.modal, .users-modal').forEach(m => document.body.appendChild(m));

    if (document.getElementById('petsTable')) {
        switchView(localStorage.getItem('petViewPreference') || 'grid');
        switchOwnerView(localStorage.getItem('ownerViewPreference') || 'grid');
        initSpecies(); initColores(); loadOwners();
        initPreviewBoxes();
        initDatePickers();
    }

    // Lógica global para añadir 'Otra' Raza o Especie usando SweetAlert2
    document.addEventListener('change', async (e) => {
        // Manejar selección de "Otra" Raza
        if (e.target.matches('#new_raza, #edit_raza') && e.target.value === 'Otra') {
            const { value: nuevaRaza } = await Swal.fire({
                title: 'Agregar nueva raza',
                input: 'text',
                inputLabel: 'Escribe el nombre de la nueva raza',
                showCancelButton: true,
                confirmButtonColor: '#0C66E4',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Aceptar',
                cancelButtonText: 'Cancelar',
                inputValidator: (value) => {
                    if (!value.trim()) return '¡Necesitas escribir un nombre!';
                },
                didOpen: () => {
                    const input = Swal.getInput();
                    input.addEventListener('input', () => {
                        const val = input.value.trim().toLowerCase();
                        const options = Array.from(e.target.options);
                        const existe = options.some(opt => opt.text.toLowerCase() === val && opt.value !== 'Otra');
                        
                        if (existe) {
                            Swal.showValidationMessage('¡Esta raza ya existe en la lista!');
                            Swal.disableButtons();
                        } else {
                            Swal.resetValidationMessage();
                            Swal.enableButtons();
                        }
                    });
                }
            });

            if (nuevaRaza) {
                let form = e.target.closest('form');
                let hiddenInput = form.querySelector('input[name="nueva_raza"]');
                if (!hiddenInput) {
                    hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'nueva_raza';
                    form.appendChild(hiddenInput);
                }
                hiddenInput.value = nuevaRaza.trim();
                
                // Actualizar visualmente la opción "Otra"
                const optionOtra = e.target.querySelector('option[value="Otra"]');
                if (optionOtra) optionOtra.textContent = `Otra: ${nuevaRaza.trim()}`;
            } else {
                // Si el usuario cancela, devolvemos el selector al estado vacío
                e.target.value = "";
            }
        }

        // Manejar selección de "Otra" Especie
        if (e.target.matches('#new_especie, #edit_especie') && e.target.value === 'Otra') {
            const { value: nuevaEspecie } = await Swal.fire({
                title: 'Agregar nueva especie',
                input: 'text',
                inputLabel: 'Escribe el nombre de la nueva especie (ej: Reptil, Mini Pig)',
                showCancelButton: true,
                confirmButtonColor: '#0C66E4',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Guardar Especie',
                cancelButtonText: 'Cancelar',
                inputValidator: (value) => {
                    if (!value.trim()) return '¡Necesitas escribir un nombre!';
                },
                didOpen: () => {
                    const input = Swal.getInput();
                    input.addEventListener('input', () => {
                        const val = input.value.trim().toLowerCase();
                        const options = Array.from(e.target.options);
                        const existe = options.some(opt => opt.text.toLowerCase() === val && opt.value !== 'Otra');
                        
                        if (existe) {
                            Swal.showValidationMessage('¡Esta especie ya existe en la lista!');
                            Swal.disableButtons();
                        } else {
                            Swal.resetValidationMessage();
                            Swal.enableButtons();
                        }
                    });
                }
            });

            if (nuevaEspecie) {
                try {
                    const fd = new FormData();
                    fd.append('nombre_especie', nuevaEspecie.trim());
                    const res = await fetch('index.php?action=registrar_especie_ajax', {
                        method: 'POST',
                        body: fd
                    });
                    const data = await res.json();
                    
                    if (data.success) {
                        Swal.fire({
                            toast: true, position: 'top-end', icon: 'success', 
                            title: 'Especie agregada', showConfirmButton: false, timer: 2000
                        });
                        // Recargar las especies y seleccionar la nueva
                        await initSpecies();
                        e.target.value = data.id_especie;
                        // También disparar el change para que se limpien las razas (al ser nueva especie no tendrá razas)
                        e.target.dispatchEvent(new Event('change'));
                    } else {
                        Swal.fire('Error', data.message || 'Error al guardar la especie', 'error');
                        e.target.value = "";
                    }
                } catch (err) {
                    console.error(err);
                    Swal.fire('Error', 'Error de conexión', 'error');
                    e.target.value = "";
                }
            } else {
                // Si el usuario cancela, devolvemos el selector al estado vacío
                e.target.value = "";
            }
        }
    });
});

// Inicializar todos los flatpickr de la página
function initDatePickers() {
    if (typeof flatpickr === 'undefined') return;
    try {
        let currentLocale = "es";
        if (flatpickr.l10ns && flatpickr.l10ns.es) {
            currentLocale = flatpickr.l10ns.es;
            if (currentLocale.weekdays) {
                currentLocale.weekdays.shorthand = ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"];
            }
        }
        const fpConfig = {
            locale: currentLocale,
            dateFormat: "Y-m-d",
            maxDate: "today",
            disableMobile: true,
            altInput: true,
            altFormat: "Y-m-d",
            monthSelectorType: "static",
            onReady: function(selectedDates, dateStr, instance) {
                const header = instance.monthNav.querySelector('.flatpickr-current-month');
                if (header) {
                    header.title = 'Seleccionar Mes/Año';
                    header.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const nav = instance.calendarContainer.querySelector('.fp-win11-nav');
                        if (nav) {
                            nav.classList.toggle('active');
                            const isNavActive = nav.classList.contains('active');
                            instance.calendarContainer.querySelector('.flatpickr-prev-month').style.visibility = isNavActive ? 'hidden' : 'visible';
                            instance.calendarContainer.querySelector('.flatpickr-next-month').style.visibility = isNavActive ? 'hidden' : 'visible';
                            if (isNavActive) nav._renderMonthsView();
                        }
                    });
                }

                const nav = document.createElement('div');
                nav.className = 'fp-win11-nav';
                
                const navHeader = document.createElement('div');
                navHeader.className = 'win11-header';
                const navTitle = document.createElement('button');
                navTitle.className = 'win11-title';
                navTitle.type = 'button';
                navHeader.appendChild(navTitle);
                nav.appendChild(navHeader);

                const navContent = document.createElement('div');
                navContent.className = 'win11-content';
                
                const monthsGrid = document.createElement('div');
                monthsGrid.className = 'win11-grid';
                
                const yearsGrid = document.createElement('div');
                yearsGrid.className = 'win11-grid win11-years';
                yearsGrid.style.display = 'none';

                navContent.appendChild(monthsGrid);
                navContent.appendChild(yearsGrid);
                nav.appendChild(navContent);
                
                instance.calendarContainer.appendChild(nav);

                let currentView = 'months'; 
                let viewYear = instance.currentYear;
                const monthNames = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];

                function renderMonthsView() {
                    currentView = 'months';
                    monthsGrid.style.display = 'grid';
                    yearsGrid.style.display = 'none';
                    navTitle.innerText = viewYear;
                    
                    monthsGrid.innerHTML = '';
                    const today = new Date();
                    const currentY = today.getFullYear();
                    const currentM = today.getMonth();
                    
                    monthNames.forEach((m, i) => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'win11-btn';
                        
                        if (instance.currentYear === viewYear && instance.currentMonth === i) btn.classList.add('active');
                        
                        // Bloquear meses futuros
                        if (viewYear > currentY || (viewYear === currentY && i > currentM)) {
                            btn.classList.add('disabled');
                            btn.disabled = true;
                        }
                        
                        btn.innerText = m;
                        
                        if (!btn.disabled) {
                            btn.onclick = (e) => {
                                e.stopPropagation();
                                instance.changeYear(viewYear);
                                instance.changeMonth(i);
                                nav.classList.remove('active');
                                instance.calendarContainer.querySelector('.flatpickr-prev-month').style.visibility = 'visible';
                                instance.calendarContainer.querySelector('.flatpickr-next-month').style.visibility = 'visible';
                            };
                        }
                        monthsGrid.appendChild(btn);
                    });
                }

                function renderYearsView() {
                    currentView = 'years';
                    monthsGrid.style.display = 'none';
                    yearsGrid.style.display = 'grid';
                    
                    yearsGrid.innerHTML = '';
                    const currentY = new Date().getFullYear();
                    const startY = currentY - 100;
                    navTitle.innerText = `${startY} - ${currentY}`;
                    
                    for (let y = currentY; y >= startY; y--) {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'win11-btn';
                        if (y === viewYear) btn.classList.add('active');
                        btn.innerText = y;
                        btn.onclick = (e) => {
                            e.stopPropagation();
                            viewYear = y;
                            renderMonthsView();
                        };
                        yearsGrid.appendChild(btn);
                    }
                    
                    // Mover scroll al año activo
                    setTimeout(() => {
                        const activeBtn = yearsGrid.querySelector('.active');
                        if (activeBtn) activeBtn.scrollIntoView({ block: 'center' });
                    }, 10);
                }

                nav._renderMonthsView = renderMonthsView; // bind for use in header click
                navTitle.onclick = (e) => {
                    e.stopPropagation();
                    if (currentView === 'months') {
                        renderYearsView();
                    } else {
                        renderMonthsView();
                    }
                };
            }
        };
        flatpickr(".flatpickr-date", fpConfig);
    } catch(e) {
        console.error("Error inicializando Flatpickr:", e);
    }
}


function filterDossierPets() {
    const termInput = document.getElementById('dossierPetSearch');
    const speciesInput = document.getElementById('dossierPetSpeciesFilter');
    if (!termInput || !speciesInput) return;

    const term = termInput.value.toLowerCase();
    const speciesSel = speciesInput.value.toLowerCase();

    document.querySelectorAll('#dossierPetsScroll .dossier-pet-card').forEach(card => {
        const nameEl = card.querySelector('.dossier-pet-card-name');
        const petName = nameEl ? nameEl.innerText.toLowerCase() : '';

        const speciesEl = card.querySelector('.dossier-pet-card-species');
        const speciesText = speciesEl ? speciesEl.innerText.toLowerCase() : '';

        const hcEl = card.querySelector('.dossier-pet-card-hc');
        const hcText = hcEl ? hcEl.innerText.toLowerCase() : '';

        const matchesSearch = petName.includes(term) || speciesText.includes(term) || hcText.includes(term);
        const matchesSpecies = speciesSel === '' || speciesText.includes(speciesSel);

        card.style.display = (matchesSearch && matchesSpecies) ? '' : 'none';
    });
}

window.filterDossierPets = filterDossierPets;

function openNewConsultationFlow() {
    openModal('modalSelectPetConsulta');
    const searchInput = document.getElementById('consultaPetSearch');
    if (searchInput) searchInput.value = '';
    const suggestions = document.getElementById('consultaPetSuggestions');
    if (suggestions) suggestions.innerHTML = '<div style="padding:1.5rem; text-align:center; color:var(--text-muted); font-size:0.9rem;"><i class="fas fa-search" style="display:block; font-size:1.5rem; margin-bottom:0.5rem; opacity:0.3;"></i>Escribe para buscar un paciente...</div>';
}

async function searchPetForConsultation(term) {
    const suggestions = document.getElementById('consultaPetSuggestions');
    if (term.length < 2) { 
        suggestions.innerHTML = '<div style="padding:1.5rem; text-align:center; color:var(--text-muted); font-size:0.9rem;"><i class="fas fa-search" style="display:block; font-size:1.5rem; margin-bottom:0.5rem; opacity:0.3;"></i>Escribe para buscar un paciente...</div>';
        return; 
    }
    
    try {
        const res = await (await fetch(`index.php?action=buscar_mascotas&query=${encodeURIComponent(term)}`)).json();
        if(res.length) {
            suggestions.innerHTML = res.map(item => `
                <div class="suggestion-item" style="display:flex; align-items:center; gap:1rem; padding:0.75rem; border:1px solid #e2e8f0; border-radius:12px; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.background='#f8fafc'; this.style.borderColor='#cbd5e1';" onmouseout="this.style.background='transparent'; this.style.borderColor='#e2e8f0';" onclick="selectPetForConsultation(${item.id_mascota}, '${item.nombre.replace(/'/g, "\\'")}')">
                    <img src="${item.url_foto ? 'uploads/mascotas/'+item.url_foto : 'https://ui-avatars.com/api/?name='+encodeURIComponent(item.nombre)}" style="width:40px; height:40px; border-radius:10px; object-fit:cover;">
                    <div>
                        <div style="font-weight:700; color:var(--text-main); font-size:0.95rem;">${item.nombre}</div>
                        <div style="font-size:0.75rem; color:var(--text-muted);">Propietario: ${item.propietario_nombre}</div>
                    </div>
                </div>
            `).join('');
        } else {
            suggestions.innerHTML = '<div style="padding:1rem; text-align:center; color:var(--text-muted);">No se encontraron pacientes.</div>';
        }
    } catch(e) { 
        console.error('Search error', e); 
        suggestions.innerHTML = '<div style="padding:1rem; text-align:center; color:red;">Error en la búsqueda.</div>';
    }
}

function selectPetForConsultation(id, nombre) {
    closeModal('modalSelectPetConsulta');
    openConsultationModal(id, nombre);
}
