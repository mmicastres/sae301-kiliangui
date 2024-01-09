<?php

require_once 'Modules/categories.php';
class CategorieManager{
    private $_db; // Instance de PDO

    public function __construct($db) {
        $this->setDb($db);
    }

    // GETTERS //
    public function db() { return $this->_db; }

    // SETTERS //
    public function setDb(PDO $db) { $this->_db = $db; }

    // CRUD //
    public function add(Categorie $categorie) {
        $q = $this->_db->prepare('INSERT INTO categories(intitule) VALUES(:intitule)');
        $q->bindValue(':intitule', $categorie->intitule());
        $q->execute();
    }

    public function delete(Categorie $categorie) {
        $this->_db->exec('DELETE FROM categories WHERE idCategorie = '.$categorie->idCategorie());
    }

    public function get(int $id) {
        $id = (int) $id;
        $q = $this->_db->query('SELECT * FROM categories WHERE idCategorie = '.$id);
        $donnees = $q->fetch(PDO::FETCH_ASSOC);
        return new Categorie($donnees);
    }

    public function getList() {
        $categories = [];
        $q = $this->_db->query('SELECT * FROM pr_categorie ORDER BY intitule');
        while ($donnees = $q->fetch()) {
            $categories[] = new Categorie($donnees);
        }
        return $categories;
    }

    public function update(Categorie $categorie) {
        $q = $this->_db->prepare('UPDATE categories SET intitule = :intitule WHERE idCategorie = :idCategorie');
        $q->bindValue(':intitule', $categorie->intitule());
        $q->bindValue(':idCategorie', $categorie->idCategorie());
        $q->execute();
    }
}