<?php
namespace controllers;

use micro\orm\DAO;
use micro\utils\RequestUtils;

use models;
use models\Lienweb;

use Ajax\semantic\html\collections\HtmlBreadcrumb;


/**
 * Controller UserController
 * @property JsUtils $jquery
 **/

class UserController extends ControllerBase
{
    /**
     * <h1>Description de la m�thode</h1> Utilisant <b>les Tags HTML</b> et {@literal <b> JavaDoc </b> }
     * Pour plus de d�tails, voir : {@link http://www.dvteclipse.com/documentation/sv/Export_HTML_Documentation.html DVT Documentation}
     * 
     * Initialise l'utilisateur connect� ainsi que son fond d'�cran (dont l'URL est enregistr� dans la BDD)
     * 
     * @author Matteo ETTORI
     * @version 1.0
     * {@inheritDoc}
     * @see \controllers\ControllerBase::initialize()
     */
    public function initialize(){
        $fond="";
        if(isset($_SESSION["user"])){
            $user=$_SESSION["user"];
            $fond=$user->getFondEcran();
            //echo $user->getLogin();
        }
        if(!RequestUtils::isAjax()){
            $this->loadView("main/vHeader.html",["fond"=>$fond]);
        }
    }
    
    /**
     * Affiche le menu de la page si un utilisateur normal est connect�
     * {@inheritDoc}
     * @see \micro\controllers\Controller::index()
     */
    public function index(){
        $semantic=$this->jquery->semantic();
        
        $img=$semantic->htmlImage("imgtest","assets/img/homepage_symbol.jpg","Image d'accueil","small");
        $menu=$semantic->htmlMenu("menu9");
        $menu->addItem("<h4 class='ui header'>Accueil</h4>");
        
        if(!isset($_SESSION["user"])) {
            $menu->addItem("<h4 class='ui header'>Connexion</h4>");
            $menu->setPropertyValues("data-ajax", ["", "connexion/UserController/submit"]);
            
            $frm=$this->jquery->semantic()->htmlForm("frm-search");
            $input=$frm->addInput("q");
            $input->labeled("Google");
            
            $frm->setProperty("action","https://www.google.fr/search?q=");
        } else {
            $moteur=DAO::getOne("models\Utilisateur","idMoteur=".$_SESSION["user"]->getMoteur());
            //var_dump($moteur->getMoteur()->getNom());
            
            $frm=$this->jquery->semantic()->htmlForm("frm-search");
            $input=$frm->addInput("q");
            $input->labeled($moteur->getMoteur()->getNom());
            
            $frm->setProperty("action",$moteur->getMoteur()->getCode());
            
            $title=$semantic->htmlHeader("header5",4);
            $title->asImage("https://semantic-ui.com/images/avatar2/large/patrick.png",$_SESSION["user"]->getNom()." ".$_SESSION["user"]->getPrenom());

            $menu->addItem("<h4 class='ui header'>Informations</h4>");
            $menu->addItem("<h4 class='ui header'>Favoris</h4>");
            $menu->addItem("<h4 class='ui header'>Moteur</h4>");
            $menu->addItem("<h4 class='ui header'>D�connexion</h4>");
            $menu->setPropertyValues("data-ajax", ["", "preferences/", "listeFavoris/", "moteur/", "deconnexion/UserController/index"]);
            
            $mess=$semantic->htmlMessage("mess3","Vous �tes d�sormais connect�, ".$_SESSION["user"]->getNom()." ".$_SESSION["user"]->getPrenom(). "!");
            $mess->addHeader("Bienvenue !");
            $mess->setDismissable();
        }
        
        $bc=new HtmlBreadcrumb("bc2", array("Accueil","Utilisateur"));
        $bc->setContentDivider(">");
        echo $bc;
        
        $frm->setProperty("method","get");
        $frm->setProperty("target","_new");
        $bt=$input->addAction("Rechercher");
        echo $frm;
        
        $menu->getOnClick("UserController/","#divUsers",["attr"=>"data-ajax"]);
        $menu->setVertical();
        
        $this->jquery->compile($this->view);
        $this->loadView("Utilisateur\index.html");
    }
    
