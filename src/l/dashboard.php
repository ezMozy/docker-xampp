<?php
session_start();
include('db.php');

// Verifica se l'utente è loggato
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Gestione del form per accorciare il link
if (isset($_POST['url'])) {
    $url = $_POST['url'];
    $short_link = substr(md5($url . time()), 0, 6);  // Accorcia il link a 6 caratteri

    // Prima di inserire, verifica se il link è già stato accorciato per l'utente
    $stmt_check = $connection->prepare("SELECT id FROM Link WHERE link_originale = ? AND user = ?");
    $stmt_check->bind_param("ss", $url, $username);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        // Se il link esiste già per l'utente, non inserirlo di nuovo
    } else {
        // Altrimenti, inserisci il nuovo link nel database
        $stmt_insert = $connection->prepare("INSERT INTO Link (link_originale, link_short, n_visite, user, created_at) VALUES (?, ?, 0, ?, NOW())");
        $stmt_insert->bind_param("sss", $url, $short_link, $username);
        $stmt_insert->execute();
    }
}

// Recupera i link dell'utente loggato
$stmt = $connection->prepare("SELECT link_originale, link_short, n_visite, created_at FROM Link WHERE user = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$links_data = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuperShorter - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&display=swap" rel="stylesheet">
    <style>
        /* Stile per la pagina, copiato dalla tua descrizione */
        body {
            font-family: 'Fredoka One', sans-serif;
            background: linear-gradient(135deg, #833ab4, #1dcaff);
            color: #333;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
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

        .choice-container {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            padding: 50px 30px;
            width: 100%;
            max-width: 1000px;
            display: flex;
            justify-content: space-between;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 10;
        }

        .left-column {
            width: 45%;
            padding-right: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .right-column {
            width: 45%;
            padding-left: 20px;
            background: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
            max-height: 500px;
            border-radius: 10px;
        }

        .emoji {
            font-size: 80px;
            margin-bottom: 15px;
        }

        h2 {
            font-size: 36px;
            color: #222;
            font-weight: 600;
            margin-bottom: 20px;
        }

        p {
            font-size: 18px;
            color: #444;
            font-weight: 400;
            margin-bottom: 15px;
        }

        .input-group {
            margin-bottom: 20px;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        label {
            margin-bottom: 10px;
            font-size: 20px;
            color: #333;
            font-weight: 600;
        }

        input[type="text"] {
            width: 80%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 16px;
            margin-bottom: 20px;
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
            font-family: 'Fredoka One', sans-serif;
            width: 80%;
        }

        button:hover {
            background: linear-gradient(135deg, #FF8C00, #FFD700);
            transform: scale(1.05);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.4);
        }

        .logout-btn {
            margin-top: 20px;
            display: inline-block;
            background: linear-gradient(135deg, #FFD700, #FF8C00);
            padding: 14px 30px;
            border-radius: 10px;
            color: #000;
            font-weight: 500;
            text-decoration: none;
            font-size: 18px;
            text-align: center;
            width: 80%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .logout-btn:hover {
            background: linear-gradient(135deg, #FF8C00, #FFD700);
            transform: scale(1.05);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.4);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #f4f4f4;
            font-weight: bold;
        }

        .visit-count {
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="circle small"></div>
    <div class="circle medium"></div>
    <div class="circle large"></div>

    <div class="choice-container">
        <!-- Colonna di sinistra con il form per accorciare il link -->
        <div class="left-column">
            <h2>Benvenuto, <?php echo $username; ?>!</h2>
            <div class="input-group">
                <label for="url-input">Inserisci il link da accorciare:</label>
                <form method="POST" action="">
                    <input type="text" name="url" id="url-input" placeholder="https://example.com" required>
                    <button type="submit">Accorcia Link</button>
                </form>
            </div>
            <!-- Pulsante "Esci" sotto il pulsante "Accorcia Link" -->
            <a href="logout.php" class="logout-btn">Esci</a>
        </div>

        <!-- Colonna di destra con la lista dei link accorciati -->
        <div class="right-column">
            <h2>I tuoi link accorciati</h2>
            <table>
                <thead>
                    <tr>
                        <th>Link Originale</th>
                        <th>Link Accorciato</th>
                        <th>Visite</th>
                        <th>Data Creazione</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($links_data as $link): ?>
                        <tr>
                            <td><a href="<?php echo $link['link_originale']; ?>" target="_blank"><?php echo $link['link_originale']; ?></a></td>
                            <td><a href="r.php?code=<?php echo $link['link_short']; ?>" target="_blank"><?php echo $link['link_short']; ?></a></td>
                            <td class="visit-count"><?php echo $link['n_visite']; ?></td>
                            <td><?php echo date("Y-m-d H:i:s", strtotime($link['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
