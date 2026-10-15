<?php
require_once 'config.php';
requireLogin();

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$message = '';
$msgType = '';

// Handle Archive (soft-delete) logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_log') {
    if (!$isAdmin) {
        $message = "Unauthorized action. Only administrators can archive records.";
        $msgType = "error";
    } else {
        $log_id = (int)$_POST['log_id'];
        $log_type = $_POST['log_type'];
        
        if ($log_id > 0) {
            $table = '';
            switch ($log_type) {
                case 'eggs': $table = 'egg_production'; break;
                case 'health': $table = 'health_records'; break;
                case 'mortality': $table = 'mortality'; break;
                case 'feed': $table = 'feed_usage'; break;
            }
            
            if ($table !== '') {
                try {
                    // Soft-delete: stamp deleted_at instead of removing the row
                    $stmtDel = $pdo->prepare("UPDATE $table SET deleted_at = CURRENT_TIMESTAMP WHERE id = ? AND deleted_at IS NULL");
                    if ($stmtDel->execute([$log_id]) && $stmtDel->rowCount() > 0) {
                        $message = "Record archived successfully. It has been hidden from active views.";
                        $msgType = "success";
                    } else {
                        $message = "Record not found or already archived.";
                        $msgType = "error";
                    }
                } catch (\PDOException $e) {
                    $message = "System error preventing archival.";
                    $msgType = "error";
                }
            }
        }
    }
}

// Fetch Log Data — only active (non-archived) records
$eggs = $pdo->query("SELECT e.*, f.batch_name FROM egg_production e JOIN flocks f ON e.flock_id = f.id WHERE e.deleted_at IS NULL ORDER BY e.collection_date DESC, e.id DESC")->fetchAll();
$mortality = $pdo->query("SELECT m.*, f.batch_name FROM mortality m JOIN flocks f ON m.flock_id = f.id WHERE m.deleted_at IS NULL ORDER BY m.record_date DESC, m.id DESC")->fetchAll();
$health = $pdo->query("SELECT h.*, f.batch_name FROM health_records h JOIN flocks f ON h.flock_id = f.id WHERE h.deleted_at IS NULL ORDER BY h.record_date DESC, h.id DESC")->fetchAll();

