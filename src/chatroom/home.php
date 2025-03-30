<?php
session_start();

// L'utente è già verificato (login effettuato)
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit(); 
}

require_once 'db.php';

// Recupera le stanze (presupponendo l'esistenza della tabella "Stanze")
$query = "SELECT id, nome FROM Stanze";
$result = $connection->query($query);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Home - Chat Room</title>
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
        /* Header in alto a destra */
        .header {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 16px;
        }
        .header a {
            color: #FF4081;
            text-decoration: none;
            font-weight: 500;
        }
        /* Container card */
        .container {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 30px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        .welcome {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: 500;
        }
        h3 {
            text-align: center;
            margin-bottom: 20px;
        }
        /* Lista stanze */
        .room-list {
            list-style: none;
            margin-top: 20px;
        }
        .room-list li {
            margin-bottom: 15px;
        }
        .room-list a {
            display: block;
            background: linear-gradient(135deg, #FF4081, #E91E63);
            color: #fff;
            padding: 15px;
            border-radius: 8px;
            text-decoration: none;
            text-align: center;
            font-weight: 500;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .room-list a:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="logout.php">Logout</a>
    </div>
    <div class="container">
        <div class="welcome">Benvenuto, <?php echo htmlspecialchars($_SESSION['user']); ?></div>
        <h3>Seleziona una stanza di chat:</h3>
        <ul class="room-list">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($room = $result->fetch_assoc()): ?>
                    <li>
                        <a href="room.php?room_id=<?php echo $room['id']; ?>">
                            <?php echo htmlspecialchars($room['nome']); ?>
                        </a>
                    </li>
                <?php endwhile; ?>
            <?php else: ?>
                <li style="text-align:center; padding:20px;">Nessuna stanza disponibile.</li>
            <?php endif; ?>
        </ul>
    </div>
</body>
</html>