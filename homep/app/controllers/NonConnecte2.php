<?php
namespace controllers;
use Ajax\semantic\html\elements\HtmlButton;
use Ubiquity\orm\DAO;

 /**
 * Controller NonConnecte2
 **/
class NonConnecte2 extends ControllerBase{

    private $site;
    private $utilisateur = 1;
    
    public function initialize(){
        parent::initialize();
        $this->site=DAO::getOne("models\Site",0);
    }
    
    /**
     * @route("/users")
     */
    
	public function index(){
	    
	    $liensPerso=DAO::getAll("models\Lienweb","idUtilisateur = ".$this->utilisateur);
	    $liens=DAO::getAll("models\Lienweb");
	    
	    $semantic=$this->jquery->semantic();
	  
	    
	    $bts=$this->jquery->semantic()->htmlButtonGroups("bts-liens");
	    $bts->fromDatabaseObjects($liens, function($lien){
	        $bt=new HtmlButton("",$lien->getLibelle(),"blue");
	        $bt->asLink($lien->getUrl(),"_new");
	        return $bt;
	    });
      
	    
	    $this->jquery->compile($this->view);
	    $this->loadView('nonConnecte.html');

	}
}