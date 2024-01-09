<?php

class Categorie{
    private int $_idCategorie;
    private string $_intitule;

    public function __construct(array $donnees) {
        // initialisation d'un produit à partir d'un tableau de données*/
        if (isset($donnees['idCategorie'])) { $this->_idCategorie = $donnees['idCategorie']; }
        if (isset($donnees['intitule'])) { $this->_intitule = $donnees['intitule']; }
    }

    // GETTERS //
    public function idCategorie() { return $this->_idCategorie;}
    public function intitule() { return $this->_intitule;}

    // SETTERS //
    public function setIdCategorie(int $idCategorie) { $this->_idCategorie = $idCategorie; }
    public function setIntitule(string $intitule) { $this->_intitule = $intitule; }

}