<?php
session_start();
require_once "db.php";

$error_message = "";
$form_submitted = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST != NULL) {
    $form_submitted = true;
    if (!empty($_POST['user']) && !empty($_POST['password'])) {
        $username = $_POST['user'];
        $pw = $_POST['password'];

        $query = "SELECT * FROM Utenti WHERE user = ? AND password = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param("ss", $username, $pw);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $_SESSION['user'] = $username;
            header('Location: home.php');
            exit();
        } else {
            $error_message = "Credenziali errate!";
        }
        $stmt->close();
    } else {
        $error_message = "Inserisci username e password!";
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,500&display=swap" rel="stylesheet">
    <style>
        /* Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #141E30, #243B55);
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 30px;
            width: 100%;
            max-width: 320px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            text-align: center;
        }
        .login-container h2 {
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: 500;
        }
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
            color: #fff;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 5px;
            font-size: 14px;
            background: rgba(255,255,255,0.05);
            color: #fff;
        }
        .form-group input:focus {
            outline: none;
            border-color: #FF4081;
        }
        .form-group button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #FF4081, #E91E63);
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        .form-group button:hover {
            background: linear-gradient(135deg, #E91E63, #FF4081);
        }
        .error {
            background: rgba(255, 64, 129, 0.2);
            color: #FF4081;
            padding: 10px;
            border-radius: 5px;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            animation: fadeIn 0.5s;
        }
        .error-icon {
            margin-right: 8px;
            font-size: 18px;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if ($form_submitted && $error_message): ?>
            <div class="error">
                <span class="error-icon">&#9888;</span> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
            <div class="form-group">
                <label for="user">Username</label>
                <input type="text" id="user" name="user" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <button type="submit">Accedi</button>
            </div>
        </form>
    </div>
</body>
</html>
