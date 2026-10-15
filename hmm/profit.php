<?php
require_once 'config.php';
requireLogin();

// Strict RBAC Interceptor: Block standard workers from executing financial queries
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Access Restricted: Admin privileges required for financial modules.";
    header('Location: dashboard.php');
    exit();
}

// Date Range Parsing
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01'); // First day of current month
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');   // Last day of current month

// ... (Queries remain untouched) ...
$stmtEggs = $pdo->prepare("SELECT SUM(quantity_good) as total_eggs FROM egg_production WHERE collection_date BETWEEN ? AND ?");
$stmtEggs->execute([$startDate, $endDate]);
$eggResult = $stmtEggs->fetch();
$totalEggs = (int)($eggResult['total_eggs'] ?? 0);

$stmtFeed = $pdo->prepare("SELECT SUM(quantity_used_kg) as total_feed FROM feed_usage WHERE usage_date BETWEEN ? AND ?");
$stmtFeed->execute([$startDate, $endDate]);
$feedResult = $stmtFeed->fetch();
$totalFeedKg = (float)($feedResult['total_feed'] ?? 0);

$stmtBirds = $pdo->prepare("SELECT SUM(initial_count) as total_sold FROM flocks WHERE status = 'sold' AND arrival_date BETWEEN ? AND ?");
$stmtBirds->execute([$startDate, $endDate]);
$birdResult = $stmtBirds->fetch();
$totalBirdsSold = (int)($birdResult['total_sold'] ?? 0);

