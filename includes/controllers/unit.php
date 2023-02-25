<?php
use \RATWEB\DB\Query;
use \RATWEB\DB\Record;

include_once __DIR__.'/../models/unitmodel.php';

/**
 * unit controller 
 * igényelt model (includes/models/unitmodel.php))
 *      methodusok: emptyRecord(), save($record), 
 *      getById($id), deleteById($id), getItems($page,$limit,$filter,$order), 
 *      getTotal($filter)
 * igényelt viewerek includes/views/unitbrowser, includes/views/unitform 
 *      a unitform legyen alkalmas show funkcióra is a record, loged, logedAdmin -tól függően
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
class Unit extends Controller {

	function __construct() {
		parent::__construct();
		// $this->model = new UnitModel();
        $this->name = "unit";
        $this->browserURL = 'index.php?task=unit.items';
        $this->addURL = 'index.php?task=unit.new';
        $this->editURL = 'index.php?task=unit.edit';
        $this->browserTask = 'unit.items';
        $this->model = new UnitModel();
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
			$result .= 'NAME_REQUERED<br>';
		}
		if ($record->code == '') {
			$result .= 'CODE_REQUERED<br>';
		}
		$recs = $this->model->getBy('code',$record->code);
		if (count($recs) > 0) {
			if ($recs[0]->id != $record->id) {
				$result .= 'CODE_EXISTS<br>';
			}
		}
        return $result;
    }
	
}


?>
