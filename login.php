<?php
/**
 * Login Page
 * Admin authentication
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/User.php';

$userModel = new User($pdo);
$error = '';
$success = '';

// Check if already logged in
if (isAuthenticated()) {
    header('Location: ' . APP_URL . '/index.php');
    exit;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validate input
    if (empty($username) || empty($password)) {
        $error = 'Username and password are required';
    }
    else {
        // Authenticate user
        $user = $userModel->authenticate($username, $password);

        if ($user) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'full_name' => $user['full_name']
            ];

            // Redirect to dashboard
            header('Location: ' . APP_URL . '/index.php');
            exit;
        }
        else {
            $error = 'Invalid username or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gym Management System</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #ff4d4d;
            --primary-hover: #e60000;
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --text-color: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=2070&auto=format&fit=crop') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            position: relative;
            overflow: hidden;
        }

        /* Overlay */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.4) 100%);
            z-index: 1;
        }
        
        .login-container {
            position: relative;
            z-index: 2;
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: 24px;
            border: 1px solid var(--glass-border);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            width: 90%;
            max-width: 420px;
            padding: 40px;
            text-align: center;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .login-header {
            margin-bottom: 35px;
        }
        
        .login-header h1 {
            color: #fff;
            font-family: 'Poppins', sans-serif;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
            letter-spacing: -0.5px;
        }
        
        .login-header p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 15px;
        }
        
        .login-icon img {
            max-height: 80px;
            margin-bottom: 20px;
            filter: drop-shadow(0 0 10px rgba(255, 255, 255, 0.3));
        }
        
        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }

        .form-group label {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 8px;
            display: block;
            margin-left: 5px;
        }
        
        .input-group {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            border: 1px solid var(--glass-border);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .input-group:focus-within {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(255, 77, 77, 0.15);
            background: rgba(255, 255, 255, 0.1);
        }
        
        .input-group-text {
            background: transparent;
            border: none;
            color: rgba(255, 255, 255, 0.5);
            padding-left: 15px;
        }
        
        .form-control {
            background: transparent;
            border: none;
            color: white;
            padding: 12px 15px;
            font-size: 15px;
        }

        .form-control:focus {
            background: transparent;
            box-shadow: none;
            color: white;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }
        
        .btn-login {
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-size: 16px;
            font-weight: 700;
            width: 100%;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn-login:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -10px rgba(255, 77, 77, 0.5);
            color: white;
        }

        .btn-login:active {
            transform: translateY(0);
        }
        
        .alert {
            background: rgba(220, 53, 69, 0.2);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #ff9999;
            border-radius: 12px;
            font-size: 14px;
            margin-bottom: 25px;
        }
        
        .demo-creds {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 15px;
            margin-top: 30px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
            border: 1px solid var(--glass-border);
        }
        
        .demo-creds strong {
            color: var(--primary-color);
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-icon">
                <img src="<?php echo APP_URL; ?>/assets/images/logo.png" alt="Logo" class="img-fluid" onerror="this.src='https://cdn-icons-png.flaticon.com/512/2964/2964514.png'">
            </div>
            <h1><?php echo APP_NAME; ?></h1>
            <p>Welcome back, Admin</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
            </div>
        <?php
endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username / Email</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Enter username or email" required autofocus>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
                </div>
            </div>
            
            <button type="submit" class="btn btn-login">
                Login <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </form>
        
        <!-- <div class="demo-creds">
            <strong><i class="fas fa-info-circle me-1"></i> Demo Access</strong><br>
            User: <strong>admin</strong> | Pass: <strong>admin123</strong>
        </div>
    </div> -->
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
