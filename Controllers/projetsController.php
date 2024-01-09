<?php

include "Modules/projets.php";
include "Modules/urls.php";

include "Models/categoriesManager.php";
include "Models/projetsManager.php";
include "Models/contextsManager.php";

class ProjetController{

    private $projetManager;
    private $membreManager;
    private $contextManager;
    private $categorieManager;
    private $urlsManager;
    private $twig;

    public function __construct($db, $twig){
        $this->projetManager = new ProjetManager($db);
        $this->membreManager = new MembreManager($db);
        $this->contextManager = new ContextsManager($db);
        $this->categorieManager = new CategorieManager($db);
        $this->urlsManager = new UrlsManager($db);
        $this->twig = $twig;
    }

    public function listeProjets(){
        $projets = $this->projetManager->getListPublier();
        echo $this->twig->render('projets_liste.html.twig',array('projets'=>$projets,'acces'=> $_SESSION['acces']));
    }



    public function formAjoutProjet(){
        $contexts = $this->contextManager->listContext();
        $categories = $this->categorieManager->getList();
        echo $this->twig->render('projet_ajout.html.twig',array('acces'=> $_SESSION['acces'],'idMembre'=>$_SESSION['idMembre'], 'contexts'=>$contexts,'categories'=>$categories));
    }
    public function ajoutProjet(){
        if (! isset($_SESSION["idMembre"])) return


        $_POST["publier"] =0;
        # Extract json
        if (!isset($_POST["imgsUrls"])) $_POST["imgsUrls"] = "[]";
        if (!isset($_POST["demosUrls"])) $_POST["demosUrls"] = "[]";
        if (!isset($_POST["sourcesUrls"])) $_POST["sourcesUrls"] = "[]";

        $_POST["imgsUrls"] = json_decode($_POST["imgsUrls"]) ;
        $_POST["demosUrls"] = json_decode($_POST["demosUrls"]);
        $_POST["sourcesUrls"] = json_decode($_POST["sourcesUrls"]);
        $_POST["idMembre"] = $_SESSION["idMembre"];
        $_POST["publier"] = 0;
        $projet = new Projet($_POST);

        $ok = $this->projetManager->add($projet);
        if ($ok){
            # add urls
            for ($i=0; $i < count($projet->imgsUrls()); $i++) {
                $url = new Url(array("idProjet"=>$projet->idProjet(),"type"=>"img","url"=>$projet->imgsUrls()[$i]));
                $this->urlsManager->addUrl($url);
            }
            for ($i=0; $i < count($projet->urlsDemos()); $i++) {
                $url = new Url(array("idProjet"=>$projet->idProjet(),"type"=>"demo","url"=>$projet->urlsDemos()[$i]));
                $this->urlsManager->addUrl($url);
            }
            for ($i=0; $i < count($projet->urlsSources()); $i++) {
                $url = new Url(array("idProjet"=>$projet->idProjet(),"type"=>"source","url"=>$projet->urlsSources()[$i]));
                $this->urlsManager->addUrl($url);
            }
            # add participants
            for ($i=0; $i < count($projet->participants()); $i++) {
                $idMembre = $projet->participants()[$i];
                $this->projetManager->addParticipant($projet->idProjet(), $idMembre );
            }

        }
        $message = $ok ? "Projet ajouté" : "probleme lors de l'ajout";
        echo $this->twig->render('index.html.twig',array('message'=>$message,'acces'=> $_SESSION['acces']));
    }

    public function choixModProjet($idMembre){
        $projets = $this->projetManager->getListMembre($idMembre);
        echo $this->twig->render('projet_choix_modification.html.twig',array('projets'=>$projets,'acces'=> $_SESSION['acces']));
    }


    public function listeMesProjets(){
        $membre = $this->membreManager->get($_SESSION["idMembre"]);
        if ($membre == false){
            $message = "Vous devez être connecté pour accéder à cette page";
            echo $this->twig->render('membre_login.html.twig',array('acces'=> $_SESSION['acces'],'message'=>$message));
            return;
        }
        $idMembre = $membre->idMembre();
        $projets = $this->projetManager->getListMembre($idMembre);
        echo $this->twig->render('utilisateur/projets_liste.html.twig',array('projets'=>$projets,'acces'=> $_SESSION['acces']));
    }


}

