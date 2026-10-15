<?php
require_once 'config.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Password is correct, set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pesez Farm Workspace</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            light: '#F8F9FA', // Soft gray/cream
                            warm: '#FAF3DD',  // Warm neutral
                            DEFAULT: '#2D6A4F', // Accent green
                            dark: '#1B4332',  // Deep mossy anchor
                        },
                        ui: {
                            red: '#E07A5F', // Softer coral for errors
                            green: '#40916C', // Success green
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-brand-light flex items-center justify-center min-h-screen font-sans antialiased overflow-hidden">
    
    <!-- The HCI Aurora Mesh Background -->
    <div class="fixed top-0 left-0 w-full h-full pointer-events-none -z-10 bg-slate-50 overflow-hidden">
        <div class="absolute -top-[10%] -left-[10%] w-[50vw] h-[50vw] bg-yellow-200/40 rounded-full blur-[100px] mix-blend-multiply opacity-70 animate-blob"></div>
        <div class="absolute top-[20%] right-[5%] w-[40vw] h-[40vw] bg-emerald-300/30 rounded-full blur-[100px] mix-blend-multiply opacity-70 animate-blob animation-delay-2000"></div>
        <div class="absolute bottom-[-10%] left-[20%] w-[45vw] h-[45vw] bg-sky-300/30 rounded-full blur-[100px] mix-blend-multiply opacity-70 animate-blob animation-delay-4000"></div>
    </div>

    <div class="bg-white/70 backdrop-blur-2xl p-10 rounded-3xl shadow-2xl border border-white/60 w-full max-w-md relative overflow-hidden group">
        <!-- Decoration -->
        <div class="absolute -right-16 -top-16 w-32 h-32 bg-brand-DEFAULT/10 rounded-full blur-2xl group-hover:bg-brand-DEFAULT/20 transition-colors"></div>
        
        <div class="text-center mb-8 relative z-10">
            <div class="inline-flex justify-center items-center w-16 h-16 rounded-2xl bg-gradient-to-br from-brand-DEFAULT to-brand-dark text-white mb-5 shadow-soft">
                <i data-lucide="leaf" class="w-8 h-8"></i>
            </div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Pesez Farm</h1>
            <p class="text-gray-500 text-sm mt-2 font-bold uppercase tracking-widest bg-gray-100/50 inline-block px-3 py-1 rounded-full">Secure Workspace</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-ui-red/10 border-l-4 border-ui-red text-ui-red p-4 mb-6 rounded-xl flex items-center animate-pulse" role="alert">
                <i data-lucide="alert-circle" class="w-5 h-5 mr-3 flex-shrink-0"></i>
                <p class="text-sm font-bold"><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>

        <form action="index.php" method="POST" class="space-y-6 relative z-10">
            <div>
                <label for="username" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1">Username</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                        <i data-lucide="user" class="w-4 h-4"></i>
                    </div>
                    <input type="text" name="username" id="username" placeholder="Enter your username" class="block w-full pl-11 pr-4 py-3 border-2 border-gray-100 rounded-xl focus:outline-none focus:ring-4 focus:ring-brand-DEFAULT/15 focus:border-brand-DEFAULT text-base bg-gray-50/50 text-gray-900 font-semibold transition-all" required autofocus>
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                    </div>
                    <input type="password" name="password" id="password" placeholder="••••••••" class="block w-full pl-11 pr-4 py-3 border-2 border-gray-100 rounded-xl focus:outline-none focus:ring-4 focus:ring-brand-DEFAULT/15 focus:border-brand-DEFAULT text-base bg-gray-50/50 text-gray-900 font-semibold transition-all" required>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full flex justify-center items-center py-3.5 px-6 rounded-xl text-base font-bold text-white bg-brand-DEFAULT hover:bg-brand-dark focus:outline-none focus:ring-4 focus:ring-brand-DEFAULT/40 transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                    Sign In to Dashboard
                </button>
            </div>
        </form>
        
        <div class="mt-8 pt-6 border-t border-gray-100 text-center text-[10px] text-gray-400 font-black uppercase tracking-widest">
            <p>Access Level: <span class="text-gray-900">Registered Staff Only</span></p>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
