<?php

include "Modules/projets.php";
include "Modules/urls.php";
include "Models/categoriesManager.php";
include "Models/projetsManager.php";
include_once "Models/contextsManager.php";
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



    /* CREATE */
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
        $projet->setProprietaire($_SESSION["idMembre"]);
        // Ajout du projet principale
        $ok = $this->projetManager->add($projet);
        if ($ok){
            $this->handleFiles($projet);
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
            # ajout des participants
            for ($i=0; $i < count($projet->participants()); $i++) {
                $membre = $projet->participants()[$i];
                $this->projetManager->addParticipant($projet->idProjet(), $membre->idMembre());
            }
            // ajout du propriétaire en participant
            $exist = false;
            foreach ($projet->participants() as $participant) {
                if ($participant->idMembre() == $projet->proprietaire()) $exist = true;
            }
            if (!$exist) $this->projetManager->addParticipant($projet->idProjet(), $projet->proprietaire());

        }
        $message = $ok ? "Projet ajouté" : "probleme lors de l'ajout";
        echo $this->twig->render('index.html.twig',array('acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"],'message'=>$message));
    }


    public function likeProjet(){
        $idProjet = $_POST["idProjet"];
        // sécurité : on ne peut pas liker son propre projet
        $projet = $this->projetManager->get($idProjet);
        if ($projet->isProprietaire()) return;

        if (!isset($_SESSION["idMembre"])) return;
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


    private function handleFiles($projet){
        # Sauvegarde des fichiers
        if (isset($_FILES["files"])){
            $max_urls = $this->urlsManager->getMaxId()+1;
            $save_dir = "uploads/";
            $files = $_FILES["files"];
            $extensions = array('jpg', 'png', 'jpeg', 'gif');
            foreach ($files["name"] as $key => $name) {
                // detect if file is empty

                if ($files["size"][$key] == 0 || $files["size"][$key] > 5000000) continue;
                $tmp_name = $files["tmp_name"][$key];
                $file_name = explode('.', $name);
                $extension = end($file_name);
                if (!in_array($extension, $extensions)) continue;
                $file_name = $projet->idProjet()."_".$max_urls.".".$extension;
                $file_url = $save_dir.$file_name;
                move_uploaded_file($tmp_name, $file_url);
                $url = new Url(array("idUrl"=>$max_urls, "idProjet"=>$projet->idProjet(),"type"=>"img","url"=>$file_url));
                $this->urlsManager->addUrl($url);
                $max_urls++;
                if (isset($_POST["imgsUrls"])){
                    $imgsUrls = json_decode($_POST["imgsUrls"]);
                    $imgsUrls[] = $file_url;
                    $_POST["imgsUrls"] = json_encode($imgsUrls);
                }else{
                    $_POST["imgsUrls"] = [$file_url];
                }
            }
        }

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



    /* READ */

    public function listeProjets(){
        $projets = $this->projetManager->getListPublier();
        echo $this->twig->render('projets_liste.html.twig',array('projets'=>$projets,'acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"]));
    }

    public function rechercheProjet(){
        if (!isset($_GET["s"]))  $projets = $this->projetManager->getListPublier();
        else{

            $recherche = $_GET["s"];
            if ($recherche == "") $projets = $this->projetManager->getListPublier();
            else $projets = $this->projetManager->rechercheProjet($recherche);

        }
        echo $this->twig->render('projets_liste.html.twig',array('projets'=>$projets,'acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"]));
    }



    public function formAjoutProjet(){
        $contexts = $this->contextManager->listContext();
        $categories = $this->categorieManager->getList();
        echo $this->twig->render('projet_ajout.html.twig',array('acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"],'idMembre'=>$_SESSION['idMembre'], 'contexts'=>$contexts,'categories'=>$categories));
    }



    /* UPDATE */
    public function modCommentaire(){
        $idCommentaire = $_POST["idCommentaire"];
        $contenu = $_POST["contenu"];
        $commentaire = new Commentaire(array("idCommentaire"=>$idCommentaire,"contenu"=>$contenu));
        $ok = $this->commentaireManager->update($commentaire);
        $message = $ok ? "Commentaire modifié" : "probleme lors de la modification";
        header("Location: index.php?action=projet&id=".$_POST["idProjet"]);
    }


    public function validerModProjet(){
        $idProjet = $_POST["idProjet"];

        $old = $this->projetManager->get($idProjet);
        $old = $this->projetManager->completeProjet($old);
        if (!$old->isParticipant() && !(isset($_SESSION["admin"]) && $_SESSION["admin"] == 1 )) return;
        $this->handleFiles($old);
        $projet = new Projet($_POST);

        $projet->setPublier($old->publier());
        if (isset($_POST["participants"]) ){ $projet->setParticipants($_POST["participants"]);}
        else{ $projet->setParticipants([]);}


        $ok = $this->projetManager->update($projet);
        if ($ok) $message = "Le projet à était éditer";
        else $message = "probleme lors de l'édition";
        $projet = $this->projetManager->get($idProjet);
        echo $this->twig->render('projet.html.twig', array('projet'=>$projet,"admin"=>$_SESSION["admin"],'acces'=>$_SESSION['acces'],'message'=>$message));
    }

    /* DELETE */

    public function delCommentaire(){
        $idCommentaire = $_POST["idCommentaire"];
        if (!isset($_SESSION["idMembre"])) return;
        $idMembre = $_SESSION["idMembre"];
        // get commentaire
        $commentaire = $this->commentaireManager->get($idCommentaire);
        if (isset($_SESSION["admin"]) || $_SESSION["admin"] == 1){ // Permissions des admins
            $ok = $this->commentaireManager->del($idCommentaire);
            $message = $ok ? "Commentaire supprimé" : "probleme lors de la suppression";
            header("Location: index.php?action=projet&id=".$_POST["idProjet"]);return;
        }
        elseif ($commentaire->idMembre() == $idMembre) {

            $ok = $this->commentaireManager->del($idCommentaire);
            $message = $ok ? "Commentaire supprimé" : "probleme lors de la suppression";
            header("Location: index.php?action=projet&id=".$_POST["idProjet"]);

        }


    }

    public function saisieModProjet(){
        $idProjet = $_POST["idProjet"];
        $projet = $this->projetManager->get($idProjet);
        $contexts = $this->contextManager->listContext();
        $categories = $this->categorieManager->getList();

        echo $this->twig->render('projet_ajout.html.twig',array('projet'=>$projet,'acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"],'contexts'=>$contexts,'categories'=>$categories));
    }



    public function supprimerProjet(){
        $idProjet = $_POST["idProjet"];
        $projet = $this->projetManager->get($idProjet);
        $projet = $this->projetManager->completeProjet($projet);
        if ($projet->isParticipant()){
            $ok = $this->projetManager->delete($projet);
            $message = $ok ? "Projet supprimé" : "probleme lors de la suppression";
            echo $this->twig->render('index.html.twig',array('message'=>$message,'acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"]));
        }else
            if (isset($_SESSION["admin"]) && $_SESSION["admin"] == 1) {
                $ok = $this->projetManager->delete($projet);
                $message = $ok ? "Projet supprimé" : "probleme lors de la suppression";
                echo $this->twig->render('index.html.twig',array('message'=>$message,'acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"]));
            }else{
                $message = "Vous n'avez pas les droits pour supprimer ce projet";
                echo $this->twig->render('index.html.twig',array('message'=>$message,'acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"]));
            }

    }
    public function selectSuppr(){
        $idProjet = $_POST["idProjet"];
        $projet = $this->projetManager->get($idProjet);
        echo $this->twig->render('suppr_confirm.html.twig',array('projet'=>$projet,'acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"]));
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
        if (!$projet->publier() && !$projet->isParticipant() && !(isset($_SESSION["admin"]) && $_SESSION["admin"] == 1)  ){
                    $message = "Ce projet n'est pas encore validé";
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
        if (!(isset($_SESSION["admin"]) && $_SESSION["admin"] == 1)) {
            $message = "Vous n'avez pas les droits pour publier ce projet";
            echo $this->twig->render('index.html.twig',array('message'=>$message,'acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"]));
            return;
        }

        $idProjet = $_POST["idProjet"];
        $projet = $this->projetManager->get($idProjet);
        $projet->setPublier(1);
        $ok = $this->projetManager->update($projet);
        $message = $ok ? "Projet validé" : "probleme lors de la validation";
        echo $this->twig->render('index.html.twig',array('message'=>$message,'acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"]));
    }
    function dePublierProjet(){
        if (!(isset($_SESSION["admin"]) && $_SESSION["admin"] == 1)) {
            $message = "Vous n'avez pas les droits pour publier ce projet";
            echo $this->twig->render('index.html.twig',array('message'=>$message,'acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"]));
            return;
        }
        $idProjet = $_POST["idProjet"];
        $projet = $this->projetManager->get($idProjet);
        $projet->setPublier(0);
        $ok = $this->projetManager->update($projet);
        $message = $ok ? "Projet dépublié" : "probleme lors de la dépublication";
        echo $this->twig->render('index.html.twig',array('message'=>$message,'acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"]));
    }

}

