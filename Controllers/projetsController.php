<?php

include "Modules/projets.php";
include "Modules/urls.php";
include "Models/categoriesManager.php";
include "Models/projetsManager.php";
include "Models/contextsManager.php";
include_once "Models/tagsManager.php";
//commentaires
include "Models/commentaireManager.php";


class ProjetController{

    private $projetManager;
    private $membreManager;
    private $contextManager;
    private $categorieManager;
    private $urlsManager;
    private $commentaireManager;
    private $_tagsManager;
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
        $this->_tagsManager = new TagsManager($db);
        $this->twig = $twig;

        //DEBUG ONLY
        $this->_db = $db;
    }

    public function ajoutProjet(){
        if (! isset($_SESSION["idMembre"])) return
            $_POST["publier"] =0;
        $_POST["idMembre"] = $_SESSION["idMembre"];
        $_POST["publier"] = 0;
        $tags = [];
        if (isset($_POST["tags"])) {
            foreach (explode(",",$_POST["tags"]) as $tag) {
                $tags[] = new Tag(array("intitule" => $tag));
            }
        }
        $_POST["tags"] = $tags;
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

            foreach ($projet->tags() as $tag) {
                $this->_tagsManager->addTagToProjet($tag, $projet);
            }
            # add participants
            for ($i=0; $i < count($projet->participants()); $i++) {
                $membre = $projet->participants()[$i];
                $this->projetManager->addParticipant($projet->idProjet(), $membre->idMembre());
            }
            $message = "Projet ajouté";

        }
        $message = $ok ? "Projet ajouté" : "probleme lors de l'ajout";
        echo $this->twig->render('index.html.twig',array('message'=>$message,'acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"],'message'=>$message));
    }





    public function ajoutCommentaire(){
        $idProjet = $_POST["idProjet"];
        $projet = $this->projetManager->get($idProjet);
        if (!$projet->isProprietaire()){
            $idMembre = $_SESSION["idMembre"];
            $contenu = $_POST["contenu"];
            $commentaire = new Commentaire(array("idProjet"=>$idProjet,"idMembre"=>$idMembre,"contenu"=>$contenu));
            $ok = $this->commentaireManager->add($commentaire);
            $message = $ok ? "Commentaire ajouté" : "probleme lors de l'ajout";
        }
        // Redirection vers la page du projet concerné
        header("Location: index.php?action=projet&id=".$_POST["idProjet"]);}


    public function listeProjets(){
        $projets = $this->projetManager->getListPublier();
        echo $this->twig->render('projets_liste.html.twig',array('projets'=>$projets,'acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"]));
    }



    public function formAjoutProjet(){
        $contexts = $this->contextManager->listContext();
        $categories = $this->categorieManager->getList();
        echo $this->twig->render('projet_ajout.html.twig',array('acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"],'idMembre'=>$_SESSION['idMembre'], 'contexts'=>$contexts,'categories'=>$categories));
    }

    public function delCommentaire(){
        $idCommentaire = $_POST["idCommentaire"];
        $ok = $this->commentaireManager->del($idCommentaire);
        $message = $ok ? "Commentaire supprimé" : "probleme lors de la suppression";
        header("Location: index.php?action=projet&id=".$_POST["idProjet"]);
    }

    public function likeProjet(){
        $idProjet = $_POST["idProjet"];
        $idMembre = $_SESSION["idMembre"];
        if (isset($_POST["liked"])){
            if ($_POST["liked"] == "1"){
                $ok = $this->projetManager->unlike($idProjet,$idMembre);}
                else{
                    $ok = $this->projetManager->like($idProjet,$idMembre);
                }
        }else{

            $ok = $this->projetManager->like($idProjet,$idMembre);
        }
        $message = $ok ? "Projet liké" : "probleme lors du like";
        header("Location: index.php?action=projet&id=".$_POST["idProjet"]);
    }
    public function saisieModProjet(){
        $idProjet = $_POST["idProjet"];
        $projet = $this->projetManager->get($idProjet);
        $contexts = $this->contextManager->listContext();
        $categories = $this->categorieManager->getList();

        echo $this->twig->render('projet_ajout.html.twig',array('projet'=>$projet,'acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"],'contexts'=>$contexts,'categories'=>$categories));
    }


    // TODO TO TEST
    public function validerModProjet(){
        $idProjet = $_POST["idProjet"];
        $old = $this->projetManager->get($idProjet);
        $projet = new Projet($_POST);
        $projet->setPublier($old->publier());
        if (isset($_POST["participants"]) ){ $projet->setParticipants($_POST["participants"]);}
        else{ $projet->setParticipants([]);}


        $ok = $this->projetManager->update($projet);

        $message = "Le projet à était éditer";
        $projet = $this->projetManager->get($idProjet);
        $proprietaire = $projet->proprietaire();
        echo $this->twig->render('projet.html.twig', array('projet'=>$projet,"admin"=>$_SESSION["admin"],'acces'=>$_SESSION['acces'],'message'=>$message));
    }

    public function selectSuppr(){
        $idProjet = $_POST["idProjet"];
        $projet = $this->projetManager->get($idProjet);
        echo $this->twig->render('suppr_confirm.html.twig',array('projet'=>$projet,'acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"]));
    }

    public function supprimerProjet(){
        $idProjet = $_POST["idProjet"];
        $projet = new Projet(array("idProjet"=>$idProjet));
        $ok = $this->projetManager->delete($projet);
        $message = $ok ? "Projet supprimé" : "probleme lors de la suppression";
        echo $this->twig->render('index.html.twig',array('message'=>$message,'acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"]));
    }


    public function listeMesProjets(){
        $membre = $this->membreManager->get($_SESSION["idMembre"]);
        if ($membre == false){
            $message = "Vous devez être connecté pour accéder à cette page";
            echo $this->twig->render('membre_login.html.twig',array('acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"],'message'=>$message));
            return;
        }
        $idMembre = $membre->idMembre();
        $projets = $this->projetManager->getListMembre($idMembre);
        echo $this->twig->render('utilisateur/projets_liste.html.twig',array('projets'=>$projets,'acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"]));
    }


    function projet(){

        $idProjet = $_GET["id"];
        $projet = $this->projetManager->get($idProjet);
        if ($projet == false){
            $message = "Ce projet n'existe pas";
            echo $this->twig->render('index.html.twig',array('acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"],'message'=>$message));
            return;
        }
        $is_proprietaire = false;
        if (isset($_SESSION["idMembre"]) ){

            $idMembre = $_SESSION["idMembre"];
            if ($idMembre != False){
                $proprietaire = $projet->proprietaire();
                if ($proprietaire == $idMembre){
                    $message = "Vous etes le propriétaire";
                    $is_proprietaire = true;
                }
            }
        }
        $this->projetManager->completeProjet($projet);
        echo $this->twig->render('projet.html.twig',array('projet'=>$projet,'is_proprietaire'=>$is_proprietaire,'acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"]));
    }

    function publierProjet(){
        if (!isset($_SESSION["admin"])) return;
        $idProjet = $_POST["idProjet"];
        $projet = $this->projetManager->get($idProjet);
        $projet->setPublier(1);
        $ok = $this->projetManager->update($projet);
        $message = $ok ? "Projet validé" : "probleme lors de la validation";
        echo $this->twig->render('index.html.twig',array('message'=>$message,'acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"]));
    }
    function dePublierProjet(){
        if (!isset($_SESSION["admin"])) return;
        $idProjet = $_POST["idProjet"];
        $projet = $this->projetManager->get($idProjet);
        $projet->setPublier(0);
        $ok = $this->projetManager->update($projet);
        $message = $ok ? "Projet dépublié" : "probleme lors de la dépublication";
        echo $this->twig->render('index.html.twig',array('message'=>$message,'acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"]));
    }

}