// Feed usage — exclude archived rows
$feed = [];
try {
    $feed = $pdo->query("SELECT * FROM feed_usage WHERE deleted_at IS NULL ORDER BY usage_date DESC, id DESC")->fetchAll();
} catch (PDOException $e) {
    // Graceful fallback if feed tracking table doesn't exist perfectly as expected yet
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

        <header class="mb-8 flex flex-col md:flex-row md:items-end justify-between">
            <div class="flex items-center">
                <div class="inline-flex justify-center items-center w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-800 to-teal-600 text-white shadow-lg shadow-emerald-500/20 mr-4">
                    <i data-lucide="database" class="w-6 h-6"></i>
                </div>
                <div>
                    <h2 class="text-3xl md:text-5xl font-black bg-gradient-to-r from-emerald-800 to-teal-600 bg-clip-text text-transparent tracking-tight">System Logs</h2>
                    <p class="text-gray-500 mt-1 text-sm font-bold tracking-wide uppercase">Unified Operations History</p>
                </div>
            </div>
            
            <?php if ($isAdmin): ?>
                <div class="mt-4 md:mt-0 flex items-center bg-gray-100 text-gray-500 px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider">
                    <i data-lucide="shield-check" class="w-4 h-4 mr-2 text-ui-green"></i> System Administrator
                </div>
            <?php endif; ?>
        </header>

        <?php if ($message): ?>
            <div class="mb-8 p-5 rounded-3xl flex items-center bg-white shadow-soft border <?= $msgType === 'success' ? 'border-ui-green/30' : 'border-ui-red/30' ?>">
                <div class="<?= $msgType === 'success' ? 'bg-ui-green/10 text-ui-green' : 'bg-ui-red/10 text-ui-red' ?> p-2.5 rounded-2xl mr-4">
                    <i data-lucide="<?= $msgType === 'success' ? 'check-circle' : 'alert-octagon' ?>" class="w-6 h-6"></i>
                </div>
                <span class="font-bold text-gray-800 text-sm tracking-wide"><?= htmlspecialchars($message) ?></span>
            </div>
        <?php endif; ?>

        <!-- Tab Controls -->
        <div class="bg-gray-200/50 p-1.5 rounded-[2rem] inline-flex space-x-1 mb-8 overflow-x-auto max-w-full">
            <button onclick="switchTab('eggs')" id="tab-btn-eggs" class="tab-btn px-6 py-3 rounded-full text-sm font-extrabold transition-all duration-300 bg-white shadow-sm text-brand-dark">Egg Production</button>
            <button onclick="switchTab('health')" id="tab-btn-health" class="tab-btn px-6 py-3 rounded-full text-sm font-extrabold transition-all duration-300 text-gray-500 hover:text-gray-900 hover:bg-white/50">Health Records</button>
            <button onclick="switchTab('mortality')" id="tab-btn-mortality" class="tab-btn px-6 py-3 rounded-full text-sm font-extrabold transition-all duration-300 text-gray-500 hover:text-gray-900 hover:bg-white/50">Mortality</button>
            <button onclick="switchTab('feed')" id="tab-btn-feed" class="tab-btn px-6 py-3 rounded-full text-sm font-extrabold transition-all duration-300 text-gray-500 hover:text-gray-900 hover:bg-white/50">Feed Logs</button>
        </div>

        <!-- TABS CONTENT AREA -->
        <div class="relative min-h-[500px]">
            
            <!-- EGGS TAB -->
            <div id="tab-eggs" class="tab-content transition-opacity duration-500">
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    <?php foreach ($eggs as $row): $total = $row['quantity_good'] + $row['quantity_damaged']; ?>
                        <div class="bg-white/80 backdrop-blur-xl rounded-3xl p-6 shadow-soft border border-white/50 hover:shadow-floating hover:-translate-y-1 transition-all group">
                            <div class="flex justify-between items-start mb-4">
                                <div class="bg-brand-DEFAULT/10 text-brand-dark font-extrabold text-xs px-3 py-1.5 rounded-xl uppercase tracking-wider"><?= htmlspecialchars($row['batch_name']) ?></div>
                                <?php if ($isAdmin): ?>
                                    <button onclick="confirmDelete(<?= $row['id'] ?>, 'eggs')" class="text-gray-300 hover:text-red-500 transition-colors p-2 bg-gray-50 rounded-xl hover:bg-red-50">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                            <h3 class="text-3xl font-black text-gray-900 tracking-tight leading-none mb-4"><?= number_format($total) ?> <span class="text-sm font-bold text-gray-400">Total Eggs</span></h3>
                            <div class="flex items-center space-x-4 border-t border-gray-50 pt-4 mt-2">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 rounded-full bg-ui-green mr-2 shadow-[0_0_10px_rgba(16,185,129,0.5)]"></div>
                                    <span class="text-sm font-bold text-gray-600"><?= number_format($row['quantity_good']) ?> Good</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 rounded-full bg-ui-red mr-2 <?= $row['quantity_damaged'] > 0 ? 'shadow-[0_0_10px_rgba(239,68,68,0.5)]' : 'bg-gray-300' ?>"></div>
                                    <span class="text-sm font-bold text-gray-600"><?= number_format($row['quantity_damaged']) ?> Damaged</span>
                                </div>
                            </div>
                            <div class="text-xs text-gray-400 font-bold mt-4 tracking-widest uppercase flex items-center">
                                <i data-lucide="calendar" class="w-3 h-3 mr-1"></i> <?= date('M d, Y', strtotime($row['collection_date'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($eggs)): ?> <div class="col-span-full text-center py-10 text-gray-400 font-bold">No egg records found.</div> <?php endif; ?>
                </div>
            </div>

            <!-- MORTALITY TAB -->
            <div id="tab-mortality" class="tab-content hidden opacity-0 transition-opacity duration-500">
                 <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    <?php foreach ($mortality as $row): ?>
                        <div class="bg-gray-900/80 backdrop-blur-xl rounded-3xl p-6 shadow-soft border border-white/10 hover:shadow-floating hover:-translate-y-1 transition-all group">
                            <div class="flex justify-between items-start mb-4">
                                <div class="bg-white/10 text-white font-extrabold text-xs px-3 py-1.5 rounded-xl uppercase tracking-wider backdrop-blur-sm border border-white/5"><?= htmlspecialchars($row['batch_name']) ?></div>
                                <?php if ($isAdmin): ?>
                                    <button onclick="confirmDelete(<?= $row['id'] ?>, 'mortality')" class="text-gray-500 hover:text-red-400 transition-colors p-2 bg-white/5 rounded-xl hover:bg-red-500/20">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="flex items-baseline space-x-2 mb-4">
                                <h3 class="text-4xl font-black text-ui-red tracking-tight leading-none"><?= number_format($row['number_of_birds']) ?></h3>
                                <span class="text-sm font-bold text-gray-500">Bird(s) Lost</span>
                            </div>
                            <div class="text-sm font-bold text-gray-400 border-t border-gray-800 pt-4 mt-2 mb-4 line-clamp-2">
                                <?= htmlspecialchars($row['cause_of_death'] ?: 'No cause specified') ?>
                            </div>
                            <div class="text-xs text-gray-500 font-bold tracking-widest uppercase flex items-center">
                                <i data-lucide="calendar-x" class="w-3 h-3 mr-1 text-ui-red/70"></i> <?= date('M d, Y', strtotime($row['record_date'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($mortality)): ?> <div class="col-span-full text-center py-10 text-gray-400 font-bold">No mortality records found. Flocks are healthy!</div> <?php endif; ?>
                </div>
            </div>

            <!-- HEALTH TAB -->
            <div id="tab-health" class="tab-content hidden opacity-0 transition-opacity duration-500">
                <div class="grid grid-cols-1 shrink-0 gap-4">
                    <?php foreach ($health as $row): ?>
                        <div class="bg-white/80 backdrop-blur-xl rounded-3xl p-5 shadow-soft border border-white/50 flex flex-col md:flex-row md:items-center justify-between hover:border-brand-sky/30 transition-colors">
                            <div class="flex items-center mb-4 md:mb-0">
                                <div class="w-14 h-14 rounded-2xl bg-brand-sky/10 text-brand-sky flex items-center justify-center mr-5 shadow-sm border border-brand-sky/10 shrink-0">
                                    <i data-lucide="<?= strtolower($row['type']) === 'vaccination' ? 'syringe' : 'activity' ?>" class="w-6 h-6"></i>
                                </div>
                                <div>
                                    <div class="flex items-center space-x-3 mb-1">
                                        <h3 class="font-black text-gray-900 text-lg"><?= htmlspecialchars($row['batch_name']) ?></h3>
                                        <span class="px-2 py-0.5 bg-gray-100 text-gray-500 rounded-lg text-[10px] font-extrabold uppercase tracking-widest"><?= htmlspecialchars($row['type']) ?></span>
                                    </div>
                                    <p class="text-sm font-bold text-brand-sky"><?= htmlspecialchars($row['medication_name']) ?> <span class="text-gray-400 ml-2">(<?= htmlspecialchars($row['dosage'] ?? 'N/A') ?>)</span></p>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between md:justify-end md:w-1/3">
                                <div class="text-xs text-gray-400 font-bold tracking-widest uppercase flex items-center mr-6">
                                    <i data-lucide="calendar" class="w-4 h-4 mr-2"></i> <?= date('M d, Y', strtotime($row['record_date'])) ?>
                                </div>
                                <?php if ($isAdmin): ?>
                                    <button onclick="confirmDelete(<?= $row['id'] ?>, 'health')" class="text-gray-400 hover:text-red-500 transition-colors p-3 bg-gray-50 rounded-xl hover:bg-red-50">
                                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($health)): ?> <div class="text-center py-10 text-gray-400 font-bold">No health interventions found.</div> <?php endif; ?>
                </div>
            </div>

            <!-- FEED TAB -->
            <div id="tab-feed" class="tab-content hidden opacity-0 transition-opacity duration-500">
                 <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                    <?php foreach ($feed as $row): ?>
                        <div class="bg-white/80 backdrop-blur-xl rounded-3xl p-6 shadow-soft border border-white/50 relative overflow-hidden group hover:border-ui-yellow/40 transition-all">
                             <div class="absolute -right-6 -top-6 w-32 h-32 bg-ui-yellow/5 rounded-full blur-2xl group-hover:bg-ui-yellow/10 transition-colors duration-500"></div>
                            
                            <div class="flex justify-between items-start mb-6">
                                <div class="bg-ui-yellow/10 text-yellow-600 p-2.5 rounded-xl">
                                    <i data-lucide="wheat" class="w-5 h-5"></i>
                                </div>
                                <?php if ($isAdmin): ?>
                                    <button onclick="confirmDelete(<?= $row['id'] ?>, 'feed')" class="text-gray-300 hover:text-red-500 transition-colors p-2 hover:bg-red-50 rounded-lg">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                            
                            <h3 class="text-3xl font-black text-gray-900 tracking-tight leading-none mb-1"><?= number_format($row['quantity_used_kg'], 1) ?><span class="text-base text-gray-400 ml-1 font-bold">kg</span></h3>
                            <p class="text-sm font-bold text-gray-500 mb-6 border-b border-gray-50 pb-4">Feed Consumed</p>
                            
                            <div class="text-xs text-gray-400 font-bold tracking-widest uppercase flex items-center">
                                <i data-lucide="calendar" class="w-3 h-3 mr-1"></i> <?= date('M d, Y', strtotime($row['usage_date'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($feed)): ?> <div class="col-span-full text-center py-10 text-gray-400 font-bold">No feed usage logged yet.</div> <?php endif; ?>
                </div>
            </div>

        </div>

    </div> <!-- Close Main Content wrap from header/sidebar -->

    <!-- MODERN CONFIRMATION MODAL (Hidden by Default) -->
    <div id="deleteModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 mt-0 opacity-0 pointer-events-none transition-opacity duration-300" style="margin-top: 0;">
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="bg-white rounded-[2rem] shadow-floating max-w-sm w-full p-8 relative z-10 transform translate-y-4 transition-transform duration-300 border border-white/50" id="modalContent">
            
            <div class="w-16 h-16 rounded-full bg-ui-red/10 text-ui-red flex items-center justify-center mx-auto mb-6 shadow-sm">
                <i data-lucide="alert-triangle" class="w-8 h-8"></i>
            </div>
            
            <h3 class="text-2xl font-black text-gray-900 text-center mb-2 tracking-tight">Archive Record?</h3>
            <p class="text-sm font-semibold text-gray-500 text-center mb-8 leading-relaxed">The record will be <strong>hidden from all active views</strong> and marked as archived. No data is permanently deleted.</p>
            
            <form id="deleteForm" action="logs.php" method="POST" class="flex flex-col space-y-3">
                <input type="hidden" name="action" value="delete_log">
                <input type="hidden" name="log_id" id="delete_log_id" value="">
                <input type="hidden" name="log_type" id="delete_log_type" value="">
                
                <button type="submit" class="w-full py-3.5 px-4 bg-amber-500 hover:bg-amber-600 focus:ring-4 focus:ring-amber-200 text-white rounded-2xl font-black text-sm transition-all shadow-md flex items-center justify-center">
                    <i data-lucide="archive" class="w-4 h-4 mr-2"></i> Yes, Archive Record
                </button>
                <button type="button" onclick="closeModal()" class="w-full py-3.5 px-4 bg-white border-2 border-gray-100 hover:bg-gray-50 text-gray-600 rounded-2xl font-black text-sm transition-all">
                    Cancel
                </button>
            </form>
        </div>
    </div>

    <!-- JS Logic for Tabs and Modals -->
    <script>
        function switchTab(tabId) {
            // Reset buttons styling
            const allBtns = document.querySelectorAll('.tab-btn');
            allBtns.forEach(btn => {
                btn.className = 'tab-btn px-6 py-3 rounded-full text-sm font-extrabold transition-all duration-300 text-gray-500 hover:text-gray-900 hover:bg-white/50';
            });
            
            // Set active button styling
            const activeBtn = document.getElementById('tab-btn-' + tabId);
            if(activeBtn) {
                activeBtn.className = 'tab-btn px-6 py-3 rounded-full text-sm font-extrabold transition-all duration-300 bg-white shadow-sm text-brand-dark';
            }

            // Hide all tab contents
            const allContents = document.querySelectorAll('.tab-content');
            allContents.forEach(content => {
                content.classList.add('hidden');
                content.classList.remove('opacity-100');
                content.classList.add('opacity-0');
            });

            // Show active content
            const activeContent = document.getElementById('tab-' + tabId);
            if(activeContent) {
                activeContent.classList.remove('hidden');
                // Small delay to trigger the CSS transition
                setTimeout(() => {
                    activeContent.classList.remove('opacity-0');
                    activeContent.classList.add('opacity-100');
                }, 50);
            }
        }

        const modal = document.getElementById('deleteModal');
        const modalContent = document.getElementById('modalContent');
        const inputId = document.getElementById('delete_log_id');
        const inputType = document.getElementById('delete_log_type');

        function confirmDelete(id, type) {
            inputId.value = id;
            inputType.value = type;
            
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modalContent.classList.remove('translate-y-4');
        }

        function closeModal() {
            modal.classList.add('opacity-0', 'pointer-events-none');
            modalContent.classList.add('translate-y-4');
        }
    </script>
</body>
</html>