include 'includes/header.php';
include 'includes/sidebar.php';
?>

        <!-- The HCI Aurora Mesh Background -->
        <div class="fixed top-0 left-0 w-full h-full pointer-events-none -z-10 bg-slate-50 overflow-hidden">
            <div class="absolute -top-[10%] -left-[10%] w-[50vw] h-[50vw] bg-yellow-200/40 rounded-full blur-[100px] mix-blend-multiply opacity-70 animate-blob"></div>
            <div class="absolute top-[20%] right-[5%] w-[40vw] h-[40vw] bg-emerald-300/30 rounded-full blur-[100px] mix-blend-multiply opacity-70 animate-blob animation-delay-2000"></div>
            <div class="absolute bottom-[-10%] left-[20%] w-[45vw] h-[45vw] bg-sky-300/30 rounded-full blur-[100px] mix-blend-multiply opacity-70 animate-blob animation-delay-4000"></div>
        </div>

        <div class="w-full flex justify-center items-start pt-6 pb-12">
            <div class="max-w-5xl mx-auto w-full px-4">
                
                <header class="mb-8 flex flex-col md:flex-row md:items-end justify-between border-b border-gray-200/50 pb-6">
                    <div class="flex items-center">
                        <div class="inline-flex justify-center items-center w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-400 text-white shadow-lg shadow-emerald-500/30 mr-4 hidden md:flex">
                            <i data-lucide="calculator" class="w-7 h-7"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl md:text-3xl font-extrabold bg-gradient-to-r from-emerald-800 to-teal-600 bg-clip-text text-transparent tracking-tight">Profitability Engine</h2>
                            <p class="text-gray-500 mt-1 text-xs font-bold tracking-wider uppercase">Financial Projection & Margins</p>
                        </div>
                    </div>
                    
                    <form action="profit.php" method="GET" class="flex flex-col sm:flex-row items-end space-y-4 sm:space-y-0 sm:space-x-4 mt-6 md:mt-0">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">From</label>
                            <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" class="px-4 py-2.5 border border-white/60 bg-white/80 backdrop-blur-sm rounded-xl focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 text-sm font-bold text-gray-700 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">To</label>
                            <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" class="px-4 py-2.5 border border-white/60 bg-white/80 backdrop-blur-sm rounded-xl focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 text-sm font-bold text-gray-700 shadow-sm">
                        </div>
                        <button type="submit" class="px-5 py-2.5 bg-gray-900 hover:bg-black text-white font-bold rounded-xl shadow-lg transition-all hover:-translate-y-0.5 whitespace-nowrap">
                            Filter
                        </button>
                    </form>
                </header>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    
                    <!-- Left Panel: Input Modifiers -->
                    <div class="col-span-1 lg:col-span-5 space-y-6">
                        
                        <div class="bg-white/80 backdrop-blur-xl p-6 rounded-2xl shadow-xl border border-white/60 relative overflow-hidden group">
                            <h3 class="text-base font-extrabold text-gray-900 mb-5 flex items-center">
                                <i data-lucide="sliders-horizontal" class="w-4 h-4 mr-2 text-emerald-600"></i> Market Variables
                            </h3>
                            
                            <!-- Static Metrics Pre-fetched -->
                            <div class="flex space-x-3 mb-5 overflow-x-auto pb-1">
                                <div class="bg-white/50 backdrop-blur-md p-2.5 rounded-xl flex-shrink-0 min-w-24 border border-white/50 shadow-sm">
                                    <p class="text-[10px] font-black uppercase text-gray-400 mb-1">Total Eggs</p>
                                    <p class="text-base font-black text-gray-800"><?= number_format($totalEggs) ?></p>
                                </div>
                                <div class="bg-white/50 backdrop-blur-md p-2.5 rounded-xl flex-shrink-0 min-w-24 border border-white/50 shadow-sm">
                                    <p class="text-[10px] font-black uppercase text-gray-400 mb-1">Feed (kg)</p>
                                    <p class="text-base font-black text-gray-800"><?= number_format($totalFeedKg, 1) ?></p>
                                </div>
                                <div class="bg-white/50 backdrop-blur-md p-2.5 rounded-xl flex-shrink-0 min-w-24 border border-white/50 shadow-sm">
                                    <p class="text-[10px] font-black uppercase text-gray-400 mb-1">Sold Birds</p>
                                    <p class="text-base font-black text-gray-800"><?= number_format($totalBirdsSold) ?></p>
                                </div>
                            </div>
                            
                            <form id="calcForm" class="space-y-4">
                                
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">Price per Single Egg</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <span class="text-gray-400 font-bold pr-2 border-r border-gray-200 text-sm">GH₵</span>
                                        </div>
                                        <input type="number" id="inp_price_egg" step="0.01" value="3.50" class="block w-full text-sm font-bold py-2.5 px-4 pl-[4rem] rounded-xl bg-white/70 border border-white/60 focus:outline-none focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 shadow-sm transition-all text-gray-800">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">Price per Sold Bird</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <span class="text-gray-400 font-bold pr-2 border-r border-gray-200 text-sm">GH₵</span>
                                        </div>
                                        <input type="number" id="inp_price_bird" step="0.01" value="85.00" class="block w-full text-sm font-bold py-2.5 px-4 pl-[4rem] rounded-xl bg-white/70 border border-white/60 focus:outline-none focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 shadow-sm transition-all text-gray-800">
                                    </div>
                                </div>

                                <div class="border-t border-gray-200/50 pt-4">
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">Cost per KG of Feed</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <span class="text-gray-400 font-bold pr-2 border-r border-gray-200 text-sm">GH₵</span>
                                        </div>
                                        <input type="number" id="inp_cost_feed" step="0.01" value="12.50" class="block w-full text-sm font-bold py-2.5 px-4 pl-[4rem] rounded-xl bg-white/70 border border-white/60 focus:outline-none focus:ring-4 focus:ring-rose-500/20 focus:border-rose-500 shadow-sm transition-all text-gray-800">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">Misc. Operational Costs <span class="text-[10px] text-gray-400">(Meds, Labour)</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <span class="text-gray-400 font-bold pr-2 border-r border-gray-200 text-sm">GH₵</span>
                                        </div>
                                        <input type="number" id="inp_cost_misc" step="0.01" value="500.00" class="block w-full text-sm font-bold py-2.5 px-4 pl-[4rem] rounded-xl bg-white/70 border border-white/60 focus:outline-none focus:ring-4 focus:ring-rose-500/20 focus:border-rose-500 shadow-sm transition-all text-gray-800">
                                    </div>
                                </div>

                                <div class="pt-3">
                                    <button type="button" onclick="calculateMargins()" class="w-full text-base font-bold py-3 rounded-xl shadow-md transition-all hover:-translate-y-0.5 hover:shadow-lg shadow-emerald-500/30 text-white bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-500/40 flex justify-center items-center">
                                        <i data-lucide="zap" class="w-4 h-4 mr-2"></i> Calculate Margins
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>
                    
                    <!-- Right Panel: The Financial Dashboard -->
                    <div class="col-span-1 lg:col-span-7">
                        <div class="bg-gray-900 rounded-2xl shadow-2xl p-8 md:p-10 relative overflow-hidden h-full flex flex-col justify-between border border-gray-800">
                            
                            <!-- Dynamic Glow Backgrounds (controlled by JS) -->
                            <div id="glowGood" class="absolute -right-32 -top-32 w-[30rem] h-[30rem] bg-ui-green/10 rounded-full blur-[80px] pointer-events-none transition-opacity duration-1000 opacity-50"></div>
                            <div id="glowBad" class="absolute -left-32 -bottom-32 w-[30rem] h-[30rem] bg-ui-red/10 rounded-full blur-[80px] pointer-events-none transition-opacity duration-1000 opacity-0"></div>

                            <div class="relative z-10">
                                <h3 class="text-gray-400 text-sm font-bold uppercase tracking-widest mb-2">Estimated Net Margins</h3>
                                
                                <div class="flex items-center mb-8">
                                    <div id="out_net_profit" class="flex items-baseline transition-all duration-700 text-white">
                                        <span class="text-3xl md:text-3xl font-black mr-3 text-gray-400">GH₵</span>
                                        <span class="text-6xl md:text-[5.5rem] font-black tracking-tight drop-shadow-md">0.00</span>
                                    </div>
                                </div>

                                <!-- Breakdown Ledger -->
                                <div class="grid grid-cols-2 gap-6 border-t border-gray-800 pt-8 mt-2">
                                    <div>
                                        <p class="text-xs font-bold tracking-widest text-gray-500 uppercase flex items-center mb-2">
                                            <i data-lucide="trending-up" class="w-4 h-4 mr-2 text-ui-green"></i> Total Revenue
                                        </p>
                                        <p id="out_revenue" class="text-3xl font-black text-white"><span class="text-xl text-gray-500 mr-2">GH₵</span>0.00</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold tracking-widest text-gray-500 uppercase flex items-center mb-2">
                                            <i data-lucide="trending-down" class="w-4 h-4 mr-2 text-ui-red"></i> Total Expenses
                                        </p>
                                        <p id="out_expenses" class="text-3xl font-black text-white"><span class="text-xl text-gray-500 mr-2">GH₵</span>0.00</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Chart.js Canvas -->
                            <div class="relative z-10 mt-12 w-full max-w-sm mx-auto">
                                <canvas id="profitChart" height="250"></canvas>
                            </div>

                        </div>
                    </div>

                </div>

            </div>
        </div>

    </div> <!-- Close Main Content wrap from header/sidebar -->

    <!-- Load Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Data populated from PHP
        const stats = {
            eggs: <?= $totalEggs ?>,
            feed: <?= $totalFeedKg ?>,
            birdsSold: <?= $totalBirdsSold ?>
        };

        const outNet = document.getElementById('out_net_profit');
        const outRev = document.getElementById('out_revenue');
        const outExp = document.getElementById('out_expenses');
        const glowGood = document.getElementById('glowGood');
        const glowBad = document.getElementById('glowBad');
        
        // Chart Instance
        let pChart = null;

        function formatCurrency(num) {
            return new Intl.NumberFormat('en-GH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Math.abs(num));
        }

        function calculateMargins() {
            // Get valid HTML inputs
            const pEgg = parseFloat(document.getElementById('inp_price_egg').value) || 0;
            const pBird = parseFloat(document.getElementById('inp_price_bird').value) || 0;
            const cFeed = parseFloat(document.getElementById('inp_cost_feed').value) || 0;
            const cMisc = parseFloat(document.getElementById('inp_cost_misc').value) || 0;

            // Math
            const revenue = (stats.eggs * pEgg) + (stats.birdsSold * pBird);
            const expenses = (stats.feed * cFeed) + cMisc;
            const profit = revenue - expenses;

            // DOM Updates (Ledger)
            outRev.innerHTML = `<span class="text-xl text-gray-500 mr-2">GH₵</span>` + formatCurrency(revenue);
            outExp.innerHTML = `<span class="text-xl text-gray-500 mr-2">GH₵</span>` + formatCurrency(expenses);

            // Dynamic Styling and formatting for Net Profit Box
            let numColorClass = "";
            let dropShadowClass = "";

            if (profit > 0) {
                numColorClass = "bg-gradient-to-br from-emerald-500 to-green-400 bg-clip-text text-transparent";
                dropShadowClass = "drop-shadow-[0_0_15px_rgba(64,145,108,0.4)]";
                glowGood.classList.remove('opacity-0');
                glowGood.classList.add('opacity-40');
                glowBad.classList.remove('opacity-40');
                glowBad.classList.add('opacity-0');
            } else if (profit < 0) {
                numColorClass = "bg-gradient-to-br from-rose-500 to-red-500 bg-clip-text text-transparent";
                dropShadowClass = "drop-shadow-[0_0_15px_rgba(224,122,95,0.4)]";
                glowBad.classList.remove('opacity-0');
                glowBad.classList.add('opacity-40');
                glowGood.classList.remove('opacity-40');
                glowGood.classList.add('opacity-0');
            } else {
                numColorClass = "text-white";
                dropShadowClass = "";
                glowBad.classList.add('opacity-0');
                glowGood.classList.add('opacity-0');
            }

            const sign = profit < 0 ? '-' : '';
            const htmlStr = `<span class="text-3xl md:text-3xl font-black mr-3 text-gray-400 opacity-60">${sign}GH₵</span><span class="text-6xl md:text-[5.5rem] font-black tracking-tight ${numColorClass} ${dropShadowClass}" style="line-height:1; min-height: 80px; display: inline-block; padding-bottom: 0.1em;">${formatCurrency(profit)}</span>`;
            
            outNet.innerHTML = htmlStr;
            outNet.className = "flex items-baseline transition-all duration-700";

            // Update Chart
            updateChart(revenue, expenses);
        }

        function updateChart(revenue, expenses) {
            const ctx = document.getElementById('profitChart').getContext('2d');
            
            if (pChart) {
                pChart.destroy();
            }

            pChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Gross Revenue', 'Total Operating Expenses'],
                    datasets: [{
                        data: [revenue, expenses],
                        backgroundColor: [
                            'rgba(64, 145, 108, 0.9)', // Deep Emerald
                            'rgba(224, 122, 95, 0.9)'  // Sunrise Orange
                        ],
                        borderColor: 'rgba(17, 24, 39, 1)', // match gray-900 background
                        borderWidth: 4,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    cutout: '75%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#9ca3af',
                                font: {
                                    family: "'Plus Jakarta Sans', sans-serif",
                                    weight: 'bold',
                                    size: 11
                                },
                                padding: 20
                            }
                        },
                        tooltip: {
                            backgroundColor: '#ffffff',
                            titleColor: '#111827',
                            bodyColor: '#374151',
                            borderColor: '#f3f4f6',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: true,
                            bodyFont: {
                                family: "'Plus Jakarta Sans', sans-serif",
                                weight: 'bold'
                            },
                            callbacks: {
                                label: function(context) {
                                    return ' GH₵ ' + formatCurrency(context.raw);
                                }
                            }
                        }
                    }
                }
            });
        }

        // Initialize Calculation on Load
        document.addEventListener('DOMContentLoaded', () => {
            calculateMargins();
            
            // Add real-time event listeners
            const inputs = ['inp_price_egg', 'inp_price_bird', 'inp_cost_feed', 'inp_cost_misc'];
            inputs.forEach(id => {
                document.getElementById(id).addEventListener('input', calculateMargins);
            });
        });
    </script>
</body>
</html>
