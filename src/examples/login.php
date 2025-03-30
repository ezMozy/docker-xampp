<?php

if($_GET != null)
    echo $_GET['user'];
    echo $_GET['password'];

else if ($_POST != null)
    echo $_POST['user'];
    echo $_POST['password'];
