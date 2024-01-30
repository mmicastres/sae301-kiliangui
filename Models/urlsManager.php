<?php

class UrlsManager
{

    private $_db; // Instance de PDO - objet de connexion au SGBD

    /**
     * Constructeur = initialisation de la connexion vers le SGBD
     */
    public function __construct($db)
    {
        $this->_db = $db;
    }



    /**
     * Ajoute une url dans la base de données
     * @param String $url
     * @param string $type
     * @return boolean true si ajout, false sinon
     */
    public function addUrl(Url $url){
        $req = "INSERT INTO pr_url (url,type,idProjet) VALUES (?,?,?)";
        $stmt = $this->_db->prepare($req);
        $res = $stmt->execute(array($url->url(),$url->type(),$url->idProjet()));

        // pour debuguer les requêtes SQL
        $errorInfo = $stmt->errorInfo();
        if ($errorInfo[0] != 0) {
            print_r($errorInfo);
        }

        return $res;
    }

    /**
     * Liste les urls d'un projet
     * @param int $idProjet
     * @return array $urls
     */
    public function listUrl(Projet $projet)
    {
        // SELECT * FROM `pr_url`
        $req = "SELECT * FROM pr_url WHERE idProjet = ".$projet->idProjet();
        $stmt = $this->_db->prepare($req);
        $stmt->execute();
        $urls = array();
        while ($data = $stmt->fetch()) {
            $urls[] = new Url($data);
        }

        return $urls;
    }

    public function deleteAll($idProjet){
        $req = "DELETE FROM pr_url WHERE idProjet = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($idProjet));
    }


}