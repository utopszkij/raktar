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
        $order->created = date('Y-m-d H:i:s');
        $error = $this->validator($order);
        if ($error == '') {
            // levél küldés;
            $this->mailer =  new PHPMailer(true);

            $mailBody = '<div>
            <h2>Munka megrendelés</h2>
            <p>Név: '.$order->name.'</p>
            <p>Email: <a href="mailto::'.$order->email.'">'.$order->email.'</a></p>
            <p>Telefon: '.$order->phone.'</p>
            <p>Helyszin: '.$order->address.'</p>
            <p>Leírás:</p>
            '.$order->desc.'
            <p>Levél küldés időpontja: '.$order->created.'</p>
            </div>';

echo $mailBody;            


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
