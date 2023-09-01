<?php
use \RATWEB\DB\Query;
use \RATWEB\DB\Record;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require 'vendor/phpmailer/src/Exception.php';
require 'vendor/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/src/SMTP.php';

include_once __DIR__.'/../models/usermodel.php';

class User extends Controller {

	function __construct() {
		parent::__construct();
		$this->model = new UserModel();
        $this->name = "user";
        $this->browserURL = 'index.php?task=user.users';
        $this->addURL = 'index.php?task=user.regist';
        $this->editURL = 'index.php?task=useredit';
        $this->browserTask = 'user.users';
        $this->mailer =  new PHPMailer(true);
		$this->mailer->isSMTP();                                //Send using SMTP
		$this->mailer->Host       = MAIL_HOST;                  //Set the SMTP server to send through
		$this->mailer->SMTPAuth   = true;                       //Enable SMTP authentication
		$this->mailer->Username   = MAIL_USERNAME;                  //SMTP username
		$this->mailer->Password   = MAIL_PASSWORD;              //SMTP password
		$this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;//Enable implicit TLS encryption
		$this->mailer->Port       = MAIL_PORT;                  //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        $this->mailer->CharSet    = 'utf-8';
	}

	protected function validator($record): string {
		$result = '';
		if ($record->username == '') {
			$result .= 'USERNAME_REQUED<br>';
		}
		if (($record->password == '') & ($record->id == 0)) {
			$result .= 'PASSWORD_REQUED<br>';
		}
		if ($record->realname == '') {
			$result .= 'REALNAME_REQUED<br>';
		}
		if ($record->email == '') {
			$result .= 'EMAIL_REQUED<br>';
		}
		if ($record->password != $record->password2) {
			$result .= 'PASSWORDS_NOT_EQUALS<br>';
		}
		$old = $this->model->getBy('username',$record->username);
		if (count($old) > 0) {
			if ($old[0]->id != $record->id) {
				$result .= 'USER_EXISTS<br>';
			}
		}
		$old = $this->model->getBy('email',$record->email);
		if (count($old) > 0) {
			if ($old[0]->id != $record->id) {
				$result .= 'EMAIL_EXISTS<br>';
			}
		}
		return $result;
	}

	protected function accessCheck(string $action, Record $record): bool {
		return true;
	}

	public function login() {
		view('login',["errorMsg" => $this->request->input('errorMsg', $this->session->input('errorMsg',''),NOFILTER),
					  "successMsg" => $this->request->input('successMsg', $this->session->input('successMsg'),NOFILTER),
					  "SITEURL" => SITEURL,
					  "redirect" => $this->request->input('redirect',''),
					  "LOGIN_MUST_VERIFYED_EMAIL" => LOGIN_MUST_VERIFYED_EMAIL,
					  "USERREGIST" => USERREGIST,
					  "flowkey" => $this->newFlowKey()]);
		$this->session->set('errorMsg','');
		$this->session->set('successMsg','');
	}
	
	public function logout() {
		$_SESSION['loged'] = -1;
		$_SESSION['logedName'] = 'guest';
		$_SESSION['logedAvatar'] = '';
		$_SESSION['logedGroup'] = '';
		?>
		<script>
				document.location="index.php";		
		</script>
		<?php			
	}
	
	public function regist() {
		if (!USERREGIST) {
			echo '<div class="alert alert-danger">Regist disabled.</div>';
			return;
		}
		$record = new Record();
		$record->id = 0;
		$record->username = '';
		$record->realname = '';
		$record->email = '';
		$record->avatar = '';
		if ($this->session->input('oldRec') != '') {
			$old = JSON_decode($this->session->input('oldRec'));
			if (isset($old->id) & ($old->id == 0)) {
				$record = $old;
			}	
		}		
		view('regist',["record" => $record,
					   "errorMsg" => $this->request->input('errorMsg', $this->session->input('errorMsg'),NOFILTER),
					   "successMsg" => $this->request->input('successMsg', $this->session->input('successMsg'),NOFILTER),
					   "SITEURL" => SITEURL,
					   "polocyAccept" => 'ACCEPT',
					   "redirect" => $this->request->input('redirect',''),
   					   "LOGIN_MUST_VERIFYED_EMAIL" => LOGIN_MUST_VERIFYED_EMAIL,
					   "flowKey" => $this->newFlowKey()]
					);
		$this->session->set('errorMsg','');
		$this->session->set('successMsg','');
	}
	
