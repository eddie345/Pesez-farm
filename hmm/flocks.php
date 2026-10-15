<?php
require_once 'config.php';
requireLogin();

$message = '';
$msgType = '';

// Handle Form Submissions
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin) {
    if (isset($_POST['action']) && $_POST['action'] === 'add_flock') {
        $batch_name = trim($_POST['batch_name']);
        $bird_type = $_POST['bird_type'];
        $initial_count = (int)$_POST['initial_count'];
        $arrival_date = $_POST['arrival_date'];

        if (!empty($batch_name) && !empty($bird_type) && $initial_count > 0 && !empty($arrival_date)) {
            $stmt = $pdo->prepare("INSERT INTO flocks (batch_name, bird_type, initial_count, current_count, arrival_date, status) VALUES (?, ?, ?, ?, ?, 'active')");
            if ($stmt->execute([$batch_name, $bird_type, $initial_count, $initial_count, $arrival_date])) {
                $message = "Flock added successfully.";
                $msgType = "success";
            } else {
                $message = "Error adding flock.";
                $msgType = "error";
            }
        } else {
            $message = "Please fill in all fields correctly.";
            $msgType = "error";
        }
    }
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

        <div class="w-full flex justify-center items-start pt-6 pb-12">
            <div class="max-w-lg mx-auto w-full px-4">
                
                <header class="mb-8 text-center">
                    <div class="inline-flex justify-center items-center w-14 h-14 rounded-2xl bg-gradient-to-br from-brand-DEFAULT to-brand-dark text-white shadow-soft mb-4">
                        <i data-lucide="bird" class="w-7 h-7"></i>
                    </div>
                    <h2 class="text-2xl md:text-3xl font-extrabold bg-gradient-to-r from-emerald-800 to-teal-600 bg-clip-text text-transparent tracking-tight">Flocks Management</h2>
                    <p class="text-gray-500 mt-2 text-xs font-bold tracking-wider uppercase">Register & Track Bird Batches</p>
                </header>

                <?php if ($message): ?>
                    <div class="mb-6 p-4 rounded-2xl flex flex-col items-center justify-center text-center bg-white shadow-soft border <?= $msgType === 'success' ? 'border-ui-green/30' : 'border-ui-red/30' ?>">
                        <div class="<?= $msgType === 'success' ? 'bg-ui-green/10 text-ui-green' : 'bg-ui-red/10 text-ui-red' ?> p-3 rounded-xl mb-3">
                            <i data-lucide="<?= $msgType === 'success' ? 'check-circle-2' : 'alert-octagon' ?>" class="w-6 h-6"></i>
                        </div>
                        <span class="font-bold text-gray-900 text-sm tracking-tight"><?= htmlspecialchars($message) ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($isAdmin): ?>
                    <!-- Compact Centered Premium Form Container -->
                    <div class="bg-white/80 backdrop-blur-xl p-6 md:p-8 rounded-2xl shadow-2xl border border-white/50 relative overflow-hidden group">
                        <div class="absolute -right-24 -top-24 w-96 h-96 bg-brand-DEFAULT/5 rounded-full blur-3xl -z-10 group-hover:bg-brand-DEFAULT/10 transition-colors duration-1000"></div>
                        
                        <form action="flocks.php" method="POST" class="space-y-5 relative z-10">
                            <input type="hidden" name="action" value="add_flock">
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5 ml-0.5">Batch Name</label>
                                <input type="text" name="batch_name" required placeholder="E.g. Batch A - Spring" class="block w-full text-base font-medium py-2.5 px-4 rounded-xl border-2 border-gray-100 focus:outline-none focus:ring-4 focus:ring-brand-DEFAULT/20 focus:border-brand-DEFAULT bg-gray-50 text-gray-900 transition-all placeholder-gray-400">
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5 ml-0.5">Bird Type</label>
                                    <div class="relative">
                                        <select name="bird_type" required class="block w-full text-base font-medium py-2.5 px-4 rounded-xl border-2 border-gray-100 focus:outline-none focus:ring-4 focus:ring-brand-DEFAULT/20 focus:border-brand-DEFAULT bg-gray-50 text-gray-900 transition-all appearance-none cursor-pointer">
                                            <option value="Layer">Layer</option>
                                            <option value="Broiler">Broiler</option>
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400">
                                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5 ml-0.5">Initial Count</label>
                                    <input type="number" name="initial_count" required min="1" placeholder="e.g. 500" class="block w-full text-base font-medium py-2.5 px-4 rounded-xl border-2 border-brand-DEFAULT/20 focus:outline-none focus:ring-4 focus:ring-brand-DEFAULT/30 focus:border-brand-DEFAULT bg-brand-DEFAULT/5 text-brand-DEFAULT transition-all">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5 ml-0.5">Arrival Date</label>
                                <input type="date" name="arrival_date" required value="<?= date('Y-m-d') ?>" class="block w-full text-base font-medium py-2.5 px-4 rounded-xl border-2 border-gray-100 focus:outline-none focus:ring-4 focus:ring-brand-DEFAULT/20 focus:border-brand-DEFAULT bg-gray-50 text-gray-900 transition-all">
                            </div>
                            
                            <div class="pt-2">
                                <button type="submit" class="w-full text-base font-bold py-3 rounded-xl shadow-md transition-all hover:-translate-y-0.5 hover:shadow-lg text-white bg-brand-DEFAULT hover:bg-brand-dark focus:outline-none focus:ring-4 focus:ring-brand-DEFAULT/40 flex justify-center items-center">
                                    <i data-lucide="bird" class="w-4 h-4 mr-2"></i> Save Batch
                                </button>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="bg-white/80 backdrop-blur-xl p-8 rounded-2xl text-center shadow-soft border border-white/50">
                        <i data-lucide="lock" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
                        <h3 class="text-base font-bold text-gray-900">Admin Access Required</h3>
                        <p class="text-gray-500 mt-1 text-sm">Only administrators can register new flocks.</p>
                    </div>
                <?php endif; ?>

            </div>
        </div>

    </div> <!-- Close Main Content wrap from header/sidebar -->
</body>
</html>
