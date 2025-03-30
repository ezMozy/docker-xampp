<?php
require 'db.php'; // Connessione al database

// Verifica se il form è stato inviato
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Controlla se le password corrispondono
    if ($password !== $confirm_password) {
        $error = "Le password non corrispondono!";
    } else {
        // Controlla se l'username è già in uso
        $query = "SELECT * FROM Utenti WHERE username = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Nome utente già in uso!";
        } else {
            // Cripta la password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Inserisce il nuovo utente nel database
            $query = "INSERT INTO Utenti (username, password) VALUES (?, ?)";
            $stmt = $connection->prepare($query);
            $stmt->bind_param("ss", $username, $hashed_password);

            if ($stmt->execute()) {
                header("Location: login.php");
                exit();
            } else {
                $error = "Errore durante la registrazione. Riprova!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuperShorter - Registrazione</title>
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
            padding: 20px;
        }
        .container {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            padding: 40px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        h2 {
            font-size: 36px;
            color: #222;
            margin-bottom: 20px;
        }
        .input-group {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
        }
        label {
            font-size: 20px;
            color: #333;
            font-weight: 600;
            margin-bottom: 10px;
        }
        input[type="text"], input[type="password"] {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 16px;
            margin-bottom: 10px;
            text-align: center;
        }
        button {
            background: linear-gradient(135deg, #FFD700, #FF8C00);
            color: #000;
            border: none;
            padding: 14px 30px;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 500;
            transition: 0.3s;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background: linear-gradient(135deg, #FF8C00, #FFD700);
            transform: scale(1.05);
        }
        .error-message {
            color: red;
            margin-bottom: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Registrazione</h2>

        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="registrazione.php">
            <div class="input-group">
                <label for="username">Nome utente</label>
                <input type="text" name="username" id="username" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="input-group">
                <label for="confirm_password">Conferma password</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>
            <button type="submit">Registrati</button>
        </form>
        <p>Hai già un account? <a href="login.php" style="color: #FF8C00;">Accedi</a></p>
    </div>

</body>
</html>
