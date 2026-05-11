
<?php
session_start();

// Already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

require_once '../config/db.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ? LIMIT 1");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin  = $result->fetch_assoc();
        $stmt->close();

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id']        = $admin['id'];
            $_SESSION['admin_username']  = $admin['username'];

            // Set remember-me cookie
            if (!empty($_POST['remember'])) {
                $token = bin2hex(random_bytes(32));
                setcookie('admin_token', $token, time() + 86400 * 30, '/', '', false, true);
            }

            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
            // Simulate constant time to prevent timing attacks
            password_verify('dummy', '$2y$12$invalid.hash.padding.for.time');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Login | Mariem Mrabet</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #0f0f1a 0%, #1a1a35 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1rem;
    }
    .login-card {
      background: #1e1e35;
      border: 1px solid #2d2d4e;
      border-radius: 24px;
      padding: 3rem 2.5rem;
      width: 100%;
      max-width: 420px;
      box-shadow: 0 32px 64px rgba(0,0,0,.5);
    }
    .login-logo {
      text-align: center;
      font-size: 2rem;
      font-weight: 700;
      color: #6c63ff;
      margin-bottom: .5rem;
    }
    .login-logo span { color: #f72585; }
    .login-subtitle {
      text-align: center;
      color: #94a3b8;
      font-size: .9rem;
      margin-bottom: 2rem;
    }
    .form-group { margin-bottom: 1.25rem; }
    .form-group label {
      display: block;
      font-size: .85rem;
      font-weight: 600;
      color: #e2e8f0;
      margin-bottom: .4rem;
    }
    .input-wrapper { position: relative; }
    .input-wrapper i {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: #64748b;
      font-size: .9rem;
    }
    .form-group input {
      width: 100%;
      padding: .8rem 1rem .8rem 2.75rem;
      background: #16162a;
      border: 1.5px solid #2d2d4e;
      border-radius: 12px;
      color: #e2e8f0;
      font-size: .95rem;
      font-family: 'Poppins', sans-serif;
      transition: border-color .2s;
    }
    .form-group input:focus {
      outline: none;
      border-color: #6c63ff;
      box-shadow: 0 0 0 3px rgba(108,99,255,.15);
    }
    .toggle-pass {
      position: absolute;
      right: 1rem;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: #64748b;
      cursor: pointer;
      font-size: .9rem;
      transition: color .2s;
    }
    .toggle-pass:hover { color: #6c63ff; }
    .remember-row {
      display: flex;
      align-items: center;
      gap: .6rem;
      margin-bottom: 1.5rem;
    }
    .remember-row label {
      font-size: .85rem;
      color: #94a3b8;
      cursor: pointer;
    }
    .remember-row input[type="checkbox"] { accent-color: #6c63ff; cursor: pointer; }
    .btn-login {
      width: 100%;
      padding: .9rem;
      background: linear-gradient(135deg, #6c63ff, #f72585);
      color: #fff;
      border: none;
      border-radius: 12px;
      font-size: 1rem;
      font-weight: 600;
      font-family: 'Poppins', sans-serif;
      cursor: pointer;
      transition: opacity .2s, transform .2s;
    }
    .btn-login:hover { opacity: .9; transform: translateY(-2px); }
    .btn-login:disabled { opacity: .6; cursor: not-allowed; transform: none; }
    .alert {
      padding: .85rem 1rem;
      border-radius: 10px;
      font-size: .9rem;
      margin-bottom: 1.25rem;
      font-weight: 500;
    }
    .alert-error { background: rgba(239,68,68,.1); color: #f87171; border: 1px solid rgba(239,68,68,.2); }
    .back-link {
      text-align: center;
      margin-top: 1.5rem;
    }
    .back-link a {
      color: #6c63ff;
      text-decoration: none;
      font-size: .85rem;
      transition: color .2s;
    }
    .back-link a:hover { color: #f72585; }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="login-logo">
      <span>&lt;</span>MM<span>/&gt;</span>
    </div>
    <p class="login-subtitle">Admin Dashboard Login</p>

    <?php if ($error): ?>
      <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="" id="loginForm" novalidate>
      <div class="form-group">
        <label for="username">Username</label>
        <div class="input-wrapper">
          <i class="fas fa-user"></i>
          <input type="text"
                 id="username"
                 name="username"
                 placeholder="admin"
                 value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                 required
                 autocomplete="username" />
        </div>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <div class="input-wrapper">
          <i class="fas fa-lock"></i>
          <input type="password"
                 id="password"
                 name="password"
                 placeholder="••••••••"
                 required
                 autocomplete="current-password" />
          <button type="button" class="toggle-pass" onclick="togglePassword()">
            <i class="fas fa-eye" id="toggleIcon"></i>
          </button>
        </div>
      </div>

      <div class="remember-row">
        <input type="checkbox" id="remember" name="remember" />
        <label for="remember">Remember me for 30 days</label>
      </div>

      <button type="submit" class="btn-login" id="loginBtn">
        <i class="fas fa-sign-in-alt"></i> Sign In
      </button>
    </form>

    <div class="back-link">
      <a href="../index.html"><i class="fas fa-arrow-left"></i> Back to Portfolio</a>
    </div>
  </div>

  <script>
    function togglePassword() {
      const pwd  = document.getElementById('password');
      const icon = document.getElementById('toggleIcon');
      if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.className = 'fas fa-eye-slash';
      } else {
        pwd.type = 'password';
        icon.className = 'fas fa-eye';
      }
    }

    document.getElementById('loginForm').addEventListener('submit', function () {
      const btn = document.getElementById('loginBtn');
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Signing in...';
    });
  </script>
</body>
</html>
