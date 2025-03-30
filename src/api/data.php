<?php
        header('Content-Type: application/json');
        $data = [
            ['nome'=>'Mario','cognome'=>'Rossi', 'email' => 'mario.rossi@example.it'],
            ['nome'=>'Rosa','cognome'=>'Borgo', 'email' => 'rosa.borgo@example.it'],
            ['nome'=>'Federico','cognome'=>'Borghi', 'email' => 'federico.borghi@example.it'],
        ];

        echo json_encode($data);
?>