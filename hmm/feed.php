<?php
require_once 'config.php';
requireLogin();

$message = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_stock') {
        $feed_type = trim($_POST['feed_type']);
        $quantity_kg = (float)$_POST['quantity_kg'];

        if (!empty($feed_type) && $quantity_kg > 0) {
            // INSERT OR UPDATE
            $stmt = $pdo->prepare("INSERT INTO feed_inventory (feed_type, quantity_in_stock_kg) VALUES (?, ?) ON DUPLICATE KEY UPDATE quantity_in_stock_kg = quantity_in_stock_kg + ?");
            if ($stmt->execute([$feed_type, $quantity_kg, $quantity_kg])) {
                $message = "Stock added successfully.";
                $msgType = "success";
            } else {
                $message = "Error adding stock.";
                $msgType = "error";
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'log_usage') {
        $flock_id = (int)$_POST['flock_id'];
        $usage_date = $_POST['usage_date'];
        $feed_type = trim($_POST['feed_type_usage']);
        $quantity_used_kg = (float)$_POST['quantity_used_kg'];
        $recorded_by = $_SESSION['user_id'];

        if ($flock_id > 0 && !empty($usage_date) && !empty($feed_type) && $quantity_used_kg > 0) {
            // Strict Validation
            $stmtCheck = $pdo->prepare("SELECT quantity_in_stock_kg FROM feed_inventory WHERE feed_type = ?");
            $stmtCheck->execute([$feed_type]);
            $stock = $stmtCheck->fetch();

            if (!$stock || $stock['quantity_in_stock_kg'] < $quantity_used_kg) {
                $message = "Transaction Blocked: Not enough stock for {$feed_type}. Available: " . ($stock['quantity_in_stock_kg'] ?? 0) . " kg";
                $msgType = "error";
            } else {
                try {
                    $pdo->beginTransaction();

                    // Deduct stock
                    $stmtDeduct = $pdo->prepare("UPDATE feed_inventory SET quantity_in_stock_kg = quantity_in_stock_kg - ? WHERE feed_type = ?");
                    $stmtDeduct->execute([$quantity_used_kg, $feed_type]);

                    // Log usage
                    $stmtLog = $pdo->prepare("INSERT INTO feed_usage (flock_id, usage_date, feed_type, quantity_used_kg, recorded_by) VALUES (?, ?, ?, ?, ?)");
                    $stmtLog->execute([$flock_id, $usage_date, $feed_type, $quantity_used_kg, $recorded_by]);

                    $pdo->commit();
                    $message = "Feed usage logged successfully. You can view history in System Logs.";
                    $msgType = "success";
                } catch (\PDOException $e) {
                    $pdo->rollBack();
                    $message = "Error logging usage: " . $e->getMessage();
                    $msgType = "error";
                }
            }
        }
    }
}

// Data Fetching
$stmtInventory = $pdo->query("SELECT * FROM feed_inventory ORDER BY quantity_in_stock_kg DESC");
$inventory = $stmtInventory->fetchAll();

$maxStock = 0;
foreach ($inventory as $item) {
    if ($item['quantity_in_stock_kg'] > $maxStock) {
        $maxStock = $item['quantity_in_stock_kg'];
    }
}
$maxStockTarget = max($maxStock, 1000); // Minimum scale of 1000kg

