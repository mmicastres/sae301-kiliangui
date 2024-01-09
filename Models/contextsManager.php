<?php

class ContextsManager {

    private $_db; // Instance de PDO - objet de connexion au SGBD

    /**
     * Constructeur = initialisation de la connexion vers le SGBD
     */
    public function __construct($db) {
        $this->_db=$db;
    }

    public function listContext(){
        // SELECT * FROM `pr_contexte`
        $req = "SELECT * FROM pr_contexte";
        $stmt = $this->_db->prepare($req);
        $stmt->execute();
        $contextes = array();
        while ($data = $stmt->fetch()) {
            $contextes[] = new Contexte($data);
        }

        return $contextes;
    }


}