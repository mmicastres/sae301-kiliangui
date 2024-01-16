<?php
require_once "Models/membresManager.php";
require_once "Modules/contexts.php";
require_once "Modules/urls.php";
require_once "Models/urlsManager.php";
class ProjetManager
{
    private $_membresManage;
    private $_urlsManage;

    private $_db; // Instance de PDO - objet de connexion au SGBD

    /**
     * Constructeur = initialisation de la connexion vers le SGBD
     * @param PDO $db
     * @return rien
     */
    public function __construct($db)
    {
        $this->_db = $db;
        $this->_urlsManage = new UrlsManager($db);
        $this->_membresManage = new MembreManager($db);
    }

    /**
     * ajout d'un projet dans la BD
     * @param Projet $projet à ajouter
     * @return int true si l'ajout a bien eu lieu, false sinon
     */
    public function add(Projet $projet){
            $stmt = $this->_db->prepare("SELECT max(idProjet) AS maximum FROM pr_projet");
            $stmt->execute();
            $projet->setIdProjet($stmt->fetchColumn()+1);

            // requete d'ajout dans la BD
            $req = "INSERT INTO pr_projet (idProjet,proprietaire,titre,description,publier,idContexte,idCategorie) VALUES (?,?,?,?,?,?,?)";
            $stmt = $this->_db->prepare($req);
            $res = $stmt->execute(array($projet->idProjet(), $projet->proprietaire() , $projet->nomProjet(),$projet->description(),$projet->publier(),$projet->idContexte(),$projet->idCategorie()));

            // pour debuguer les requêtes SQL
            $errorInfo = $stmt->errorInfo();
            if ($errorInfo[0] != 0) {
                print_r($errorInfo);
            }

return $res;
}

    public function modifier(){
        // MISE A JOUR DU PROJET
        $idProjet = $_POST['idProjet'];
        $projet = $this->get($idProjet);
        $titre = $_POST['titre'];
        $description = $_POST['description'];
        $publier = $projet->publier();
        $idContexte = $_POST['idContexte'];
        $idCategorie = $_POST['idCategorie'];
        $req = "UPDATE pr_projet SET titre = ?, description = ?, publier = ?, idContexte = ?, idCategorie = ? WHERE idProjet = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($titre,$description,$publier,$idContexte,$idCategorie,$idProjet));
        // Mise a jour des urls
        $this->_urlsManage->deleteAll($idProjet);
        $imgsUrls = json_decode($_POST["imgsUrls"]);
        $demosUrls = json_decode($_POST["demosUrls"]);
        $sourcesUrls = json_decode($_POST["sourcesUrls"]);
        foreach ($imgsUrls as $url) {
            $url = new Url(array("idProjet"=>$idProjet,"type"=>"img","url"=>$url));
            $this->_urlsManage->addUrl($url);
        }
        foreach ($demosUrls as $url) {
            $url = new Url(array("idProjet"=>$idProjet,"type"=>"demo","url"=>$url));
            $this->_urlsManage->addUrl($url);
        }
        foreach ($sourcesUrls as $url) {
            $url = new Url(array("idProjet"=>$idProjet,"type"=>"source","url"=>$url));
            $this->_urlsManage->addUrl($url);
        }
        // Mise a jour des tags
        //$this->deleteAllTags($idProjet);
        //$tags = json_decode($_POST["tags"]);
        //foreach ($tags as $tag) {
        //    $this->addTag($idProjet,$tag);
        //}
        // Mise a jour des participants
        $this->deleteAllParticipants($idProjet);
        $participants = json_decode($_POST["participants"]);
        foreach ($participants as $participant) {
            $this->addParticipant($idProjet,$participant);
        }

    }


    public function deleteAllParticipants($idProjet){
        $req = "DELETE FROM pr_participer WHERE idProjet = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($idProjet));
    }