    /**
     * Affiche/Masque les �l�ments du site � partir de boutons
     */
    public function elementsMasques() {
        // D�claration d'une nouvelle Semantic-UI
        $semantic=$this->jquery->semantic();
        
        // Affectation du langage fran�ais � la 'semantic'
        $semantic->setLanguage("fr");
        
        $btt1=$semantic->htmlButton("btt1","Activer l'image de fond");
        $btt1->onClick("$('body').css('background-image', 'url(". $_SESSION["user"]->getFondEcran() .")');");
        
        $btt2=$semantic->htmlButton("btt2","D�sactiver l'image de fond");
        $btt2->onClick("$('body').css('background-image', 'none');");
        
        $btt3=$semantic->htmlButton("btt3","Activer la couleur de fond");
        $btt3->onClick("$('body').css('background-color', 'red');");
        
        $btt4=$semantic->htmlButton("btt4","D�sactiver la couleur de fond");
        $btt4->onClick("$('body').css('background-color', 'white');");
        
        echo $btt1->compile($this->jquery);
        echo $btt2->compile($this->jquery);
        echo $btt3->compile($this->jquery);
        echo $btt4->compile($this->jquery);
        
        echo $this->jquery->compile();
    }
    
    /**
     * <h1>Description de la m�thode</h1>
     *
     * Affiche le moteur de recherche s�lectionn� par l'utilisateur.
     *
     * @author Matteo ETTORI
     * @version 1.0
     */
    public function afficheMoteur() {
        $moteur=DAO::getOne("models\Utilisateur","idMoteur=".$_SESSION["user"]->getMoteur());
        //var_dump($moteur->getMoteur()->getNom());
        
        $frm=$this->jquery->semantic()->htmlForm("frm-search");
        $input=$frm->addInput("q");
        $input->labeled($moteur->getMoteur()->getNom());
        
        $frm->setProperty("action",$moteur->getMoteur()->getCode());
        $frm->setProperty("method","get");
        $frm->setProperty("target","_new");
        $bt=$input->addAction("Rechercher");
        echo $frm;
    }
    
    /**
     * <h1>Description de la m�thode</h1>
     *
     * Cr�e une liste des liens web li�s � l'utilisateur sous forme de tableau.
     *
     * @author Matteo ETTORI
     * @version 1.0
     */
    private function _listeFavoris() {
        $semantic=$this->jquery->semantic();
        
        $liens=DAO::getAll("models\Lienweb","idUtilisateur=".$_SESSION["user"]->getId()." ORDER BY ordre ASC");
        
        $table=$semantic->dataTable("tblLiens", "models\Utilisateur", $liens);
        $table->setIdentifierFunction("getId");
        $table->setFields(["libelle","url"]);
        $table->setCaptions(["Nom du lien","URL", "Action"]);
        $table->addEditDeleteButtons(true,["ajaxTransition"=>"random","method"=>"post"]);
        $table->setUrls(["","UserController/editLink","UserController/deleteLink"]);
        $table->setTargetSelector("#divUsers");
        
        echo $table->compile($this->jquery);
        echo $this->jquery->compile();   
    }
    
    /**
     * <h1>Description de la m�thode</h1>
     *
     * Affiche la liste des liens web li�s � l'utilisateur sous forme de tableau.
     *
     * @author Matteo ETTORI
     * @version 1.0
     */
    public function listeFavoris() {
        // Affectation de _all � la classe actuelle de variable 'this'
        $this->_listeFavoris();
        
        // G�n�ration du JavaScript/JQuery en tant que variable � l'int�rieur de la vue
        $this->jquery->compile($this->view);
        
        // Affiliation � la vue d'URL 'sites\index.html'
        $this->loadView("Utilisateur\index.html");
    }
    
