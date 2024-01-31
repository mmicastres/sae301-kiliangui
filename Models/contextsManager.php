<?php

class ContextsManager {

    private $_db; // Instance de PDO - objet de connexion au SGBD
    private $_projetManager;

    /**
     * Constructeur = initialisation de la connexion vers le SGBD
     */
    public function __construct($db) {
        $this->_db=$db;
        $this->_projetManager = new projetManager($db);
    }

    public function add(Contexte $contexte) {
        $req = "INSERT INTO pr_contexte (identifiant, intitule, semestre) VALUES (:identifiant, :intitule, :semestre)";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array(":identifiant" => $contexte->identifiant(), ":intitule" => $contexte->intitule(), ":semestre" => $contexte->semestre()));
        header("Location: index.php?action=espaceAdmin");
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
    public function getById($contextId){
        $req = "SELECT * FROM pr_contexte WHERE idContexte = :idContexte";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array(":idContexte" => $contextId));
        $data = $stmt->fetch();
        if ($data == null) return null;
        return new Contexte($data);
    }

    public function update(Contexte $contexte){
        $req = "UPDATE pr_contexte SET identifiant = :identifiant, intitule = :intitule, semestre = :semestre WHERE idContexte = :idContexte";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array(":identifiant" => $contexte->identifiant(), ":intitule" => $contexte->intitule(), ":semestre" => $contexte->semestre(), ":idContexte" => $contexte->idContexte()));
    }

    public function delete(Contexte $contexte){
        $this->_projetManager->deleteAllFromContexte($contexte);

        $req = "DELETE FROM pr_contexte WHERE idContexte = :idContexte";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array(":idContexte" => $contexte->idContexte()));
    }


}