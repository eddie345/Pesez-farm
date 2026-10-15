    <!-- Sidebar Navigation -->
    <nav id="sidebar" class="w-full md:w-72 flex-shrink-0 hidden md:flex flex-col min-h-screen relative z-40 transition-all duration-300">
        <!-- Floating container on desktop -->
        <div class="md:fixed md:top-6 md:bottom-6 md:left-6 md:w-[256px] bg-white/60 backdrop-blur-2xl border border-white/60 shadow-floating rounded-4xl flex flex-col overflow-hidden">
            
            <!-- Logo Area -->
            <div class="px-5 py-5 hidden md:flex items-center border-b border-white/40">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-DEFAULT to-brand-dark flex items-center justify-center mr-3 shadow-soft flex-shrink-0">
                    <i data-lucide="leaf" class="text-white w-[18px] h-[18px]"></i>
                </div>
                <div>
                    <h1 class="text-base font-extrabold tracking-tight text-gray-900 leading-tight">Pesez Farm</h1>
                    <p class="text-[10px] uppercase tracking-wider text-brand-DEFAULT font-bold bg-brand-DEFAULT/10 inline-block px-2 py-0.5 rounded-full mt-0.5">Workspace</p>
                </div>
            </div>
            
            <!-- Nav Links -->
            <div class="flex-1 py-3 px-3 overflow-y-auto space-y-0.5 custom-scrollbar">
                <?php
                    $currentPage = basename($_SERVER['PHP_SELF']);
                    $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

                    $activeLink   = 'flex items-center min-h-[40px] pl-2.5 pr-3 py-2 rounded-xl text-sm font-bold transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] border-l-4 border-emerald-500 bg-gradient-to-r from-emerald-500/15 to-transparent text-emerald-800';
                    $inactiveLink = 'flex items-center min-h-[40px] pl-3 pr-3 py-2 rounded-xl text-sm font-semibold transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] border-l-4 border-transparent text-gray-500 hover:bg-gradient-to-r hover:from-emerald-500/10 hover:to-transparent hover:text-emerald-700 hover:translate-x-1.5 group';
                    $activeIcon   = 'mr-2.5 w-[18px] h-[18px] flex-shrink-0 text-emerald-600';
                    $inactiveIcon = 'mr-2.5 w-[18px] h-[18px] flex-shrink-0 text-gray-400 group-hover:text-emerald-600 transition-colors duration-300';
                ?>
                
                <!-- FARM OPERATIONS Section -->
                <p class="text-[10px] font-bold uppercase text-gray-400 tracking-wider px-3 pt-2 pb-1.5">Farm Operations</p>
                
                <a href="dashboard.php" class="<?= $currentPage == 'dashboard.php' ? $activeLink : $inactiveLink ?>">
                    <i data-lucide="layout-dashboard" class="<?= $currentPage == 'dashboard.php' ? $activeIcon : $inactiveIcon ?>"></i> Dashboard
                </a>
                
                <a href="flocks.php" class="<?= $currentPage == 'flocks.php' ? $activeLink : $inactiveLink ?>">
                    <i data-lucide="bird" class="<?= $currentPage == 'flocks.php' ? $activeIcon : $inactiveIcon ?>"></i> Flocks & Batches
                </a>
                
                <a href="eggs.php" class="<?= $currentPage == 'eggs.php' ? $activeLink : $inactiveLink ?>">
                    <i data-lucide="egg" class="<?= $currentPage == 'eggs.php' ? $activeIcon : $inactiveIcon ?>"></i> Egg Production
                </a>
                
                <a href="feed.php" class="<?= $currentPage == 'feed.php' ? $activeLink : $inactiveLink ?>">
                    <i data-lucide="wheat" class="<?= $currentPage == 'feed.php' ? $activeIcon : $inactiveIcon ?>"></i> Feed Inventory
                </a>
                
                <a href="health.php" class="<?= $currentPage == 'health.php' ? $activeLink : $inactiveLink ?>">
                    <i data-lucide="heart-pulse" class="<?= $currentPage == 'health.php' ? $activeIcon : $inactiveIcon ?>"></i> Health Records
                </a>
                
                <a href="mortality.php" class="<?= $currentPage == 'mortality.php' ? $activeLink : $inactiveLink ?>">
                    <i data-lucide="ghost" class="<?= $currentPage == 'mortality.php' ? $activeIcon : $inactiveIcon ?>"></i> Mortality Logs
                </a>
                
                <!-- MANAGEMENT Section -->
                <p class="text-[10px] font-bold uppercase text-gray-400 tracking-wider px-3 pt-4 pb-1.5">Management</p>
                
                <a href="reports.php" class="<?= $currentPage == 'reports.php' ? $activeLink : $inactiveLink ?>">
                    <i data-lucide="bar-chart-3" class="<?= $currentPage == 'reports.php' ? $activeIcon : $inactiveIcon ?>"></i> Analytics & Reports
                </a>
                
                <?php if ($isAdmin): ?>
                <a href="profit.php" class="<?= $currentPage == 'profit.php' ? $activeLink : $inactiveLink ?>">
                    <i data-lucide="calculator" class="<?= $currentPage == 'profit.php' ? $activeIcon : $inactiveIcon ?>"></i> Profit Calculator
                </a>
                <?php endif; ?>
                
                <a href="logs.php" class="<?= $currentPage == 'logs.php' ? $activeLink : $inactiveLink ?>">
                    <i data-lucide="database" class="<?= $currentPage == 'logs.php' ? $activeIcon : $inactiveIcon ?>"></i> System Logs
                </a>
                
                <?php if ($isAdmin): ?>
                <!-- ADMIN Section -->
                <p class="text-[10px] font-bold uppercase text-gray-400 tracking-wider px-3 pt-4 pb-1.5">Administration</p>
                <a href="users.php" class="<?= $currentPage == 'users.php' ? $activeLink : $inactiveLink ?>">
                    <i data-lucide="users" class="<?= $currentPage == 'users.php' ? $activeIcon : $inactiveIcon ?>"></i> Manage Staff
                </a>
                <?php endif; ?>
            </div>
            
            <!-- User Profile + Logout -->
            <div class="p-4 border-t border-white/40 bg-white/30 backdrop-blur-md">
                <div class="flex items-center mb-3 px-1">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-r from-emerald-400 to-teal-500 flex items-center justify-center text-white font-extrabold text-sm mr-2.5 shadow-sm flex-shrink-0">
                        <?= isset($_SESSION['username']) ? strtoupper(substr($_SESSION['username'], 0, 1)) : 'U' ?>
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-sm font-bold text-gray-900 truncate"><?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User' ?></div>
                        <div class="text-[11px] font-semibold text-brand-DEFAULT capitalize flex items-center mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full <?= isset($_SESSION['role']) && $_SESSION['role'] === 'admin' ? 'bg-brand-accent' : 'bg-brand-DEFAULT' ?> mr-1.5 flex-shrink-0"></span>
                            <?= isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role']) : '' ?>
                        </div>
                    </div>
                </div>
                <a href="logout.php" class="flex items-center justify-center w-full py-2 px-4 rounded-xl border border-gray-200 bg-white text-gray-500 hover:bg-red-50 hover:text-ui-red hover:border-red-200 transition-all duration-300 text-xs font-bold group shadow-sm min-h-[36px]">
                    <i data-lucide="log-out" class="w-3.5 h-3.5 mr-1.5 group-hover:-translate-x-0.5 transition-transform duration-300"></i>
                    Sign Out
                </a>
            </div>
        </div>
    </nav>

    <script>
        // Init Lucide Icons globally
        lucide.createIcons();

        // Mobile Sidebar Toggle
        const btn = document.getElementById('mobile-menu-btn');
        const sidebar = document.getElementById('sidebar');

        if(btn) {
            btn.addEventListener('click', () => {
                sidebar.classList.toggle('hidden');
                if (!sidebar.classList.contains('hidden')) {
                    sidebar.classList.add('absolute', 'w-full', 'bg-white', 'z-50', 'inset-0');
                    sidebar.querySelector('div').classList.remove('md:fixed', 'md:w-[256px]', 'rounded-4xl', 'md:top-6', 'md:bottom-6', 'md:left-6');
                }
            });
        }
    </script>
    
    <!-- Main Content Wrapper -->
    <div class="flex-1 w-full min-h-screen relative z-10 p-4 md:p-8 md:pl-0 lg:p-10 lg:pl-0">
