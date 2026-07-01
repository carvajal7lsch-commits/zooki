<?php
require_once '../config/Database.php';

$database = new Database();
$db = $database->getConnection();

// Filtros de fecha
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-30 days'));
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

// Convertir a string seguro
$ini = $fecha_inicio;
$fin = $fecha_fin;

// 1. Total Citas en el periodo
$stmt = $db->prepare("SELECT COUNT(*) as total FROM citas WHERE fecha BETWEEN ? AND ?");
$stmt->execute([$ini, $fin]);
$total_citas = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// 2. Citas Completadas
$stmt = $db->prepare("SELECT COUNT(*) as total FROM citas WHERE estado = 'completada' AND fecha BETWEEN ? AND ?");
$stmt->execute([$ini, $fin]);
$citas_completadas = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// 3. Citas Canceladas
$stmt = $db->prepare("SELECT COUNT(*) as total FROM citas WHERE estado = 'cancelada' AND fecha BETWEEN ? AND ?");
$stmt->execute([$ini, $fin]);
$citas_canceladas = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Tasa de asistencia
$tasa_asistencia = $total_citas > 0 ? round(($citas_completadas / $total_citas) * 100, 1) : 0;

// 4. Citas por Tipo (Para gráfico de Anillo)
$stmt = $db->prepare("
    SELECT tc.nombre_tipo as tipo, COUNT(c.id_cita) as total 
    FROM citas c
    LEFT JOIN tipos_cita tc ON c.id_tipo_cita = tc.id_tipo_cita
    WHERE c.fecha BETWEEN ? AND ?
    GROUP BY c.id_tipo_cita, tc.nombre_tipo
");
$stmt->execute([$ini, $fin]);
$citas_por_tipo = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5. Citas por Veterinario (Para gráfico de barras)
$stmt = $db->prepare("
    SELECT u.nombre_completo as veterinario, COUNT(c.id_cita) as total 
    FROM citas c
    JOIN usuarios u ON c.doc_veterinario = u.documento
    WHERE c.fecha BETWEEN ? AND ? AND c.estado = 'completada'
    GROUP BY u.documento, u.nombre_completo
");
$stmt->execute([$ini, $fin]);
$citas_por_vet = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 6. Tendencia de Citas (Para gráfico de líneas)
$stmt = $db->prepare("
    SELECT fecha, COUNT(*) as total 
    FROM citas 
    WHERE fecha BETWEEN ? AND ?
    GROUP BY fecha
    ORDER BY fecha ASC
");
$stmt->execute([$ini, $fin]);
$tendencia_citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 7. Vacunaciones Pendientes (Alerta operativa)
$vacunaciones_pendientes = [];
try {
    $vacunaciones_pendientes = $db->query("
        SELECT m.nombre as nombre_mascota, u.nombre_completo as propietario, u.telefono, v.nombre_vacuna, v.fecha_aplicacion
        FROM vacunas v
        JOIN mascotas m ON v.id_mascota = m.id_mascota
        JOIN usuarios u ON m.doc_propietario = u.documento
        WHERE v.fecha_aplicacion BETWEEN DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND DATE_ADD(CURDATE(), INTERVAL 15 DAY)
        ORDER BY v.fecha_aplicacion ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

// JSON Encodes para JS
$json_tipos = json_encode($citas_por_tipo);
$json_vets = json_encode($citas_por_vet);
$json_tendencia = json_encode($tendencia_citas);
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="reports-container animate__animated animate__fadeIn">
    <!-- Header y Filtros -->
    <div class="reports-header">
        <div class="header-title">
            <h1>Dashboard de Rendimiento</h1>
            <p>Métricas operativas y análisis de citas</p>
        </div>
        
        <form method="GET" action="panel.php" class="reports-filter">
            <input type="hidden" name="action" value="reportes">
            <div class="filter-group">
                <label class="filter-label">Desde</label>
                <input type="date" name="fecha_inicio" value="<?= $fecha_inicio ?>" class="filter-input">
            </div>
            <div class="filter-group">
                <label class="filter-label">Hasta</label>
                <input type="date" name="fecha_fin" value="<?= $fecha_fin ?>" class="filter-input">
            </div>
            <button type="submit" class="btn-filter">
                <i class="fas fa-filter"></i> Filtrar
            </button>
            <button type="button" onclick="window.print()" class="btn-print">
                <i class="fas fa-print"></i> Imprimir
            </button>
        </form>
    </div>

    <!-- KPIs -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon-box stat-icon-primary">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div>
                <p class="stat-title">Total Citas</p>
                <h3 class="stat-value"><?= number_format($total_citas) ?></h3>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-box stat-icon-success">
                <i class="fas fa-percentage"></i>
            </div>
            <div>
                <p class="stat-title">Tasa Asistencia</p>
                <h3 class="stat-value"><?= $tasa_asistencia ?>%</h3>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-box stat-icon-purple">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <p class="stat-title">Completadas</p>
                <h3 class="stat-value"><?= number_format($citas_completadas) ?></h3>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-box stat-icon-danger">
                <i class="fas fa-times-circle"></i>
            </div>
            <div>
                <p class="stat-title">Canceladas</p>
                <h3 class="stat-value"><?= number_format($citas_canceladas) ?></h3>
            </div>
        </div>
    </div>

    <!-- Gráficos Superior -->
    <div class="charts-grid">
        <!-- Tendencia de Citas (Línea) -->
        <div class="chart-card">
            <h3>Flujo de Citas</h3>
            <div class="chart-wrapper">
                <?php if(empty($tendencia_citas)): ?>
                    <div class="chart-empty"><p>No hay datos para las fechas seleccionadas.</p></div>
                <?php else: ?>
                    <canvas id="chartTendencia"></canvas>
                <?php endif; ?>
            </div>
        </div>

        <!-- Citas por Tipo (Doughnut) -->
        <div class="chart-card">
            <h3>Demanda por Servicio</h3>
            <div class="chart-wrapper" style="display: flex; justify-content: center;">
                <?php if(empty($citas_por_tipo)): ?>
                    <div class="chart-empty"><p>No hay datos para las fechas seleccionadas.</p></div>
                <?php else: ?>
                    <canvas id="chartTipos"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Inferior -->
    <div class="charts-grid">
        <!-- Rendimiento por Veterinario (Barras) -->
        <div class="chart-card">
            <h3>Citas Completadas por Especialista</h3>
            <div class="chart-wrapper">
                <?php if(empty($citas_por_vet)): ?>
                    <div class="chart-empty"><p>No hay datos para las fechas seleccionadas.</p></div>
                <?php else: ?>
                    <canvas id="chartVets"></canvas>
                <?php endif; ?>
            </div>
        </div>

        <!-- Alertas de Vacunación -->
        <div class="chart-card" style="display: flex; flex-direction: column;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                <div>
                    <h3 style="display: flex; align-items: center; gap: 0.5rem;"><i class="fas fa-bell" style="color: #F59E0B;"></i> Alertas de Vacunación</h3>
                    <p style="font-size: 0.85rem; color: #64748b; margin: 0; margin-top: 0.2rem;">Oportunidades de agendar citas (+15 / -30 días)</p>
                </div>
            </div>
            
            <div style="flex: 1; overflow-y: auto; padding-right: 0.5rem;" class="custom-scroll">
                <?php if(empty($vacunaciones_pendientes)): ?>
                    <div style="text-align: center; padding: 2rem; color: #94a3b8;">
                        <div style="width: 60px; height: 60px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto;">
                            <i class="fas fa-check" style="font-size: 1.5rem; color: #10B981;"></i>
                        </div>
                        <p style="margin:0; font-weight: 500;">Todo al día. No hay vacunas pendientes.</p>
                    </div>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 0.8rem;">
                        <?php foreach($vacunaciones_pendientes as $vac): 
                            $fecha_app = new DateTime($vac['fecha_aplicacion']);
                            $hoy = new DateTime();
                            $dias = $hoy->diff($fecha_app)->days;
                            $es_pasada = $fecha_app < $hoy;
                            $color_border = $es_pasada ? '#EF4444' : '#F59E0B';
                            $color_bg = $es_pasada ? '#FEF2F2' : '#FFFBEB';
                            $estado_txt = $es_pasada ? "Vencida hace $dias días" : "Faltan $dias días";
                        ?>
                        <div style="background: <?= $color_bg ?>; border-left: 4px solid <?= $color_border ?>; padding: 1rem; border-radius: 8px;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.4rem;">
                                <span style="font-weight: 800; color: #1e293b; font-size: 0.95rem; display: flex; align-items: center; gap: 0.4rem;">
                                    <i class="fas fa-paw" style="color: <?= $color_border ?>;"></i> <?= htmlspecialchars($vac['nombre_mascota']) ?>
                                </span>
                                <span style="font-size: 0.7rem; font-weight: 800; color: white; background: <?= $color_border ?>; padding: 0.2rem 0.6rem; border-radius: 20px; text-transform: uppercase;">
                                    <?= $estado_txt ?>
                                </span>
                            </div>
                            <div style="font-size: 0.85rem; color: #334155; margin-bottom: 0.4rem;">
                                <strong>Vacuna:</strong> <?= htmlspecialchars($vac['nombre_vacuna']) ?>
                            </div>
                            <div style="font-size: 0.8rem; color: #64748b; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 0.4rem; margin-top: 0.4rem;">
                                <span><i class="far fa-user"></i> <?= htmlspecialchars($vac['propietario']) ?></span>
                                <span><i class="fas fa-phone"></i> <?= htmlspecialchars($vac['telefono'] ?? 'Sin teléfono') ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const datosTipos = <?= $json_tipos ?>;
    const datosVets = <?= $json_vets ?>;
    const datosTendencia = <?= $json_tendencia ?>;

    Chart.defaults.font.family = "'Inter', 'Arial', sans-serif";
    Chart.defaults.color = '#64748b';

    // 1. Gráfico de Tendencia
    if(document.getElementById('chartTendencia') && datosTendencia.length > 0) {
        new Chart(document.getElementById('chartTendencia').getContext('2d'), {
            type: 'line',
            data: {
                labels: datosTendencia.map(d => d.fecha),
                datasets: [{
                    label: 'Citas Atendidas/Programadas',
                    data: datosTendencia.map(d => d.total),
                    borderColor: '#0052FF',
                    backgroundColor: 'rgba(0, 82, 255, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#0052FF',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 12,
                        titleFont: { size: 13, family: 'Inter' },
                        bodyFont: { size: 14, family: 'Inter', weight: 'bold' },
                        displayColors: false
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { borderDash: [4, 4], color: '#e2e8f0' },
                        ticks: { stepSize: 1 }
                    },
                    x: { 
                        grid: { display: false } 
                    }
                }
            }
        });
    }

    // 2. Gráfico Tipos de Cita
    if(document.getElementById('chartTipos') && datosTipos.length > 0) {
        new Chart(document.getElementById('chartTipos').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: datosTipos.map(d => d.tipo || 'General'),
                datasets: [{
                    data: datosTipos.map(d => d.total),
                    backgroundColor: [
                        '#0052FF', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899', '#14B8A6'
                    ],
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        position: 'right', 
                        labels: { boxWidth: 12, usePointStyle: true, font: { size: 12, family: 'Inter' }, padding: 20 } 
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 12,
                        titleFont: { size: 13, family: 'Inter' },
                        bodyFont: { size: 14, family: 'Inter', weight: 'bold' }
                    }
                },
                cutout: '70%'
            }
        });
    }

    // 3. Gráfico Veterinarios
    if(document.getElementById('chartVets') && datosVets.length > 0) {
        new Chart(document.getElementById('chartVets').getContext('2d'), {
            type: 'bar',
            data: {
                labels: datosVets.map(d => d.veterinario || 'Dr.'),
                datasets: [{
                    label: 'Citas Completadas',
                    data: datosVets.map(d => d.total),
                    backgroundColor: '#10B981',
                    borderRadius: 6,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 12,
                        titleFont: { size: 13, family: 'Inter' },
                        bodyFont: { size: 14, family: 'Inter', weight: 'bold' },
                        displayColors: false
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { borderDash: [4, 4], color: '#e2e8f0' },
                        ticks: { stepSize: 1 }
                    },
                    x: { 
                        grid: { display: false } 
                    }
                }
            }
        });
    }
});
</script>

<style>
/* Ocultar barra de scroll para alertas */
.custom-scroll::-webkit-scrollbar {
    width: 6px;
}
.custom-scroll::-webkit-scrollbar-track {
    background: transparent; 
}
.custom-scroll::-webkit-scrollbar-thumb {
    background: #cbd5e1; 
    border-radius: 10px;
}
.custom-scroll::-webkit-scrollbar-thumb:hover {
    background: #94a3b8; 
}

@media print {
    .reports-filter { display: none !important; }
    .reports-container { padding: 0 !important; }
    body { background: white !important; }
    .reports-container > div { margin-bottom: 20px !important; }
}
</style>
