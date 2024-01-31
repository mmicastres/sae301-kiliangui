<?php
require_once "Modules/tag.php";
class TagsManager
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
     * Ajoute un tag dans la base de données
     * @param String $intitule
     * @return boolean true si ajout, false sinon
     */
    public function addTag(Tag $tag)
    {
        if ($tag->intitule() == "" || $tag->intitule() == " ") return false;
        $req = "INSERT INTO pr_tag (intitule) VALUES (?)";
        $stmt = $this->_db->prepare($req);
        $res = $stmt->execute(array($tag->intitule()));
    }

    /**
     * Ajoute un tag à un projet
     * @param Tag $tag
     * @param Projet $projet
     * @return void
     */

    public function addTagToProjet(Tag $tag, Projet $projet)
    {
        if ($this->getTag($tag->intitule()) == false) $this->addTag($tag);
        $req = "INSERT INTO pr_caracterise (intitule, idProjet) VALUES (?,?)";
        $stmt = $this->_db->prepare($req);
        $res = $stmt->execute(array($tag->intitule(), $projet->idProjet()));
    }


    /**
     * Liste les tags d'un projet
     * @param int $idProjet
     * @return array $tags
     */
    public function listTag(Projet $projet)
    {
        // SELECT * FROM `pr_tag`
        $req = "SELECT * FROM pr_caracterise WHERE idProjet = ? ";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($projet->idProjet()));
        $tags = array();
        while ($data = $stmt->fetch()) {
            $tags[] = new Tag($data);
        }

        return $tags;
    }

    public function getTag($intitule){
        $req = "SELECT * FROM pr_tag WHERE intitule = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($intitule));
        $data = $stmt->fetch();
        if ($data == false) return false;
        $tag = new Tag($data);
        return $tag;
    }


    /**
     * Supprime un tag
     * @param int $idTag
     * @return boolean true si suppression, false sinon
     */
    public function deleteTag($idTag)
    {
        $req = "DELETE FROM pr_tag WHERE idTag = ?";
        $stmt = $this->_db->prepare($req);
        $res = $stmt->execute(array($idTag));
        return $res;
    }

    /**
     * Supprime tous les tags d'un projet
     * @param int $idProjet
     * @return boolean true si suppression, false sinon
     */
    public function deleteAll($projetId)
    {
        $req = "DELETE FROM pr_caracterise WHERE idProjet = ?";
        $stmt = $this->_db->prepare($req);
        $res = $stmt->execute(array($projetId));
        return $res;
    }

}