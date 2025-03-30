<?php
session_start();

// Verifica se l'utente è loggato, altrimenti reindirizza alla pagina di login
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Mostra il nome dell'utente loggato
$username = $_SESSION['user'];

// Funzione per creare una cartella per ogni utente se non esiste
function createUserFolder($username) {
    $user_folder = 'links/' . $username . '/';  // Cartella separata per ogni utente
    
    // Crea la cartella dell'utente se non esiste
    if (!is_dir($user_folder)) {
        mkdir($user_folder, 0755, true);
    }

    return $user_folder;
}

// Funzione per creare un link accorciato e salvarlo in un file
function createShortenedLink($original_url, $username) {
    $user_folder = createUserFolder($username);  // Assicurati che la cartella dell'utente esista
    $short_code = substr(md5($original_url), 0, 6);  // codice univoco basato sull'URL
    
    // Salva l'URL originale in un file
    $file_name = $user_folder . $short_code . '.txt';
    file_put_contents($file_name, $original_url);

    // Inizializza il contatore delle visite solo se il file non esiste
    $count_file = $user_folder . $short_code . '.count';
    if (!file_exists($count_file)) {
        file_put_contents($count_file, 0);  // inizializza a 0
    }

    // Scrive il timestamp nel file, solo la prima volta che il link è creato
    $timestamp_file = $user_folder . $short_code . '.timestamp';
    if (!file_exists($timestamp_file)) {
        file_put_contents($timestamp_file, time()); // Salva il timestamp al momento della creazione
    }

    return $short_code;
}

// Gestione dell'invio del link
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url'])) {
    $url = $_POST['url'];
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        $short_code = createShortenedLink($url, $username);
    } else {
        echo "<script>alert('URL non valido!');</script>";
    }
}

// Mostra tutti i link accorciati per l'utente
$user_folder = createUserFolder($username);  // Assicurati che la cartella dell'utente esista
$files = scandir($user_folder);

// Inizializzazione di links_data
$links_data = [];

foreach ($files as $file) {
    // Esclude i file con estensione .count e .timestamp
    if ($file != '.' && $file != '..' && !strpos($file, '.count') && !strpos($file, '.timestamp')) {
        // Prendi i dati del link
        $url = file_get_contents($user_folder . $file);
        $short_code = basename($file, '.txt');
        
        // Verifica se i file del contatore e del timestamp esistono prima di leggerli
        $timestamp_file = $user_folder . $short_code . '.timestamp';
        if (file_exists($timestamp_file)) {
            $timestamp = file_get_contents($timestamp_file);
        } else {
            $timestamp = 0;  // Se non esiste, assegna 0 (anche se non dovrebbe mai succedere)
        }

        $count_file = $user_folder . $short_code . '.count';
        if (file_exists($count_file)) {
            $visit_count = (int)file_get_contents($count_file) ?: 0;
        } else {
            $visit_count = 0;  // Se non esiste, assegna 0
        }

        // Aggiungi i dati all'array
        $links_data[] = [
            'url' => $url,
            'short_code' => $short_code,
            'timestamp' => $timestamp,
            'visit_count' => $visit_count
        ];
    }
}
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
                            <td><a href="<?php echo $link['url']; ?>" target="_blank"><?php echo $link['url']; ?></a></td>
                            <td><a href="redirect.php?code=<?php echo $link['short_code']; ?>" target="_blank"><?php echo $link['short_code']; ?></a></td>
                            <td class="visit-count"><?php echo $link['visit_count']; ?></td>
                            <td><?php echo date('d-m-Y H:i', $link['timestamp']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
