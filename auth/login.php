<?php
require_once '../components/config/db.php';
require_once '../components/toast.php'; // Include the toast function
require_once '../components/flash.php'; // Include flash messages

// Redirect if already logged in
if (!empty($_SESSION['logged_in'])) {
    header("Location: ../index.php");
    exit();
}

$messages = [
    'empty' => 'Please fill in all fields',
    'auth' => 'Invalid email or password',
    'db' => 'Database error occurred',
    'logout' => 'You have been logged out successfully',
    'login' => 'You have successfully logged in',
];

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';

// Show error toast if exists
if ($error && isset($messages[$error])) {
    showToast($messages[$error], 'error');
}

// Show success toast if exists
if ($success && isset($messages[$success])) {
    showToast($messages[$success], 'success');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Asset Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        * {
            font-family: 'Inter', sans-serif;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #0345e4 0%, #026af2 100%);
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .floating-label {
            transition: all 0.3s ease;
        }

        .input-group input:focus+.floating-label,
        .input-group input:not(:placeholder-shown)+.floating-label {
            transform: translateY(-43px) scale(0.85);
            color: white;
            font-weight: 500;
        }

        .input-animated {
            transition: all 0.3s ease;
        }

        .input-animated:focus {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.2);
        }

        .social-btn {
            transition: all 0.3s ease;
        }

        .social-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        .notification-badge {
            animation: pulse-glow 2s infinite;
        }

        @keyframes pulse-glow {

            0%,
            100% {
                transform: scale(1);
                box-shadow: 0 0 5px rgba(34, 197, 94, 0.5);
            }

            50% {
                transform: scale(1.1);
                box-shadow: 0 0 15px rgba(34, 197, 94, 0.8);
            }
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .animate-pulse-custom {
            animation: pulse-custom 4s ease-in-out infinite;
        }

        @keyframes pulse-custom {

            0%,
            100% {
                opacity: 0.3;
            }

            50% {
                opacity: 0.6;
            }
        }
    </style>
</head>

<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <!-- Animated background elements -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-white opacity-10 rounded-full animate-float"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-white opacity-5 rounded-full animate-pulse-custom"></div>
        <div class="absolute top-1/2 left-1/4 w-64 h-64 bg-white opacity-5 rounded-full animate-float" style="animation-delay: 2s;"></div>
    </div>

    <div class="glass-effect rounded-3xl shadow-2xl p-8 w-full max-w-md relative z-10">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="relative">
                <div class="w-20 h-20 bg-gradient-to-br from-[#0345e4] via-[#026af2] to-[#00279c] rounded-2xl mx-auto mb-4 flex items-center justify-center shadow-lg">
                    <i class="fas fa-cube text-white text-2xl lg:text-3xl"></i>
                </div>
                <div class="absolute -top-1 right-36 w-3 h-3 lg:w-4 lg:h-4 bg-green-400 rounded-full border-2 border-slate-800 notification-badge"></div>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Asset Management</h1>
            <p class="text-blue-100">Welcome back! Please sign in to continue</p>
        </div>

        <form action="authentication.php" method="post" class="space-y-10">
            <input type="hidden" name="csrf_token">

            <!-- Email Input -->
            <div class="input-group relative">
                <input type="email" id="email" name="email" required
                    placeholder=" "
                    class="input-animated w-full px-4 py-4 bg-white/20 border border-white/30 rounded-2xl focus:ring-2 focus:ring-blue-400 focus:border-transparent text-white placeholder-transparent backdrop-blur-sm">
                <label for="email" class="floating-label absolute left-4 top-4 text-blue-100 pointer-events-none">
                    <i class="fas fa-envelope mr-2"></i>Email Address
                </label>
            </div>

            <!-- Password Input -->
            <div class="input-group relative">
                <input type="password" id="password" name="password" required
                    placeholder=" "
                    class="input-animated w-full px-4 py-4 bg-white/20 border border-white/30 rounded-2xl focus:ring-2 focus:ring-blue-400 focus:border-transparent text-white placeholder-transparent backdrop-blur-sm">
                <label for="password" class="floating-label absolute left-4 top-4 text-blue-100 pointer-events-none">
                    <i class="fas fa-lock mr-2"></i>Password
                </label>
                <button type="button" class="absolute right-4 top-4 text-blue-100 hover:text-white transition-colors">
                    <i class="fas fa-eye"></i>
                </button>
            </div>

            <!-- Sign in button -->
            <button type="submit"
                class="bg-gradient-to-br from-[#0345e4] via-[#026af2] to-[#00279c] w-full py-4 px-6 rounded-2xl text-white font-semibold text-lg shadow-lg">
                <i class="fas fa-sign-in-alt mr-2"></i>Sign In
            </button>
        </form>

        <?php showToast(); ?>

        <script>
            // Password visibility toggle
            document.querySelector('button[type="button"]').addEventListener('click', function() {
                const passwordInput = document.getElementById('password');
                const icon = this.querySelector('i');

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        </script>
</body>

</html>