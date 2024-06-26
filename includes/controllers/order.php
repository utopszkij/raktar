<?php
use \RATWEB\DB\Query;
use \RATWEB\DB\Record;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require 'vendor/phpmailer/src/Exception.php';
require 'vendor/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/src/SMTP.php';
include_once __DIR__.'/../urlprocess.php';

class Order extends Controller {

	function __construct() {
		parent::__construct();
        $this->name = "order";
	}

	/**
     * rekord ellenörzés felvitelnél, modosításnál van hivva
     * @param Record $record
     * @return string üres vagy hibaüzenet
     */
    protected function validator($record):string {
        $result = '';
        if ($record->name == '') {
            $result = 'NAME_REQUESTED<br>';
        }
        if ($record->email == '') {
            $result .= 'EMAIL_REQUESTED<br>';
        }
        if ($record->phone == '') {
            $result .= 'PHONE_REQUESTED<br>';
        }
        if ($record->address == '') {
            $result .= 'ADDRESS_REQUESTED<br>';
        }
        if ($record->desc == '') {
            $result .= 'DESC_REQUESTED<br>';
        }
        
        return $result;
    }


   /*
    * Megrendelő form
    */
	public function form() {
        $order = new \RATWEB\DB\Record();
        $order->name = '';
        $order->email = '';
        $order->phone = '';
        $order->address = '';
        $order->desc = '';
        $order->worktype = '';
        $db = new \RATWEB\DB\Query('worktypes');
        $order->worktypes = $db->orderBy('name')->all();
        view('orderform',["flowKey" => $this->newFlowKey(),
            'order' => $order,
            "errorMsg" => $this->session->input('errorMsg',''),
            "successMsg" => $this->session->input('successMsg','')
        ]);
	}

    public function send() {
        if (!$this->checkFlowKey('index.php?task=order.form')) {
            echo '<div class="alert alert-danger">Lejárt a munkamenet!</div>';
            exit();
        }
        $order = new \RATWEB\DB\Record();
        $order->name = $this->request->input('name','');
        $order->email = $this->request->input('email','');
        $order->phone = $this->request->input('phone','');
        $order->address = $this->request->input('address','');
        $order->desc = $this->request->input('desc','',HTML);
        $order->worktype = $this->request->input('worktype','');
        $order->created = date('Y-m-d H:i:s');
        $error = $this->validator($order);
        if ($error == '') {
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

            $mailBody = '<div>
            <h2>Munka megrendelés</h2>
            <p>Név: '.$order->name.'</p>
            <p>Email: <a href="mailto::'.$order->email.'">'.$order->email.'</a></p>
            <p>Telefon: '.$order->phone.'</p>
            <p>Helyszin: '.$order->address.'</p>
            <p>Munka típusa: '.$order->worktype.'</p>
            <p>Leírás:</p>
            '.$order->desc.'
            <p>Levél küldés időpontja: '.$order->created.'</p>
            </div>';
            $this->mailer->setFrom(MAIL_FROM_ADDRESS);
            $this->mailer->addAddress(ADMINEMAIL);
            $this->mailer->isHTML(true);          
            $this->mailer->Subject = 'Munka rendeles';
            $this->mailer->Body    = $mailBody;
            $result = $this->mailer->send();
            if ($result) {
                $this->session->set('successMsg','EMAIL_SENDED');
            } else {
                echo '<div class="alert alert-danger">Hiba email küldés közben'.JSON_encode($result).'</div>';
                exit();
            }            
            $this->session->set('successMsg','Sikeresen elküldve');
        } else {
            $this->session->set('errorMsg',$error);
        }
        $this->form();
        $this->session->set('errorMsg','');
        $this->session->set('successMsg','');
    }

}


?>
