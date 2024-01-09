<?php
// utilisation des sessions
session_start();


include "connect.php";

include "Modules/membre.php";
include "Models/membresManager.php";

$membreManager = new MembreManager($bdd);


// texte du message
$message = "";

// ============================== connexion / deconnexion - sessions ==================

// si la variable de session n'existe pas, on la crée
if (!isset($_SESSION['acces'])) {
    $_SESSION['acces']="non";
}
// click sur le bouton connexion
if (isset($_GET["action"]) && ($_GET["action"]=="search"))  {
    if (isset($_GET["search"]) && strlen($_GET["search"]) >= 3){
        $search = $_GET["search"];
        $membres = $membreManager->searchMembre($search);
        // serialize method
        for ($i = 0; $i < count($membres); $i++){
            $membres[$i] = $membres[$i]->jsonSerialize();
        }
        echo json_encode($membres);
    }
}


?>
