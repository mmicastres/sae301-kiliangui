<?php
require_once "Models/membresManager.php";
require_once "Models/tagsManager.php";
require_once "Modules/contexts.php";
require_once "Modules/categories.php";
require_once "Modules/urls.php";
require_once "Models/urlsManager.php";
//require_once  "Models/commentaireManager.php";
class ProjetManager
{
    private $_membresManage;
    private $_urlsManager;
    private $_tagsManager;
    private $_commentaireManage;
    private $_db; // Instance de PDO - objet de connexion au SGBD

    /**
     * Constructeur = initialisation de la connexion vers le SGBD
     * @param PDO $db
     * @return rien
     */
    public function __construct($db)
    {
        $this->_db = $db;
        $this->_urlsManager = new UrlsManager($db);
        $this->_tagsManager = new TagsManager($db);
        $this->_membresManage = new MembreManager($db);
        $this->_commentaireManage = new CommentaireManager($db);
    }

    /**
     * ajout d'un projet dans la BD
     * @param Projet $projet à ajouter
     * @return int true si l'ajout a bien eu lieu, false sinon
     */
    public function add(Projet $projet){
            $stmt = $this->_db->prepare("SELECT max(idProjet) AS maximum FROM pr_projet");
            $stmt->execute();
            $projet->setIdProjet($stmt->fetchColumn()+1);

            // requete d'ajout dans la BD
            $req = "INSERT INTO pr_projet (idProjet,proprietaire,titre,description,publier,idContexte,idCategorie) VALUES (?,?,?,?,?,?,?)";
            $stmt = $this->_db->prepare($req);
            $res = $stmt->execute(array($projet->idProjet(), $_SESSION["idMembre"] , $projet->nomProjet(),$projet->description(),$projet->publier(),$projet->idContexte(),$projet->idCategorie()));

return $res;
}

public function like($idProjet, $idMembre){
    $req = "INSERT INTO pr_aime (idProjet,idMembre,aime) VALUES (?,?,?)";
    $stmt = $this->_db->prepare($req);
    $res = $stmt->execute(array($idProjet,$idMembre,1));
    return $res;
}
public function unlike($idProjet, $idMembre){
    $req = "DELETE FROM pr_aime WHERE idProjet = ? AND idMembre = ?";
    $stmt = $this->_db->prepare($req);
    $res = $stmt->execute(array($idProjet,$idMembre));
    return $res;
}
    public function modifier(){
        // MISE A JOUR DU PROJET
        $idProjet = $_POST['idProjet'];
        $projet = $this->get($idProjet);
        $titre = $_POST['titre'];
        $description = $_POST['description'];
        $publier = $projet->publier();
        $idContexte = $_POST['idContexte'];
        $idCategorie = $_POST['idCategorie'];
        $req = "UPDATE pr_projet SET titre = ?, description = ?, publier = ?, idContexte = ?, idCategorie = ? WHERE idProjet = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($titre,$description,$publier,$idContexte,$idCategorie,$idProjet));
        // Mise a jour des urls
        $this->_urlsManager->deleteAll($idProjet);
        $imgsUrls = json_decode($_POST["imgsUrls"]);
        $demosUrls = json_decode($_POST["demosUrls"]);
        $sourcesUrls = json_decode($_POST["sourcesUrls"]);
        foreach ($imgsUrls as $url) {
            $url = new Url(array("idProjet"=>$idProjet,"type"=>"img","url"=>$url));
            $this->_urlsManager->addUrl($url);
        }
        foreach ($demosUrls as $url) {
            $url = new Url(array("idProjet"=>$idProjet,"type"=>"demo","url"=>$url));
            $this->_urlsManager->addUrl($url);
        }
        foreach ($sourcesUrls as $url) {
            $url = new Url(array("idProjet"=>$idProjet,"type"=>"source","url"=>$url));
            $this->_urlsManager->addUrl($url);
        }
        $this->deleteAllParticipants($idProjet);
        $participants = json_decode($_POST["participants"]);
        foreach ($participants as $participant) {
            $this->addParticipant($idProjet,$participant);
        }

    }

