<?php
use \RATWEB\DB\Query;
use \RATWEB\DB\Record;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require 'vendor/phpmailer/src/Exception.php';
require 'vendor/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/src/SMTP.php';

include_once __DIR__.'/../models/productmodel.php';
include_once __DIR__.'/../models/tagmodel.php';
include_once __DIR__.'/../uploader.php';
include_once __DIR__.'/../urlprocess.php';

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
        $this->ckeditorFields = ['description'];
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
		//+ duplaság ellenörzés
		$recs = $this->model->getBy('sort_name',$record->sort_name); // megpróbál olvasni az adatbázisból ilyen nevű rekordot.
		if (count($recs) > 0) {
			// ha van ilyen nevű rekord és az nem azonos a most javítottal akkor ez duplikátum!
			// ez esetben hibajelzést kell visszadnia ennek a rutinnak.
			if ($recs[0]->id != $record->id) {
				$result = 'NAME_EXISTS';
			}
		}
		//- duplaság ellenörzés
		
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
        
        $record->description2 = urlprocess($record->description);
        
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
                    // v2.2.2
                    $newRecord = ($record->id == 0);
					$id = $this->model->save($record);
		            $this->session->delete('oldRecord');
					$record->id = $id;
					// kép file tárolás
					if (isset($_FILES['image'])) {
						if ($_FILES['image']['name'] != '') {
							$uploader = new Uploader();
							$res = $uploader->doUpload('image', 'images', 
												'product_'.$id.'.*', 
												['jpg','jpeg','png','gif','webp']); 
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
												['jpg','jpeg','png','gif','webp']); 
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
													['doc','pdf','docx','odt','txt','xlsx']); 
								if ($res->error != '') {
									echo 'image upload error '.$res->error; exit();
								}					
							}
						}
					}
					$this->session->set('successMsg','SAVED');
                    // v.2.2.2 új felvitel után kezdő készlet megadás
                    if ($newRecord) {
                        // kezdő készlet megadás
                        echo 'REDIRECT '.SITEURL.'/index.php?task=event.plus&product_id='.$id.'
                        <script>
                        location="'.SITEURL.'/index.php?task=event.plus&product_id='.$id.'";
                        </script>
                        </body>
                        </html>
                        ';
                        exit();
                    } else {
					  $this->items();
                    }  
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
	}

	/**
     * browser
     * GET| POST: page,order,filter,limit
     *     filter= 'filter_name|value|filter_code|value|filter_tag|value'
	 * (felülírás oka: tags -ot is át ad a viewernek, 
     *  $fullTree paramétert is a a modelnek)
     */
    public function items($order = 1) {

        // csak bejelentkezett user használhatja
        //if ($this->loged <= 0) {
        //    echo '<div class="alert alert-danger">Ezt a funkciót csak bejelenkzett felhasználók használhatják.</div>';
        //    return;
        //}
        

        // paraméter olvasása get vagy sessionból
        $page = $this->session->input($this->name.'page',1);
        $page = $this->request->input('page',$page);
        $limit = round((int)$_SESSION['screen_height'] / 80);
        $limit = $this->session->input($this->name.'limit',$limit);
        $limit = $this->request->input('limit',$limit);
        $order = $this->session->input($this->name.'order',$order);
        $order = $this->request->input('order',$order);

		// filter kezelés	
        $sFilter = $this->session->input($this->name.'filter',''); // 'name|value...'
        $sFilterArray = $this->filterParse($sFilter); // [name => value,...]
        $rFilter = $this->request->input('filter'); // 'name|value...' vagy 'all'
        if ($rFilter == 'all') {
			$sFilterArray = [];
			$filter = '';
		} else {
			$rFilterArray = $this->filterParse($rFilter); // [name => value,...]
			foreach ($rFilterArray as $fn => $fv) {
				$sFilterArray[$fn] = $fv;
			}
			$filter = $this->filterToStr($sFilterArray); // 'name|value...'
		}
        
		// adatok a paginátor számára
        $total = $this->model->getTotal($filter, true);
        $pages = [];
        $p = 1;
        while ((($p - 1) * $limit) < $total) {
            $pages[] = $p;
            $p++;
        }
        $p = $p - 1;

        // hibás paraméter kezelés
        if ($page > $p) { 
            $page = $p; 
        }
        if ($page < 1) {
            $page = 1;
        }

        // paraméter tárolás sessionba
        $this->session->set($this->name.'page',$page);
        $this->session->set($this->name.'limit',$limit);
        $this->session->set($this->name.'filter',$filter);
        $this->session->set($this->name.'order',$order);
        
        // rekordok olvasása az adatbázisból
        $items = $this->model->getItems($page,$limit,$filter,$order,true);

		//+ tags -ot is átad a viewer -nek
		$tagModel = new TagModel();
		$tags = $tagModel->getItems(1, 9999, '', '');
		//-

        // megjelenítés
        view($this->name.'browser',[
            "items" => $items,
			"tags" => $tags,
            "page" => $page,
            "total" => $total,
            "pages" => $pages,
            "task" => $this->browserTask,
            "filter" => $filter,
            "loged" => $this->loged,
            "logedName" => $this->loged,
            "logedAdmin" => (strpos($this->logedGroup,'admin') > 0),
            "previous" => SITEURL,
            "browserUrl" => $this->browserURL,
            "errorMsg" => $this->session->input('errorMsg',''),
            "successMsg" => $this->session->input('successMsg','')
        ]);
        $this->session->delete('errorMsg');
        $this->session->delete('successMsg');
        $this->session->delete('oldRecord');
    }
    
    /**
     * cikk lista küldése emailben
     */ 
    public function sendMail() {
        // rekordok olvasása az adatbázisból
        $items = $this->model->getItems(1,100000,'','sort_name',true);
        // html mail body összeállítása
        $mailBody = '<style type="text/css"> 
        table {font-size:14pt}
        td { border-style:solid; border-width:1px } 
        </style>';
        $mailBody .= '<h2>Raktár készlet</h2><table>';
        foreach ($items as $item) {
				$mailBody .= '<tr>';
				$mailBody .= '<td>'.$item->id.'</td>';
				$mailBody .= '<td>'.$item->sort_name.'</td>';
				$mailBody .= '<td>'.$item->stock.'</td>';
				$mailBody .= '<td>'.$item->unit.'</td>';
				if (($item->optional_stock > 0) & (($item->unit == 'l') | ($item->unit == 'ml'))) {
					$mailBody .= '<td>'.round(100*$item->stock/$item->optional_stock).'%</td>';
				} else {
					$mailBody .= '<td></td>';
				}
				if ($item->stock < $item->warning_stock) {
					$mailBody .= '<td>Szerezd be!</td>';
				} else {
					$mailBody .= '<td></td>';
				}
				$mailBody .= '</tr>';
		}
		$mailBody .= '</table>'.date('Y-m-d H:i');
        
        // email küldés az adminnak MAIL_USERNAME cimre
            // levél küldés;
            $this->mailer =  new PHPMailer(true);
			$this->mailer->isSMTP();                                //Send using SMTP
			$this->mailer->Host       = MAIL_HOST;                  //Set the SMTP server to send through
			$this->mailer->SMTPAuth   = true;                       //Enable SMTP authentication
			$this->mailer->Username   = MAIL_USERNAME;                  //SMTP username
			$this->mailer->Password   = MAIL_PASSWORD;              //SMTP password
			$this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;//Enable implicit TLS encryption
			$this->mailer->Port       = MAIL_PORT;                  //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
			$this->mailer->CharSet    = 'utf-8';            

            $this->mailer->setFrom(MAIL_FROM_ADDRESS);
            $this->mailer->addAddress(ADMINEMAIL);
            $this->mailer->isHTML(true);          
            $this->mailer->Subject = 'Raktar keszlet';
            $this->mailer->Body    = $mailBody;
            $result = $this->mailer->send();
            if ($result) {
                $this->session->set('successMsg','EMAIL_SENDED');
            } else {
                echo '<div class="alert alert-danger">Hiba email küldés közben'.JSON_encode($result).'</div>';
                exit();
            }            
            $this->session->set('successMsg','Sikeresen elküldve');
        
        echo '<script>document.location="#";</script>'; 
        
	}

	/**
     * tree browser
     * 
     * NEM JÓ!!!!
     * 
     * GET| POST: page,order,filter,limit
     *     filter= 'filter_name|value|filter_code|value|filter_tag|value'
	 * (felülírás oka: tags -ot is át ad a viewernek, 
     *  $fullTree paramétert is a a modelnek)
     */
    public function tree($order = 1) {
        // paraméter olvasása get vagy sessionból
        $this->name='productTree';
        $page = $this->session->input($this->name.'page',1);
        $page = $this->request->input('page',$page);
        $limit = round((int)$_SESSION['screen_height'] / 80);
        $limit = $this->session->input($this->name.'limit',$limit);
        $limit = $this->request->input('limit',$limit);
        $order = $this->session->input($this->name.'order',$order);
        $order = $this->request->input('order',$order);

		// filter kezelés	
        $sFilter = $this->session->input($this->name.'filter',''); // 'name|value...'
        $sFilterArray = $this->filterParse($sFilter); // [name => value,...]
        $rFilter = $this->request->input('filter'); // 'name|value...' vagy 'all'
        if ($rFilter == 'all') {
			$sFilterArray = [];
			$filter = '';
		} else {
			$rFilterArray = $this->filterParse($rFilter); // [name => value,...]
			foreach ($rFilterArray as $fn => $fv) {
				$sFilterArray[$fn] = $fv;
			}
			$filter = $this->filterToStr($sFilterArray); // 'name|value...'
		}

        // adatok a paginátor számára
        $total = $this->model->getTotal($filter, false);
        $pages = [];
        $p = 1;
        while ((($p - 1) * $limit) < $total) {
            $pages[] = $p;
            $p++;
        }
        $p = $p - 1;

        // hibás paraméter kezelés
        if ($page > $p) { 
            $page = $p; 
        }
        if ($page < 1) {
            $page = 1;
        }

        // paraméter tárolás sessionba
        $this->session->set($this->name.'page',$page);
        $this->session->set($this->name.'limit',$limit);
        $this->session->set($this->name.'filter',$filter);
        $this->session->set($this->name.'order',$order);
        
        // rekordok olvasása az adatbázisból
        $items = $this->model->getItems($page,$limit,$filter,$order,false);

		//+ tags -ot is átad a viewer -nek
		$tagModel = new TagModel();
		$tags = $tagModel->getItems(1, 9999, '', '');
		//-

        // megjelenítés
        view('producttree',[
            "items" => $items,
			"tags" => $tags,
            "page" => $page,
            "total" => $total,
            "pages" => $pages,
            "task" => $this->browserTask,
            "filter" => $filter,
            "loged" => $this->loged,
            "logedName" => $this->loged,
            "logedAdmin" => (strpos($this->logedGroup,'admin') > 0),
            "previous" => SITEURL,
            "browserUrl" => $this->browserURL,
            "errorMsg" => $this->session->input('errorMsg',''),
            "successMsg" => $this->session->input('successMsg','')
        ]);
        $this->session->delete('errorMsg');
        $this->session->delete('successMsg');
        $this->session->delete('oldRecord');
    }

} 


?>
