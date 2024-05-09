<?php
use \RATWEB\DB\Query;
use \RATWEB\DB\Record;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require 'vendor/phpmailer/src/Exception.php';
require 'vendor/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/src/SMTP.php';

include_once __DIR__.'/../models/messagesmodel.php';

/**
 * messages controller 
 * igényelt model (includes/models/messagesmodel.php))
 *      methodusok: emptyRecord(), save($record), 
 *      getById($id), deleteById($id), getItems($page,$limit,$filter,$order), 
 *      getTotal($filter)
 * igényelt viewerek includes/views/messagesbrowser, includes/views/messagesform 
 *      a messagesform legyen alkalmas show funkcióra is a record, loged, logedAdmin -tól függően
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
class Messages extends Controller {

	function __construct() {
		parent::__construct();
		// $this->model = new MessagesModel();
        $this->name = "messages";
        $this->browserURL = 'index.php?task=messages.items';
        $this->addURL = 'index.php?task=messages.new';
        $this->editURL = 'index.php?task=messages.edit';
        $this->browserTask = 'messages.items';
        $this->model = new MessagesModel();
		//$this->ckeditorFields=['txt'];
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
		if (($action == 'edit') | ($action == 'delete')) {
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
		if ($record->sender_name == '') {
			$result = 'NAME_REQUERED';
		}
		if ($record->sender_email == '') {
			$result = 'EMAIL_REQUERED';
		}
		if ($record->txt == '') {
			$result = 'TXT_REQUERED';
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
	
	/**
	 * blog add/edit képernyő tárolása
	 * session[loged] és [logedGroup] jogosultság ellenörzéssel 
	 * POSTban: blog_id, blog rekord adatai
     * tárolás után --> blogs
	 */ 
	public function save($record = '') {
        if (!$this->checkFlowKey('index.php')) {
            echo 'flowKey error.'; exit();
        }
        $id = $this->request->input('id',0);
        $record = $this->model->emptyRecord();
        $record->id = $this->request->input('id','');
        $record->sender_name = $this->request->input('sender_name','');
        $record->sender_email = $this->request->input('sender_email','');
        $record->received = $this->request->input('received',date('Y-m-d'));
        $record->txt = $this->request->input('txt','',HTML);
        $record->answered = $this->request->input('answered','');
        $record->closed = $this->request->input('closed','');
        $record->status = $this->request->input('status','active');
        $record->comment = $this->request->input('comment','');

        $errorMsg = ($this->validator($record));
        if ($errorMsg == '') {    
                unset($record->allCategories);
                unset($record->categories);
                $newId = $this->model->save($record);
                // email küldés
                $mailBody .= $record->sender_name.'<br />'.
                $record->received.'<br />'. 
                $record->sender_email.'<br /><br />'.
                str_replace("\n","<br />",$record->txt);
        
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
                    $this->mailer->Subject = 'Raktar program üzenet';
                    $this->mailer->Body    = $mailBody;
                    $result = $this->mailer->send();
                    if ($result) {
                        $this->session->set('successMsg','EMAIL_SENDED');
                    } else {
                        echo '<div class="alert alert-danger">Hiba email küldés közben'.JSON_encode($result).'</div>';
                        exit();
                    }            
                    $this->session->set('successMsg','Sikeresen elküldve');
        
        }    
        if ($errorMsg == '') {    
                $this->session->set('successMsg', 'SAVED');
                $this->session->set('errorMsg', '');
				if ($id > 0) {
					$this->items();
				} else {
					echo '<script>location="index.php";</script>';
				}
        } else {
                $this->session->set('successMsg', '');
                $this->session->set('errorMsg', $errorMsg);
				echo '<script>location="index.php";</script>';
        }
    }
	
}


?>
