<?php
// utilisation des sessions
session_start();

include "moteurtemplate.php";
include "connect.php";

include "Controllers/membresController.php";
include "Controllers/projetsController.php";
include "Controllers/contexteController.php";
include "Controllers/categorieController.php";
$projetController = new ProjetController($bdd,$twig);
$membreController = new MembreController($bdd,$twig);
$categorieController = new CategorieController($bdd,$twig);
$contexteController = new ContexteController($bdd,$twig);


// texte du message
$message = "";

// ============================== connexion / deconnexion - sessions ==================

// si la variable de session n'existe pas, on la crée
if (!isset($_SESSION['acces']) ) {
    $_SESSION['acces'] = "non";
    $_SESSION["admin"] = 0;
    $_SESSION["idMembre"] = 0;
    session_commit();
}


// if (isset($_POST)) { // LOL $_post est définie dans n'importe quel contexte
if (!empty($_POST)) {
    if (isset($_POST["connexion"])) {
        $message = $membreController->membreConnexion($_POST);
    } elseif (isset($_POST["inscription"])) {
        $message = $membreController->membreInscription($_POST);
    }

    //TODO : Pourquoi en POST ???
    // Modification d'un projet
    elseif (isset($_POST["saisie_modif"])) {
        $projetController->saisieModProjet();
    } elseif (isset($_POST["valider_ajout_projet"])) {
        $projetController->ajoutProjet();
    }
    //modification d'un projet : enregistrement dans la bd
    // --> au clic sur le bouton "valider_modif" du form précédent
    elseif (isset($_POST["valider_mod_projet"])) {
        $projetController->validerModProjet();
    }
    elseif (isset($_POST["select_supprimer_projet"])) {
        $projetController->selectSuppr();
    }
    elseif (isset($_POST["valider_supprimer_projet"])) {
        $projetController->supprimerProjet();
    } elseif (isset($_POST["envoie_commentaire"])) {
        $projetController->ajoutCommentaire();
    }
    elseif(isset($_POST["mod_commentaire"])) {
        $projetController->modCommentaire();
    }
    elseif (isset($_POST["del_commentaire"])) {
        $projetController->delCommentaire();
    } elseif (isset($_POST["modifier_membre"])) {
        $membreController->modifierMembre();
    }elseif (isset($_POST["like"])){
        $projetController->likeProjet();
    }
    elseif (isset($_POST["supprMembre"])) {
        $membreController->deleteMembre();
    }elseif (isset($_POST["addCategorie"])) {
        $categorieController->addCategorie();
    }elseif (isset($_POST["modCategorie"])) {
        $categorieController->modCategorie();
    }elseif(isset($_POST["modContexte"])) {
        $contexteController->modContexte();
    }elseif (isset($_POST["delCategorie"])) {
        $categorieController->deleteCategorie();
    }
    elseif (isset($_POST["delContexte"])) {
        $contexteController->deleteContexte();
    }

    elseif (isset($_POST["addContexte"])) {
        $contexteController->addContext();
    }
    elseif (isset($_POST["publierProjet"])) {
        $projetController->publierProjet();
    }
    elseif (isset($_POST["dePublierProjet"])) {
        $projetController->dePublierProjet();
    }
    return;
}



// deconnexion : click sur le bouton deconnexion
if (isset($_GET["action"]) && $_GET['action']=="logout") {
    $message = $membreController->membreDeconnexion();
 }

// formulaire de connexion
elseif (isset($_GET["action"])  && $_GET["action"]=="login") {
  $membreController->membreLoginForm();
}

// formulaire de connexion
elseif (isset($_GET["action"])  && $_GET["action"]=="register") {
    $membreController->membreRegisterForm();
}
elseif(isset($_GET["action"]) && $_GET["action"] == "modifMembre"){
    $membreController->modifMembreView();
}

// Recherche d'utilisateur
elseif (isset($_GET["action"])  && $_GET["action"]=="searchUser") {
    # CORS
    header("Access-Control-Allow-Origin: *");
    $membreController->searchUser();
}

// ============================== page d'accueil ==================

// cas par défaut = page d'accueil
elseif (!isset($_GET["action"]) && empty($_POST)) {
  echo $twig->render('index.html.twig',array('acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"]));
}

// ============================== gestion des Projet ==================

// liste des projets
//  https://.../index/php?action=liste
elseif (isset($_GET["action"]) && $_GET["action"]=="liste") {
  $projetController->listeProjets();
}




// recherche d'un projet par le nom
//  https://.../index/php?action=recherc
elseif (isset($_GET["action"]) && $_GET["action"]=="recher") {
    $projetController->rechercheProjet();
}



// Gestion des espaces :

//Espace utilisateur
elseif (isset($_GET["action"]) && $_GET["action"] == "espaceMembre"){
    $membreController->espaceMembre();
}
elseif (isset($_GET["action"]) && $_GET["action"] == "mesProjets"){
    $projetController->listeMesProjets();
}
elseif (isset($_GET["action"]) && $_GET["action"] == "ajoutProjet"){
    $projetController->formAjoutProjet();
}
elseif (isset($_GET["action"]) && $_GET["action"] == "projet"){
    $projetController->projet();
}
//Espace Admin
elseif (isset($_GET["action"]) && $_GET["action"] == "espaceAdmin"){
    $membreController->espaceAdmin();
}
elseif (isset($_GET["action"]) && $_GET["action"] == "supprMembre"){
    $membreController->confirmSupprMembre();
}


else{
    echo $twig->render('index.html.twig',array('acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"], 'message'=>"404"));
}

?>
