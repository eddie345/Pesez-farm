<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesez Farm Poultry Management System</title>
    <!-- PWA Web Manifest -->
    <link rel="manifest" href="/hmm/manifest.json">
    <meta name="theme-color" content="#0A8754">
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            light: '#F4F7FA', // very soft cool gray for background
                            DEFAULT: '#0A8754', // Deep Emerald
                            dark: '#055F38', // Darker emerald
                            accent: '#FF6B35', // Sunrise Orange
                            sky: '#00B4D8', // Soft Sky Blue
                        },
                        ui: {
                            red: '#EF4444', 
                            green: '#10B981', 
                            yellow: '#F59E0B',
                            blue: '#3B82F6', 
                            purple: '#8B5CF6',
                            brown: '#78350F'
                        }
                    },
                    boxShadow: {
                        'soft': '0 20px 40px -15px rgba(0,0,0,0.05)',
                        'floating': '0 25px 50px -12px rgba(0,0,0,0.08)',
                    },
                    borderRadius: {
                        '3xl': '1.5rem',
                        '4xl': '2rem',
                    },
                    keyframes: {
                        blob: {
                            '0%': { transform: 'translate(0px, 0px) scale(1)' },
                            '33%': { transform: 'translate(30px, -50px) scale(1.1)' },
                            '66%': { transform: 'translate(-20px, 20px) scale(0.9)' },
                            '100%': { transform: 'translate(0px, 0px) scale(1)' },
                        }
                    },
                    animation: {
                        blob: "blob 7s infinite ease-in-out",
                    }
                }
            }
        }
    </script>
    <style>
        /* Custom Animation Delays */
        .animation-delay-2000 { animation-delay: 2s; }
        .animation-delay-4000 { animation-delay: 4s; }

        body {
            background-color: #F4F7FA;
            background-image: radial-gradient(circle at top right, rgba(10, 135, 84, 0.05), transparent 40%),
                              radial-gradient(circle at bottom left, rgba(255, 107, 53, 0.03), transparent 40%);
            background-attachment: fixed;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,0.2); }
    </style>
    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/hmm/service-worker.js', { scope: '/hmm/' })
                    .then(reg => console.log('[SW] Registered, scope:', reg.scope))
                    .catch(err => console.warn('[SW] Registration failed:', err));
            });
        }
    </script>
</head>
<body class="text-gray-800 font-sans min-h-screen flex flex-col md:flex-row antialiased selection:bg-brand-DEFAULT selection:text-white">
    
    <!-- Mobile Header -->
    <div class="md:hidden bg-white/80 backdrop-blur-xl border-b border-gray-100 text-gray-900 p-4 flex justify-between items-center z-50 sticky top-0">
        <div class="font-extrabold text-xl tracking-tight flex items-center text-brand-dark">
            <div class="bg-brand-DEFAULT/10 p-2 rounded-xl mr-3">
                <i data-lucide="leaf" class="w-5 h-5 text-brand-DEFAULT"></i>
            </div>
            Pesez Farm
        </div>
        <button id="mobile-menu-btn" class="focus:outline-none p-2 rounded-xl bg-gray-50 text-gray-600 hover:bg-gray-100 transition">
            <i data-lucide="menu" class="w-6 h-6"></i>
        </button>
    </div>

    <!-- Sidebar component will be included next -->
