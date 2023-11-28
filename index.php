<?php
// utilisation des sessions
session_start();

include "moteurtemplate.php";
include "connect.php";

include "Controllers/itinerairesController.php";
include "Controllers/membresController.php";
$itiController = new ItineraireController($bdd,$twig);
$memController = new MembreController($bdd,$twig);


// texte du message
$message = "";

// ============================== connexion / deconnexion - sessions ==================

// si la variable de session n'existe pas, on la crée
if (!isset($_SESSION['acces'])) {
   $_SESSION['acces']="non";
}
// click sur le bouton connexion
if (isset($_POST["connexion"]))  {  
  $message = $memController->membreConnexion($_POST);  
}

// deconnexion : click sur le bouton deconnexion
if (isset($_GET["action"]) && $_GET['action']=="logout") { 
    $message = $memController->membreDeconnexion(); 
 } 

// formulaire de connexion
if (isset($_GET["action"])  && $_GET["action"]=="login") {
  $memController->membreFormulaire(); 
}

// ============================== page d'accueil ==================

// cas par défaut = page d'accueil
if (!isset($_GET["action"]) && empty($_POST)) {
  echo $twig->render('index.html.twig',array('acces'=> $_SESSION['acces'])); 
}

// ============================== gestion des itineraires ==================

// liste des itinéraires dans un tableau HTML
//  https://.../index/php?action=liste
if (isset($_GET["action"]) && $_GET["action"]=="liste") {
  $itiController->listeItineraires();
}
// liste de mes itinéraires dans un tableau HTML
if (isset($_GET["action"]) && $_GET["action"]=="mesitis") { 
  $itiController->listeMesItineraires($_SESSION['idmembre']);
}

// formulaire ajout d'un itineraire : saisie des caractéristiques à ajouter dans la BD
//  https://.../index/php?action=ajout
// version 0 : l'itineraire est rattaché automatiquement à un membre déjà présent dans la BD
//              l'idmembre est en champ caché dans le formulaire
if (isset($_GET["action"]) && $_GET["action"]=="ajout") {
  $itiController->formAjoutItineraire();
 }

// ajout de l'itineraire dans la base
// --> au clic sur le bouton "valider_ajout" du form précédent
if (isset($_POST["valider_ajout"])) {
  $itiController->ajoutItineraire();
}


// suppression d'un itineraire : choix de l'itineraire
//  https://.../index/php?action=suppr
if (isset($_GET["action"]) && $_GET["action"]=="suppr") { 
  $itiController->choixSuppItineraire($_SESSION['idmembre']);
}

// supression d'un itineraire dans la base
// --> au clic sur le bouton "valider_supp" du form précédent
if (isset($_POST["valider_supp"])) { 
  $itiController->suppItineraire();
}

// modification d'un itineraire : choix de l'itineraire
//  https://.../index/php?action=modif
if (isset($_GET["action"]) && $_GET["action"]=="modif") { 
  $itiController->choixModItineraire($_SESSION['idmembre']);
}

// modification d'un itineraire : saisie des nouvelles valeurs
// --> au clic sur le bouton "saisie modif" du form précédent
//  ==> version 0 : pas modif de l'iditi ni de l'idmembre
if (isset($_POST["saisie_modif"])) {   
  $itiController->saisieModItineraire();
}

//modification d'un itineraire : enregistrement dans la bd
// --> au clic sur le bouton "valider_modif" du form précédent
if (isset($_POST["valider_modif"])) {
  $itiController->modItineraire();
}


// recherche d'itineraire : saisie des critres de recherche dans un formulaire
//  https://.../index/php?action=recherc
if (isset($_GET["action"]) && $_GET["action"]=="recher") {
  $itiController->formRechercheItineraire();
}

// recherche des itineraires : construction de la requete SQL en fonction des critères 
// de recherche et affichage du résultat dans un tableau HTML 
// --> au clic sur le bouton "valider_recher" du form précédent
if (isset($_POST["valider_recher"])) { 
  $itiController->rechercheItineraire();
}

?>
