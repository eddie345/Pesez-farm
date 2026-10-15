<?php
require_once 'config.php';
requireLogin();

$message = '';
$msgType = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'record_eggs') {
    $flock_id = (int)$_POST['flock_id'];
    $collection_date = $_POST['collection_date'];
    $quantity_good = (int)$_POST['quantity_good'];
    $quantity_damaged = (int)$_POST['quantity_damaged'];
    $recorded_by = $_SESSION['user_id'];

    if ($flock_id > 0 && !empty($collection_date) && ($quantity_good >= 0) && ($quantity_damaged >= 0)) {
        if ($quantity_good > 0 || $quantity_damaged > 0) {
            $stmt = $pdo->prepare("INSERT INTO egg_production (flock_id, collection_date, quantity_good, quantity_damaged, recorded_by) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$flock_id, $collection_date, $quantity_good, $quantity_damaged, $recorded_by])) {
                $message = "Egg collection recorded successfully. You can view it in System Logs.";
                $msgType = "success";
            } else {
                $message = "Error recording egg collection.";
                $msgType = "error";
            }
        } else {
            $message = "Please enter a quantity greater than 0.";
            $msgType = "error";
        }
    } else {
        $message = "Please fill in all details correctly.";
        $msgType = "error";
    }
}

// Fetch Active Layer Flocks for Dropdown
$stmtLayers = $pdo->query("SELECT id, batch_name FROM flocks WHERE bird_type = 'Layer' AND status = 'active' ORDER BY batch_name ASC");
$activeLayers = $stmtLayers->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

        <!-- The HCI Aurora Mesh Background -->
        <div class="fixed top-0 left-0 w-full h-full pointer-events-none -z-10 bg-slate-50 overflow-hidden">
            <div class="absolute -top-[10%] -left-[10%] w-[50vw] h-[50vw] bg-yellow-200/40 rounded-full blur-[100px] mix-blend-multiply opacity-70 animate-blob"></div>
            <div class="absolute top-[20%] right-[5%] w-[40vw] h-[40vw] bg-emerald-300/30 rounded-full blur-[100px] mix-blend-multiply opacity-70 animate-blob animation-delay-2000"></div>
            <div class="absolute bottom-[-10%] left-[20%] w-[45vw] h-[45vw] bg-sky-300/30 rounded-full blur-[100px] mix-blend-multiply opacity-70 animate-blob animation-delay-4000"></div>
        </div>

        <!-- Offline status banner -->
        <div id="offline-banner" class="hidden mb-4 px-5 py-3 rounded-2xl bg-amber-50 border border-amber-200 flex items-center text-amber-700 text-sm font-bold shadow-sm">
            <i data-lucide="wifi-off" class="w-4 h-4 mr-2 shrink-0"></i>
            <span>You're offline. Your submission will be saved locally and synced when connectivity returns.</span>
        </div>

        <!-- Sync success banner (shown after offline payload restored) -->
        <div id="sync-banner" class="hidden mb-4 px-5 py-3 rounded-2xl bg-sky-50 border border-sky-200 flex items-center text-sky-700 text-sm font-bold shadow-sm">
            <i data-lucide="cloud-upload" class="w-4 h-4 mr-2 shrink-0"></i>
            <span id="sync-banner-msg">Offline entry detected and re-submitted for processing.</span>
        </div>

        <div class="w-full flex justify-center items-start pt-6 pb-12">
            <div class="max-w-lg mx-auto w-full px-4">
                
                <header class="mb-8 text-center">
                    <div class="inline-flex justify-center items-center w-14 h-14 rounded-2xl bg-gradient-to-br from-brand-DEFAULT to-brand-dark text-white shadow-soft mb-4">
                        <i data-lucide="egg" class="w-7 h-7"></i>
                    </div>
                    <h2 class="text-2xl md:text-3xl font-extrabold bg-gradient-to-r from-emerald-800 to-teal-600 bg-clip-text text-transparent tracking-tight">Record Harvest</h2>
                    <p class="text-gray-500 mt-2 text-xs font-bold tracking-wider uppercase">Egg Production Processing Form</p>
                </header>

                <?php if ($message): ?>
                    <div class="mb-6 p-4 rounded-2xl flex flex-col items-center justify-center text-center bg-white shadow-soft border <?= $msgType === 'success' ? 'border-ui-green/30' : 'border-ui-red/30' ?>">
                        <div class="<?= $msgType === 'success' ? 'bg-ui-green/10 text-ui-green' : 'bg-ui-red/10 text-ui-red' ?> p-2.5 rounded-xl mb-3">
                            <i data-lucide="<?= $msgType === 'success' ? 'check-circle-2' : 'alert-octagon' ?>" class="w-6 h-6"></i>
                        </div>
                        <span class="font-bold text-gray-900 text-sm tracking-tight"><?= htmlspecialchars($message) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Compact Centered Form Container -->
                <div class="bg-white/80 backdrop-blur-xl p-6 md:p-8 rounded-2xl shadow-2xl border border-white/50 relative overflow-hidden group">
                    <div class="absolute -right-24 -top-24 w-96 h-96 bg-brand-DEFAULT/5 rounded-full blur-3xl -z-10 group-hover:bg-brand-DEFAULT/10 transition-colors duration-1000"></div>
                    
                    <form id="egg-form" action="eggs.php" method="POST" class="space-y-5 relative z-10">
                        <input type="hidden" name="action" value="record_eggs">
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5 ml-0.5">Target Flock Batch</label>
                            <div class="relative">
                                <select id="flock_id" name="flock_id" required class="block w-full text-base font-medium py-2.5 px-4 rounded-xl border-2 border-gray-100 focus:outline-none focus:ring-4 focus:ring-brand-DEFAULT/20 focus:border-brand-DEFAULT bg-gray-50 text-gray-900 transition-all appearance-none cursor-pointer">
                                    <option value="" disabled selected class="text-gray-400 font-medium">-- Select a flock --</option>
                                    <?php foreach ($activeLayers as $layer): ?>
                                        <option value="<?= $layer['id'] ?>" class="font-bold"><?= htmlspecialchars($layer['batch_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400">
                                    <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5 ml-0.5">Date of Collection</label>
                            <input type="date" id="collection_date" name="collection_date" required value="<?= date('Y-m-d') ?>" class="block w-full text-base font-medium py-2.5 px-4 rounded-xl border-2 border-gray-100 focus:outline-none focus:ring-4 focus:ring-brand-DEFAULT/20 focus:border-brand-DEFAULT bg-gray-50 text-gray-900 transition-all">
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-2">
                            
                            <!-- Digital Counter: Good Eggs -->
                            <div>
                                <label class="block text-sm font-extrabold text-center uppercase tracking-wider text-ui-green mb-2">Good Eggs</label>
                                <div class="relative">
                                    <input type="number" id="quantity_good" name="quantity_good" required min="0" value="0" class="block w-full text-3xl md:text-4xl font-black text-center py-4 rounded-2xl text-ui-green bg-ui-green/5 border-2 border-ui-green/30 focus:border-ui-green focus:ring-4 focus:ring-ui-green/20 transition-all placeholder-ui-green/30 focus:outline-none">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-ui-green/40">
                                        <i data-lucide="check" class="w-5 h-5"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Digital Counter: Damaged Eggs -->
                            <div>
                                <label class="block text-sm font-extrabold text-center uppercase tracking-wider text-ui-red mb-2">Damaged / Rejected</label>
                                <div class="relative">
                                    <input type="number" id="quantity_damaged" name="quantity_damaged" required min="0" value="0" class="block w-full text-3xl md:text-4xl font-black text-center py-4 rounded-2xl text-ui-red bg-ui-red/5 border-2 border-ui-red/30 focus:border-ui-red focus:ring-4 focus:ring-ui-red/20 transition-all placeholder-ui-red/30 focus:outline-none">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-ui-red/40">
                                        <i data-lucide="x" class="w-5 h-5"></i>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Live Total Preview -->
                        <div id="total-preview" class="pt-2 text-center text-sm font-bold text-gray-400 tracking-wide uppercase transition-all">
                            Total: <span id="total-count" class="text-gray-700 font-black">0</span> eggs &mdash; Ensure counts are exact before submitting.
                        </div>

                        <div>
                            <button type="submit" id="submit-btn" class="w-full text-base font-bold py-3 rounded-xl shadow-md transition-all hover:-translate-y-0.5 hover:shadow-lg text-white bg-brand-DEFAULT hover:bg-brand-dark focus:outline-none focus:ring-4 focus:ring-brand-DEFAULT/40 flex justify-center items-center">
                                <i data-lucide="save" class="w-4 h-4 mr-2"></i> Save Collection
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>

    </div> <!-- Close Main Content wrap from header/sidebar -->

<script>
// ─── Live total preview counter ────────────────────────────────────────────
const goodInput    = document.getElementById('quantity_good');
const damagedInput = document.getElementById('quantity_damaged');
const totalCount   = document.getElementById('total-count');

function updateTotal() {
    const g = parseInt(goodInput.value) || 0;
    const d = parseInt(damagedInput.value) || 0;
    totalCount.textContent = (g + d).toLocaleString();
}

goodInput.addEventListener('input', updateTotal);
damagedInput.addEventListener('input', updateTotal);

// ─── Offline-aware form submission ─────────────────────────────────────────
const OFFLINE_KEY   = 'pesez_eggs_offline_payload';
const form          = document.getElementById('egg-form');
const offlineBanner = document.getElementById('offline-banner');
const syncBanner    = document.getElementById('sync-banner');
const syncMsg       = document.getElementById('sync-banner-msg');

// Check connection and show banner
function updateConnectionUI() {
    if (!navigator.onLine) {
        offlineBanner.classList.remove('hidden');
    } else {
        offlineBanner.classList.add('hidden');
    }
}
updateConnectionUI();
window.addEventListener('online',  updateConnectionUI);
window.addEventListener('offline', updateConnectionUI);

// Intercept form submit when offline
form.addEventListener('submit', function(e) {
    if (!navigator.onLine) {
        e.preventDefault(); // Stop normal POST

        // Collect form data into a plain object
        const payload = {
            action:           'record_eggs',
            flock_id:         document.getElementById('flock_id').value,
            collection_date:  document.getElementById('collection_date').value,
            quantity_good:    document.getElementById('quantity_good').value,
            quantity_damaged: document.getElementById('quantity_damaged').value,
            savedAt:          new Date().toISOString()
        };

        localStorage.setItem(OFFLINE_KEY, JSON.stringify(payload));
        offlineBanner.classList.remove('hidden');
        offlineBanner.innerHTML = '<i data-lucide="save" class="w-4 h-4 mr-2 shrink-0"></i><span>Offline: entry saved locally. It will auto-submit when you reconnect.</span>';
        if (window.lucide) lucide.createIcons();
        return;
    }
});

// On reconnect: replay any stored offline payloads
window.addEventListener('online', function() {
    const raw = localStorage.getItem(OFFLINE_KEY);
    if (!raw) return;

    try {
        const payload = JSON.parse(raw);
        const body    = new URLSearchParams(payload);

        fetch('eggs.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body })
            .then(res => {
                if (res.ok) {
                    localStorage.removeItem(OFFLINE_KEY);
                    syncMsg.textContent = `Offline entry from ${payload.savedAt ? new Date(payload.savedAt).toLocaleString() : 'earlier'} was synced successfully.`;
                    syncBanner.classList.remove('hidden');
                    setTimeout(() => syncBanner.classList.add('hidden'), 6000);
                }
            })
            .catch(() => { /* will retry on next online event */ });
    } catch (_) {
        localStorage.removeItem(OFFLINE_KEY); // Corrupt payload — discard
    }
});
</script>
</body>
</html>
