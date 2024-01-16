<?php
include "Modules/membre.php";
include "Models/membresManager.php";
/**
* Définition d'une classe permettant de gérer les membres 
*   en relation avec la base de données	
*/
class MembreController {
    private $membreManager; // instance du manager
    private $projetManager;
    private $twig;

	/**
	* Constructeur = initialisation de la connexion vers le SGBD
	*/
	public function __construct($db, $twig) {
		$this->membreManager = new MembreManager($db);
        $this->projetManager = new ProjetManager($db);
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
            # convert idMmebre to string
            $_SESSION["idMembre"] = strval($membre->idMembre());
            if ($membre->admin()==1){
                $_SESSION["admin"] = 1;
            }
            //var_dump($_SESSION['idMembre']);
            #save session
            session_commit();

			$message = "Bonjour ".$membre->prenom()." ".$membre->nom()."!";
			echo $this->twig->render('index.html.twig',array('acces'=> $_SESSION['acces'],'idMembre'=>$_SESSION["idMembre"],'message'=>$message));
		} else { // acces non autorisé : variable de session acces = non
			$message = "identification incorrecte";
			$_SESSION['acces'] = "non";
			echo $this->twig->render('membre_login.html.twig',array('acces'=> $_SESSION['acces'],'idMembre'=>$_SESSION["idMembre"],'message'=>$message));
    	} 
	}

    /**
     * inscription
     * @param aucun
     * @return rien
     */
    function membreInscription($data){
        $domain = substr($_POST["email"], strpos($_POST["email"], '@') + 1);
        // ignore subdomain
        $domain = explode('.', $domain);
        $domain = array_slice($domain, -2, 2);
        $domain = implode('.', $domain);
        //echo $domain;
        if ($domain != "iut-tlse3.fr"){
            $message = "Vous devez utiliser votre adresse mail de l'IUT";
            echo $this->twig->render('membre_register.html.twig',array('acces'=> $_SESSION['acces'],'message'=>$message));
            return;
        }
        $membre = $this->membreManager->InscriptionMembre($_POST["nom"],$_POST["prenom"],$_POST["id_iut"],$_POST["email"],$_POST["password"],0);
        //check type of $membre
        //echo gettype($membre);
        if ($membre != false && gettype($membre) == "object" ){
            $_SESSION["acces"] = "oui";
            $_SESSION["idMembre"] = $membre->idMembre();
            $message = "Bonjour ".$membre->prenom()." ".$membre->nom()."!";
            echo $this->twig->render("index.html.twig", array('acces' => $_SESSION["acces"], 'message'=>$message));
        } if ($membre != false && gettype($membre) == "string"){
            $message = $membre;
            echo $this->twig->render('membre_register.html.twig',array('acces'=> $_SESSION['acces'],'message'=>$message));
        } else { // acces non autorisé : variable de session acces = non
            $message = "identification incorrecte";
            $_SESSION['acces'] = "non";
            echo $this->twig->render('membre_register.html.twig',array('acces'=> $_SESSION['acces'],'message'=>$message));
        }
    }

	/**
	* deconnexion
	* @param aucun
	* @return rien
	*/
	function membreDeconnexion() {
        unset($_SESSION["acces"]);
        unset($_SESSION["idMembre"]);
        unset($_SESSION["admin"]);
		$message = "vous êtes déconnecté";
		echo $this->twig->render('index.html.twig',array('acces'=> $_SESSION['acces'],'message'=>$message)); 
	 
	}

	/**
	* formulaire de connexion
	* @param aucun
	* @return rien
	*/
	public function membreLoginForm() {
		echo $this->twig->render('membre_login.html.twig',array('acces'=> $_SESSION['acces']));
	}

    /**
     * formulaire d'inscription
     * @param aucun
     * @return rien
     */
    public function membreRegisterForm() {
        echo $this->twig->render('membre_register.html.twig',array('acces'=> $_SESSION['acces']));
    }

    public function espaceMembre() {
        if (!isset($_SESSION["idMembre"])){
            $message = "Vous devez être connecté pour accéder à cette page";
            echo $this->twig->render('membre_login.html.twig',array('acces'=> $_SESSION['acces'],'message'=>$message));
            return;
        }
        $membre = $this->membreManager->get($_SESSION["idMembre"]);
        $projetsParticiper = $this->projetManager->getListParticiper($_SESSION["idMembre"]);
        echo $this->twig->render('membre_espace.html.twig',array('membre'=>$membre,'projets'=>$projetsParticiper, 'acces'=>$_SESSION["acces"]));
    }
    public function modifierMembre(){
        if (!isset($_SESSION["idMembre"])){
            $message = "Vous devez être connecté pour accéder à cette page";
            echo $this->twig->render('membre_login.html.twig',array('acces'=> $_SESSION['acces'],'message'=>$message));
            return;
        }
        $membre = $this->membreManager->get($_SESSION["idMembre"]);
        $membre->setNom($_POST["nom"]);
        $membre->setPrenom($_POST["prenom"]);
        $membre->setEmail($_POST["email"]);

        $membre->setIdIut($_POST["idIUT"]);
        $this->membreManager->modifier($membre);
        header("Location: index.php?action=espaceMembre");

    }


    public function searchUser(){
        $search = $_GET["search"];
        if (empty($search) || strlen($search) < 3){
            return;
        }
        $membres = $this->membreManager->searchMembre($search);
        // return json
        $json = array();
        foreach ($membres as $membre){
            $json[] = $membre->jsonSerialize();
        }
        echo json_encode($json);
    }

}

?>