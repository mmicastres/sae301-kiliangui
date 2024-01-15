<?php

class Url{
    private int $_idUrl;
    private string $_url;
    private string $_type;
    private string $_idProjet;
    private string  $_titre;


    public function __construct(array $donnees) {
        // initialisation d'un produit à partir d'un tableau de données*/
        if (isset($donnees['idUrl'])) { $this->_idUrl = $donnees['idUrl']; }
        if (isset($donnees['url'])) { $this->_url = $donnees['url']; }
        if (isset($donnees['type'])) { $this->_type = $donnees['type']; }
        if (isset($donnees['idProjet'])) { $this->_idProjet = $donnees['idProjet']; }
        if (isset($donnees['titre'])) { $this->_titre = $donnees['titre']; }

    }

    // GETTERS //
    public function idUrl() { return $this->_idUrl;}
    public function url() { return $this->_url;}
    public function type() { return $this->_type;}
    public function idProjet() { return $this->_idProjet;}
    public function titre() {
        if (isset($this->_titre) && $this->_titre != ""){
            var_dump($this->_titre == "");
            return $this->_titre;
        }
        else{
            return $this->_url;
        }}

    // SETTERS //
    public function setIdUrl(int $idUrl) { $this->_idUrl = $idUrl; }
    public function setUrl(string $url) { $this->_url = $url; }
    public function setType(string $type) { $this->_type = $type; }
    public function setIdProjet(int $idProjet) { $this->_idProjet = $idProjet; }
    public function setTitre(string $titre) { $this->_titre = $titre; }

}