<?php

@require_once "Modules/commentaire.php";
@require_once "Models/membresManager.php";

class CommentaireManager{
    private $_db; // Instance de PDO
    private $_membreManager;

    public function __construct($db) {
        $this->_db = $db;
        $this->_membreManager = new MembreManager($db);
    }

    // CRUD //
    public function add(Commentaire $commentaire) {
        $commentaire->setDate(date("Y-m-d H:i:s"));
        $req = $this->_db->prepare('INSERT INTO pr_commentaire (idMembre, idProjet, contenu, date_commentaire) VALUES(:idMembre, :idProjet, :contenu, :date)');
        $stmp = $req->execute(array(
            'idMembre' => $commentaire->idMembre(),
            'idProjet' => $commentaire->idProjet(),
            'contenu' => $commentaire->contenu(),
            'date' => $commentaire->date()
        ));
        return $stmp;
    }

    public function del(int $idCommentaire) {
        $req = 'DELETE FROM pr_commentaire WHERE idCommentaire = :idCommentaire';
        $stmp = $this->_db->prepare($req);
        $stmp->execute(array(
            'idCommentaire' => $idCommentaire
        ));
        return $stmp->execute();
    }

    public function getList(Projet $projet) {
        $req = 'SELECT * FROM pr_commentaire WHERE idProjet = :idProjet order by date_commentaire desc limit 25';
        $stmp = $this->_db->prepare($req);
        $stmp->execute(array(
            'idProjet' => $projet->idProjet()
        ));
        $commentaires = array();
        while ($data = $stmp->fetch()) {
            $data["membre"] = $this->_membreManager->get($data["idMembre"]);
            $commentaires[] = new Commentaire($data);
        }
        return $commentaires;
    }

public function update(Commentaire $commentaire) {
        $req = $this->_db->prepare('UPDATE commentaires SET contenu = :contenu WHERE idCommentaire = :idCommentaire');
        $stmp = $req->execute(array(
            'contenu' => $commentaire->contenu(),
            'idCommentaire' => $commentaire->idCommentaire()
        ));
        $stmp->execute();
    }



}