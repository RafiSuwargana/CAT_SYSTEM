<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAT System - Laravel</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --info-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --hover-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        
        .header-section {
            background: var(--primary-gradient);
            color: white;
            margin-bottom: 2rem;
            border-radius: 0 0 20px 20px;
            position: relative;
            overflow: hidden;
        }
        
        .header-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="%23ffffff" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="%23ffffff" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="%23ffffff" opacity="0.1"/><circle cx="20" cy="80" r="0.5" fill="%23ffffff" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .header-section > * {
            position: relative;
            z-index: 1;
        }
        
        .card {
            box-shadow: var(--card-shadow);
            border: none;
            border-radius: 15px;
            transition: all 0.3s ease;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        
        .card:hover {
            box-shadow: var(--hover-shadow);
            transform: translateY(-5px);
        }
        
        .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: none;
            border-radius: 15px 15px 0 0 !important;
            padding: 1.5rem;
            font-weight: 600;
            color: #495057;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        #item-display {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 2px solid #e9ecef;
            border-radius: 15px;
            transition: all 0.3s ease;
        }
        
        #item-display:hover {
            border-color: #667eea;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.1);
        }
        
        .btn {
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn-primary {
            background: var(--primary-gradient);
        }
        
        .btn-success {
            background: var(--success-gradient);
        }
        
        .btn-danger {
            background: var(--secondary-gradient);
        }
        
        .btn-success:hover, .btn-danger:hover, .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }
        
        .progress {
            height: 15px;
            border-radius: 10px;
            background: #f8f9fa;
            overflow: hidden;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .progress-bar {
            background: var(--success-gradient);
            transition: width 0.6s ease;
            border-radius: 10px;
            position: relative;
            overflow: hidden;
        }
        
        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            background-image: linear-gradient(
                -45deg,
                rgba(255, 255, 255, .2) 25%,
                transparent 25%,
                transparent 50%,
                rgba(255, 255, 255, .2) 50%,
                rgba(255, 255, 255, .2) 75%,
                transparent 75%,
                transparent
            );
            background-size: 50px 50px;
            animation: move 2s linear infinite;
        }
        
        @keyframes move {
            0% {
                background-position: 0 0;
            }
            100% {
                background-position: 50px 50px;
            }
        }
        
        .alert {
            border: none;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .alert-info {
            background: var(--info-gradient);
            color: white;
        }
        
        .alert-warning {
            background: linear-gradient(135deg, #ffeaa7 0%, #fab1a0 100%);
            color: #2d3436;
        }
        
        .alert-success {
            background: var(--success-gradient);
            color: white;
        }
        
        .alert-danger {
            background: var(--secondary-gradient);
            color: white;
        }
        
        .alert-secondary {
            background: linear-gradient(135deg, #ddd6fe 0%, #c7d2fe 100%);
            color: #4c1d95;
        }
        
        #chart-placeholder {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border: 2px dashed #dee2e6;
            border-radius: 15px;
            transition: all 0.3s ease;
        }
        
        #chart-placeholder:hover {
            border-color: #667eea;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        }
        
        .modal-content {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .modal-header {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 2rem;
        }
        
        .modal-body {
            padding: 2rem;
        }
        
        .modal-footer {
            border: none;
            padding: 1.5rem 2rem 2rem;
        }
        
        .spinner-border {
            color: #667eea;
        }
        
        .text-primary { color: #667eea !important; }
        .border-primary { border-color: #667eea !important; }
        .border-success { border-color: #00f2fe !important; }
        .border-info { border-color: #38f9d7 !important; }
        
        /* Custom scrollbar */
        #results-display::-webkit-scrollbar {
            width: 8px;
        }
        
        #results-display::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        #results-display::-webkit-scrollbar-thumb {
            background: var(--primary-gradient);
            border-radius: 10px;
        }
        
        #results-display::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-gradient);
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .card {
            animation: fadeInUp 0.6s ease forwards;
        }
        
        .card:nth-child(2) {
            animation-delay: 0.1s;
        }
        
        .card:nth-child(3) {
            animation-delay: 0.2s;
        }
        
        /* Algorithm card toggle animation */
        #algorithm-card-body {
            transition: all 0.3s ease;
        }
        
        /* Formula styling */
        code {
            font-family: 'Courier New', monospace;
            font-weight: 600;
        }
        
        /* Algorithm steps styling */
        .rounded-pill {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            transition: transform 0.2s ease;
        }
        
        .rounded-pill:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="header-section text-center py-5">
                    <div class="container">
                        <h1 class="display-3 text-white mb-3">🎯 Computer Adaptive Testing</h1>
                        <h4 class="text-light mb-4">Sistem Tes Adaptif Berbasis Model IRT 3PL</h4>
                        <div class="alert alert-warning mt-4 mx-auto text-dark" style="max-width: 1000px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);">
                            <h5 class="mb-4"><i class="fas fa-info-circle text-primary"></i> <strong>Kriteria & Aturan CAT System</strong></h5>
                            <div class="row text-start">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <strong>📊 Kriteria Stopping:</strong>
                                        <ul class="mb-0 mt-2">
                                            <li>Min 10 soal, Max 30 soal</li>
                                            <li>SE target ≤ 0.25</li>
                                            <li>Item b_max/b_min tercapai</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <strong>📈 Change Control:</strong>
                                        <ul class="mb-0 mt-2">
                                            <li>Soal 1-5: Δθ ≤ 1.0</li>
                                            <li>Soal 6+: Δθ ≤ 0.25</li>
                                            <li>Theta range: [-6, +6]</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <strong>🔧 Algoritma:</strong>
                                        <ul class="mb-0 mt-2">
                                            <li>Model: IRT 3PL</li>
                                            <li>Estimasi: EAP</li>
                                            <li>Seleksi: Expected Fisher Info (EFI)</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Panel - Test Interface -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5><i class="fas fa-clipboard-list text-primary me-2"></i>Interface Tes</h5>
                    </div>
                    <div class="card-body">
                        <!-- Status Display -->
                        <div id="status-display" class="alert alert-secondary">
                            <strong><i class="fas fa-info-circle me-1"></i>Status:</strong> <span id="status-text">Klik 'Mulai Tes' untuk memulai</span>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Progress Tes:</label>
                            <div class="progress">
                                <div id="progress-bar" class="progress-bar" role="progressbar" style="width: 0%">0/30</div>
                            </div>
                        </div>

                        <!-- Item Display -->
                        <div id="item-display" class="border rounded p-4 mb-4" style="min-height: 350px; display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0"><i class="fas fa-question-circle text-info me-2"></i>Item: <span id="item-id" class="badge bg-primary"></span></h6>
                                <span class="badge bg-secondary">Soal Simulasi</span>
                            </div>
                            
                            <div class="mb-4 p-3 bg-light rounded">
                                <p class="mb-2"><strong>📝 Ini adalah soal simulasi CAT System.</strong></p>
                                <p class="mb-0 text-muted">Di implementasi real, di sini akan ditampilkan pertanyaan sebenarnya dari bank soal.</p>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6><i class="fas fa-cog text-warning me-1"></i>Parameter Item:</h6>
                                    <ul class="list-unstyled mb-0">
                                        <li><strong>a</strong> (Diskriminasi): <span id="param-a" class="badge bg-info"></span></li>
                                        <li><strong>b</strong> (Kesulitan): <span id="param-b" class="badge bg-warning"></span></li>
                                        <li><strong>g</strong> (Guessing): <span id="param-g" class="badge bg-secondary"></span></li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-chart-line text-success me-1"></i>Statistik:</h6>
                                    <ul class="list-unstyled mb-0">
                                        <li><strong>θ</strong> Current: <span id="current-theta" class="badge bg-primary"></span></li>
                                        <li><strong>P(θ)</strong>: <span id="probability" class="badge bg-success"></span>%</li>
                                        <li><strong>Info</strong>: <span id="information" class="badge bg-danger"></span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Control Buttons -->
                        <div class="text-center">
                            <button id="btn-start" class="btn btn-primary btn-lg me-3 mb-2" onclick="startTest()">
                                <i class="fas fa-play me-2"></i>Mulai Tes
                            </button>
                            <button id="btn-correct" class="btn btn-success me-2 mb-2" onclick="submitResponse(1)" disabled>
                                <i class="fas fa-check me-2"></i>Benar
                            </button>
                            <button id="btn-incorrect" class="btn btn-danger mb-2" onclick="submitResponse(0)" disabled>
                                <i class="fas fa-times me-2"></i>Salah
                            </button>
                        </div>

                        <!-- Loading Spinner -->
                        <div id="loading" class="text-center mt-4" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Memproses...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Panel - Results and Chart -->
            <div class="col-lg-6">
                <div class="row g-4">
                    <!-- Final Results Display (shown after test completion) -->
                    <div class="col-12" id="final-results-card" style="display: none;">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h5><i class="fas fa-trophy me-2"></i>Hasil Akhir Tes CAT</h5>
                            </div>
                            <div class="card-body">
                                <div id="final-results-summary"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Results Display -->
                    <div class="col-12" id="results-card">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-chart-bar text-success me-2"></i>Hasil Tes Real-time</h5>
                            </div>
                            <div class="card-body">
                                <div id="results-display" style="max-height: 300px; overflow-y: auto;">
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-chart-line fa-3x mb-3 opacity-50"></i>
                                        <p>Hasil akan ditampilkan setelah tes dimulai...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Algorithm & Formula Display -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-calculator text-warning me-2"></i>Rumus & Algoritma CAT</h5>
                                <button class="btn btn-sm btn-outline-secondary" type="button" onclick="toggleAlgorithmCard()">
                                    <i class="fas fa-chevron-up" id="algorithm-toggle-icon"></i>
                                </button>
                            </div>
                            <div class="card-body" id="algorithm-card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="bg-light p-3 rounded">
                                            <h6 class="text-primary mb-3"><i class="fas fa-formula me-1"></i>Model IRT 3PL</h6>
                                            <div class="text-center mb-2">
                                                <code style="font-size: 0.9em; background: #f8f9fa; padding: 8px; border-radius: 5px; display: block;">
                                                    P(θ) = g + (u - g) / (1 + e^(-a(θ - b)))
                                                </code>
                                            </div>
                                            <small class="text-muted">
                                                <strong>a:</strong> Diskriminasi, <strong>b:</strong> Kesulitan<br>
                                                <strong>g:</strong> Guessing, <strong>u:</strong> Upper asymptote
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="bg-light p-3 rounded">
                                            <h6 class="text-success mb-3"><i class="fas fa-chart-line me-1"></i>Expected A Posteriori (EAP)</h6>
                                            <div class="text-center mb-2">
                                                <code style="font-size: 0.8em; background: #f8f9fa; padding: 8px; border-radius: 5px; display: block; line-height: 1.4;">
                                                    θ_EAP = Σ(θ_k × P(θ_k|U)) / Σ(P(θ_k|U))
                                                </code>
                                            </div>
                                            <small class="text-muted">
                                                Estimasi kemampuan berdasarkan distribusi posterior dengan U = pola respon
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="bg-light p-3 rounded">
                                            <h6 class="text-info mb-3"><i class="fas fa-bullseye me-1"></i>Expected Fisher Information</h6>
                                            <div class="text-center mb-2">
                                                <code style="font-size: 0.7em; background: #f8f9fa; padding: 8px; border-radius: 5px; display: block; line-height: 1.4;">
                                                    EFI = Σ[I_j(θ_k) × P(θ_k|U)]<br>
                                                    I_j(θ) = a_j² × (P_j - g_j)² × Q_j / (P_j × (u_j - g_j)²)
                                                </code>
                                            </div>
                                            <small class="text-muted">
                                                Informasi Fisher yang diharapkan untuk pemilihan item optimal
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="bg-light p-3 rounded">
                                            <h6 class="text-danger mb-3"><i class="fas fa-square-root-alt me-1"></i>Standard Error EAP</h6>
                                            <div class="text-center mb-2">
                                                <code style="font-size: 0.75em; background: #f8f9fa; padding: 8px; border-radius: 5px; display: block; line-height: 1.4;">
                                                    SE_EAP = √[Σ(θ_k² × P(θ_k|U)) - (Σ(θ_k × P(θ_k|U)))²]
                                                </code>
                                            </div>
                                            <small class="text-muted">
                                                Ukuran presisi estimasi kemampuan berdasarkan varians posterior
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Algorithm Steps -->
                                <div class="mt-4">
                                    <h6 class="text-secondary mb-3"><i class="fas fa-cogs me-2"></i>Alur Algoritma CAT:</h6>
                                    <div class="row g-2">
                                        <div class="col-md-2">
                                            <div class="text-center p-2 bg-primary text-white rounded-pill">
                                                <small><strong>1. Init</strong><br>θ = 0</small>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="text-center p-2 bg-info text-white rounded-pill">
                                                <small><strong>2. EFI</strong><br>Select Item</small>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="text-center p-2 bg-warning text-white rounded-pill">
                                                <small><strong>3. Present</strong><br>Show Item</small>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="text-center p-2 bg-success text-white rounded-pill">
                                                <small><strong>4. EAP</strong><br>Update θ</small>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="text-center p-2 bg-secondary text-white rounded-pill">
                                                <small><strong>5. Check</strong><br>SE ≤ 0.25?</small>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="text-center p-2 bg-danger text-white rounded-pill">
                                                <small><strong>6. Stop</strong><br>Final θ</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chart Display -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-chart-area text-info me-2"></i>Grafik Perkembangan θ dan SE_EAP</h5>
                            </div>
                            <div class="card-body">
                                <div id="chart-container" class="position-relative">
                                    <canvas id="progressChart" width="400" height="300" style="display: none;"></canvas>
                                    <div id="chart-placeholder" class="text-center text-muted py-5">
                                        <i class="fas fa-chart-line fa-4x mb-3 opacity-50"></i>
                                        <h6>Grafik Perkembangan</h6>
                                        <p class="mb-0">Akan ditampilkan setelah tes dimulai</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Complete Modal -->
    <div class="modal fade" id="testCompleteModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-trophy me-2"></i>Hasil Tes CAT</h4>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="final-results"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Tutup
                    </button>
                    <button type="button" class="btn btn-primary" onclick="startNewTest()">
                        <i class="fas fa-redo me-2"></i>Tes Baru
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global variables
        let currentSession = null;
        let currentItem = null;
        let chart = null;
        let testHistory = [];

        // API base URL
        const API_BASE = '{{ url("/api") }}/';

        // CSRF token for Laravel
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Initialize chart
        function initChart() {
            // Destroy existing chart if it exists
            if (chart) {
                chart.destroy();
                chart = null;
            }
            
            // Hide placeholder and show canvas
            const placeholder = document.getElementById('chart-placeholder');
            const canvas = document.getElementById('progressChart');
            
            if (placeholder) placeholder.style.display = 'none';
            if (canvas) canvas.style.display = 'block';
            
            const ctx = canvas.getContext('2d');
            chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Theta (θ)',
                            data: [],
                            borderColor: 'rgb(54, 162, 235)',
                            backgroundColor: 'rgba(54, 162, 235, 0.1)',
                            borderWidth: 3,
                            fill: false,
                            tension: 0.1,
                            yAxisID: 'y'
                        },
                        {
                            label: 'SE_EAP',
                            data: [],
                            borderColor: 'rgb(255, 99, 132)',
                            backgroundColor: 'rgba(255, 99, 132, 0.1)',
                            borderWidth: 3,
                            fill: false,
                            tension: 0.1,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Perkembangan Estimasi Kemampuan (Theta) dan SE EAP'
                        },
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Nomor Soal'
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Estimasi Theta (θ)',
                                color: 'rgb(54, 162, 235)'
                            },
                            min: -6.5,
                            max: 6.5,
                            grid: {
                                drawOnChartArea: true,
                            },
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Standard Error EAP',
                                color: 'rgb(255, 99, 132)'
                            },
                            min: 0,
                            max: 1.1,
                            grid: {
                                drawOnChartArea: false,
                            },
                        }
                    }
                }
            });
        }

        // Update chart with new data
        function updateChart(itemNumber, theta, se) {
            if (!chart) {
                initChart();
            }
            
            chart.data.labels.push(itemNumber);
            chart.data.datasets[0].data.push(theta);
            chart.data.datasets[1].data.push(se);
            chart.update();
        }

        // Show loading spinner
        function showLoading() {
            document.getElementById('loading').style.display = 'block';
        }

        // Hide loading spinner
        function hideLoading() {
            document.getElementById('loading').style.display = 'none';
        }

        // Update status
        function updateStatus(text, type = 'secondary') {
            const statusDisplay = document.getElementById('status-display');
            const statusText = document.getElementById('status-text');
            
            statusDisplay.className = `alert alert-${type}`;
            statusText.textContent = text;
        }

        // Update progress bar
        function updateProgress(current, max = 30) {
            const progressBar = document.getElementById('progress-bar');
            const percentage = (current / max) * 100;
            progressBar.style.width = percentage + '%';
            progressBar.textContent = `${current}/${max}`;
        }

        // Display item
        function displayItem(item, theta, probability, information, itemNumber) {
            document.getElementById('item-display').style.display = 'block';
            document.getElementById('item-id').textContent = item.id;
            document.getElementById('param-a').textContent = parseFloat(item.a).toFixed(3);
            document.getElementById('param-b').textContent = parseFloat(item.b).toFixed(3);
            document.getElementById('param-g').textContent = parseFloat(item.g).toFixed(3);
            document.getElementById('current-theta').textContent = theta.toFixed(3);
            document.getElementById('probability').textContent = (probability * 100).toFixed(1);
            document.getElementById('information').textContent = information.toFixed(3);
            
            // Enable answer buttons
            document.getElementById('btn-correct').disabled = false;
            document.getElementById('btn-incorrect').disabled = false;
            document.getElementById('btn-start').disabled = true;
            
            updateStatus(`Item ${itemNumber}: ${item.id} - Jawab Benar atau Salah`, 'info');
            updateProgress(itemNumber);
        }

        // Add result to display
        function addResult(itemNumber, item, answer, theta, se, probability, information) {
            const resultsDisplay = document.getElementById('results-display');
            
            // Clear placeholder if this is the first result
            if (itemNumber === 1) {
                resultsDisplay.innerHTML = '';
            }
            
            const answerText = answer === 1 ? 'Benar' : 'Salah';
            const answerClass = answer === 1 ? 'success' : 'danger';
            const answerIcon = answer === 1 ? 'check-circle' : 'times-circle';
            
            const resultHTML = `
                <div class="card mb-3 border-${answerClass}">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="card-title mb-2">
                                    <i class="fas fa-${answerIcon} text-${answerClass} me-2"></i>
                                    Item ${itemNumber}: <span class="badge bg-primary">${item.id}</span>
                                </h6>
                                <p class="card-text mb-2">
                                    <span class="badge bg-${answerClass}">${answerText}</span>
                                    <span class="badge bg-info ms-2">θ = ${theta.toFixed(3)}</span>
                                    <span class="badge bg-warning ms-1">SE = ${se.toFixed(3)}</span>
                                </p>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block">P(θ) = ${(probability * 100).toFixed(1)}%</small>
                                <small class="text-muted">Info = ${information.toFixed(3)}</small>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            resultsDisplay.innerHTML += resultHTML;
            resultsDisplay.scrollTop = resultsDisplay.scrollHeight;
        }

        // Start new test function (closes modal first)
        function startNewTest() {
            // Close the modal first
            const modal = bootstrap.Modal.getInstance(document.getElementById('testCompleteModal'));
            if (modal) {
                modal.hide();
            }
            
            // Reset UI state
            currentSession = null;
            currentItem = null;
            testHistory = [];
            
            // Hide final results card
            document.getElementById('final-results-card').style.display = 'none';
            
            // Clear results
            document.getElementById('results-display').innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="fas fa-chart-line fa-3x mb-3 opacity-50"></i>
                    <p>Hasil akan ditampilkan setelah tes dimulai...</p>
                </div>
            `;
            
            // Hide item display
            document.getElementById('item-display').style.display = 'none';
            
            // Reset chart
            if (chart) {
                chart.destroy();
                chart = null;
            }
            
            // Show chart placeholder
            const placeholder = document.getElementById('chart-placeholder');
            const canvas = document.getElementById('progressChart');
            if (placeholder) placeholder.style.display = 'block';
            if (canvas) canvas.style.display = 'none';
            
            // Reset progress bar
            updateProgress(0);
            
            // Reset status
            updateStatus('Klik "Mulai Tes" untuk memulai', 'secondary');
            
            // Reset buttons
            document.getElementById('btn-correct').disabled = true;
            document.getElementById('btn-incorrect').disabled = true;
            document.getElementById('btn-start').disabled = false;
            
            // Start new test
            setTimeout(() => {
                startTest();
            }, 500); // Small delay to ensure modal is closed
        }

        // Start test
        async function startTest() {
            showLoading();
            
            try {
                const response = await fetch(API_BASE + 'start-test', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    }
                });
                
                const data = await response.json();
                
                if (data.error) {
                    throw new Error(data.error);
                }
                
                currentSession = data.session_id;
                currentItem = data.item;
                testHistory = [];
                
                // Clear previous results
                document.getElementById('results-display').innerHTML = '';
                
                // Initialize chart
                initChart();
                updateChart(0, 0, 1.0); // Initial point
                
                // Display first item
                displayItem(data.item, data.theta, data.probability, data.information, data.item_number);
                
            } catch (error) {
                updateStatus('Error: ' + error.message, 'danger');
                console.error('Error starting test:', error);
            } finally {
                hideLoading();
            }
        }

        // Submit response
        async function submitResponse(answer) {
            if (!currentSession || !currentItem) {
                updateStatus('Tidak ada sesi aktif', 'warning');
                return;
            }
            
            showLoading();
            
            // Disable buttons to prevent double submission
            document.getElementById('btn-correct').disabled = true;
            document.getElementById('btn-incorrect').disabled = true;
            
            try {
                const response = await fetch(API_BASE + 'submit-response', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    body: JSON.stringify({
                        session_id: currentSession,
                        item_id: currentItem.id,
                        answer: answer
                    })
                });
                
                const data = await response.json();
                
                if (data.error) {
                    throw new Error(data.error);
                }
                
                // Add result
                const itemNumber = testHistory.length + 1;
                addResult(itemNumber, currentItem, answer, data.theta, data.se, 
                         data.probability || 0, data.information || 0);
                
                // Update chart
                updateChart(itemNumber, data.theta, data.se);
                
                // Store history
                testHistory.push({
                    item: currentItem,
                    answer: answer,
                    theta: data.theta,
                    se: data.se
                });
                
                if (data.test_completed) {
                    // Test completed
                    document.getElementById('item-display').style.display = 'none';
                    updateStatus('Tes selesai!', 'success');
                    
                    // Show final results modal
                    showFinalResults(data);
                    
                } else {
                    // Continue with next item
                    currentItem = data.item;
                    displayItem(data.item, data.theta, data.probability, data.information, data.item_number);
                }
                
            } catch (error) {
                updateStatus('Error: ' + error.message, 'danger');
                console.error('Error submitting response:', error);
                
                // Re-enable buttons on error
                document.getElementById('btn-correct').disabled = false;
                document.getElementById('btn-incorrect').disabled = false;
            } finally {
                hideLoading();
            }
        }

        // Show final results
        function showFinalResults(data) {
            const finalResults = document.getElementById('final-results');
            
            // Also create persistent final results summary
            const finalResultsSummary = document.getElementById('final-results-summary');
            const finalResultsCard = document.getElementById('final-results-card');
            
            // Show persistent results card
            finalResultsCard.style.display = 'block';
            
            // Create compact summary for persistent display
            finalResultsSummary.innerHTML = `
                <div class="row g-3">
                    <div class="col-md-4 text-center">
                        <div class="p-3 bg-primary text-white rounded">
                            <i class="fas fa-brain fa-2x mb-2"></i>
                            <h6 class="mb-1">Estimasi Kemampuan</h6>
                            <h4 class="mb-0">${data.theta.toFixed(3)}</h4>
                            <small>Theta (θ)</small>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="p-3 bg-success text-white rounded">
                            <i class="fas fa-chart-line fa-2x mb-2"></i>
                            <h6 class="mb-1">Skor Final</h6>
                            <h4 class="mb-0">${data.final_score.toFixed(1)}</h4>
                            <small>Skala 100 ± 15θ</small>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="p-3 bg-info text-white rounded">
                            <i class="fas fa-bullseye fa-2x mb-2"></i>
                            <h6 class="mb-1">Presisi</h6>
                            <h4 class="mb-0">${data.se.toFixed(3)}</h4>
                            <small>Standard Error</small>
                        </div>
                    </div>
                </div>
                
                <div class="row g-3 mt-3">
                    <div class="col-md-6">
                        <div class="bg-light p-3 rounded">
                            <h6><i class="fas fa-info-circle text-primary me-2"></i>Detail Tes:</h6>
                            <ul class="mb-0 small">
                                <li><strong>Jumlah Soal:</strong> ${data.total_items}</li>
                                <li><strong>Alasan Berhenti:</strong> ${data.stop_reason}</li>
                                <li><strong>Reliabilitas:</strong> ${(1 - Math.pow(data.se, 2)).toFixed(3)}</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light p-3 rounded">
                            <h6><i class="fas fa-chart-bar text-success me-2"></i>Interpretasi:</h6>
                            <p class="mb-0 small">
                                <strong>${data.theta >= 1.5 ? 'Kemampuan Tinggi' : 
                                  data.theta >= 0.5 ? 'Kemampuan Menengah Atas' :
                                  data.theta >= -0.5 ? 'Kemampuan Menengah' :
                                  data.theta >= -1.5 ? 'Kemampuan Menengah Bawah' : 'Kemampuan Rendah'}</strong>
                                <br>
                                <span class="text-muted">θ = ${data.theta.toFixed(3)}</span>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <button class="btn btn-outline-primary btn-sm me-2" onclick="showDetailedResults()">
                        <i class="fas fa-eye me-1"></i>Lihat Detail Lengkap
                    </button>
                    <button class="btn btn-outline-success btn-sm" onclick="startNewTest()">
                        <i class="fas fa-redo me-1"></i>Tes Baru
                    </button>
                </div>
            `;
            
            finalResults.innerHTML = `
                <div class="text-center">
                    <div class="mb-4">
                        <i class="fas fa-trophy fa-4x text-warning mb-3"></i>
                        <h3 class="text-success mb-3">🎉 Tes CAT Selesai!</h3>
                    </div>
                    
                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <div class="card border-primary h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-brain fa-3x text-primary mb-3"></i>
                                    <h5 class="card-title">Estimasi Kemampuan</h5>
                                    <h2 class="text-primary">${data.theta.toFixed(3)}</h2>
                                    <p class="text-muted mb-0">Theta (θ)</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-success h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-chart-line fa-3x text-success mb-3"></i>
                                    <h5 class="card-title">Skor Final</h5>
                                    <h2 class="text-success">${data.final_score.toFixed(1)}</h2>
                                    <p class="text-muted mb-0">Skala 100 ± 15θ</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-info h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-bullseye fa-3x text-info mb-3"></i>
                                    <h5 class="card-title">Presisi</h5>
                                    <h2 class="text-info">${data.se.toFixed(3)}</h2>
                                    <p class="text-muted mb-0">Standard Error</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="alert alert-info text-start">
                                <h6><i class="fas fa-info-circle me-2"></i>Detail Tes:</h6>
                                <ul class="mb-0">
                                    <li><strong>Jumlah Soal:</strong> ${data.total_items}</li>
                                    <li><strong>Alasan Berhenti:</strong> ${data.stop_reason}</li>
                                    <li><strong>Reliabilitas:</strong> ${(1 - Math.pow(data.se, 2)).toFixed(3)}</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-success text-start">
                                <h6><i class="fas fa-chart-bar me-2"></i>Interpretasi:</h6>
                                <p class="mb-0">
                                    ${data.theta >= 1.5 ? 'Kemampuan Tinggi' : 
                                      data.theta >= 0.5 ? 'Kemampuan Menengah Atas' :
                                      data.theta >= -0.5 ? 'Kemampuan Menengah' :
                                      data.theta >= -1.5 ? 'Kemampuan Menengah Bawah' : 'Kemampuan Rendah'}
                                    <br>
                                    <small class="text-muted">Berdasarkan estimasi θ = ${data.theta.toFixed(3)}</small>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('testCompleteModal'));
            modal.show();
            
            // Reset buttons
            document.getElementById('btn-correct').disabled = true;
            document.getElementById('btn-incorrect').disabled = true;
            document.getElementById('btn-start').disabled = false;
        }

        // Toggle algorithm card visibility
        function toggleAlgorithmCard() {
            const cardBody = document.getElementById('algorithm-card-body');
            const toggleIcon = document.getElementById('algorithm-toggle-icon');
            
            if (cardBody.style.display === 'none') {
                cardBody.style.display = 'block';
                toggleIcon.className = 'fas fa-chevron-up';
            } else {
                cardBody.style.display = 'none';
                toggleIcon.className = 'fas fa-chevron-down';
            }
        }

        // Show detailed results modal again
        function showDetailedResults() {
            const modal = new bootstrap.Modal(document.getElementById('testCompleteModal'));
            modal.show();
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            updateStatus('Klik "Mulai Tes" untuk memulai', 'secondary');
        });
    </script>
</body>
</html>
