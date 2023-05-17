<?php
use \RATWEB\DB\Query;
use \RATWEB\DB\Record;

include_once __DIR__.'/../models/categorymodel.php';
class Category extends Controller {

	function __construct() {
        parent::__construct();
        $this->name = "category";
        $this->model = new CategoryModel();
        $this->browserURL = 'index.php?task=category.items';
        $this->addURL = 'index.php?task=category.new';
        $this->editURL = 'index.php?task=category.edit';
        $this->browserTask = 'category.list';
        $this->ckeditorFields = [];
	}
	
    /**
     * rekord ellenörzés
     * @param Record $record
     * @return string üres vagy hibaüzenet
     */
    protected function validator($record):string {
        $result = '';
        if (trim($record->name) == '') {
            $result .= 'Nem lehet üres!<br />';
        }
        if (($record->id == $record->parent) & ($record->id > 0)) {
            $result .= 'Nem lehet a tulajdonos önmaga!<br />';
        }
        $recs = $this->model->getBy('name',$record->name);
        if (count($recs) > 0) {
            if ($recs[0]->id != $record->id) {
                $result .= 'Már van ilyen!<br />'; 
            }
        }
        return $result;
    }
    
    /**
     * bejelentkezett user jogosult erre?
     * @param string $action new|edit|delete
     * @return bool
     */
    protected function  accessRight(string $action, $record):bool {
        return $this->logedAdmin;
    }

    /**
     * category tárolása POST -ban: id, nev
     */
    public function categorysave() {
        $record = new Record();
        $record->id = $this->request->input('id');
        $record->parent = $this->request->input('parent',0);
        $record->name = trim($this->request->input('name',''));
        $error = $this->validator($record);
        if ($error == '') {
            $this->save($record); 
            $this->session->set('errorMsg','');
            $this->session->set('successMsg','Tárolva');
        } else {
            $this->session->set('errorMsg',$error);
            if ($record->id == 0) {
                $this->new();
            } else {
                $this->edit();
            }
        }    
    }
  
    /**
     * category törlése GET-ben: id
     */
    public function categorydelete() {
        $category = $this->model->getById($this->request->input('id',0));
        if (isset($category->id)) {
            $this->delete();
            $db = new Query('blog_category');
            $db->where('category_id','=',$category->id)->delete();
        }    
    }    
}
?>