	public function dologin() {
		// $this->checkFlowKey($this->browserURL);
		$userName = $_POST['username'];
		$password = $_POST['password'];
		$redirect = urlencode($_POST['redirect']);
		$recs = $this->model->getBy('username',$userName);
		/*
		if ($redirect == '') {
			$redirect = base64_encode('index.php');
		}
		*/
		if (count($recs) == 0) {
				$error = 'USER_NOT_FOUND';
				$this->session->set('errorMsg',$error);
				$this->login();
				return;			
		} else {
			$error = '';
			$rec = $recs[0];
			if ($rec->password != hash('sha256',$password.$rec->id)) {
				$error = 'WRONG_PASSWORD<br>';
			}
			if ($rec->enabled != 1) {
				$error .= 'DISABLED<br>';
			}
			if (LOGIN_MUST_VERIFYED_EMAIL) {
				if ($rec->email_verifyed != 1) {
					$error .= 'NOT_ACTIVATED<br>';
				}
			}
			if ($rec->deleted == 1) {
				$error .= 'USER_NOT_FOUND<br>';
			}
			if ($error == '') {
				$_SESSION['loged'] = $rec->id;
				$_SESSION['logedName'] = $rec->username;
				$_SESSION['logedAvatar'] = $rec->avatar;
				$_SESSION['logedGroup'] = JSON_encode($this->model->getGroups($rec->id));
				?>
				<script>
					document.location="<?php echo SITEURL; ?>";		
				</script>
				<?php			
			} else {
				$this->session->set('errorMsg',$error);
				$this->login();
			} 
		}	
	}
	
	public function doregist() {
		if (!USERREGIST) {
			echo '<div class="alert alert-danger">Regist disabled.</div>';
			return;
		}
		$this->checkFlowKey($this->browserURL);
		$record = new Record();
		$record->id = 0; 
		$record->username = $this->request->input('username');
		$record->password = $this->request->input('password');
		$record->password2 = $this->request->input('password2');
		$record->realname = $this->request->input('realname');
		$record->email = $this->request->input('email');
		$record->email_verifyed = $this->request->input('email_verifyed',0);
		$record->enabled = $this->request->input('enabled',0);
		$this->session->set('oldRec', JSON_encode($record));
		$record->deleted = 0;
		$redirect = base64_decode($this->request->input('redirect'));
		$error = $this->validator($record);
		if ($this->request->input('accept') != '1') {
			$error .= 'ACCEPT_REQUED<br>';
		}
		if ($error == '') {
			$record->enabled = 1;
			$record->email_verifyed = 0;
			$id = $this->model->save($record);
			$this->sendactivator($record->email);
			if (LOGIN_MUST_VERIFYED_EMAIL) {
				$this->session->set('successMsg','SAVED<br>EMAIL_SENDED');
			} else {
				$this->session->set('successMsg','SAVED');
			}			
			$this->session->set('errorMsg','');
			$this->session->delete('oldRec');
			$this->login();
		} else {
			$this->session->set('successMsg','');
			$this->session->set('errorMsg',$error);
			$this->regist();
		}
	}	
	
	/**
	 * aktiváló email küldése. az email-ben van egy link amivel a fiók aktiválható:
	 * domain?task=user.doactivte&code=base64_encode($rec->email.'-'.$rec->id)
	 * email érkezhet paraméterből (regist hivta) vagy 
	 * $_GET -ből $username (user kérte az újra küldést)
	 * hibaüzenetet, sikeres üzenetet csak akkor it ki ha az email $_GET -ből érkezett
	 */
	public function sendactivator(string $email = '') {
		$error = '';
		$pemail = $email;
		if ($email == '') {
			$recs = $this->model->getBy('username',$this->request->input('username',''));
			if (count($recs) > 0) {
				$email = $recs[0]->email;
			} else {
				$error = 'NOT_FOUND<br>USERNAME_REQUED<br>';
			}
		}
		if ($error == '') {
		$recs = $this->model->getBy('email',$email);
			if (count($recs) == 0) {
				$error .= 'NOT_FOUND<br>';
			}	
		}
		if ($error == '') {
			// unit test ne küldjön levelet
			if (($email != 'test@test.test') & (LOGIN_MUST_VERIFYED_EMAIL)) {
				// aktiváló email küldése $recs[0] alapján
				$code = base64_encode($recs[0]->password.'-'.$recs[0]->id);
				$mailBody = '<div>
				<h2>Fiók aktiváláshoz ksattints az alábbi linkre!</h2>
				<p> </p>
				<p><a href="'.SITEURL.'/index.php?task=user.doactivate&code='.$code.'">
					'.SITEURL.'/index.php?task=user.doactivate&code='.$code.'
				   </a>
				</p>
				<p> </p>
			    <p>vagy másold a fenti web címet a böngésző cím sorába!</p>
				<p> </p>
				</div>';
				
				$this->mailer->setFrom(MAIL_FROM_ADDRESS);
				$this->mailer->addAddress($recs[0]->email);     //Add a recipient
				$this->mailer->isHTML(true);                                  //Set email format to HTML
				$this->mailer->Subject = 'Fiok aktivalas';
				$this->mailer->Body    = $mailBody;
				$result = $this->mailer->send();
				
				if ($result) {
					$this->session->set('successMsg','EMAIL_SENDED');
					$this->login();
				} else {
					echo '<div class="alert alert-danger">Hiba email küldés közben'.JSON_encode($result).'</div>';
				}
			}	
		} else if ($pemail == '') {
			$this->session->set('errorMsg',$error);
			$this->login();
		}	
	}

