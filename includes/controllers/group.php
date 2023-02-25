<?php
use \RATWEB\DB\Query;
use \RATWEB\DB\Record;

include_once __DIR__.'/../models/groupmodel.php';

/**
 * group rekord kezelő osztály
 * views: groupform, groupbrowser
 */
class Group extends Controller {

	function __construct() {
		parent::__construct();
        $this->model = new GroupModel();
		$this->model = new GroupModel();
        $this->name = "group";
        $this->browserTask = 'group.groups';
        $this->browserURL = 'index.php?task=group.items';
        $this->addURL = 'index.php?task=group.new';
        $this->editURL = 'index.php?task=group.edit';
	}

    /**
     * loged user hozzáférés ellenörzése
     * @param string $action  'new'|'edit'|'delete'|'show'
     * @param RecordObject $record
     * @return bool
     */    
    protected function accessRight(string $action, $record): bool {
        $result = false;
        if ($action == 'show') {
            $result = true;
        } else if (isAdmin()) {
            $result = true;
        }
        if (($action == 'delete') & ($record->name == 'admin')) {
            $result = false;
        }
        return $result;
    }

    /**
     * rekord ellenörzés (update vagy insert előtt)
     * @param RecordObject $record
     * @return string üres ha minden OK, egyébként hibaüzenet
     */    
    protected function validator($record): string {
        $result = '';
        if ($record->name == '') {
            $result = 'GROUP_NAME_REQUED';
        } else {
            $recs = $this->model->getBy('name',$record->name);
            if (count($recs) > 0) {
                if ($recs[0]->id != $record->id) {
                    $result = 'GROUP_EXISTS<br>'.$record->name;
                }
            } 
        }
        return $result;
    }

}


?>