    /**
     * nombre de projets dans la base de données
     * @return int le nb de projets
     * @param rien
     */
    public function count():int
    {
        $stmt = $this->_db->prepare('SELECT COUNT(*) FROM pr_projet');
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * suppression d'un projet dans la base de données
     * @param Projet
     * @return boolean true si suppression, false sinon
     */
    public function delete(Projet $projet) : bool
    {
        // delete urls
        $this->_urlsManage->deleteAll($projet->idProjet());
        // delete participants
        $req = "DELETE FROM pr_participer WHERE idProjet = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($projet->idProjet()));
        // delete tags
        $req = "DELETE FROM pr_caracterise WHERE idProjet = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($projet->idProjet()));
        // delete aime
        $req = "DELETE FROM pr_aime WHERE idProjet = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($projet->idProjet()));

        // delete projet

        $req = "DELETE FROM pr_projet WHERE idProjet = ?";
        $stmt = $this->_db->prepare($req);
        return $stmt->execute(array($projet->idProjet()));
    }

    /**
     * recherche dans la BD d'un projet à partir de son id
     * @param int $idProjet
     * @return Projet
     * @param rien
     */
    public function get(int $idProjet) : Projet
    {
        $req = 'SELECT * FROM pr_projet WHERE idProjet=?';
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($idProjet));
        // pour debuguer les requêtes SQL
        $errorInfo = $stmt->errorInfo();
        if ($errorInfo[0] != 0) {
            print_r($errorInfo);
        }
        $data = $stmt->fetch();
        $projet = new Projet($data);
        return $this->completeProjet($projet);
    }

    /**
     * Retourne l'ensemble des projets où un utilisateur est membre
     * @param int $idmembre
     * @return array tableau d'objets de type Projet
     */
    public function getListMembre(int $idmembre):array
    {
        $stmt = $this->_db->prepare('SELECT * FROM pr_projet inner join pr_participer WHERE pr_participer.idProjet = pr_projet.idProjet AND  proprietaire=?');
        $stmt->execute(array($idmembre));
        $projets = [];
        while ($data = $stmt->fetch()) {
// get a list of users
            $data['participants'] = $this->_membresManage->get_participants($data['idProjet']);
            // get tags
            $data['tags'] = $this->get_Tag($data['idProjet']);
            $uncomplete = new Projet($data);
            $projets[] = $this->completeProjet($uncomplete);

        }

        return $projets;
    }


    public function addParticipant(int $idProjet, int $idMembre):bool
    {
        $req = "INSERT INTO pr_participer (idProjet,idMembre) VALUES (?,?)";
        $stmt = $this->_db->prepare($req);
        return $stmt->execute(array($idProjet,$idMembre));
    }

    /**
     * Retourne le projet completer de sont nombre de like, tags, et urls
     * @param Projet $projet
     * @return Projet
     */
    public function completeProjet(Projet $projet){

        // get list of urls
        $urls = $this->_urlsManage->listUrl($projet);
        $imgs = array();
        $demos = array();
        $sources = array();
        foreach ($urls as $url) {
            if ($url->type() == "img") {
                $imgs[] = $url;
            } else if ($url->type() == "demo") {
                $demos[] = $url;
            } else if ($url->type() == "source") {

                $sources[] = $url;
            }
        }
        $projet->setImgsUrls($imgs);
        $projet->setUrlsDemos($demos);
        $projet->setUrlsSources($sources);
        $projet->setParticipants($this->_membresManage->get_participants($projet->idProjet()));
        $projet->setTags($this->get_Tag($projet->idProjet()));
        return $projet;

    }




    /**
     * retourne l'ensemble des projets publier
     * @return array tableau d'objets de type Projet
     * @param rien
     */
    public function getListPublier():array
    {
        $stmt = $this->_db->prepare('SELECT DISTINCT * FROM pr_projet WHERE publier=1');
        $stmt->execute();
        $projets = [];
        while ($data = $stmt->fetch()) {
// get a list of users
            $projet = new Projet($data);
            $projet = $this->completeProjet($projet);
            $projets[] = $projet;

        }

        //var_dump($projets);
        return $projets;
    }

    public function getListMembrePublier(int $idmembre):array
    {
        $stmt = $this->_db->prepare('SELECT * FROM pr_projet WHERE publier=1 AND idmembre=?');
        $stmt->execute(array($idmembre));
        $projets = [];
        while ($data = $stmt->fetch()) {
            $projets[] = new Projet($data);
        }
        return $projets;
    }

    public function get_Tag(int $idProjet):array
    {
        $stmt = $this->_db->prepare('SELECT * FROM pr_tag JOIN pr_caracterise WHERE pr_caracterise.intitule = pr_tag.intitule AND pr_caracterise.idProjet=?');
        $stmt->execute(array($idProjet));
        $tags = [];
        while ($data = $stmt->fetch()) {
            $tags[] = $data['intitule'];
        }
        return $tags;
    }



}