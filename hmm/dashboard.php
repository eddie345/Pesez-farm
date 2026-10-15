<?php
require_once 'config.php';
requireLogin();

// Calculate total active birds
$stmtBirds = $pdo->query("SELECT SUM(current_count) as total_birds FROM flocks WHERE status = 'active'");
$totalBirds = $stmtBirds->fetch()['total_birds'] ?? 0;

// Calculate today's good eggs (exclude archived rows)
$stmtEggs = $pdo->prepare("SELECT SUM(quantity_good) as total_good FROM egg_production WHERE collection_date = CURRENT_DATE AND deleted_at IS NULL");
$stmtEggs->execute();
$todayGoodEggs = $stmtEggs->fetch()['total_good'] ?? 0;

// Calculate total feed in stock (kg)
$stmtFeed = $pdo->query("SELECT SUM(quantity_in_stock_kg) as total_feed FROM feed_inventory");
$totalFeed = $stmtFeed->fetch()['total_feed'] ?? 0;

// Calculate today's mortality (exclude archived rows)
$stmtMortality = $pdo->prepare("SELECT SUM(number_of_birds) as total_dead FROM mortality WHERE record_date = CURRENT_DATE AND deleted_at IS NULL");
$stmtMortality->execute();
$todayMortality = $stmtMortality->fetch()['total_dead'] ?? 0;

