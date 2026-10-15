<?php
require_once 'config.php';
requireLogin();
requireRole('admin'); // Only admins can access this page

$message = '';
$msgType = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_user') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (!empty($username) && !empty($password) && in_array($role, ['admin', 'worker'])) {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
            $stmt->execute([$username, $hash, $role]);
            
            $message = "User created successfully.";
            $msgType = "success";
        } catch (\PDOException $e) {
            // Check for duplicate entry error codes
            if ($e->getCode() == 23000) {
                $message = "Username already exists.";
                $msgType = "error";
            } else {
                $message = "Error creating user.";
                $msgType = "error";
            }
        }
    } else {
        $message = "Please fill in all fields correctly.";
        $msgType = "error";
    }
}

// Fetch all users
$stmtUsers = $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY created_at DESC");
$users = $stmtUsers->fetchAll();

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
            <div class="max-w-7xl mx-auto w-full px-4">
                
                <header class="mb-8 text-center">
                    <div class="inline-flex justify-center items-center w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-600 to-indigo-900 text-white shadow-soft mb-4">
                        <i data-lucide="users" class="w-7 h-7"></i>
                    </div>
                    <h2 class="text-3xl md:text-4xl font-extrabold bg-gradient-to-r from-emerald-800 to-teal-600 bg-clip-text text-transparent tracking-tight">User Management</h2>
                    <p class="text-gray-500 mt-2 text-xs font-bold tracking-wider uppercase">Add staff members & define roles</p>
                </header>

                <?php if ($message): ?>
                    <div class="mb-8 p-4 rounded-2xl bg-white shadow-soft border <?= $msgType === 'success' ? 'border-ui-green/30' : 'border-ui-red/30' ?> flex items-center justify-center text-center max-w-xl mx-auto">
                        <div class="<?= $msgType === 'success' ? 'bg-ui-green/10 text-ui-green' : 'bg-ui-red/10 text-ui-red' ?> p-2 rounded-xl mr-3">
                            <i data-lucide="<?= $msgType === 'success' ? 'check-circle' : 'alert-circle' ?>" class="w-5 h-5"></i>
                        </div>
                        <span class="font-bold text-gray-900 text-sm tracking-tight"><?= htmlspecialchars($message) ?></span>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- Add User Form -->
                    <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/50 p-7 h-fit lg:col-span-1 relative overflow-hidden group">
                        <div class="absolute -right-16 -top-16 w-64 h-64 bg-purple-500/5 rounded-full blur-3xl -z-10 group-hover:bg-purple-500/10 transition-colors duration-1000"></div>
                        
                        <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                             <div class="bg-purple-100 text-purple-600 p-2 rounded-xl mr-3">
                                <i data-lucide="user-plus" class="w-4 h-4"></i>
                             </div>
                             Register New User
                        </h3>
                        <form action="users.php" method="POST" class="space-y-5 relative z-10">
                            <input type="hidden" name="action" value="add_user">
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5 ml-0.5">Username</label>
                                <input type="text" name="username" required autocomplete="new-password" placeholder="e.g. jdoe_worker" class="block w-full px-4 py-2.5 border-2 border-gray-100 rounded-xl focus:outline-none focus:ring-4 focus:ring-purple-500/10 focus:border-purple-500 text-base bg-gray-50 text-gray-900 font-semibold transition-all">
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5 ml-0.5">Password</label>
                                <input type="password" name="password" required autocomplete="new-password" placeholder="••••••••" class="block w-full px-4 py-2.5 border-2 border-gray-100 rounded-xl focus:outline-none focus:ring-4 focus:ring-purple-500/10 focus:border-purple-500 text-base bg-gray-50 text-gray-900 font-semibold transition-all">
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5 ml-0.5">Assigned Role</label>
                                <div class="relative">
                                    <select name="role" required class="block w-full px-4 py-2.5 border-2 border-gray-100 rounded-xl focus:outline-none focus:ring-4 focus:ring-purple-500/10 focus:border-purple-500 text-base bg-gray-50 text-gray-900 font-semibold transition-all appearance-none cursor-pointer">
                                        <option value="worker">Standard Worker</option>
                                        <option value="admin">System Administrator</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400">
                                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="w-full flex justify-center items-center py-3 px-6 rounded-xl text-base font-bold text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-4 focus:ring-purple-500/30 transition-all shadow-md hover:shadow-lg hover:-translate-y-0.5">
                                <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> Create Access Credentials
                            </button>
                        </form>
                    </div>

                    <!-- Users Table -->
                    <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/50 overflow-hidden lg:col-span-2">
                        <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/30 flex justify-between items-center">
                            <h3 class="text-lg font-bold text-gray-900 flex items-center">
                                <i data-lucide="users" class="w-5 h-5 mr-3 text-gray-400"></i> Active Accounts
                            </h3>
                            <span class="text-[10px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full font-black uppercase tracking-widest"><?= count($users) ?> total</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-gray-50/50 border-b border-gray-100 text-gray-400 font-bold uppercase tracking-widest text-[10px]">
                                    <tr>
                                        <th class="px-8 py-4">Identity</th>
                                        <th class="px-8 py-4">Permissions</th>
                                        <th class="px-8 py-4">Registered</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    <?php foreach ($users as $u): ?>
                                        <tr class="hover:bg-emerald-50/30 transition-colors group">
                                            <td class="px-8 py-5">
                                                <div class="flex items-center">
                                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white font-black text-sm mr-4 shadow-sm group-hover:scale-110 transition-transform">
                                                        <?= strtoupper(substr($u['username'], 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-black text-gray-900"><?= htmlspecialchars($u['username']) ?></div>
                                                        <div class="text-[10px] text-gray-400 font-bold">ID: #<?= str_pad($u['id'], 3, '0', STR_PAD_LEFT) ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-8 py-5">
                                                <?php if ($u['role'] === 'admin'): ?>
                                                    <span class="inline-flex items-center px-3 py-1 text-[10px] font-black rounded-lg bg-emerald-100 text-emerald-700 border border-emerald-200 uppercase tracking-widest">
                                                        <i data-lucide="shield-check" class="w-3 h-3 mr-1.5"></i> Admin
                                                    </span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center px-3 py-1 text-[10px] font-black rounded-lg bg-blue-100 text-blue-700 border border-blue-200 uppercase tracking-widest">
                                                        <i data-lucide="user" class="w-3 h-3 mr-1.5"></i> Worker
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-8 py-5 font-bold text-gray-500 text-xs">
                                                <?= date('M d, Y', strtotime($u['created_at'])) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($users)): ?>
                                        <tr>
                                            <td colspan="3" class="px-8 py-16 text-center">
                                                <div class="flex flex-col items-center justify-center opacity-20">
                                                    <i data-lucide="ghost" class="w-16 h-16 mb-4"></i>
                                                    <p class="font-black text-xl tracking-tight">Vault is Empty</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div> <!-- Close Main Content wrap from header/sidebar -->
</body>
</html>
