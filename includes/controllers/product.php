<?php
use \RATWEB\DB\Query;
use \RATWEB\DB\Record;

include_once __DIR__.'/../models/productmodel.php';
include_once __DIR__.'/../uploader.php';

/**
 * product controller 
 * igényelt model (includes/models/productmodel.php))
 *      methodusok: emptyRecord(), save($record), 
 *      getById($id), deleteById($id), getItems($page,$limit,$filter,$order), 
 *      getTotal($filter)
 * igényelt viewerek includes/views/productbrowser, includes/views/productform 
 *      a productform legyen alkalmas show funkcióra is a record, loged, logedAdmin -tól függően
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
class Product extends Controller {

	function __construct() {
		parent::__construct();
		// $this->model = new ProductModel();
        $this->name = "product";
        $this->browserURL = 'index.php?task=product.items';
        $this->addURL = 'index.php?task=product.new';
        $this->editURL = 'index.php?task=product.edit';
        $this->browserTask = 'product.items';
        $this->model = new ProductModel();
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
		if ($record->sort_name == '') {
			$result = 'NAME_REQUERED';
		}
        return $result;
    }
    
    /**
     * Új item felvivő képernyő
     */
    public function new() {
        $item = $this->model->emptyRecord();
        if (!$this->accessRight('new',$item)) {
            $this->session->set('errorMsg','ACCESDENIED');
            $this->items();
        }
        if ($this->session->isset('oldRecord')) {
            $item = $this->session->input('oldRecord');
        }
        $this->browserURL = $this->request->input('browserUrl', $this->browserURL);
        $units = $this->model->getRecords('units','code');
        $storages = $this->model->getRecords('storages','code');
        $alltags = $this->model->getRecords('tags','name');
        view($this->name.'form',[
            "flowKey" => $this->newFlowKey(),
            "record" => $item,
            "attachments" => [],
            "units" => $units,
            "storages" => $storages,
            "alltags" => $alltags,
            "loged" => $this->loged,
            "logedName" => $this->loged,
            "logedAdmin" => (strpos($this->logedGroup,'admin') > 0),
            "previous" => $this->browserURL,
            "browserUrl" => $this->browserURL,
            "errorMsg" => $this->session->input('errorMsg','')
        ]);
        $this->session->delete('errorMsg');
    }

    /**
     * meglévő item edit/show képernyő
     * a viewernek a record, loged, loagedAdmin alapján vagy editor vagy show
     * képernyőt kell megjelenitenie
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
        
        // attachmentek olvasása
        $attachments = [];
        if (is_dir('images/upload/'.$record->id)) {
			$attachments = array_diff(scandir('images/upload/'.$record->id), array('.', '..'));
		}
        
        $this->browserURL = $this->request->input('browserUrl', $this->browserURL);
        $units = $this->model->getRecords('units','code');
        $storages = $this->model->getRecords('storages','code');
        $alltags = $this->model->getRecords('tags','name');
        view($this->name.'form',[
            "flowKey" => $this->newFlowKey(),
            "record" => $record,
            "attachments" => $attachments,
            "units" => $units,
            "storages" => $storages,
            "alltags" => $alltags,
            "logedAdmin" => (strpos($this->logedGroup,'admin') > 0),
            "loged" => $this->loged,
            "previous" => $this->browserURL,
            "browserUrl" => $this->browserURL,
            "errorMsg" => $this->session->input('errorMsg',''),
        ]);
        $this->session->delete('errorMsg');
    }
    
    public function save($record = '') {
			if (!$this->checkFlowKey('index.php?task=product.items')) {
				echo '<div class="alert alert-danger">Lejárt a munkamenet!</div>';
			}
			$record = $this->model->emptyRecord();
			foreach ($record as $fn => $fv) {
				$record->$fn = $this->request->input($fn);
			}
			$this->session->set('oldRecord',$record);
			$record->description = $this->request->input('description','',HTML);
			if ($this->accessRight('edit', $record)) {
				$error = $this->validator($record);
				if ($error == '') {
					$id = $this->model->save($record);
		            $this->session->delete('oldRecord');
					$record->id = $id;
					
					// kép file tárolás
					if (isset($_FILES['image'])) {
						if ($_FILES['image']['name'] != '') {
							$uploader = new Uploader();
							$res = $uploader->doUpload('image', 'images', 
												'product_'.$id.'.*', 
												['jpg','jpeg','png','gif']); 
							if ($res->error == '') {
								$record->image_link = 'images/'.$res->name;
								$this->model->save($record);
							} else {
								echo 'image upload error '.$res->error; exit();
							}					
						}
					}

					// kép file tárolás
					if (isset($_FILES['image2'])) {
						if ($_FILES['image2']['name'] != '') {
							$uploader = new Uploader();
							$res = $uploader->doUpload('image2', 'images', 
												'product_'.$id.'.*', 
												['jpg','jpeg','png','gif']); 
							if ($res->error == '') {
								$record->image_link = 'images/'.$res->name;
								$this->model->save($record);
							} else {
								echo 'image upload error '.$res->error; exit();
							}					
						}
					}

					// csatolt dokumentumok tárolása
					for ($i=0; $i<5; $i++) {
						if (isset($_FILES['attachment'.$i])) {
							if ($_FILES['attachment'.$i]['name'] != '') {
								$uploader = new Uploader();
								$res = $uploader->doUpload('attachment'.$i, 
													'images/upload/'.$record->id, 
													'', 
													['doc','pdf','docx','odt','txt']); 
								if ($res->error != '') {
									echo 'image upload error '.$res->error; exit();
								}					
							}
						}
					}
					
					$this->session->set('successMsg','SAVED');
					$this->items();
				} else {
					$this->session->set('errorMsg',$error);
					$this->items();
				}	
			} else {
				$this->session->set('errorMsg','ACCESDENIED');
				$this->items();
			}
			$this->session->set('successMsg','');
			$this->session->set('errorMsg','');
	} 
	
	/**
	 * attachment törlése
	 */ 
	public function delattach() {
		$id = $this->request->input('id','0');
		$attachment = $this->request->input('attachment','');
		unlink('images/upload/'.$id.'/'.$attachment);
		$this->edit();
		$this->edit();
	}

}


?>
