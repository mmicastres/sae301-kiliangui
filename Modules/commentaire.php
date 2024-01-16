<?php

class Commentaire {
    private int $_idCommentaire;
    private string $_contenu;
    private int $_idMembre;
    private Membre $_membre;
    private bool $_proprietaire;
    private int $_idProjet;
    private string $_date;

    // contructeur
    public function __construct(array $data) {
        $this->_proprietaire=false;
        // initialisation d'un produit à partir d'un tableau de données
        if (isset($data['idCommentaire']))       { $this->_idCommentaire =       $data['idCommentaire']; }
        if (isset($data['contenu']))    { $this->_contenu =    $data['contenu']; }
        if (isset($data['idMembre']))    { $this->_idMembre =    $data['idMembre']; if($data["idMembre"] == $_SESSION["idMembre"]){$this->_proprietaire=true;}  }
        if (isset($data["membre"])){ $this->_membre = $data["membre"];}
        if (isset($data['idProjet']))    { $this->_idProjet =    $data['idProjet'];}
        if (isset($data['date_commentaire']))  { $this->_date =  $data['date_commentaire']; }
    }

    // GETTERS //
    public function idCommentaire()       { return $this->_idCommentaire;}
    public function contenu()    { return $this->_contenu;}
    public function idMembre()    { return $this->_idMembre;}
    public function membre()    { return $this->_membre;}
    public function proprietaire()    { return $this->_proprietaire;}
    public function idProjet()    { return $this->_idProjet;}
    public function date()  { return $this->_date;}

    // SETTERS //
    public function setIdCommentaire(int $id)             { $this->_idCommentaire = $id; }
    public function setContenu(string $contenu)       { $this->_contenu = $contenu; }
    public function setIdMembre(int $id)             { $this->_idMembre = $id; }
    public function setMembre(Membre $_membre)             { $this->_membre = $_membre; }
    public function setIdProjet(int $id)             { $this->_idProjet = $id; }
    public function setDate(string $date)         { $this->_date = $date; }

}