// Chart Data: 7 days historical trend for egg production (exclude archived)
$chartStmt = $pdo->query("
    SELECT collection_date, SUM(quantity_good) as good, SUM(quantity_damaged) as damaged
    FROM egg_production 
    WHERE collection_date >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
      AND deleted_at IS NULL
    GROUP BY collection_date
    ORDER BY collection_date ASC
");
$chartData = $chartStmt->fetchAll();

$dates = [];
$goodEggs = [];
$damagedEggs = [];

foreach ($chartData as $row) {
    // Format date nicely for chart (e.g. "Mar 15")
    $dates[] = date('M d', strtotime($row['collection_date']));
    $goodEggs[] = $row['good'];
    $damagedEggs[] = $row['damaged'];
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

        <!-- The HCI Aurora Mesh Background -->
        <div class="fixed top-0 left-0 w-full h-full pointer-events-none -z-10 bg-slate-50 overflow-hidden">
            <div class="absolute -top-[10%] -left-[10%] w-[50vw] h-[50vw] bg-yellow-200/40 rounded-full blur-[100px] mix-blend-multiply opacity-70 animate-blob"></div>
            <div class="absolute top-[20%] right-[5%] w-[40vw] h-[40vw] bg-emerald-300/30 rounded-full blur-[100px] mix-blend-multiply opacity-70 animate-blob animation-delay-2000"></div>
            <div class="absolute bottom-[-10%] left-[20%] w-[45vw] h-[45vw] bg-sky-300/30 rounded-full blur-[100px] mix-blend-multiply opacity-70 animate-blob animation-delay-4000"></div>
        </div>

        <header class="mb-10 flex flex-col md:flex-row md:items-end justify-between">
            <div class="flex items-center">
                <div class="inline-flex justify-center items-center w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-400 text-white shadow-lg shadow-emerald-500/20 mr-4">
                    <i data-lucide="layout-dashboard" class="w-6 h-6"></i>
                </div>
                <div>
                    <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight bg-gradient-to-r from-emerald-800 to-teal-600 bg-clip-text text-transparent">Overview</h2>
                    <p class="text-gray-500 mt-1 text-sm font-semibold">Welcome back to your workspace</p>
                </div>
            </div>
            <div class="mt-4 md:mt-0 flex gap-3">
                <a href="eggs.php" class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 text-white text-sm font-bold rounded-xl shadow-lg shadow-emerald-500/30 hover:shadow-xl hover:-translate-y-1 transition-all">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Log Collection
                </a>
            </div>
        </header>

        <!-- Dynamic Security Interceptor: Catches workers bounced back from admin-only pages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-8 p-5 rounded-2xl flex items-center bg-ui-red/10 text-ui-red border border-ui-red/20 shadow-sm animate-fade-in-down">
                <i data-lucide="shield-alert" class="w-6 h-6 mr-3"></i>
                <span class="font-bold text-sm tracking-wide uppercase"><?= htmlspecialchars($_SESSION['error']) ?></span>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <!-- Bento Grid: Top Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            
            <!-- RBAC Pattern Example: Wrapping a financial widget in an admin-check 
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <div class="bg-gray-900 rounded-3xl p-6 shadow-soft ...">
                    ... Content completely hidden from the DOM for workers ...
                </div>
            <?php endif; ?>
            -->

            <!-- Bento Box: Active Birds -->
            <div class="bg-white/80 backdrop-blur-xl rounded-3xl p-6 shadow-xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 border border-white/60 relative overflow-hidden group">
                <div class="absolute -right-6 -top-6 w-32 h-32 bg-brand-sky/10 rounded-full blur-2xl group-hover:bg-brand-sky/20 transition-colors duration-500"></div>
                <div class="flex flex-col h-full justify-between relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-brand-sky/10 text-brand-sky p-3 rounded-2xl">
                            <i data-lucide="bird" class="w-6 h-6"></i>
                        </div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Live</span>
                    </div>
                    <div>
                        <p class="text-4xl font-black text-gray-900 tracking-tight"><?= number_format($totalBirds) ?></p>
                        <h3 class="text-sm font-semibold text-gray-600 mt-1">Active Birds</h3>
                    </div>
                </div>
            </div>
            
            <!-- Bento Box: Today's Good Eggs -->
            <div class="bg-white/80 backdrop-blur-xl rounded-3xl p-6 shadow-xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 border border-white/60 relative overflow-hidden group">
                <div class="absolute -right-6 -top-6 w-32 h-32 bg-brand-DEFAULT/10 rounded-full blur-2xl group-hover:bg-brand-DEFAULT/20 transition-colors duration-500"></div>
                <div class="flex flex-col h-full justify-between relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-brand-DEFAULT/10 text-brand-DEFAULT p-3 rounded-2xl">
                            <i data-lucide="egg" class="w-6 h-6"></i>
                        </div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Today</span>
                    </div>
                    <div>
                        <p class="text-4xl font-black text-gray-900 tracking-tight"><?= number_format($todayGoodEggs) ?></p>
                        <h3 class="text-sm font-semibold text-gray-600 mt-1">Good Eggs Harvested</h3>
                    </div>
                </div>
            </div>
            
            <!-- Bento Box: Feed Stock -->
            <div class="bg-white/80 backdrop-blur-xl rounded-3xl p-6 shadow-xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 border border-white/60 relative overflow-hidden group">
                <div class="absolute -right-6 -top-6 w-32 h-32 bg-ui-yellow/10 rounded-full blur-2xl group-hover:bg-ui-yellow/20 transition-colors duration-500"></div>
                <div class="flex flex-col h-full justify-between relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-ui-yellow/10 text-ui-yellow p-3 rounded-2xl">
                            <i data-lucide="wheat" class="w-6 h-6"></i>
                        </div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Stock</span>
                    </div>
                    <div>
                        <p class="text-4xl font-black text-gray-900 tracking-tight"><?= number_format($totalFeed, 0) ?><span class="text-lg text-gray-400 font-bold ml-1">kg</span></p>
                        <h3 class="text-sm font-semibold text-gray-600 mt-1">Total Feed Remaining</h3>
                    </div>
                </div>
            </div>
            
            <!-- Bento Box: Today's Mortality -->
            <div class="bg-white/80 backdrop-blur-xl rounded-3xl p-6 shadow-xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 border border-white/60 relative overflow-hidden group">
                <div class="absolute -right-6 -top-6 w-32 h-32 bg-ui-red/10 rounded-full blur-2xl group-hover:bg-ui-red/20 transition-colors duration-500"></div>
                <div class="flex flex-col h-full justify-between relative z-10 text-gray-900">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-ui-red/10 text-ui-red p-3 rounded-2xl">
                            <i data-lucide="skull" class="w-6 h-6"></i>
                        </div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Today</span>
                    </div>
                    <div>
                        <p class="text-4xl font-black text-gray-900 tracking-tight"><?= number_format($todayMortality) ?></p>
                        <h3 class="text-sm font-semibold text-gray-600 mt-1">Recorded Mortalities</h3>
                    </div>
                </div>
            </div>
            
        </div>

        <!-- Bento Grid: Main Content -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            
            <!-- Bento Box: Chart (Spans 2 columns) -->
            <div class="xl:col-span-2 bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl hover:shadow-2xl transition-shadow duration-300 border border-white/60 p-6 sm:p-8 flex flex-col relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-600 via-teal-500 to-sky-400"></div>
                
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h3 class="text-xl font-extrabold text-gray-900 tracking-tight">Egg Production Trend</h3>
                        <p class="text-xs font-semibold text-gray-500 uppercase mt-1 tracking-wider">Last 7 Days</p>
                    </div>
                    <div class="p-2.5 bg-gray-50 text-gray-400 rounded-xl border border-gray-100">
                        <i data-lucide="trending-up" class="w-5 h-5"></i>
                    </div>
                </div>
                
                <div class="relative flex-1 min-h-[320px] w-full">
                    <canvas id="eggChart"></canvas>
                </div>
            </div>

            <!-- Bento Box: Quick Actions -->
            <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl border border-white/60 p-6 sm:p-8 flex flex-col">
                <h3 class="text-xl font-extrabold text-gray-900 tracking-tight mb-6">Quick Actions</h3>
                
                <!-- Inner 2x2 Bento Grid for tools -->
                <div class="grid grid-cols-2 gap-4 flex-1">
                    
                    <a href="eggs.php" class="flex flex-col justify-center items-center text-center p-4 rounded-2xl bg-brand-DEFAULT/5 hover:bg-ui-brown text-brand-DEFAULT hover:text-white transition-all duration-300 group hover:-translate-y-1 hover:shadow-lg">
                        <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center mb-3 shadow-sm group-hover:scale-110 transition-transform duration-300">
                            <i data-lucide="egg" class="w-6 h-6 text-brand-DEFAULT"></i>
                        </div>
                        <span class="font-bold text-sm">Harvest</span>
                    </a>

                    <a href="feed.php" class="flex flex-col justify-center items-center text-center p-4 rounded-2xl bg-ui-yellow/10 hover:bg-ui-green hover:text-white text-yellow-600 transition-all duration-300 group hover:-translate-y-1 hover:shadow-lg">
                        <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center mb-3 shadow-sm group-hover:scale-110 transition-transform duration-300">
                            <i data-lucide="wheat" class="w-6 h-6 text-ui-yellow"></i>
                        </div>
                        <span class="font-bold text-sm">Feed</span>
                    </a>

                    <a href="mortality.php" class="flex flex-col justify-center items-center text-center p-4 rounded-2xl bg-ui-red/10 hover:bg-ui-red hover:text-white text-ui-red transition-all duration-300 group hover:-translate-y-1 hover:shadow-lg">
                         <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center mb-3 shadow-sm group-hover:scale-110 transition-transform duration-300">
                            <i data-lucide="skull" class="w-6 h-6 text-ui-red"></i>
                        </div>
                        <span class="font-bold text-sm">Mortality</span>
                    </a>

                    <a href="health.php" class="flex flex-col justify-center items-center text-center p-4 rounded-2xl bg-brand-sky/10 hover:bg-brand-sky hover:text-white text-brand-sky transition-all duration-300 group hover:-translate-y-1 hover:shadow-lg">
                         <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center mb-3 shadow-sm group-hover:scale-110 transition-transform duration-300">
                            <i data-lucide="activity" class="w-6 h-6 text-brand-sky"></i>
                        </div>
                        <span class="font-bold text-sm">Health</span>
                    </a>

                </div>
            </div>

        </div>
    </div> <!-- End Main Content Div from header/sidebar logic -->

<script>
    const ctx = document.getElementById('eggChart').getContext('2d');
    
    // Create glowing smooth gradient for "Good Eggs"
    const gradient = ctx.createLinearGradient(0, 0, 0, 350);
    gradient.addColorStop(0, 'rgba(10, 135, 84, 0.5)');  // brand-DEFAULT
    gradient.addColorStop(1, 'rgba(10, 135, 84, 0.0)');  // fade to transparent

    // Create gradient for "Damaged Eggs"
    const damagedGradient = ctx.createLinearGradient(0, 0, 0, 350);
    damagedGradient.addColorStop(0, 'rgba(239, 68, 68, 0.2)');  // ui-red
    damagedGradient.addColorStop(1, 'rgba(239, 68, 68, 0.0)');

    const eggChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($dates) ?>,
            datasets: [
                {
                    label: 'Good Eggs',
                    data: <?= json_encode($goodEggs) ?>,
                    borderColor: '#0A8754', 
                    backgroundColor: gradient,
                    borderWidth: 4,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#0A8754',
                    pointBorderWidth: 3,
                    pointRadius: 0, // hide by default for sleekness
                    pointHoverRadius: 6,
                    tension: 0.5, // Apple-style curved tension
                    fill: true
                },
                {
                    label: 'Damaged Eggs',
                    data: <?= json_encode($damagedEggs) ?>,
                    borderColor: '#EF4444',
                    backgroundColor: damagedGradient,
                    borderWidth: 3,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#EF4444',
                    pointBorderWidth: 2,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    tension: 0.5,
                    fill: true,
                    borderDash: [5, 5]
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 8,
                        boxHeight: 8,
                        padding: 20,
                        font: {
                            family: '"Plus Jakarta Sans", sans-serif',
                            size: 13,
                            weight: '700'
                        },
                        color: '#6B7280'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(5, 95, 56, 0.95)', // brand-dark backdrop
                    titleFont: {
                        family: '"Plus Jakarta Sans", sans-serif',
                        size: 14,
                        weight: '800'
                    },
                    bodyFont: {
                        family: '"Plus Jakarta Sans", sans-serif',
                        size: 13,
                        weight: '600'
                    },
                    padding: 14,
                    cornerRadius: 12,
                    displayColors: true,
                    boxPadding: 6,
                    borderColor: 'rgba(255,255,255,0.1)',
                    borderWidth: 1
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false, // Totally hidden x grid
                        drawBorder: false
                    },
                    ticks: {
                        font: { 
                            family: '"Plus Jakarta Sans", sans-serif',
                            weight: '600'
                        },
                        color: '#9CA3AF',
                        padding: 10
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        display: false, // Totally hidden y grid for maximum smoothness
                        drawBorder: false
                    },
                    ticks: {
                        font: { 
                            family: '"Plus Jakarta Sans", sans-serif',
                            weight: '600'
                        },
                        color: '#9CA3AF',
                        padding: 15,
                        stepSize: 100 // Adjust based on flock size
                    }
                }
            }
        }
    });

    // Re-trigger lucide icons
    setTimeout(() => { if(window.lucide) lucide.createIcons(); }, 100);
</script>

</body>
</html>
