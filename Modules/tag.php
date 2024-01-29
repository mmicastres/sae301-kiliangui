<?php


class Tag
{

    private int $_idTag;
    private string $_intitule;

    public function __construct(array $donnees) {
        if (isset($donnees['idTag'])) { $this->_idTag = $donnees['idTag']; }
        if (isset($donnees['intitule'])) { $this->_intitule = $donnees['intitule']; }
    }

    // GETTERS //
    public function idTag() { return $this->_idTag;}
    public function intitule() { return $this->_intitule;}

    // SETTERS //
    public function setIdTag(int $idTag) { $this->_idTag = $idTag; }
    public function setIntitule(string $intitule) { $this->_intitule = $intitule; }


}