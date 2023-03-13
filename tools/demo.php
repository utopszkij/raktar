<?php
use \RATWEB\DB\Query;
use \RATWEB\DB\Record;

include_once __DIR__.'/../models/demomodel.php';

/**
 * demo controller 
 * igényelt model (includes/models/demomodel.php))
 *      methodusok: emptyRecord(), save($record), 
 *      getById($id), deleteById($id), getItems($page,$limit,$filter,$order), 
 *      getTotal($filter)
 * igényelt viewerek includes/views/demobrowser, includes/views/demoform 
 *      a demoform legyen alkalmas show funkcióra is a record, loged, logedAdmin -tól függően
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
class Demo extends Controller {

	function __construct() {
		parent::__construct();
		// $this->model = new DemoModel();
        $this->name = "demo";
        $this->browserURL = 'index.php?task=demo.items';
        $this->addURL = 'index.php?task=demo.new';
        $this->editURL = 'index.php?task=demo.edit';
        $this->browserTask = 'demo.items';
        $this->model = new DemoModel();
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
    
    /**
     * rekord készlet lekérdezés
     * GET|POST page, order, limit, filter, 
     * POST filter_name....
     */ 
    public function items($order = 1) {
		// képernyöről POST -ban érkező filter_name paraméterek
		// átalakitása 'name|value...' string formára
		$pFilter = [];
		foreach ($_POST as $fn => $fv) {
			if (substr($fn,0,7) == 'filter_') {
				$fv = $this->request->input($fn); // sql injection szürés
				$pFilter[] = substr($fn,7,100); 
				$pFilter[] = $fv; 
			}
		}
		if ($this->request->input('filter') == '') {
			$this->request->set('filter', implode('|',$pFilter));
		}
		parent::items();
	}
	
}


?>
