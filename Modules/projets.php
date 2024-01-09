<?php
/**
 * Definition
 */

@include_once "Modules/membre.php";
class Projet{
    private int $_idProjet;
    private string $_titre;
    private string $_description;
    private array $_imgsUrls;
    private array $_urlsDemos;
    private array $_urlsSources;
    private int $_publier;
    private int $_idContexte;
    private int $_idCategorie;
    private array $_participants;
    private array $_tags;
    private int $_proprietaire;

    public function __construct(array $donnees) {
        // initialisation d'un produit à partir d'un tableau de données*/
        if (isset($donnees['idProjet'])) { $this->_idProjet = $donnees['idProjet']; }
        if (isset($donnees['titre'])) { $this->_titre = $donnees['titre']; }
        if (isset($donnees['description'])) { $this->_description = $donnees['description']; }
        if (isset($donnees['imgsUrls'])) { $this->_imgsUrls = $donnees['imgsUrls']; }
        if (isset($donnees['demosUrls'])) { $this->_urlsDemos = $donnees['demosUrls']; }
        if (isset($donnees['sourcesUrls'])) { $this->_urlsSources = $donnees['sourcesUrls']; }
        if (isset($donnees['publier'])) { $this->_publier = $donnees['publier']; }
        if (isset($donnees['idContexte'])) { $this->_idContexte = intval($donnees['idContexte']); }
        if (isset($donnees['idCategorie'])) { $this->_idCategorie = intval($donnees['idCategorie']); }
        if (isset($donnees['participants'])) {
            if (is_array($donnees['participants'])) {
                if (get_class($donnees["participants"][0]) == "Membre") {
                    $this->_participants = $donnees["participants"];

                }else {
                    $this->_participants = [];
                    foreach ($donnees["participants"] as $participant) {
                        $user = ["idMembre" => $participant];
                        $user = new Membre($user);
                        echo $user->idMembre();
                        array_push($this->_participants, $user);
                    }
                    $this->_participants = $donnees['participants'];
                }
            }
            else{
                echo "oui, oui. Baguette";
            }
            #explode(',', $donnees['participants'])
            var_dump($this->participants());
        }
        if (isset($donnees['tags'])) {
            if (is_array($donnees['tags'])) {
                $this->_tags = $donnees['tags'];
            } else {
                $this->_tags = explode(',', $donnees['tags']);
            }
        }
        if (isset($_SESSION['idMembre'])) { $this->_proprietaire = $donnees['idMembre']; }
    }

    // GETTERS //
    public function idProjet() { return $this->_idProjet;}
    public function nomProjet() { return $this->_titre;}
    public function description() { return $this->_description;}
    public function imgsUrls() { return isset($this->_imgsUrls) ? $this->_imgsUrls : [];}
    public function urlsDemos() { return isset($this->_urlsDemos) ? $this->_urlsDemos : [];}
    public function urlsSources() { return isset($this->_urlsSources) ? $this->_urlsSources : [] ;}
    public function publier() { return $this->_publier;}
    public function idContexte() { return $this->_idContexte;}
    public function idCategorie() { return $this->_idCategorie;}
    public function participants() { return $this->_participants;}
    public function tags() { return $this->_tags;}
    public function proprietaire() { return $this->_proprietaire;}

    // SETTERS //
    public function setIdProjet(int $idProjet) { $this->_idProjet = $idProjet; }
    public function setTitre(string $nomProjet) { $this->_titre = $nomProjet; }
    public function setDescription(string $description) { $this->_description = $description; }
    public function setImgsUrls(array $imgsUrls) { $this->_imgsUrls = $imgsUrls; }
    public function setUrlsDemos(array $urlsDemos) { $this->_urlsDemos = $urlsDemos; }
    public function setUrlsSources(array $urlsSources) {  $this->_urlsSources = $urlsSources ; }
    public function setPublier(bool $publier) { $this->_publier = $publier; }
    public function setIdContexte(int $idcontexte) { $this->_idcontexte = $idcontexte; }
    public function setIdCategorie(int $idcategorie) { $this->idCategorie = $idcategorie; }
    public function setIdMembre(array $idmembre) { $this->_idMembre = $idmembre; }
    public function setTags(array $tags) { $this->_tags = $tags; }
    public function setProprietaire(int $proprietaire) { $this->_proprietaire = $proprietaire; }
}