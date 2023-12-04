<?php
include "Modules/membre.php";
include "Models/membresManager.php";
/**
* Définition d'une classe permettant de gérer les membres 
*   en relation avec la base de données	
*/
class MembreController {
    private $membreManager; // instance du manager
    private $twig;

	/**
	* Constructeur = initialisation de la connexion vers le SGBD
	*/
	public function __construct($db, $twig) {
		$this->membreManager = new MembreManager($db);
		$this->twig = $twig;
	}
        
	/**
	* connexion
	* @param aucun
	* @return rien
	*/
	function membreConnexion($data) {
		// verif du login et mot de passe
		// if ($_POST['login']=="user" && $_POST['passwd']=="pass")
		$membre = $this->membreManager->verif_identification($_POST['login'], $_POST['passwd']);
		if ($membre != false) { // acces autorisé : variable de session acces = oui
			$_SESSION['acces'] = "oui";
			$_SESSION['idmembre'] = $membre->idMembre();
			$message = "Bonjour ".$membre->prenom()." ".$membre->nom()."!";
			echo $this->twig->render('index.html.twig',array('acces'=> $_SESSION['acces'],'message'=>$message));
		} else { // acces non autorisé : variable de session acces = non
			$message = "identification incorrecte";
			$_SESSION['acces'] = "non";
			echo $this->twig->render('membre_login.html.twig',array('acces'=> $_SESSION['acces'],'message'=>$message));
    	} 
	}

    /**
     * inscription
     * @param aucun
     * @return rien
     */
    function membreInscription($data){
        $domain = substr($_POST["email"], strpos($_POST["email"], '@') + 1);
        if ($domain != "iut-tlse3.fr"){
            $message = "Vous devez utiliser votre adresse mail de l'IUT";
            echo $this->twig->render('membre_register.html.twig',array('acces'=> $_SESSION['acces'],'message'=>$message));
            return;
        }
        $membre = $this->membreManager->InscriptionMembre($_POST["nom"],$_POST["prenom"],$_POST["id_iut"],$_POST["email"],$_POST["password"],0);
        if ($membre != false){
            $_SESSION["acces"] = "oui";
            $_SESSION["idMembre"] = $membre->idMembre();
            $message = "Bonjour ".$membre->prenom()." ".$membre->nom()."!";
            echo $this->twig->render("index.html.twig", array('acces' => $_SESSION["acces"], 'message'=>$message));
        }else { // acces non autorisé : variable de session acces = non
            $message = "identification incorrecte";
            $_SESSION['acces'] = "non";
            echo $this->twig->render('index.html.twig',array('acces'=> $_SESSION['acces'],'message'=>$message));
        }
    }

	/**
	* deconnexion
	* @param aucun
	* @return rien
	*/
	function membreDeconnexion() {
		$_SESSION['acces'] = "non"; // acces non autorisé
		$message = "vous êtes déconnecté";
		echo $this->twig->render('index.html.twig',array('acces'=> $_SESSION['acces'],'message'=>$message)); 
	 
	}

	/**
	* formulaire de connexion
	* @param aucun
	* @return rien
	*/
	function membreLoginForm() {
		echo $this->twig->render('membre_login.html.twig',array('acces'=> $_SESSION['acces']));
	}

    /**
     * formulaire d'inscription
     * @param aucun
     * @return rien
     */
    function membreRegisterForm() {
        echo $this->twig->render('membre_register.html.twig',array('acces'=> $_SESSION['acces']));
    }
}