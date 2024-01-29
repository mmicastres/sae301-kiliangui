<?php


class CategorieController
{

    private $categorieManager;
    private $twig;

    public function __construct($db, $twig)
    {
        $this->categorieManager = new CategorieManager($db);
        $this->twig = $twig;
    }



    public function addCategorie()
    {
        if (!isset($_SESSION["idMembre"])) return;
        if (!isset($_POST["intitule"])) return;
        $categorie = new Categorie($_POST);

        if ($this->categorieManager->get($categorie) != null) return;
        $this->categorieManager->add($categorie);
    }

    public function modCategorie(){
        if (!isset($_SESSION["idMembre"]) || !isset($_SESSION["admin"])) header("Location: index.php");;
        if (!isset($_POST["intitule"])) header("Location: index.php?action=admin_espace");;
        $categorie = new Categorie($_POST);
        echo "the categorie : ";
        if ($this->categorieManager->getId($categorie) == null) return;
        echo "modifying categorie;";
        $this->categorieManager->update($categorie);
        header("Location: index.php?action=espaceAdmin");
    }

    public function deleteCategorie()
    {
        if (!isset($_SESSION["idMembre"])) return;
        if (!isset($_POST["intitule"])) return;
        $categorie = new Categorie($_POST);

        if ($this->categorieManager->get($categorie) == null) return;
        $this->categorieManager->delete($categorie);
    }

}