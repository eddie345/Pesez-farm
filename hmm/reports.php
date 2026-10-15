<?php
require_once 'config.php';
requireLogin();

$report_type = $_GET['report_type'] ?? 'daily';
$date = $_GET['date'] ?? date('Y-m-d');

if ($report_type === 'daily') {
    $start_date = $date;
    $end_date = $date;
    $display_period = date('l, M d, Y', strtotime($date));
} elseif ($report_type === 'weekly') {
    $dto = new DateTime($date);
    $dto->modify('Monday this week');
    $start_date = $dto->format('Y-m-d');
    $dto->modify('Sunday this week');
    $end_date = $dto->format('Y-m-d');
    $display_period = date('M d', strtotime($start_date)) . ' – ' . date('M d, Y', strtotime($end_date));
} elseif ($report_type === 'monthly') {
    $start_date = date('Y-m-01', strtotime($date));
    $end_date = date('Y-m-t', strtotime($date));
    $display_period = date('F Y', strtotime($date));
} else {
    $start_date = $date;
    $end_date = $date;
    $display_period = date('M d, Y', strtotime($date));
}

// Data Array
$data = [
    'eggs_good' => 0,
    'eggs_damaged' => 0,
    'feed_consumed' => 0.00,
    'mortality' => 0
];

// Aggregations — all queries exclude soft-deleted (archived) rows
$stmtEggs = $pdo->prepare("SELECT SUM(quantity_good) as total_good, SUM(quantity_damaged) as total_damaged FROM egg_production WHERE collection_date BETWEEN ? AND ? AND deleted_at IS NULL");
$stmtEggs->execute([$start_date, $end_date]);
$eggsRes = $stmtEggs->fetch();
$data['eggs_good'] = (int)($eggsRes['total_good'] ?? 0);
$data['eggs_damaged'] = (int)($eggsRes['total_damaged'] ?? 0);

$stmtFeed = $pdo->prepare("SELECT SUM(quantity_used_kg) as total_feed FROM feed_usage WHERE usage_date BETWEEN ? AND ? AND deleted_at IS NULL");
$stmtFeed->execute([$start_date, $end_date]);
$feedRes = $stmtFeed->fetch();
$data['feed_consumed'] = (float)($feedRes['total_feed'] ?? 0);

$stmtMortality = $pdo->prepare("SELECT SUM(number_of_birds) as total_mortality FROM mortality WHERE record_date BETWEEN ? AND ? AND deleted_at IS NULL");
$stmtMortality->execute([$start_date, $end_date]);
$mortalityRes = $stmtMortality->fetch();
$data['mortality'] = (int)($mortalityRes['total_mortality'] ?? 0);