    /**
     * <h1>Description de la m�thode</h1>
     *
     * Affiche le formulaire d'ajout des donn�es des sites.
     *
     * @author Matteo ETTORI
     * @version 1.0
     */
    private function _formFavoris($liens, $action, $libelle, $url, $ordre){
        // D�claration d'une nouvelle Semantic-UI
        $semantic=$this->jquery->semantic();
        
        // Affectation du langage fran�ais � la 'semantic'
        $semantic->setLanguage("fr");
        
        // Variable 'form' affectant la 'semantic' locale au formulaire d'id 'frmSite' au param�tre '$site'
        $form=$semantic->dataForm("frmLink", $liens);
        
        // Envoi des param�tres du formulaire lors de sa validation
        $form->setValidationParams(["on"=>"blur", "inline"=>true]);
        
        // Envoi des champs de chaque �l�ment de la table 'Site' � 'form'
        $form->setFields(["libelle","url","ordre","submit"]);
        
        // Envoi des titres � chaque champ des �l�ments de la table 'Site' � 'table'
        $form->setCaptions(["Libelle","URL","Ordre","Valider"]);
        
        // Ajout d'un bouton de validation 'submit' de couleur verte 'green' r�cup�rant l'action et l'id du bloc '#divSites'
        $form->fieldAsSubmit("submit","green",$action,"#divUsers");
        
        // Chargement de la page HTML 'index.html' de la vue
        $this->loadView("Utilisateur\index.html");
        
        echo $form->compile($this->jquery);
        echo $this->jquery->compile();
    }
    
    
    /**
     * <h1>Description de la m�thode</h1>
     *
     * Cr�e le formulaire des pr�f�rences utilisateurs.
     * 
     * @param user     : Utilisateur connect�
     * @param action   : Action du bouton de validation
     * @param login    : Login r�cup�r� de l'utilisateur connect�
     * @param password : Mot de passe r�cup�r� de l'utilisateur connect�
     *
     * @author Matteo ETTORI
     * @version 1.0
     */
    private function _preferences($user, $action, $login, $password){
        // D�claration d'une nouvelle Semantic-UI
        $semantic=$this->jquery->semantic();
        
        // Affectation du langage fran�ais � la 'semantic'
        $semantic->setLanguage("fr");
        
        // Variable 'form' affectant la 'semantic' locale au formulaire d'id 'frmSite' au param�tre '$site'
        $form=$semantic->dataForm("frmUser", $user);
        
        // Envoi des param�tres du formulaire lors de sa validation
        $form->setValidationParams(["on"=>"blur", "inline"=>true]);
        
        // Envoi des champs de chaque �l�ment de la table 'Site' � 'form'
        $form->setFields(["login", "password\n", "elementsMasques", "fondEcran", "couleur\n", "ordre", "submit"]);
        
        // Envoi des titres � chaque champ des �l�ments de la table 'Site' � 'table'
        $form->setCaptions(["Login","Mot de passe","��l�ments masqu�s","Fond d'�cran","Couleur", "Ordre","Valider"]);
        
        // Ajout d'un bouton de validation 'submit' de couleur verte 'green' r�cup�rant l'action et l'id du bloc '#divSites'
        $form->fieldAsSubmit("submit", "green", $action, "#divUsers");
        
        // Chargement de la page HTML 'index.html' de la vue
        $this->loadView("Utilisateur\index.html");
        
        echo $form->compile($this->jquery);
        echo $this->jquery->compile();
    }
    
    
    /**
     * <h1>Description de la m�thode</h1>
     *
     * Affiche le formulaire des pr�f�rences utilisateurs.
     *
     * @author Matteo ETTORI
     * @version 1.0
     */
    public function preferences(){
        $id=$_SESSION["user"]->getId();
        $user=DAO::getOne("models\Utilisateur", $id);
        $this->_preferences($user, "UserController/updateUser/".$id."/Utilisateur", $user->getLogin(), $user->getPassword());
    }

    /**
     * <h1>Description de la m�thode</h1>
     *
     * Met � jour les donn�es de l'utilisateur connect�.
     * 
     * @param id : Identifiant r�cup�r� de l'utilisateur connect�
     *
     * @author Matteo ETTORI
     * @version 1.0
     */
    public function updateUser($id){
        $user=DAO::getOne("models\Utilisateur", $id);
        RequestUtils::setValuesToObject($user,$_POST);
        if(DAO::update($user)){
            echo "L'utilisateur ".$user->getLogin()." a �t� modifi�.";
            $_SESSION["user"] = $user;
            echo $this->jquery->compile($this->view);
            //var_dump($_SESSION["user"]);
        }
    }
    
    
    /**
     * <h1>Description de la m�thode</h1>
     *
     * Ex�cute de l'ajout d'un nouveau lien.
     *
     * @param id : Identifiant r�cup�r� de l'utilisateur connect�
     *
     * @author Matteo ETTORI
     * @version 1.0
     */
    public function newLink(){
        
        // Variable 'site' r�cup�rant toutes les donn�es d'un nouveau site
        $lien=new Lienweb();
        
        // Ex�cution de la requ�te d'insertion de toutes les valeurs entr�es dans le formulaire d'ajout d'un nouveau lien web
        RequestUtils::setValuesToObject($lien,$_POST);
        
        // Condition si l'insertion d'un nouveau site est ex�cut�e
        if(DAO::insert($lien)){
            // Affichage du message suivant
            echo "Le lien ".$user->getNom()." a �t� ajout�.";
        }
    }
    
