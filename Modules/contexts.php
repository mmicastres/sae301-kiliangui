<?php

class Contexte {
    private int $_idContexte;
    private string $_identifiant;
    private string $_intitule;
    private string $_semestre;

    // contructeur
    public function __construct(array $data) {
        // initialisation d'un produit à partir d'un tableau de données
        if (isset($data['idContexte']))       { $this->_idContexte =       intval($data['idContexte']); }
        if (isset($data['identifiant']))    { $this->_identifiant =    $data['identifiant']; }
        if (isset($data['intitule']))    { $this->_intitule =    $data['intitule']; }
        if (isset($data['semestre']))  { $this->_semestre =  intval($data['semestre']); }
    }

    // GETTERS //
    public function idContexte()       { return $this->_idContexte;}
    public function identifiant()    { return $this->_identifiant;}
    public function intitule()    { return $this->_intitule;}
    public function semestre()  { return $this->_semestre;}

    // SETTERS //
    public function setIdContexte(int $id)             { $this->_idContexte = $id; }
    public function setIdentifiant(string $identifiant)       { $this->_identifiant = $identifiant; }
    public function setIntitule(string $intitule)   { $this->_intitule= $intitule; }
    public function setSemestre(string $semestre)         { $this->_semestre = $semestre; }

}

?>