	/**
	 * email virifyed beállítása, --> kezdő lap SAVED üzenettel
	 * $_GET $code base64_encode($rec-password.'-'.$rec->id)
	 */
	public function doactivate() {
		$error = '';
		$code = base64_decode($this->request->input('code'));
		$w = explode('-',$code);
		$w[] = '0';
		$rec = $this->model->getById($w[1]);
		if (isset($rec->password)) {
			if ($rec->password == $w[0]) {
				$rec->email_verifyed = 1;
				$q = new Query('users');
				$q->where('id','=',$rec->id)->update($rec);
			} else {
				$error = 'WRONG_PASSWORD';
			}
		} else {
			$error = 'NOT_FOUND';
		}
		if ($error == '') {
			$this->session->set('errorMsg','');
			$this->session->set('successMsg','SAVED');
			$this->login();
		} else {
			$this->session->set('errorMsg',$error);
			$this->session->set('successMsg','');
			$this->login();
		}
	}

	/**
	 * elfelejtett jelszó email küldése -->login képernyő EMAL_SENDED üzenettel
	 * $_GET -ben username
	 * a levélben vagy egy link amivel a progil oldalra lehet belépni bejelentkezés nélkül.
	 * domain?task=profile&ode=base64_encode($rec->email.'-'.$rec->id)
	 */
	public function forgetpsw() {
		$username = $this->request->input('username');
		$error = '';
		if ($username == '') {
			$error = 'USERNAME_REQUED<br>';
		} else {
			$recs = $this->model->getBy('username',$username);
			if (count($recs) == 0) {
				$error .= 'NOT_FOUND<br>';
			}
		}
		if ($error == '') {
			if ($recs[0]->email != 'test@test.test') {
				$code = base64_encode($recs[0]->password.'-'.$recs[0]->id);
				$mailBody = '<div>
				<h2>Új jelszó beállításához ksattints az alábbi linkre!</h2>
				<p> </p>
				<p><a href="'.SITEURL.'/index.php?task=user.profile&code='.$code.'">
					'.SITEURL.'/index.php?task=user.profile&code='.$code.'
				   </a>
				</p>
				<p> </p>
			    <p>vagy másold a fenti web címet a böngésző cím sorába!</p>
				<p> </p>
				</div>';
				
				$this->mailer->setFrom(MAIL_FROM_ADDRESS);
				$this->mailer->addAddress($recs[0]->email);     //Add a recipient
				$this->mailer->isHTML(true);                                  //Set email format to HTML
				$this->mailer->Subject = 'Elfelejett jelszo';
				$this->mailer->Body    = $mailBody;
				$result = $this->mailer->send();

			}
			$this->session->set('successMsg','EMAIL_SENDED');
			$this->login();
		} else {
			$this->session->set('errorMsg',$error);
			$this->login();
		}
	}

	/**
	 * user profil képernyő. admin, adott user, mások esetén eltérő adatok és lehetőségek
	 * $_GET $id user hivta menüből vagy user böngészöből 
	 * $-GET $code  elfelejtett jelszó email-ben lévő link hívta
	 */
	public function profile() {
		$error = '';
		$id = $this->request->input('id',0,INTEGER);
		$code = $this->request->input('code','');
		$backtask = 'home.show';
		$errorMsg = $this->request->input('errorMsg', $this->session->input('errorMsg'),NOFILTER);
		$successMsg = $this->request->input('successMsg', $this->session->input('successMsg'),NOFILTER);
		if ($code != '') {
			$w = explode('-',base64_decode($code));
			$psw = $w[0];
			$id = $w[1];
			$record = $this->model->getById($id);
			if (isset($record->password)) {
				if ($record->password != $psw) {
					$error = 'NOT_FOUND';
				} else {
					$_SESSION['loged'] = $record->id;
					$_SESSION['logedName'] = $record->username;
					$_SESSION['logedAvatar'] = $record->avatar;
				}
			} else {
				$error = 'NOT_FOUND';
			}
		} else {
			$record = $this->model->getById($id);
			if (!isset($record->id)) {
				$error = 'NOT_FOUND';
			}
		}
		if ($this->session->input('oldRec','') != '') {
			$old = JSON_decode($this->session->input('oldRec'));
			if ($old->id == $record->id) {
				$record = $old;
			}
		}		
		if ($error == '') {
			if ($record->avatar == '')  {
				$record->avatar = 'noimage.png';
			}
			view('profile',[
				"record" => $record,
				"flowKey" => $this->newFlowKey(),
				"loged" => $this->session->input('loged'),
				"logedAdmin" => isAdmin(),
				"errorMsg" => $errorMsg,
				"successMsg" => $successMsg,
				"userGroups" => $this->model->getGroups($record->id),
				"allGroups" => $this->model->getAllGroups(),
				"logedGroup" => $this->logedGroup,
			    "LOGIN_MUST_VERIFYED_EMAIL" => LOGIN_MUST_VERIFYED_EMAIL,
				"backtask" => $backtask
			]);
			$this->session->delete('errorMsg');
			$this->session->delete('successMsg');
		} else {
			$this->session->set('errorMsg',$error);
			?>
			<script>
				document.location=HREF('home.show',{errorMsg:"<?php echo $error; ?>"});
			</script>
			<?php
		}

	}

