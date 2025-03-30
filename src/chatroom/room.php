<?php
session_start();

// L'utente è già verificato
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';

// Se il parametro GET "room_id" non è presente o non valido, reindirizza alla home
if (!isset($_GET['room_id']) || empty($_GET['room_id'])) {
    header("Location: home.php");
    exit();
}

$room_id = intval($_GET['room_id']);

// Verifica che la stanza esista
$stmt = $connection->prepare("SELECT nome FROM Stanze WHERE id = ?");
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header("Location: home.php");
    exit();
}
$room = $result->fetch_assoc();
$room_name = $room['nome'];

// Assicurati che l'ID dell'utente sia salvato in sessione
if (!isset($_SESSION['user_id'])) {
    $stmt_user = $connection->prepare("SELECT id FROM Utenti WHERE user = ?");
    $stmt_user->bind_param("s", $_SESSION['user']);
    $stmt_user->execute();
    $res_user = $stmt_user->get_result();
    if ($res_user->num_rows > 0) {
        $row_user = $res_user->fetch_assoc();
        $_SESSION['user_id'] = $row_user['id'];
    }
}

// Gestione dell'invio di un nuovo messaggio
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['messaggio'])) {
    $messaggio = trim($_POST['messaggio']);
    if (!empty($messaggio)) {
        $user_id = $_SESSION['user_id'];
        // Inserisci il messaggio nel database
        $stmt_insert = $connection->prepare("INSERT INTO Messaggi (utente_id, stanza_id, testo, data_messaggio) VALUES (?, ?, ?, NOW())");
        $stmt_insert->bind_param("iis", $user_id, $room_id, $messaggio);
        $stmt_insert->execute();
        $stmt_insert->close();
        // Redirect per evitare la doppia sottomissione del form
        header("Location: room.php?room_id=" . $room_id);
        exit();
    }
}

// Recupera i messaggi della stanza, includendo l'utente_id per poterli allineare correttamente
$query = "SELECT m.utente_id, m.testo, m.data_messaggio, u.user 
          FROM Messaggi m
          JOIN Utenti u ON m.utente_id = u.id
          WHERE m.stanza_id = ?
          ORDER BY m.data_messaggio ASC";
$stmt_messages = $connection->prepare($query);
$stmt_messages->bind_param("i", $room_id);
$stmt_messages->execute();
$messages_result = $stmt_messages->get_result();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($room_name); ?> - Chat Room</title>
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
        /* Container principale */
        .container {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 12px;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        /* Header */
        .header {
            background: #1C1F26;
            padding: 20px;
            position: relative;
            text-align: center;
        }
        .header h2 {
            font-size: 24px;
            font-weight: 500;
        }
        .back, .logout {
            position: absolute;
            top: 20px;
            font-size: 16px;
        }
        .back {
            left: 20px;
        }
        .logout {
            right: 20px;
        }
        .back a, .logout a {
            color: #FF4081;
            text-decoration: none;
            font-weight: 500;
        }
        /* Chat box */
        .chat-box {
            padding: 20px;
            height: 400px;
            overflow-y: auto;
            background: rgba(0, 0, 0, 0.2);
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            max-width: 70%;
            clear: both;
        }
        /* Stile per i messaggi dell'utente loggato: allineamento a destra e background diverso */
        .message.own {
            background: rgba(255, 255, 255, 0.2);
            margin-left: auto;
        }
        .message .user {
            font-weight: 600;
            color: #FF4081;
            margin-bottom: 5px;
        }
        .message .date {
            font-size: 12px;
            color: #ccc;
            margin-bottom: 5px;
        }
        .message .text {
            font-size: 16px;
            line-height: 1.4;
        }
        /* Form per invio messaggio */
        .send-message {
            display: flex;
            padding: 20px;
            background: #1C1F26;
        }
        .send-message input[type="text"] {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 8px 0 0 8px;
            font-size: 16px;
        }
        .send-message button {
            padding: 12px 20px;
            border: none;
            background: linear-gradient(135deg, #FF4081, #E91E63);
            color: #fff;
            font-size: 16px;
            border-radius: 0 8px 8px 0;
            cursor: pointer;
            transition: background 0.3s;
        }
        .send-message button:hover {
            background: linear-gradient(135deg, #E91E63, #FF4081);
        }
        /* Scrollbar personalizzata per chat-box */
        .chat-box::-webkit-scrollbar {
            width: 6px;
        }
        .chat-box::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
        }
        .chat-box::-webkit-scrollbar-thumb {
            background: #FF4081;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="back"><a href="home.php">&larr; Home</a></div>
            <div class="logout"><a href="logout.php">Logout</a></div>
            <h2><?php echo htmlspecialchars($room_name); ?></h2>
        </div>
        <div class="chat-box">
            <?php while ($msg = $messages_result->fetch_assoc()): 
                // Aggiungi la classe "own" se il messaggio è dell'utente loggato
                $class = ($msg['utente_id'] == $_SESSION['user_id']) ? 'message own' : 'message';
            ?>
                <div class="<?php echo $class; ?>">
                    <div class="user"><?php echo htmlspecialchars($msg['user']); ?></div>
                    <div class="date"><?php echo htmlspecialchars($msg['data_messaggio']); ?></div>
                    <div class="text"><?php echo nl2br(htmlspecialchars($msg['testo'])); ?></div>
                </div>
            <?php endwhile; ?>
        </div>
        <form class="send-message" method="POST" action="room.php?room_id=<?php echo $room_id; ?>">
            <input type="text" name="messaggio" placeholder="Scrivi un messaggio..." required>
            <button type="submit">Invia</button>
        </form>
    </div>
</body>
</html>
