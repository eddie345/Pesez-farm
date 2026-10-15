<?php
require_once 'config.php';
requireLogin();

$message = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'log_health') {
    $flock_id = (int)$_POST['flock_id'];
    $record_date = $_POST['record_date'];
    $type = $_POST['type']; // Vaccination or Treatment
    $medication_name = trim($_POST['medication_name']);
    $notes = trim($_POST['notes']);
    $next_due_date = !empty($_POST['next_due_date']) ? $_POST['next_due_date'] : null;

    if ($flock_id > 0 && !empty($record_date) && !empty($type) && !empty($medication_name)) {
        $stmt = $pdo->prepare("INSERT INTO health_records (flock_id, record_date, type, medication_name, notes, next_due_date) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$flock_id, $record_date, $type, $medication_name, $notes, $next_due_date])) {
            $message = "Health record added successfully. You can view the full history in System Logs.";
            $msgType = "success";
        } else {
            $message = "Error saving health record.";
            $msgType = "error";
        }
    } else {
        $message = "Please fill in all required fields.";
        $msgType = "error";
    }
}

// Fetch Active Flocks
$stmtFlocks = $pdo->query("SELECT id, batch_name FROM flocks WHERE status = 'active' ORDER BY batch_name");
$activeFlocks = $stmtFlocks->fetchAll();

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
                    <div class="inline-flex justify-center items-center w-14 h-14 rounded-2xl bg-gradient-to-br from-ui-blue to-blue-900 text-white shadow-soft mb-4">
                        <i data-lucide="heart-pulse" class="w-7 h-7"></i>
                    </div>
                    <h2 class="text-2xl md:text-3xl font-extrabold bg-gradient-to-r from-emerald-800 to-teal-600 bg-clip-text text-transparent tracking-tight">Health Operations</h2>
                    <p class="text-gray-500 mt-2 text-xs font-bold tracking-wider uppercase">Medical & Vaccination Logging</p>
                </header>

                <?php if ($message): ?>
                    <div class="mb-6 p-4 rounded-2xl flex flex-col items-center justify-center text-center bg-white shadow-soft border <?= $msgType === 'success' ? 'border-ui-green/30' : 'border-ui-red/30' ?>">
                        <div class="<?= $msgType === 'success' ? 'bg-ui-green/10 text-ui-green' : 'bg-ui-red/10 text-ui-red' ?> p-2.5 rounded-xl mb-3">
                            <i data-lucide="<?= $msgType === 'success' ? 'check-circle-2' : 'alert-octagon' ?>" class="w-6 h-6"></i>
                        </div>
                        <span class="font-bold text-gray-900 text-sm tracking-tight"><?= htmlspecialchars($message) ?></span>
                        <?php if ($msgType === 'success'): ?>
                            <a href="logs.php" class="mt-4 text-base font-bold text-brand-sky hover:text-blue-600 transition-colors flex items-center">
                                View System Logs <i data-lucide="arrow-right" class="w-5 h-5 ml-1.5"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Compact Centered Form Container -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/50 p-6 md:p-8 relative overflow-hidden group">
                    <div class="absolute -right-24 -top-24 w-96 h-96 bg-ui-blue/5 rounded-full blur-3xl -z-10 group-hover:bg-ui-blue/10 transition-colors duration-1000"></div>
                    
                    <form action="health.php" method="POST" class="space-y-4 relative z-10">
                        <input type="hidden" name="action" value="log_health">
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5 ml-0.5">Target Flock</label>
                            <div class="relative">
                                <select name="flock_id" required class="block w-full px-4 py-2.5 border-2 border-gray-100 rounded-xl focus:outline-none focus:ring-4 focus:ring-ui-blue/20 focus:border-ui-blue text-base bg-gray-50 text-gray-900 font-semibold transition-all appearance-none cursor-pointer">
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
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5 ml-0.5">Date Administered</label>
                                <input type="date" name="record_date" required value="<?= date('Y-m-d') ?>" class="block w-full px-4 py-2.5 border-2 border-gray-100 rounded-xl focus:outline-none focus:ring-4 focus:ring-ui-blue/20 focus:border-ui-blue text-base bg-gray-50 text-gray-900 font-semibold transition-all">
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5 ml-0.5">Operation Type</label>
                                <div class="relative">
                                    <select name="type" required class="block w-full px-4 py-2.5 border-2 border-gray-100 rounded-xl focus:outline-none focus:ring-4 focus:ring-ui-blue/20 focus:border-ui-blue text-base bg-gray-50 text-gray-900 font-semibold transition-all appearance-none cursor-pointer">
                                        <option value="Vaccination" class="font-bold">Vaccination</option>
                                        <option value="Treatment" class="font-bold">Treatment / Medication</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400">
                                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5 ml-0.5">Medication / Vaccine Name</label>
                            <input type="text" name="medication_name" placeholder="E.g. Newcastle Disease Vaccine" required class="block w-full px-4 py-2.5 border-2 border-ui-blue/20 rounded-xl focus:outline-none focus:ring-4 focus:ring-ui-blue/30 focus:border-ui-blue text-base bg-ui-blue/5 text-ui-blue font-semibold transition-all placeholder-ui-blue/40">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5 ml-0.5 flex items-center justify-between">
                                Next Due Date 
                                <span class="bg-gray-100 text-gray-500 text-[10px] px-2 py-0.5 rounded-full uppercase tracking-wider font-extrabold">Optional</span>
                            </label>
                            <input type="date" name="next_due_date" class="block w-full px-4 py-2.5 border-2 border-gray-100 rounded-xl focus:outline-none focus:ring-4 focus:ring-ui-yellow/20 focus:border-ui-yellow text-base bg-gray-50 text-gray-900 font-semibold transition-all">
                            <p class="text-xs text-gray-400 font-bold mt-1.5 ml-0.5 tracking-wide">Leave blank if this is a one-time treatment.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5 ml-0.5 flex items-center justify-between">
                                Clinical Notes
                                <span class="bg-gray-100 text-gray-500 text-[10px] px-2 py-0.5 rounded-full uppercase tracking-wider font-extrabold">Optional</span>
                            </label>
                            <textarea name="notes" rows="3" placeholder="Symptoms, dosages, or general observations..." class="block w-full px-4 py-2.5 border-2 border-gray-100 rounded-xl focus:outline-none focus:ring-4 focus:ring-ui-blue/20 focus:border-ui-blue text-base bg-gray-50 text-gray-900 font-medium transition-all resize-none"></textarea>
                        </div>
                        
                        <div class="pt-2 border-t border-gray-100">
                            <button type="submit" class="w-full flex justify-center items-center py-3 px-6 rounded-xl text-base font-bold text-white bg-ui-blue hover:bg-blue-600 focus:outline-none focus:ring-4 focus:ring-ui-blue/30 transition-all shadow-md hover:shadow-lg hover:-translate-y-0.5">
                                <i data-lucide="shield-plus" class="w-4 h-4 mr-2"></i> Secure Medical Record
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>

    </div> <!-- Close Main Content wrap from header/sidebar -->
</body>
</html>
