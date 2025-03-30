<?php
session_start();
require_once "db.php";

$error_message = "";
$form_submitted = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST)) {
    $form_submitted = true;
    if (!empty($_POST['user']) && !empty($_POST['password']) && !empty($_POST['confirm_password'])) {
        $username = $_POST['user'];

        // Controllo se l'username contiene solo lettere e numeri
        if (!preg_match('/^[a-zA-Z0-9]*$/', $username)) {
            $error_message = "L'username può contenere solo lettere e numeri!";
        } else {
            // Verifica che le password corrispondano
            if ($_POST['password'] !== $_POST['confirm_password']) {
                $error_message = "Le password non corrispondono!";
            } else {
                // Cripta la password con bcrypt (meglio di MD5)
                $pw = password_hash($_POST['password'], PASSWORD_BCRYPT);

                // Verifica se l'utente esiste già nel database
                $query = "SELECT * FROM Utenti WHERE user = ?";
                $stmt = $connection->prepare($query);
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $error_message = "L'utente esiste già!";
                } else {
                    // Inserisce i dati nel database
                    $query = "INSERT INTO Utenti (user, password) VALUES (?, ?)";
                    $stmt = $connection->prepare($query);
                    $stmt->bind_param("ss", $username, $pw);
                    $stmt->execute();
                    

                    // Imposta la sessione e redirige l'utente alla pagina di login
                    $_SESSION['user'] = $username;
                    $_SESSION['user_logged_in'] = true;
                    header('Location: login.php');
                    exit();
                }
                $stmt->close();
            }
        }
    } else {
        $error_message = "Inserisci tutti i campi!";
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione</title>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Fredoka One', sans-serif;
            background: linear-gradient(135deg, #833ab4, #1dcaff);
            color: #333;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        .circle {
            position: absolute;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            opacity: 0.7;
            filter: blur(8px);
        }

        .circle.small { width: 80px; height: 80px; top: 10%; left: 10%; }
        .circle.medium { width: 150px; height: 150px; bottom: 10%; right: 15%; }
        .circle.large { width: 250px; height: 250px; top: 30%; left: 50%; transform: translateX(-50%); }

        .signup-container {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            padding: 50px 30px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 10;
        }

        h2 {
            font-size: 36px;
            color: #222;
            font-weight: 600;
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 18px;
            color: #333;
            margin-bottom: 8px;
            text-align: left;
        }

        input[type="text"], input[type="password"] {
            padding: 10px;
            font-size: 16px;
            width: 100%;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ddd;
            box-sizing: border-box;
        }

        button {
            padding: 10px 20px;
            background-color: #FFD700;
            color: #000;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 18px;
            font-weight: 500;
            transition: 0.3s;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            font-family: 'Fredoka One', sans-serif;
        }

        button:hover {
            background-color: #FF8C00;
            transform: scale(1.05);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.4);
        }

        .divider {
            margin: 20px 0;
            border-top: 1px solid rgba(0, 0, 0, 0.3);
            display: flex;
            justify-content: center;
            align-items: center;
            color: #666;
        }

        .divider span {
            padding: 0 10px;
            font-size: 16px;
        }

        .error-message {
            color: red;
            font-size: 16px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="circle small"></div>
    <div class="circle medium"></div>
    <div class="circle large"></div>

    <div class="signup-container">
        <h2>Registrati al tuo account</h2>

        <?php if ($error_message) { ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php } ?>

        <form action="signup.php" method="POST">
            <label for="user">Nome utente</label>
            <input type="text" name="user" id="user" required>

            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>

            <label for="confirm_password">Conferma Password</label>
            <input type="password" name="confirm_password" id="confirm_password" required>

            <button type="submit">Registrati</button>
        </form>

        <div class="divider"><span>o</span></div>
        <p>Hai già un account?</p>
        <a href="login.php" style="color: #1dcaff; text-decoration: none;">Accedi</a>
    </div>
</body>
</html>
