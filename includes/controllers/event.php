<?php
use \RATWEB\DB\Query;
use \RATWEB\DB\Record;

include_once __DIR__.'/../models/eventmodel.php';

/**
 * event controller 
 * igényelt model (includes/models/eventmodel.php))
 *      methodusok: emptyRecord(), save($record), 
 *      getById($id), deleteById($id), getItems($page,$limit,$filter,$order), 
 *      getTotal($filter)
 * igényelt viewerek includes/views/eventbrowser, includes/views/eventform 
 *      a eventform legyen alkalmas show funkcióra is a record, loged, logedAdmin -tól függően
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
class Event extends Controller {

	function __construct() {
		parent::__construct();
		// $this->model = new EventModel();
        $this->name = "event";
        $this->browserURL = 'index.php?task=event.items';
        $this->addURL = 'index.php?task=event.new';
        $this->editURL = 'index.php?task=event.edit';
        $this->browserTask = 'event.items';
        $this->model = new EventModel();
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
        return $result;
    }

    /**
     * editor képernyő
     * GET: id
     */ 
    public function edit() {
        $id = $this->request->input('id',0);
        $record = $this->model->getById($id);
        if (!$this->accessRight('edit',$record) & !$this->accessRight('show',$record)) {
            $this->session->set('errorMsg','ACCESDENIED');
            $this->items();
        }
        if ($this->session->isset('oldRecord')) {
            $record = $this->session->input('oldRecord');
        }
        $this->browserURL = $this->request->input('browserUrl', $this->browserURL);
        $eventtypes = $this->model->getEventTypes($record->direction);
        $product = $this->model->getProduct($record->product_id);
        view($this->name.'form',[
            "flowKey" => $this->newFlowKey(),
            "record" => $record,
            "product" => $product,
            "eventtypes" => $eventtypes,
            "logedAdmin" => (strpos($this->logedGroup,'admin') > 0),
            "loged" => $this->loged,
            "previous" => $this->browserURL,
            "browserUrl" => $this->browserURL,
            "errorMsg" => $this->session->input('errorMsg',''),
        ]);
        $this->session->delete('errorMsg');
		
	}
   
    /**
     * beérkezés képernyő
     * GET: product_id
     */ 
    public function plus() {
        $record = $this->model->emptyRecord();
        $record->product_id = $this->request->input('product_id',0,INTEGER);
        $record->direction = 'INPUT';
        if (!$this->accessRight('new',$record) & !$this->accessRight('show',$record)) {
            $this->session->set('errorMsg','ACCESDENIED');
            $this->items();
        }
        if ($this->session->isset('oldRecord')) {
            $record = $this->session->input('oldRecord');
        }
        $this->browserURL = $this->request->input('browserUrl', $this->browserURL);
        $eventtypes = $this->model->getEventTypes('INPUT');
        $product = $this->model->getProduct($record->product_id);
        view($this->name.'form',[
            "flowKey" => $this->newFlowKey(),
            "record" => $record,
            "product" => $product,
            "eventtypes" => $eventtypes,
            "logedAdmin" => (strpos($this->logedGroup,'admin') > 0),
            "loged" => $this->loged,
            "previous" => $this->browserURL,
            "browserUrl" => $this->browserURL,
            "errorMsg" => $this->session->input('errorMsg',''),
        ]);
        $this->session->delete('errorMsg');
	}

    /**
     * beszerzendő képernyő
     * GET: product_id
     */ 
    public function shoping() {
        $record = $this->model->emptyRecord();
        $record->product_id = $this->request->input('product_id',0,INTEGER);
        $record->direction = 'SHOPING';
        if (!$this->accessRight('new',$record) & !$this->accessRight('show',$record)) {
            $this->session->set('errorMsg','ACCESDENIED');
            $this->items();
        }
        if ($this->session->isset('oldRecord')) {
            $record = $this->session->input('oldRecord');
        }
        $this->browserURL = $this->request->input('browserUrl', $this->browserURL);
        $eventtypes = $this->model->getEventTypes('SHOPING');

        $product = $this->model->getProduct($record->product_id);
        view($this->name.'form',[
            "flowKey" => $this->newFlowKey(),
            "record" => $record,
            "product" => $product,
            "eventtypes" => $eventtypes,
            "logedAdmin" => (strpos($this->logedGroup,'admin') > 0),
            "loged" => $this->loged,
            "previous" => $this->browserURL,
            "browserUrl" => $this->browserURL,
            "errorMsg" => $this->session->input('errorMsg',''),
        ]);
        $this->session->delete('errorMsg');
	}


    /**
     * kiadás képernyő
     * GET: product_id
     */ 
    public function minus() {
        $record = $this->model->emptyRecord();
        $record->product_id = $this->request->input('product_id',0,INTEGER);
        $record->direction = 'OUTPUT';
        if (!$this->accessRight('new',$record) & !$this->accessRight('show',$record)) {
            $this->session->set('errorMsg','ACCESDENIED');
            $this->items();
        }
        if ($this->session->isset('oldRecord')) {
            $record = $this->session->input('oldRecord');
        }
        $this->browserURL = $this->request->input('browserUrl', $this->browserURL);
        $eventtypes = $this->model->getEventTypes('OUTPUT');
        $product = $this->model->getProduct($record->product_id);
        view($this->name.'form',[
            "flowKey" => $this->newFlowKey(),
            "record" => $record,
            "product" => $product,
            "eventtypes" => $eventtypes,
            "logedAdmin" => (strpos($this->logedGroup,'admin') > 0),
            "loged" => $this->loged,
            "previous" => $this->browserURL,
            "browserUrl" => $this->browserURL,
            "errorMsg" => $this->session->input('errorMsg',''),
        ]);
        $this->session->delete('errorMsg');
	}
	
	public function save($record = '') {
		if ($record == '') {
			$record = $this->model->emptyRecord();
			foreach ($record as $fn => $fv) {
				$record->$fn = $this->request->input($fn, $fv);
			}	
		}
        if (!$this->checkFlowKey($this->browserURL)) {
            $this->session->set('flowKey','used');
            $this->session->set('errorMsg','FLOWKEY_ERROR');
            $this->items();
            return;
        }
        $this->session->set('flowKey','used');
        $this->session->set('oldRecord',$record);
        $this->browserURL = $this->request->input('browserUrl',$this->browserURL);
        if ($record->id == 0) {
            if (!$this->accessRight('new',$record)) {
                $this->session->set('errorMsg','ACCESDENIED');
                $this->items();
            }
        } else {
            if (!$this->accessRight('edit',$record)) {
                $this->session->set('errorMsg','ACCESDENIED');
                $this->items();
            }
        }   
        $error = $this->validator($record);
        if ($error != '') {
            $this->session->set('errorMsg',$error);
            if ($record->id == 0) {
				$this->new();
				return;
            } else {
				$this->edit();
				return;
            } 
        } else {
            $this->session->delete('oldRecord');
            if (($record->direction == 'OUTPUT') & ($record->quantity > 0)) {
				$record->quantity = 0 - $record->quantity;
			}
            $this->model->save($record);
            if ($this->model->errorMsg == '') {
                $this->session->delete('errorMsg');
                $this->session->set('successMsg','SAVED');
                echo '<script>
                location="index.php?task=product.items&successMsg=SAVED";
                </script>
                ';
            } else {
                echo $this->model->errorMsg; exit();
            }
        }
	}
	
       
}


?>
