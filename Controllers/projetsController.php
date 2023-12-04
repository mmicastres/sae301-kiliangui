<?php

include "Modules/projets.php";
include "Models/projetsManager.php";

class ProjetController{

    private $projetManager;
    private $twig;

    public function __construct($db, $twig){
        $this->projetManager = new ProjetManager($db);
        $this->twig = $twig;
    }

    public function listeProjets(){
        $projets = $this->projetManager->getListPublier();
        echo $this->twig->render('projets_liste.html.twig',array('projets'=>$projets,'acces'=> $_SESSION['acces']));
    }
}