	/**
	 * profile képernyő tárolása egyes adatokat csak admin modosithat, --> home
	 * egyes adatokat csak admin és az adott user modosithat
	 * egyes adatokat csak admin és az adott user láthat
	 */
	public function saveprofile() {
		$this->checkFlowKey($this->browserURL);
		$record = new Record();
		$record->id = $this->request->input('id',0); 
		$old = $this->model->getById($record->id);
		$record->username = $old->username; 
		$record->realname = $this->request->input('realname',''); 
		$record->email = $this->request->input('email',''); 
		$record->password = $this->request->input('password',''); 
		$record->password2 = $this->request->input('password2',''); 
		$record->avatar = $old->avatar;
		$backtask = $this->request->input('backtask','home.show');
		if (!isAdmin() & ($record->id != $this->session->input('loged'))) {
			echo 'Access violation';
			return;
		}
		if (isAdmin()) {	
			$record->email_verifyed = $this->request->input('email_verifyed',0);
			$record->enabled = $this->request->input('enabled',0);
		}
		$this->session->set('oldRec', JSON_encode($record));
		$error = $this->validator($record);
		if ($error == '') {
			$id = $this->model->save($record);
			if (isAdmin()) {
				$this->model->saveUserGroups($record->id, $this->request);
			}
			$this->session->set('successMsg','SAVED');
			$this->session->set('errorMsg','');
			$this->session->delete('oldRec');
			if ($this->loged > 0) {
				$user = $this->model->getById($this->loged);
				$this->session->set('logedAvatar',$user->avatar);
			}
			$this->profile();
		} else {
			$this->session->set('successMsg','');
			$this->session->set('errorMsg',$error);
			$this->profile();
		}	
	}

	/**
	 * user fiók logikai törlése. --> home DELETED üzenettel
	 * admin bárkit törölhet, mások csak saját magukat
	 * $_GET $id
	 */
	public function dodelete() {
		$id = $this->request->input('id',0);
		$error = '';
		if (isAdmin() | $id == $this->session->input('loged')) {
			$rec = $this->model->getById($id);
			if (isset($rec->username)) {
				if ($rec->username != ADMIN) {
					$rec->username = 'deleted';
					$rec->realname = 'deleted';
					$rec->password = rand(1000000,9000000); 
					$rec->email = '';
					$rec->avatar = '';
					$rec->enabled = 0;
					$rec->deleted = 1;
					$q = new Query('users');
					$q->where('id','=',$rec->id)->update($rec);
					if ($this->session->input('loged') == $rec->id) {
						$this->logout();
					}
					?>
					<script>
						document.location=HREF('home.show',{successMsg:'DELETED'});
					</script>	
					<?php
				} else {
					$error = 'ACCESDENIED';
				}
			} else {
				$error = 'NOT_FOUND';
			}
		} else {
			$error = 'ACCESDENIED';
		}
		if ($error != '') {	
			?>
			<script>
				document.location=HREF('home.show',{errorMsg:"<?php echo $error; ?>"});
			</script>	
			<?php
		}
	}

	/**
     * user browser GET -ben: page, order, filter
	 * névre kattintva a profil képernyőt hívja
	 */
    public function users() {
		$this->session->delete('oldRec');
        $this->items('username');
    }
    
	public function mydata() {
		$id = $this->request->input('id',0,INTEGER);
		$rec = $this->model->getbyId($id);
		if (isset($rec->id)) {
			if (($rec->id == $this->session->input('loged')) |
			    isAdmin()) {
				$rec->groups = $this->model->getGroups($id);
				unset($rec->password);
				echo '<p>Copy - paste this json code into a local file, or send it into your partner!</p>';
				echo '<pre style="height:400px"><code style="height:400px">'.
					JSON_encode($rec, JSON_PRETTY_PRINT).
				'</code></pre>';
			}
		}	
	}
}


?>
