<?php

include "Modules/projets.php";
include "Modules/urls.php";

include "Models/categoriesManager.php";
include "Models/projetsManager.php";
include "Models/contextsManager.php";
//commentaires
include "Models/commentaireManager.php";


class ProjetController{

    private $projetManager;
    private $membreManager;
    private $contextManager;
    private $categorieManager;
    private $urlsManager;
    private $commentaireManager;
    private $twig;

    //DEBUG
    private $_db;

    public function __construct($db, $twig){
        $this->projetManager = new ProjetManager($db);
        $this->membreManager = new MembreManager($db);
        $this->contextManager = new ContextsManager($db);
        $this->categorieManager = new CategorieManager($db);
        $this->urlsManager = new UrlsManager($db);
        $this->commentaireManager = new CommentaireManager($db);
        $this->twig = $twig;

        //DEBUG ONLY
        $this->_db = $db;
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

    public function ajoutCommentaire(){
        $idProjet = $_POST["idProjet"];
        $idMembre = $_SESSION["idMembre"];
        $contenu = $_POST["contenu"];
        $commentaire = new Commentaire(array("idProjet"=>$idProjet,"idMembre"=>$idMembre,"contenu"=>$contenu));
        $ok = $this->commentaireManager->add($commentaire);
        $message = $ok ? "Commentaire ajouté" : "probleme lors de l'ajout";
        $projet = $this->projetManager->get($idProjet);
        $this->projetManager->completeProjet($projet);
        $proprietaire = $projet->proprietaire() == $idMembre;
        echo $this->twig->render('projet.html.twig', array('projet'=>$projet,'is_proprietaire'=>$proprietaire,'acces'=>$_SESSION['acces']));
    }

    public function choixModProjet($idMembre){
        $projets = $this->projetManager->getListMembre($idMembre);

        echo $this->twig->render('projet_choix_modification.html.twig',array('projets'=>$projets,'acces'=> $_SESSION['acces']));
    }
    public function saisieModProjet(){
        $idProjet = $_POST["idProjet"];
        $projet = $this->projetManager->get($idProjet);
        $this->projetManager->completeProjet($projet);
        $contexts = $this->contextManager->listContext();
        $categories = $this->categorieManager->getList();

        echo $this->twig->render('projet_ajout.html.twig',array('projet'=>$projet,'acces'=> $_SESSION['acces'],'contexts'=>$contexts,'categories'=>$categories));
    }
    public function validerModProjet(){
$idProjet = $_POST["idProjet"];
        $projet = $this->projetManager->get($idProjet);
        $titre = $_POST['titre'];
        $description = $_POST['description'];
        $publier = $projet->publier();
        $idContexte = $_POST['idContexte'];
        $idCategorie = $_POST['idCategorie'];
        $req = "UPDATE pr_projet SET titre = ?, description = ?, publier = ?, idContexte = ?, idCategorie = ? WHERE idProjet = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($titre,$description,$publier,$idContexte,$idCategorie,$idProjet));
        // Mise a jour des urls
        $this->urlsManager->deleteAll($idProjet);
        $imgsUrls = json_decode($_POST["imgsUrls"]);
        $demosUrls = json_decode($_POST["demosUrls"]);
        $sourcesUrls = json_decode($_POST["sourcesUrls"]);
        foreach ($imgsUrls as $url) {
            $url = new Url(array("idProjet"=>$idProjet,"type"=>"img","url"=>$url));
            $this->urlsManager->addUrl($url);
        }
        foreach ($demosUrls as $url) {
            $url = new Url(array("idProjet"=>$idProjet,"type"=>"demo","url"=>$url));
            $this->urlsManager->addUrl($url);
        }
        foreach ($sourcesUrls as $url) {
            $url = new Url(array("idProjet"=>$idProjet,"type"=>"source","url"=>$url));
            $this->urlsManager->addUrl($url);
        }
        // Mise a jour des tags
        //$this->deleteAllTags($idProjet);
        //$tags = json_decode($_POST["tags"]);
        //foreach ($tags as $tag) {
        //    $this->addTag($idProjet,$tag);
        //}
        // Mise a jour des participants
        $this->projetManager->deleteAllParticipants($idProjet);
        $participants = $_POST["participants"];
        foreach ($participants as $participant) {
            $this->projetManager->addParticipant($idProjet,$participant);
        }
        $message = "Le projet à était éditer";
        $projet = $this->projetManager->get($idProjet);

        $proprietaire = $projet->proprietaire();
        echo $this->twig->render('projet.html.twig', array('projet'=>$projet,'is_proprietaire'=>true,'acces'=>$_SESSION['acces']));
    }

    public function selectSuppr(){
        $idProjet = $_POST["idProjet"];
        $projet = $this->projetManager->get($idProjet);
        echo $this->twig->render('suppr_confirm.html.twig',array('projet'=>$projet,'acces'=> $_SESSION['acces']));
    }

    public function supprimerProjet(){
        $idProjet = $_POST["idProjet"];
        $projet = new Projet(array("idProjet"=>$idProjet));
        $ok = $this->projetManager->delete($projet);
        $message = $ok ? "Projet supprimé" : "probleme lors de la suppression";
        echo $this->twig->render('index.html.twig',array('message'=>$message,'acces'=> $_SESSION['acces']));
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


    function projet($idMembre){
        $idProjet = $_GET["id"];
        $projet = $this->projetManager->get($idProjet);
        if ($projet == false){
            $message = "Ce projet n'existe pas";
            echo $this->twig->render('index.html.twig',array('acces'=> $_SESSION['acces'],'message'=>$message));
            return;
        }
        $proprietaire = $projet->proprietaire();
        $is_proprietaire = false;
        if ($proprietaire == $idMembre){
            $message = "Vous etes le propriétaire";
            $is_proprietaire = true;
        }
        $this->projetManager->completeProjet($projet);
        echo $this->twig->render('projet.html.twig',array('projet'=>$projet,'is_proprietaire'=>$is_proprietaire,'acces'=> $_SESSION['acces']));
    }

}

