<?php
session_start();
include 'koneksi.php';

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($query) === 1) {
        $row = mysqli_fetch_assoc($query);
        if (password_verify($password, $row['password'])) {
            $_SESSION['login'] = true;
            $_SESSION['username'] = $username;
            header("Location: index.php");
            exit;
        }
    }
    $error = true;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Login Manajemen Event</title>
    <style>
        body { font-family: Arial, sans-serif; background: #2c3e50; display: flex; justify-content: center; align-items: center; height: 100vh; margin:0; }
        .login-card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); width: 320px; }
        .input-control { margin-bottom: 15px; }
        .input-control input { width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 5px; }
        button { width: 100%; padding: 10px; background: #e74c3c; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2 style="text-align:center; margin-top:0;">Sign In</h2>
        <?php if(isset($error)) echo "<p style='color:red; text-align:center;'>Username/Password salah!</p>"; ?>
        <form action="" method="POST">
            <div class="input-control">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="input-control">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" name="login">Masuk Sistem</button>
        </form>
    </div>
</body>
</html>