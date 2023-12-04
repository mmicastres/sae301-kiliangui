<?php
/**
 * Definition
 */
class Projet{
    private int $_idProjet;
    private string $_nomProjet;
    private string $_description;
    private string $_imgUrl;
    private string $_urlDemo;
    private string$_urlSources;
    private bool $_publier;
    private int $_idcontexte;
    private int $_idcategorie;
    private array $_idmembre;
    private array $_tags;

    public function __construct(array $donnees) {
        // initialisation d'un produit à partir d'un tableau de données*/
        if (isset($donnees['idProjet'])) { $this->_idProjet = $donnees['idProjet']; }
        if (isset($donnees['nomProjet'])) { $this->_nomProjet = $donnees['nomProjet']; }
        if (isset($donnees['description'])) { $this->_description = $donnees['description']; }
        if (isset($donnees['imgUrl'])) { $this->_imgUrl = $donnees['imgUrl']; }
        if (isset($donnees['urlDemo'])) { $this->_urlDemo = $donnees['urlDemo']; }
        if (isset($donnees['urlSources'])) { $this->_urlSources = $donnees['urlSources']; }
        if (isset($donnees['publier'])) { $this->_publier = $donnees['publier']; }
        if (isset($donnees['idContexte'])) { $this->_idcontexte = $donnees['idcontexte']; }
        if (isset($donnees['idCategorie'])) { $this->_idcategorie = $donnees['idcategorie']; }
        if (isset($donnees['idmembre'])) {
            if (is_array($donnees['idmembre'])) {
                $this->_idmembre = $donnees['idmembre'];
            } else {
                $this->_idmembre = explode(',', $donnees['idmembre']);
            }
        }
        if (isset($donnees['tags'])) {
            if (is_array($donnees['tags'])) {
                $this->_tags = $donnees['tags'];
            } else {
                $this->_tags = explode(',', $donnees['tags']);
            }
        }
    }

    // GETTERS //
    public function idProjet() { return $this->_idProjet;}
    public function nomProjet() { return $this->_nomProjet;}
    public function description() { return $this->_description;}
    public function imgUrl() { return $this->_imgUrl;}
    public function urlDemo() { return $this->_urlDemo;}
    public function urlSources() { return $this->_urlSources;}
    public function publier() { return $this->_publier;}
    public function idcontexte() { return $this->_idcontexte;}
    public function idcategorie() { return $this->_idcategorie;}
    public function idmembre() { return $this->_idmembre;}
    public function tags() { return $this->_tags;}

    // SETTERS //
    public function setIdProjet(int $idProjet) { $this->_idProjet = $idProjet; }
    public function setNomProjet(string $nomProjet) { $this->_nomProjet = $nomProjet; }
    public function setDescription(string $description) { $this->_description = $description; }
    public function setImgUrl(string $imgUrl) { $this->_imgUrl = $imgUrl; }
    public function setUrlDemo(string $urlDemo) { $this->_urlDemo = $urlDemo; }
    public function setUrlSources(string $urlSources) { $this->_urlSources = $urlSources; }
    public function setPublier(bool $publier) { $this->_publier = $publier; }
    public function setIdContexte(int $idcontexte) { $this->_idcontexte = $idcontexte; }
    public function setIdCategorie(int $idcategorie) { $this->_idcategorie = $idcategorie; }
    public function setIdMembre(array $idmembre) { $this->_idmembre = $idmembre; }
    public function setTags(array $tags) { $this->_tags = $tags; }
}