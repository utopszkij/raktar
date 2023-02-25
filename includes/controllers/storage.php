<?php
use \RATWEB\DB\Query;
use \RATWEB\DB\Record;

include_once __DIR__.'/../models/storagemodel.php';
include_once __DIR__.'/../uploader.php';

/**
 * storage controller 
 * igényelt model (includes/models/storagemodel.php))
 *      methodusok: emptyRecord(), save($record), 
 *      getById($id), deleteById($id), getItems($page,$limit,$filter,$order), 
 *      getTotal($filter)
 * igényelt viewerek includes/views/storagebrowser, includes/views/storageform 
 *      a storageform legyen alkalmas show funkcióra is a record, loged, logedAdmin -tól függően
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
class Storage extends Controller {

	function __construct() {
		parent::__construct();
		// $this->model = new StorageModel();
        $this->name = "storage";
        $this->browserURL = 'index.php?task=storage.items';
        $this->addURL = 'index.php?task=storage.new';
        $this->editURL = 'index.php?task=storage.edit';
        $this->browserTask = 'storage.items';
        $this->model = new StorageModel();
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
    
    public function save($record = '') {
		$record = $this->model->emptyRecord();
		foreach ($record as $fn => $fv) {
			$record->$fn = $this->request->input($fn);
		}
		$this->session->set('oldRecord',$record);			
		if ($this->accessRight('edit', $record)) {
			if (!$this->checkFlowKey('index.php?task=storage.list')) {
				echo '<div class="alert alert-danger">Lejárt a munkamenet!</div>';
			}
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
											'storage_'.$id.'.*', 
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
											'storage_'.$id.'.*', 
											['jpg','jpeg','png','gif']); 
						if ($res->error == '') {
							$record->image_link = 'images/'.$res->name;
							$this->model->save($record);
						} else {
							echo 'image upload error '.$res->error; exit();
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
	
}


?>
