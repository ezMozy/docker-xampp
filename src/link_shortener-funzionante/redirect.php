<?php
session_start();

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    $username = $_SESSION['user'];
    $user_folder = 'links/' . $username . '/';
    $file_name = $user_folder . $code . '.txt';

    if (file_exists($file_name)) {
        // Incrementa il contatore delle visite
        $count_file = $user_folder . $code . '.count';
        $current_count = (int) file_get_contents($count_file);
        file_put_contents($count_file, $current_count + 1);

        // Leggi l'URL originale e reindirizza
        $original_url = file_get_contents($file_name);
        header("Location: {$original_url}");
        exit();
    } else {
        echo "Link non trovato!";
    }
} else {
    echo "Codice non valido!";
}
?>
