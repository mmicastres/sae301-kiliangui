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
    private array $_commentaires;
    private int $_proprietaire;
    private int $_likes;
    private bool $_liked;

    //Initialisation d'un projet à partir des données
    public function __construct(array $donnees) {
        if (isset($donnees['idProjet'])) { $this->_idProjet = $donnees['idProjet']; }
        if (isset($donnees['titre'])) { $this->_titre = $donnees['titre']; }
        if (isset($donnees['description'])) { $this->_description = $donnees['description']; }
        if (isset($donnees['imgsUrls'])) {
            if (is_array($donnees['imgsUrls'])) {
                $this->_imgsUrls = $donnees['imgsUrls'];
            } else {
                $this->_imgsUrls = json_decode($donnees['imgsUrls']);
            }
        }
        if (isset($donnees['demosUrls'])) {
            if (is_array($donnees['demosUrls'])) {
                $this->_urlsDemos = $donnees['demosUrls'];
            } else {
                $this->_urlsDemos = json_decode($donnees['demosUrls']);
            }
        }
        if (isset($donnees['sourcesUrls'])) {
            if (is_array($donnees['sourcesUrls'])) {
                $this->_urlsSources = $donnees['sourcesUrls'];
            } else {
                $this->_urlsSources = json_decode($donnees['sourcesUrls']);
            }
        }
        if (isset($donnees['publier'])) { $this->_publier = $donnees['publier']; }
        if (isset($donnees['idContexte'])) { $this->_idContexte = intval($donnees['idContexte']); }
        if (isset($donnees['idCategorie'])) { $this->_idCategorie = intval($donnees['idCategorie']); }
        if (isset($donnees['participants'])) {
            if (is_array($donnees['participants'])) {
                if (is_string($donnees['participants'][0])) {
                    $data = explode(",",$donnees['participants'][0]);
                    $this->_participants = [];
                    foreach ($data as $participant) {
                        $user = ["idMembre" => $participant];
                        $user = new Membre($user);
                        array_push($this->_participants, $user);
                    }

                }else{



                    // Verifie si le tableau est un tableau d'id ou de Membre
                    if (get_class($donnees["participants"][0]) == "Membre") {
                        $this->_participants = $donnees["participants"];

                    }else {
                        $this->_participants = [];
                        foreach ($donnees["participants"] as $participant) {
                            $user = ["idMembre" => $participant];
                            $user = new Membre($user);
                            array_push($this->_participants, $user);
                        }
                        $this->_participants = $donnees['participants'];
                    }


                }
            }
        }
        if (isset($donnees['tags'])) {
            if (is_array($donnees['tags'])) {
                $this->_tags = $donnees['tags'];
            } else {
                $this->_tags = explode(',', $donnees['tags']);
            }
        }
        if (isset($donnees['commentaires'])) { $this->_commentaires = $donnees['commentaires']; }
        if (isset($donnees['proprietaire'])) { $this->_proprietaire = intval($donnees['proprietaire']); }
        if (isset($donnees["likes"])){ $this->_likes = intval($donnees["likes"]);}
        if (isset($donnees["liked"])){ $this->_liked = $donnees["liked"];}
    }


    // GETTERS //
    public function idProjet() { return $this->_idProjet;}
    public function nomProjet() { return $this->_titre;}
    public function description() { return $this->_description;}
    //Description courte
    public function courte() { return substr($this->_description, 0, 100)."[...]";}
    public function imgsUrls() { return isset($this->_imgsUrls) ? $this->_imgsUrls : [];}
    //Premiere image
    public function thumbnail() { return isset($this->_imgsUrls) ? $this->_imgsUrls[0] : "";}
    public function urlsDemos() { return isset($this->_urlsDemos) ? $this->_urlsDemos : [];}
    public function urlsSources() { return isset($this->_urlsSources) ? $this->_urlsSources : [] ;}
    public function publier() { return $this->_publier;}
    public function idContexte() { return $this->_idContexte;}
    public function idCategorie() { return $this->_idCategorie;}
    public function participants() { return isset($this->_participants) ? $this->_participants : [];}
    public function tags() { return $this->_tags;}
    public function tagsstr() {
        $tagsstr = "";
        foreach ($this->_tags as $tag) {
            $tagsstr .= $tag->intitule().", ";
        }
        return $tagsstr;
    }
    public function commentaires() { return isset($this->_commentaires) ? $this->_commentaires : [];}
    public function proprietaire() { return isset($this->_proprietaire) ? $this->_proprietaire : false;}
    public function isProprietaire() { return isset($_SESSION["idMembre"]) ? $this->_proprietaire == $_SESSION["idMembre"] : false  ;}
    public function likes() { return   $this->_likes;}
    //Detect si le projet est liké par l'utilisateur
    public function liked() { return   $this->_liked;}

    public function jsonSerialize(){
        $vars = get_object_vars($this);
        $vars["_url"] = "?action=projet&id=".$this->_idProjet;
        return $vars;
    }

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
    public function setParticipants(array $idmembre) { $this->_participants = $idmembre; }
    public function setTags(array $tags) { $this->_tags = $tags; }
    public function setCommentaires(array $commentaires) { $this->_commentaires = $commentaires; }
    public function setProprietaire(int $proprietaire) { $this->_proprietaire = $proprietaire; }
    public function setLikes(int $likes) { $this->_likes = $likes; }
    public function setLiked(bool $liked) { $this->_liked = $liked; }
}