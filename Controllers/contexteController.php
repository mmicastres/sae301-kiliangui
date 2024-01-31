<?php


class ContexteController
{
    private $contextManager;
    private $twig;

    public function __construct($db, $twig)
    {
        $this->contextManager = new ContextsManager($db);
        $this->twig = $twig;
    }

    public function addContext()
    {
        if (!isset($_SESSION["admin"]) || $_SESSION["admin"] != 1)  header("Location: index.php");;
        if (!isset($_POST["identifiant"])) return;
        if (!isset($_POST["intitule"])) return;
        if (!isset($_POST["semestre"])) return;

        $context = new Contexte($_POST);

        $this->contextManager->add($context);
    }

    public function modContexte(){
        if (!isset($_SESSION["admin"]) || $_SESSION["admin"] != 1)  header("Location: index.php");;
        if (!isset($_POST["identifiant"])) header("Location: index.php?action=admin_espace");;
        if (!isset($_POST["intitule"])) header("Location: index.php?action=admin_espace");;
        if (!isset($_POST["semestre"])) header("Location: index.php?action=admin_espace");;
        $context = new Contexte($_POST);
        $this->contextManager->update($context);
        header("Location: index.php?action=espaceAdmin");
    }

    public function deleteContexte(){
        if (!isset($_SESSION["idMembre"]) || !isset($_SESSION["admin"])) header("Location: index.php");;
        if (!isset($_POST["identifiant"])) header("Location: index.php?action=admin_espace");;
        if (!isset($_POST["intitule"])) header("Location: index.php?action=admin_espace");;
        if (!isset($_POST["semestre"])) header("Location: index.php?action=admin_espace");;
        if (!isset($_SESSION["admin"]) || $_SESSION["admin"] != 1)  header("Location: index.php");;
        $context = new Contexte($_POST);
        $this->contextManager->delete($context);
        header("Location: index.php?action=espaceAdmin");
    }

}