$stmtHealth = $pdo->prepare("SELECT r.type, r.medication_name, r.record_date, f.batch_name FROM health_records r JOIN flocks f ON r.flock_id = f.id WHERE r.record_date BETWEEN ? AND ? AND r.deleted_at IS NULL ORDER BY r.record_date DESC");
$stmtHealth->execute([$start_date, $end_date]);
$healthInterventions = $stmtHealth->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

        <!-- Header Section (Hide on Print) -->
        <div class="w-full flex justify-center items-start pt-6 mb-10 print:hidden">
            <div class="max-w-xl text-center">
                <div class="inline-flex justify-center items-center w-14 h-14 rounded-2xl bg-gradient-to-br from-brand-dark to-black text-white shadow-soft mb-4">
                    <i data-lucide="bar-chart-3" class="w-7 h-7"></i>
                </div>
                <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 tracking-tight">Analytics & Reports</h2>
                <p class="text-gray-500 mt-2 text-sm font-semibold">Generate operational summaries</p>
            </div>
        </div>
        
        <header class="mb-10 flex flex-col md:flex-row md:items-end justify-between print:hidden">
            <div></div> <!-- Spacer for the layout -->
            
            <form action="reports.php" method="GET" class="mt-6 md:mt-0 flex flex-wrap items-center gap-3 bg-white p-2 rounded-2xl shadow-soft border border-gray-100">
                <select name="report_type" class="pl-4 pr-10 py-2.5 border-none bg-gray-50 font-bold text-sm text-gray-700 outline-none focus:ring-4 focus:ring-brand-DEFAULT/10 rounded-xl cursor-pointer">
                    <option value="daily" <?= $report_type === 'daily' ? 'selected' : '' ?>>Daily Report</option>
                    <option value="weekly" <?= $report_type === 'weekly' ? 'selected' : '' ?>>Weekly Report</option>
                    <option value="monthly" <?= $report_type === 'monthly' ? 'selected' : '' ?>>Monthly Report</option>
                </select>
                <input type="date" name="date" value="<?= htmlspecialchars($date) ?>" class="px-4 py-2.5 border-none bg-gray-50 font-bold text-sm text-gray-700 outline-none focus:ring-4 focus:ring-brand-DEFAULT/10 rounded-xl cursor-pointer">
                <button type="submit" class="px-5 py-2.5 bg-brand-dark text-white rounded-xl hover:bg-black transition-all shadow-md hover:shadow-lg hover:-translate-y-0.5 text-sm font-bold flex items-center">
                    <i data-lucide="filter" class="w-4 h-4 mr-2"></i> Apply Context
                </button>
            </form>
        </header>

        <!-- Printable Document Area -->
        <div id="report-document" class="bg-white rounded-[2.5rem] shadow-soft border border-gray-100 p-8 md:p-14 relative print:shadow-none print:border-none print:p-0 print:m-0 print:rounded-none">
            
            <div class="absolute top-8 right-8 print:hidden">
                <button onclick="window.print()" class="flex items-center px-5 py-3 bg-brand-accent text-white rounded-xl hover:bg-orange-600 transition-all shadow-md hover:shadow-lg hover:-translate-y-0.5 font-bold text-sm">
                    <i data-lucide="download" class="w-4 h-4 mr-2"></i> Download / Print
                </button>
            </div>

            <!-- Report Header -->
            <div class="text-center mb-16 pb-8 border-b border-gray-100 relative">
                <div class="inline-flex justify-center items-center w-20 h-20 rounded-[1.5rem] bg-gradient-to-br from-brand-DEFAULT to-brand-dark text-white shadow-soft mb-6">
                    <i data-lucide="leaf" class="w-10 h-10"></i>
                </div>
                <h1 class="text-4xl md:text-5xl font-black text-gray-900 tracking-tight">Operations Protocol</h1>
                <p class="text-xl text-brand-DEFAULT mt-3 font-extrabold capitalize tracking-wide"><?= htmlspecialchars($report_type) ?> Summary</p>
                <div class="mt-6 py-1.5 px-6 bg-gray-50 border border-gray-100 inline-block rounded-full">
                    <span class="text-sm font-bold text-gray-600 tracking-widest uppercase"><?= $display_period ?></span>
                </div>
            </div>

            <!-- Bento Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-16">
                
                <div class="bg-brand-sky/5 rounded-3xl p-6 border-2 border-brand-sky/10 text-center relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-20 h-20 bg-brand-sky/10 rounded-full blur-xl"></div>
                    <div class="bg-white w-14 h-14 rounded-2xl flex items-center justify-center shadow-sm mx-auto mb-4 text-brand-sky">
                         <i data-lucide="egg" class="w-7 h-7"></i>
                    </div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Good Eggs</p>
                    <p class="text-4xl font-black text-gray-900"><?= number_format($data['eggs_good']) ?></p>
                </div>

                <div class="bg-ui-red/5 rounded-3xl p-6 border-2 border-ui-red/10 text-center relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-20 h-20 bg-ui-red/10 rounded-full blur-xl"></div>
                    <div class="bg-white w-14 h-14 rounded-2xl flex items-center justify-center shadow-sm mx-auto mb-4 text-ui-red">
                         <i data-lucide="egg-fried" class="w-7 h-7"></i>
                    </div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Damaged</p>
                    <p class="text-4xl font-black text-ui-red"><?= number_format($data['eggs_damaged']) ?></p>
                </div>

                <div class="bg-ui-yellow/10 rounded-3xl p-6 border-2 border-ui-yellow/20 text-center relative overflow-hidden">
                     <div class="absolute -right-4 -top-4 w-20 h-20 bg-ui-yellow/20 rounded-full blur-xl"></div>
                    <div class="bg-white w-14 h-14 rounded-2xl flex items-center justify-center shadow-sm mx-auto mb-4 text-yellow-600">
                         <i data-lucide="wheat" class="w-7 h-7"></i>
                    </div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Feed Burn</p>
                    <p class="text-4xl font-black text-gray-900"><?= number_format($data['feed_consumed'], 1) ?> <span class="text-lg text-gray-400 font-bold ml-0.5">kg</span></p>
                </div>

                <div class="bg-gray-50 rounded-3xl p-6 border-2 border-gray-200 text-center relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-20 h-20 bg-gray-200 rounded-full blur-xl"></div>
                    <div class="bg-white w-14 h-14 rounded-2xl flex items-center justify-center shadow-sm mx-auto mb-4 text-gray-600">
                         <i data-lucide="skull" class="w-7 h-7"></i>
                    </div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Mortalities</p>
                    <p class="text-4xl font-black text-gray-900"><?= number_format($data['mortality']) ?></p>
                </div>

            </div>

            <!-- Event Tables Area -->
            <div>
                <h3 class="text-2xl font-black text-gray-900 mb-6 flex items-center">
                    <div class="bg-brand-DEFAULT/10 p-2.5 rounded-xl text-brand-DEFAULT mr-3">
                        <i data-lucide="activity" class="w-5 h-5"></i>
                    </div>
                    Health Operations Log
                </h3>
                
                <?php if (empty($healthInterventions)): ?>
                    <div class="p-10 bg-gray-50/50 border-2 border-dashed border-gray-100 rounded-[2rem] text-center">
                        <i data-lucide="shield-check" class="w-12 h-12 text-gray-300 mx-auto mb-4"></i>
                        <p class="text-gray-500 font-bold">No health operations flagged within this timeframe.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto border-2 border-gray-50 rounded-3xl">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead class="bg-gray-50 border-b border-gray-100 text-gray-400 font-bold uppercase tracking-wider text-[11px]">
                                <tr>
                                    <th class="px-6 py-4">Logged Date</th>
                                    <th class="px-6 py-4">Target Batch</th>
                                    <th class="px-6 py-4">Protocol Type</th>
                                    <th class="px-6 py-4">Medication / Context</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 bg-white">
                                <?php foreach ($healthInterventions as $h): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-5 font-bold text-gray-700"><?= date('M d, Y', strtotime($h['record_date'])) ?></td>
                                        <td class="px-6 py-5 font-black text-gray-900"><?= htmlspecialchars($h['batch_name']) ?></td>
                                        <td class="px-6 py-5 font-extrabold text-brand-sky tracking-wide uppercase text-[10px]"><span class="bg-brand-sky/10 py-1.5 px-3 rounded-lg"><?= htmlspecialchars($h['type']) ?></span></td>
                                        <td class="px-6 py-5 font-medium text-gray-500"><?= htmlspecialchars($h['medication_name']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mt-16 pt-8 border-t border-gray-100 flex flex-col items-center text-center text-xs text-gray-400 font-bold uppercase tracking-widest">
                <i data-lucide="fingerprint" class="w-6 h-6 mb-3 text-gray-300"></i>
                <p>Authenticated Generation • <?= date('Y-m-d H:i:s') ?></p>
                <p class="mt-1">Generated by: <?= isset($_SESSION['username']) ? htmlspecialchars(strtoupper($_SESSION['username'])) : 'SYSTEM_NODE' ?></p>
            </div>
            
        </div>
    </div>
</div>

<!-- Dynamic Print Styles -->
<style>
    @media print {
        body { 
            background: white !important; 
            background-image: none !important;
            -webkit-print-color-adjust: exact; 
        }
        #sidebar, .print\:hidden { display: none !important; }
        .flex-1 { margin-left: 0 !important; width: 100% !important; overflow: visible !important; padding: 0 !important; }
        @page { margin: 1cm; }
        
        #report-document {
            box-shadow: none !important;
            border: none !important;
        }
    }
</style>

</body>
</html>