$stmtActiveFlocks = $pdo->query("SELECT id, batch_name FROM flocks WHERE status = 'active'");
$activeFlocks = $stmtActiveFlocks->fetchAll();

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
            <div class="w-full max-w-xl px-4">
                
                <header class="mb-8 text-center">
                    <div class="inline-flex justify-center items-center w-14 h-14 rounded-2xl bg-gradient-to-br from-ui-yellow to-yellow-600 text-white shadow-soft mb-4">
                        <i data-lucide="wheat" class="w-7 h-7"></i>
                    </div>
                    <h2 class="text-2xl md:text-3xl font-extrabold bg-gradient-to-r from-emerald-800 to-teal-600 bg-clip-text text-transparent tracking-tight">Feed Management</h2>
                    <p class="text-gray-500 mt-2 text-xs font-bold tracking-wider uppercase">Inflow & Consumption Control</p>
                </header>

                <?php if ($message): ?>
                    <div class="mb-6 p-4 rounded-2xl flex flex-col items-center justify-center text-center bg-white shadow-soft border <?= $msgType === 'success' ? 'border-ui-green/30' : 'border-ui-red/30' ?>">
                        <div class="<?= $msgType === 'success' ? 'bg-ui-green/10 text-ui-green' : 'bg-ui-red/10 text-ui-red' ?> p-2.5 rounded-xl mb-3">
                            <i data-lucide="<?= $msgType === 'success' ? 'check-circle-2' : 'alert-octagon' ?>" class="w-6 h-6"></i>
                        </div>
                        <span class="font-bold text-gray-900 text-sm tracking-tight"><?= htmlspecialchars($message) ?></span>
                        <?php if ($msgType === 'success' && strpos($message, 'usage') !== false): ?>
                            <a href="logs.php" class="mt-3 text-sm font-bold text-brand-sky hover:text-blue-600 transition-colors flex items-center">
                                View System Logs <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Log Usage Form (Primary Action) -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/50 p-6 md:p-8 relative overflow-hidden group mb-6">
                    <div class="absolute -left-24 -top-24 w-96 h-96 bg-ui-yellow/10 rounded-full blur-3xl -z-10 transition-colors duration-1000"></div>
                    
                    <h3 class="text-base font-extrabold text-gray-900 mb-5 flex items-center">
                        <div class="bg-orange-100 text-orange-600 p-2 rounded-xl mr-3">
                            <i data-lucide="package-minus" class="w-4 h-4"></i>
                        </div>
                        Log Consumption
                    </h3>

                    <form action="feed.php" method="POST" class="space-y-4 relative z-10">
                        <input type="hidden" name="action" value="log_usage">
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5 ml-0.5 flex justify-between items-center">
                                Target Flock
                                <span class="bg-orange-100 text-orange-600 text-[10px] px-2 py-0.5 rounded-full uppercase tracking-wider font-extrabold flex items-center"><i data-lucide="trending-down" class="w-2.5 h-2.5 mr-1"></i> Stock Reduction</span>
                            </label>
                            <div class="relative">
                                <select name="flock_id" required class="block w-full px-4 py-2.5 border-2 border-gray-100 rounded-xl focus:outline-none focus:ring-4 focus:ring-orange-500/20 focus:border-orange-500 text-base bg-gray-50 text-gray-900 font-semibold transition-all appearance-none cursor-pointer">
                                    <option value="" disabled selected class="text-gray-400 font-medium">-- Select a flock --</option>
                                    <?php foreach ($activeFlocks as $f): ?>
                                        <option value="<?= $f['id'] ?>" class="font-bold"><?= htmlspecialchars($f['batch_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400">
                                    <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5 ml-0.5">Consumption Date</label>
                            <input type="date" name="usage_date" required value="<?= date('Y-m-d') ?>" class="block w-full px-4 py-2.5 border-2 border-gray-100 rounded-xl focus:outline-none focus:ring-4 focus:ring-orange-500/20 focus:border-orange-500 text-base bg-gray-50 text-gray-900 font-semibold transition-all">
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5 ml-0.5">Select Feed Type</label>
                                <div class="relative">
                                    <select name="feed_type_usage" required class="block w-full px-4 py-2.5 border-2 border-gray-100 rounded-xl focus:outline-none focus:ring-4 focus:ring-orange-500/20 focus:border-orange-500 text-base bg-gray-50 text-gray-900 font-semibold transition-all appearance-none cursor-pointer">
                                        <?php if (empty($inventory)): ?>
                                            <option value="" disabled selected class="text-gray-400 font-medium">No inventory data!</option>
                                        <?php endif; ?>
                                        <?php foreach ($inventory as $inv): ?>
                                            <option value="<?= htmlspecialchars($inv['feed_type']) ?>" class="font-bold"><?= htmlspecialchars($inv['feed_type']) ?> (<?= number_format($inv['quantity_in_stock_kg'],1) ?>kg)</option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400">
                                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-black text-orange-600 uppercase tracking-wider mb-1.5 ml-0.5">Quantity Used <span class="text-gray-400 font-semibold">(kg)</span></label>
                                <input type="number" step="0.01" name="quantity_used_kg" required min="0.1" placeholder="e.g. 50" class="block w-full px-4 py-2.5 border-2 border-orange-500/20 rounded-xl focus:outline-none focus:ring-4 focus:ring-orange-500/30 focus:border-orange-500 text-xl bg-orange-50/50 text-orange-600 font-black transition-all placeholder-orange-500/30">
                            </div>
                        </div>

                        <div class="pt-2 border-t border-gray-100">
                            <button type="submit" class="w-full flex justify-center items-center py-3 px-6 rounded-xl text-base font-bold text-white bg-orange-500 hover:bg-orange-600 focus:outline-none focus:ring-4 focus:ring-orange-500/30 transition-all shadow-md hover:shadow-lg hover:-translate-y-0.5">
                                <i data-lucide="activity" class="w-4 h-4 mr-2"></i> Confirm Consumption
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Add Stock Form (Secondary Action) -->
                <div class="bg-white/60 backdrop-blur-xl rounded-2xl shadow-sm border border-white/50 p-6 relative overflow-hidden group mb-6">
                    <h3 class="text-base font-extrabold text-gray-900 mb-4 flex items-center">
                        <div class="bg-brand-sky/10 text-brand-sky p-2 rounded-xl mr-3">
                            <i data-lucide="package-plus" class="w-4 h-4"></i>
                        </div>
                        Receive New Stock
                    </h3>

                    <form action="feed.php" method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="add_stock">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5 ml-0.5">Feed Type</label>
                                <input type="text" name="feed_type" placeholder="e.g. Starter, Finisher" required class="block w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-4 focus:ring-brand-sky/20 focus:border-brand-sky text-base bg-white text-gray-900 font-semibold transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5 ml-0.5">Quantity <span class="text-gray-400 font-semibold">(kg)</span></label>
                                <input type="number" step="0.01" name="quantity_kg" required min="0.1" class="block w-full px-4 py-2.5 border border-brand-sky/30 rounded-xl focus:outline-none focus:ring-4 focus:ring-brand-sky/30 focus:border-brand-sky text-lg bg-white text-brand-sky font-black transition-all">
                            </div>
                        </div>
                        <div class="pt-2">
                            <button type="submit" class="w-full flex justify-center items-center py-3 px-6 rounded-xl text-base font-bold text-white bg-brand-sky hover:bg-blue-500 focus:outline-none focus:ring-4 focus:ring-brand-sky/30 transition-all shadow-md hover:-translate-y-0.5">
                                <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> Register Stock Inflow
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Current Inventory (Progress Bars) -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl border border-white/50 p-6 md:p-8 mb-6">
                    <h3 class="text-base font-extrabold text-gray-900 mb-5 flex items-center justify-center">
                        <i data-lucide="bar-chart-2" class="w-5 h-5 mr-2 text-brand-DEFAULT"></i> Warehouse Inventory
                    </h3>
                    <div class="space-y-5">
                        <?php foreach ($inventory as $inv): 
                            $percentage = min(100, ($inv['quantity_in_stock_kg'] / $maxStockTarget) * 100);
                            $barColor = $percentage < 20 ? 'bg-ui-red' : ($percentage < 50 ? 'bg-ui-yellow' : 'bg-ui-green');
                        ?>
                        <div>
                            <div class="flex justify-between items-end mb-3">
                                <span class="text-sm font-black text-gray-700 uppercase tracking-widest"><?= htmlspecialchars($inv['feed_type']) ?></span>
                                <span class="text-xl font-black text-gray-900"><?= number_format($inv['quantity_in_stock_kg'], 1) ?> <span class="text-gray-400 font-bold text-sm">kg</span></span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-4 overflow-hidden shadow-inner">
                                <div class="<?= $barColor ?> h-4 rounded-full transition-all duration-1000 ease-out shadow-sm" style="width: <?= $percentage ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($inventory)): ?>
                            <div class="flex flex-col items-center justify-center py-10">
                                <i data-lucide="box" class="w-16 h-16 mb-4 text-gray-200"></i>
                                <p class="text-gray-400 font-bold text-lg">Warehouse is currently empty.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>

    </div> <!-- Close Main Content wrap from header/sidebar -->
</body>
</html>