    public function rechercheProjet($recherche){
        $idMembre = isset($_SESSION["idMembre"]) ? $_SESSION["idMembre"] : -1;
        $req = "SELECT pr_projet.*,(SELECT COUNT(pr_aime.idProjet) FROM pr_aime WHERE pr_aime.idProjet = pr_projet.idProjet) as likes FROM pr_projet WHERE (publier = 1 or proprietaire = ? ) AND (titre LIKE ? OR description LIKE ?)";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($idMembre,"%".$recherche."%","%".$recherche."%"));
        $projets = [];
        while ($data = $stmt->fetch()) {
            $projets[] = $this->completeProjet(new Projet($data));
        }
        return $projets;
    }


    public function deleteAllParticipants($idProjet){
        $req = "DELETE FROM pr_participer WHERE idProjet = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($idProjet));
    }

    /**
     * nombre de projets dans la base de données
     * @return int le nb de projets
     * @param rien
     */
    public function count():int
    {
        $stmt = $this->_db->prepare('SELECT COUNT(*) FROM pr_projet');
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * suppression d'un projet dans la base de données
     * @param Projet
     * @return boolean true si suppression, false sinon
     */
    public function delete(Projet $projet) : bool
    {
        // delete urls
        $this->_urlsManager->deleteAll($projet->idProjet());
        // delete participants
        $req = "DELETE FROM pr_participer WHERE idProjet = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($projet->idProjet()));
        // delete tags
        $req = "DELETE FROM pr_caracterise WHERE idProjet = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($projet->idProjet()));
        // delete aime
        $req = "DELETE FROM pr_aime WHERE idProjet = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($projet->idProjet()));
        $this->_commentaireManage->delList($projet);

        // delete projet

        $req = "DELETE FROM pr_projet WHERE idProjet = ?";
        $stmt = $this->_db->prepare($req);
        return $stmt->execute(array($projet->idProjet()));
    }

    /**
     * recherche dans la BD d'un projet à partir de son id
     * @param int $idProjet
     * @return Projet | bool
     * @param rien
     */
    public function get(int $idProjet)
    {
        $req = 'SELECT pr_projet.*,(SELECT COUNT(pr_aime.idProjet) FROM pr_aime WHERE pr_aime.idProjet = pr_projet.idProjet) as likes FROM pr_projet WHERE idProjet=?';
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($idProjet));
        $data = $stmt->fetch();
        if (!$data) return $data;
        $projet = new Projet($data);
        return $this->completeProjet($projet);
    }

    /**
     * Retourne l'ensemble des projets où un utilisateur est membre
     * @param int $idmembre
     * @return array tableau d'objets de type Projet
     */
    public function getListMembre(int $idmembre):array
    {
        $stmt = $this->_db->prepare('SELECT pr_projet.*,(SELECT COUNT(pr_aime.idProjet) FROM pr_aime WHERE pr_aime.idProjet = pr_projet.idProjet) as likes FROM pr_projet inner join pr_participer WHERE pr_participer.idProjet = pr_projet.idProjet AND  proprietaire=?');
        $stmt->execute(array($idmembre));
        $projets = [];
        while ($data = $stmt->fetch()) {
            $projets[] = $this->completeProjet(new Projet($data));
        }
        return $projets;
    }

    public function getListParticiper(int $idMembre):array
    {
        $req = 'SELECT pr_projet.*,(SELECT COUNT(pr_aime.idProjet) FROM pr_aime WHERE pr_aime.idProjet = pr_projet.idProjet) as likes FROM pr_projet inner join pr_participer WHERE (pr_participer.idProjet = pr_projet.idProjet AND idMembre=? OR proprietaire=?) GROUP BY pr_projet.idProjet';
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($idMembre,$idMembre));
        $projets=[];
        while ($data = $stmt->fetch()) {
            $data["idProjet"] = $data["0"];
            $projets[] = $this->completeProjet(new Projet($data));
        }
        return $projets;
    }


    public function addParticipant(int $idProjet, int $idMembre):bool
    {
        $req = "INSERT INTO pr_participer (idProjet,idMembre) VALUES (?,?)";
        $stmt = $this->_db->prepare($req);
        return $stmt->execute(array($idProjet,$idMembre));
    }

    /**
     * Retourne le projet completer de sont nombre de like, tags, et urls
     * @param Projet $projet
     * @return Projet
     */
    public function completeProjet(Projet $projet){
        $urls = $this->_urlsManager->listUrl($projet);
        $commantaires = $this->_commentaireManage->getList($projet);
        $imgs = array();
        $demos = array();
        $sources = array();
        foreach ($urls as $url) {
            if ($url->type() == "img") $imgs[] = $url;
            else if ($url->type() == "demo") $demos[] = $url;
            else if ($url->type() == "source") $sources[] = $url;
            $url_parsed = parse_url($url->url());
            $titre = $url_parsed["host"] ?? $url->url();
            $url->setTitre($titre);

        }
        // get tags
        $tags = $this->_tagsManager->listTag($projet);
        $projet->setTags($tags);
        $projet->setImgsUrls($imgs);
        $projet->setUrlsDemos($demos);
        $projet->setUrlsSources($sources);
        $projet->setParticipants($this->_membresManage->get_participants($projet->idProjet()));
        $projet->setCommentaires($commantaires);
        $projet->setLiked($this->liked($projet->idProjet()));

        // get the list of contextes
        $req = "SELECT * FROM pr_contexte WHERE idContexte = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($projet->idContexte()));
        $data = $stmt->fetch();
        $contexte = new Contexte($data);
        $projet->setContexte($contexte);
        // get the list of categories
        $req = "SELECT * FROM pr_categorie WHERE idCategorie = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($projet->idCategorie()));
        $data = $stmt->fetch();
        $categorie = new Categorie($data);
        $projet->setCategorie($categorie);


        return $projet;

    }
    public function liked($idProjet){
        $idMembre = isset($_SESSION["idMembre"]) ? $_SESSION["idMembre"] : -1;
        $req = "SELECT COUNT(*) FROM pr_aime WHERE idProjet = ? and idMembre = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($idProjet,$idMembre));
        return $stmt->fetch()[0];
    }



    function update($projet){
        $sql = "UPDATE pr_projet SET titre = :titre, description = :description, publier = :publier, idContexte = :idContexte, idCategorie = :idCategorie WHERE idProjet = :idProjet";
        $req = $this->_db->prepare($sql);
        $req->execute(array(
            'titre' => $projet->nomProjet(),
            'description' => $projet->description(),
            'publier' => $projet->publier(),
            'idContexte' => $projet->idContexte(),
            'idCategorie' => $projet->idCategorie(),
            'idProjet' => $projet->idProjet()
        ));

        $idProjet = $projet->idProjet();
        $imgsUrls = $projet->imgsUrls();
        $demosUrls = $projet->urlsDemos();
        $sourcesUrls = $projet->urlsSources();

        // update urls
        $this->_urlsManager->deleteAll($idProjet);
        for ($i=0; $i < count($imgsUrls); $i++) {
            // check that the $imgsUrls[$i] is a string
            if (is_string($imgsUrls[$i])) {
                $url = new Url(array("idProjet"=>$idProjet,"type"=>"img","url"=>$imgsUrls[$i]));
                $this->_urlsManager->addUrl($url);
            }else{
                $url = $imgsUrls[$i];

                $url->setIdProjet($idProjet);
                $this->_urlsManager->addUrl($url);
            }
        }
        for ($i=0; $i < count($demosUrls); $i++) {
            if (is_string($demosUrls[$i])) {
                $url = new Url(array("idProjet"=>$idProjet,"type"=>"demo","url"=>$demosUrls[$i]));
                $this->_urlsManager->addUrl($url);
            }else{
                $url = $demosUrls[$i];
                $url->setIdProjet($idProjet);
                $this->_urlsManager->addUrl($url);
            }
        }
        for ($i=0; $i < count($sourcesUrls); $i++) {
            if (is_string($sourcesUrls[$i])) {
                $url = new Url(array("idProjet"=>$idProjet,"type"=>"source","url"=>$sourcesUrls[$i]));
                $this->_urlsManager->addUrl($url);
            }else{
                $url = $sourcesUrls[$i];
                $url->setIdProjet($idProjet);
                $this->_urlsManager->addUrl($url);
        }
    }
        $this->_tagsManager->deleteAll($idProjet);
        foreach ($projet->tags() as $tag) {
            if (is_string($tag)){
                $tag = new Tag(array("intitule"=>$tag));
            }
            //$tag = new Tag(array("intitule"=>$tag));
            $this->_tagsManager->addTagToProjet($tag, $projet);
        }
        // update participants
        if (isset($_POST["participants"])) {
            $this->deleteAllParticipants($idProjet);
            for ($i=0; $i < count($_POST["participants"]); $i++) {
                $idMembre = $_POST["participants"][$i];
                $this->addParticipant($idProjet, $idMembre );
            }
        }
        if (!$projet->isParticipant()) $this->addParticipant($projet->idProjet(), $_SESSION["idMembre"]);

        return true;



    }

    public function deleteAllFromCatetorie(Categorie $categorie){
        // select all projet from categorie
        $req = "SELECT * FROM pr_projet WHERE idCategorie = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($categorie->idCategorie()));
        $projets = [];
        while ($data = $stmt->fetch()) {
            $projets[] = new Projet($data);
        }
        // delete all projet
        foreach ($projets as $projet) {
            $this->delete($projet);
        }
    }

    public function deleteAllFromContexte(Contexte $contexte){
        // select all projet from categorie
        $req = "SELECT * FROM pr_projet WHERE idContexte = ?";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array($contexte->idContexte()));
        $projets = [];
        while ($data = $stmt->fetch()) {
            $projets[] = new Projet($data);
        }
        // delete all projet
        foreach ($projets as $projet) {
            $this->delete($projet);
        }

    }


    /**
     * retourne l'ensemble des projets publier
     * @return array tableau d'objets de type Projet
     * @param rien
     */
    public function getListPublier():array
    {
        $stmt = $this->_db->prepare('SELECT pr_projet.*,(SELECT COUNT(pr_aime.idProjet) FROM pr_aime WHERE pr_aime.idProjet = pr_projet.idProjet) as likes FROM pr_projet WHERE publier=1');
        $stmt->execute();
        $projets = [];
        while ($data = $stmt->fetch()) {
            $projet = $this->completeProjet(new Projet($data));
            $projets[] = $projet;
        }
        return $projets;
    }
    public function getListAPublier():array
    {
        $stmt = $this->_db->prepare('SELECT pr_projet.*,(SELECT COUNT(pr_aime.idProjet) FROM pr_aime WHERE pr_aime.idProjet = pr_projet.idProjet) as likes FROM pr_projet WHERE publier=0');
        $stmt->execute();
        $projets = [];
        while ($data = $stmt->fetch()) {
            $projet = new Projet($data);
            $projet = $this->completeProjet($projet);
            $projets[] = $projet;
        }
        return $projets;
    }

    public function getListMembrePublier(int $idmembre):array
    {
        $stmt = $this->_db->prepare('SELECT pr_projet.*,(SELECT COUNT(pr_aime.idProjet) FROM pr_aime WHERE pr_aime.idProjet = pr_projet.idProjet) as likes FROM pr_projet WHERE publier=1 AND idmembre=?');
        $stmt->execute(array($idmembre));
        $projets = [];
        while ($data = $stmt->fetch()) {
            $projets[] = new Projet($data);
        }
        return $projets;
    }






    ## Outils de suppression de membres

    public function deleteAllParticipationsFromMembre($membre){
        $req = "DELETE FROM pr_participer WHERE idMembre = :idMembre";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array(":idMembre" => $membre->idMembre()));
    }


    public function deleteAllLikesFromMembre($membre){
        $req = "DELETE FROM pr_aime WHERE idMembre = :idMembre";
        $stmt = $this->_db->prepare($req);
        $stmt->execute(array(":idMembre" => $membre->idMembre()));
    }

}