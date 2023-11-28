<?php
include "Modules/membre.php";
include "Models/membreManager.php";
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
	function membreFormulaire() {
		echo $this->twig->render('membre_connexion.html.twig',array('acces'=> $_SESSION['acces'])); 
	}
	
}