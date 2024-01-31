<?php

require_once 'Modules/categories.php';

class CategorieManager{
    private $_db; // Instance de PDO
    private projetManager $_projetManager;

    public function __construct($db) {
        $this->_db=$db;
        $this->_projetManager = new projetManager($db);

    }

    public function add(Categorie $categorie) {
        $req = "INSERT INTO pr_categorie (intitule) VALUES (:identifiant)";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array(":identifiant" => $categorie->intitule()));
        header("Location: index.php?action=espaceAdmin");
    }
    public function getById($id){
        $req = "SELECT * FROM pr_categorie WHERE idCategorie = :idCategorie";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array(":idCategorie" => $id));
        $data = $stmt->fetch();
        if ($data == null) return null;
        return new Categorie($data);
    }
    public function get(Categorie $categorie){
        $req = "SELECT * FROM pr_categorie WHERE intitule = :intitule";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array(":intitule" => $categorie->intitule()));
        $data = $stmt->fetch();
        if ($data == null) return null;
        return new Categorie($data);
    }
    public function getId(Categorie $categorie){
        $req = "SELECT * FROM pr_categorie WHERE idCategorie = :idCategorie";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array(":idCategorie" => $categorie->idCategorie()));
        $data = $stmt->fetch();
        if ($data == null) return null;
        return new Categorie($data);
    }


    public function getList() {
        $req = "SELECT * FROM pr_categorie";
        $stmt = $this->_db->prepare($req);
        $stmt->execute();
        $categories = array();
        while ($data = $stmt->fetch()) {
            $categories[] = new Categorie($data);
        }
        return $categories;
    }

    public function update(Categorie $categorie) {
        $req = "UPDATE pr_categorie SET intitule = :intitule WHERE idCategorie = :idCategorie";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array(":intitule" => $categorie->intitule(), ":idCategorie" => $categorie->idCategorie()));
        echo "request executed";

    }

    public function delete(Categorie $categorie) {
        $this->_projetManager->deleteAllFromCatetorie($categorie);
        $req = "DELETE FROM pr_categorie WHERE pr_categorie.idCategorie = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($categorie->idCategorie()));
        header("Location: index.php?action=espaceAdmin");
    }


}