    /**
     * <h1>Description de la m�thode</h1>
     *
     * Ex�cute de la suppression d'un lien.
     *
     * @param id : Identifiant r�cup�r� de l'utilisateur connect�
     *
     * @author Matteo ETTORI
     * @version 1.0
     */
    public function deleteLink($id){
        // Variable $liens r�cup�rant toutes les donn�es d'un site selon son id et le mod�le 'Site'
        $liens=DAO::getOne("models\Lienweb", "id=".$id);
        
        // Instanciation du mod�le 'Site' sur le site r�cup�r� et ex�cution de la requ�te de suppression
        $liens instanceof models\Lienweb && DAO::remove($liens);
        
        // Retour sur la page d'affichage de tous les sites
        $this->forward("controllers\UserController","listeFavoris");
    }
    
    /**
     * <h1>Description de la m�thode</h1>
     *
     * Ex�cute de la modification d'un lien.
     *
     * @param id : Identifiant r�cup�r� de l'utilisateur connect�
     *
     * @author Matteo ETTORI
     * @version 1.0
     */
    public function editLink($id){
        $liens=DAO::getOne("models\Lienweb", $id);
        $this->_formFavoris($liens,"UserController/updateLink/".$id."/Lienweb",$liens->getLibelle(),$liens->getUrl(),$liens->getOrdre());
    }
    
    /**
     * <h1>Description de la m�thode</h1>
     *
     * Ex�cute de la mise � jour d'un lien.
     *
     * @param id : Identifiant r�cup�r� de l'utilisateur connect�
     *
     * @author Matteo ETTORI
     * @version 1.0
     */
    public function updateLink($id){
        $liens=DAO::getOne("models\Lienweb", $id);
        RequestUtils::setValuesToObject($liens,$_POST);
        if(DAO::update($liens)){
            echo "Le lien ".$liens->getLibelle()." a �t� modifi�.";
        }
    }
    
    /**
     * <h1>Description de la m�thode</h1>
     *
     * Affiche la liste des moteurs disponibles sous forme de tableau.
     *
     * @author Matteo ETTORI
     * @version 1.0
     */
    public function moteur(){
        $semantic=$this->jquery->semantic();

        $moteurSelected=$_SESSION['user']->getMoteur(); // R�cup�ration du moteur selectionn�
        $moteurs=DAO::getAll("models\Moteur"); // R�cuperation de tout les moteurs

        $table=$semantic->dataTable("tblMoteurs", "models\Moteur", $moteurs); // Stockage des moteurs dans un tableau

        $table->setIdentifierFunction("getId"); // R�cup�ration de l'identifiant du moteur
        $table->setFields(["nom", "code"]); // Champs de la table 'moteur' � afficher
        $table->setCaptions(["Nom", "Code", "S�lectionner"]); // Titre des champs du moteur
        $table->setTargetSelector("#divUsers");
        
        // Diff�renciation du moteur d�j� selectionn� par rapport aux autres
        $table->addFieldButton("S�lectionner",false,function(&$bt,$instance) use($moteurSelected){
            if($instance->getId()==$moteurSelected->getId()){
                $bt->addClass("disabled");
            }else{
                $bt->addClass("_toSelect");
            }
        });
        $this->jquery->getOnClick("._toSelect", "UserController/selectionner","#divUsers",["attr"=>"data-ajax"]);
        echo $table->compile($this->jquery);
        echo $this->jquery->compile();
    }
    

    /**
     * <h1>Description de la m�thode</h1>
     *
     * S�lectionne le moteur pour le site concern�.
     *
     * @author Joffrey MARION
     * @version 1.0
     */
    public function selectionner()
    {
        $recupId = explode('/', $_GET['c']); // R�cup�ration de l'identifiant du moteur � s�lectionner avec un explode de l'URL

        $moteur=DAO::getOne("models\Moteur", "id=".$recupId[2]); // R�cup�ration du moteur � s�lectionner
        
        $_SESSION["user"]->setMoteur($moteur); // Modification du moteur du site
        
        $_SESSION["user"] instanceof models\Utilisateur && DAO::update($_SESSION["user"]); // Envoi de la requete modifiant le moteur s�lectionn� pour le site
        $this->forward("controllers\UserController","moteur"); // Retour vers l'index du controlleur
    }
}