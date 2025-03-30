<?php
echo "Tipo di richiesta:" . $_SERVER['REQUEST_METHOD'] . "<br>";

echo "<br>";

echo "Visualizzazione della variabile \$_REQUEST con echo:<br>";
echo $_REQUEST . "<br>";
echo "<br>";

echo "Visualizzazione della variabile \$_REQUEST con print_r():<br>";
print_r($_REQUEST);
echo "<br><br>";
