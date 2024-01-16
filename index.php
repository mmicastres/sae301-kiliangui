<?php
// utilisation des sessions
session_start();

include "moteurtemplate.php";
include "connect.php";

include "Controllers/itinerairesController.php";
include "Controllers/membresController.php";
include "Controllers/projetsController.php";
$itiController = new ItineraireController($bdd,$twig);
$projetController = new ProjetController($bdd,$twig);
$membreController = new MembreController($bdd,$twig);


// texte du message
$message = "";

// ============================== connexion / deconnexion - sessions ==================

// si la variable de session n'existe pas, on la crée
if (!isset($_SESSION['acces']) ) {
   $_SESSION['acces']="non";
}

// click sur le bouton connexion
elseif (isset($_POST["connexion"]))  {
  $message = $membreController->membreConnexion($_POST);
}

elseif (isset($_POST["inscription"]))  {
  $message = $membreController->membreInscription($_POST);
}

// deconnexion : click sur le bouton deconnexion
elseif (isset($_GET["action"]) && $_GET['action']=="logout") {
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

// Recherche d'utilisateur
elseif (isset($_GET["action"])  && $_GET["action"]=="searchUser") {
    # CORS
    header("Access-Control-Allow-Origin: *");
    $membreController->searchUser();
}

// ============================== page d'accueil ==================

// cas par défaut = page d'accueil
elseif (!isset($_GET["action"]) && empty($_POST)) {
  echo $twig->render('index.html.twig',array('acces'=> $_SESSION['acces'])); 
}

// ============================== gestion des itineraires ==================

// liste des itinéraires dans un tableau HTML
//  https://.../index/php?action=liste
elseif (isset($_GET["action"]) && $_GET["action"]=="liste") {
  $projetController->listeProjets();
}

// modification d'un itineraire : choix de l'itineraire
//  https://.../index/php?action=modif
elseif (isset($_GET["action"]) && $_GET["action"]=="modif") {
  $projetController->choixModProjet($_SESSION['idMembre']);
}

// modification d'un itineraire : saisie des nouvelles valeurs
// --> au clic sur le bouton "saisie modif" du form précédent
//  ==> version 0 : pas modif de l'iditi ni de l'idmembre
elseif (isset($_POST["saisie_modif"])) {
  //$itiController->saisieModItineraire();
    $projetController->saisieModProjet();
}

//modification d'un itineraire : enregistrement dans la bd
// --> au clic sur le bouton "valider_modif" du form précédent
elseif (isset($_POST["valider_mod_projet"])) {
  $projetController->validerModProjet();
}

elseif (isset($_POST["select_supprimer_projet"])){
    $projetController->selectSuppr();
}
elseif (isset($_POST["valider_supprimer_projet"])){
    echo "SUPPRI";
    $projetController->supprimerProjet();
}

elseif (isset($_POST["envoie_commentaire"])){
    $projetController->ajoutCommentaire();
}
elseif (isset($_POST["del_commentaire"])){
    $projetController->delCommentaire();
}


// recherche d'itineraire : saisie des critres de recherche dans un formulaire
//  https://.../index/php?action=recherc
elseif (isset($_GET["action"]) && $_GET["action"]=="recher") {
  $itiController->formRechercheItineraire();
}

// recherche des itineraires : construction de la requete SQL en fonction des critères 
// de recherche et affichage du résultat dans un tableau HTML 
// --> au clic sur le bouton "valider_recher" du form précédent
elseif (isset($_POST["valider_recher"])) {
  $itiController->rechercheItineraire();
}


// Gestion des espaces :



elseif(isset($_POST["modifier_membre"])){
    $membreController->modifierMembre();
}
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
    $projetController->projet($_SESSION['idMembre']);
}

elseif (isset($_POST["valider_ajout_projet"])) {
    $projetController->ajoutProjet();
}
else{
    echo $twig->render('index.html.twig',array('acces'=> $_SESSION['acces'], 'message'=>"404"));
}

?>
