<?php
use \RATWEB\DB\Query;
use \RATWEB\DB\Record;

include_once __DIR__.'/../models/tagmodel.php';
class Tag extends Controller {

	function __construct() {
        parent::__construct();
        $this->name = "tag";
        $this->model = new TagModel();
        $this->browserURL = 'index.php?task=tag.items';
        $this->addURL = 'index.php?task=tag.new';
        $this->editURL = 'index.php?task=tag.edit';
        $this->browserTask = 'tag.items';
	}
	
    /**
     * rekord ellenörzés
     * @param Record $record
     * @return string üres vagy hibaüzenet
     */
    protected function validator($record):string {
        $result = '';
        if (trim($record->name) == '') {
            $result .= 'NAME_REQUIRED<br />';
        }
        if (($record->id == $record->parent) & ($record->id > 0)) {
            $result .= 'PARENT_ERROR';
		}
        return $result;
    }
    
    /**
     * bejelentkezett user jogosult erre?
     * @param string $action new|edit|delete
     * @return bool
     */
    protected function  accessRight(string $action, $record):bool {
		if ($action == 'show') {
			return true;
		} else {
			return $this->logedAdmin;
		}	
    }

}
?>
