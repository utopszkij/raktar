<?php
use \RATWEB\DB\Query;
use \RATWEB\DB\Record;

include_once __DIR__.'/../models/eventtypemodel.php';


/**
 * eventtype controller 
 * igényelt model (includes/models/eventtypemodel.php))
 *      methodusok: emptyRecord(), save($record), 
 *      getById($id), deleteById($id), getItems($page,$limit,$filter,$order), 
 *      getTotal($filter)
 * igényelt viewerek includes/views/eventtypebrowser, includes/views/eventtypeform 
 *      a eventtypeform legyen alkalmas show funkcióra is a record, loged, logedAdmin -tól függően
 *      a browser jelenitse meg szükség szerint az errorMsg, successMsg adatot is!
 *      a form jelenitse meg szükség szerint az errorMsg adatot is, a rekord mezőivel azonos nevü
 *             input vagy select elemeket tartalmazzon 
 *      (beleértve az id -t is)
 * igényelt session adatok: loged,logedName, logedGroup
 *      opcionálisan: errorMsg, successMsg
 * 
 * A taskok public function -ként legyenek definiálva 
 *   standart taskok: items, edit, new, save, delete.
 */
class Eventtype extends Controller {

	function __construct() {
		parent::__construct();
		// $this->model = new EventtypeModel();
        $this->name = "eventtype";
        $this->browserURL = 'index.php?task=eventtype.items';
        $this->addURL = 'index.php?task=eventtype.new';
        $this->editURL = 'index.php?task=eventtype.edit';
        $this->browserTask = 'eventtype.items';
        $this->model = new EventtypeModel();
        
	}

    /**
     * loged user hozzáférés ellenörzése
     * @param string $action  'new'|'edit'|'delete'|'show'
     * @param RecordObject $record
     * @return bool
     */    
    protected function accessRight(string $action, $record): bool {
		// $this->loged  -- a bejelentkezett user azonosítója
		// $this->logedGroup -- '[group1,group2,...]'
		$result = true;
		if (($action == 'new') | ($action == 'edit') | ($action == 'delete')) {
			if ($this->loged <= 0) {
				$result = false;
			}
			if (strpos($this->logedGroup,'admin') <= 0) {
				$result = false;
			}
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
			$result = 'NAME_REQUERED';
		}
        return $result;
    }

}


?>
