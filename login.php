<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Register</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>

    <div class="container">
        <div class="form-box active" id = "login-form">
            <form action="">
                <a href="index.php" style="text-decoration: none; color: rgba(0,0,0,0.6)">←</a>
                <h2>Login</h2>
                <input type="email" name="email" placeholder="email" required>
                <input type="password" name="password" placeholder="password" required>
                <button type="submit" name="login">Login</button>
                <p>Don't have an account? <a href="#" onclick="showForm('register-form')">Register</a></p>
            </form>
        </div>

        <div class="form-box" id = "register-form">
            <form action="">
                 <a href="index.php" style="text-decoration: none; color: rgba(0,0,0,0.6)">←</a>
                <h2>Register</h2>
                <input type="text" name="username" placeholder="username" required>
                <input type="email" name="email" placeholder="email" required>
                <input type="password" name="password" placeholder="password" required>
                <button type="submit" name="register">Register</button>
                <p>Already have an account? <a href="#" onclick="showForm('login-form')">Login</a></p>
            </form>
        </div>
    </div>

    <script src= "javascript/login.js"></script>
    
</body>
</html>