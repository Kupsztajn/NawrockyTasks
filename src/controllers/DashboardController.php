<?php

require_once 'AppController.php';

class DashboardCntroller extends AppController {

    public function login()
    {
        // TODO pobieramy z formularza email, haslo
        // todo sprawdzamy czy taki user istnieje w db
        // jezeli nie istnieje to zwracamy odpowiednie komunikaty
        //jezeli istnieje to logujemy usera (tworzymy sesje)
        return $this->render('login');
    }

    public function logout()
    {
        // Logic for logging out the user
        session_start();
        session_destroy();
        header('Location: /login');
        exit();
    }

}