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
            echo("idCOntexte : ");
            var_dump($projet->idContexte());
            $res = $stmt->execute(array($projet->idProjet(), $projet->proprietaire() , $projet->nomProjet(),$projet->description(),$projet->publier(),$projet->idContexte(),$projet->idCategorie()));

            //
            // pour debuguer les requêtes SQL
            $errorInfo = $stmt->errorInfo();
            if ($errorInfo[0] != 0) {
                print_r($errorInfo);
            }

return $res;
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
     * @param rien
     */
    public function delete(Projet $projet) : bool
    {
        $req = "DELETE FROM projet WHERE idProjet = ?";
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
        $req = 'SELECT idProjet,nomProjet,description,imgUrl,urlDemo,urlSources,publier,idcontexte,idcategorie,idmembre FROM pr_projet WHERE idProjet=?';
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($idProjet));
        // pour debuguer les requêtes SQL
        $errorInfo = $stmt->errorInfo();
        if ($errorInfo[0] != 0) {
            print_r($errorInfo);
        }
        return new Projet($stmt->fetch());
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

            $projets[] = new Projet($data);

        }
        echo count($projets);

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
        $projet->setIdMembre($this->_membresManage->get_participants($projet->idProjet()));
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