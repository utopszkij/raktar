<html lang="en">
<head>
  <meta>
    <meta charset="UTF-8">
	<meta property="og:title"  content="<?php echo $title; ?>" />
	<base href="<?php echo SITEURL; ?>/">
	<link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <title><?php echo $title; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- bootstrap -->	
	<link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
	<!-- vue -->
    <script src="vendor/vue/vue.global.js"></script>
	<!-- axios -->
	<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
	<!-- fontawesome --> 
	<script src="vendor/fontawesome/js/all.min.js"></script>
	<link rel="stylesheet" href="vendor/fontawesome/css/all.min.css">
	<!-- jquery -->
	<script type="text/javascript" src="vendor/jquery/jquery-2.2.4.min.js"></script>
	<!-- qrcode -->
    <script type="text/javascript" src="vendor/qrcode/qrcode.js"></script>

	<link rel="stylesheet" href="<?php echo SITEURL; ?>/styles/default/style.css?t=<?php echo $fileVerzio; ?>">

	<!-- multi language -->
	<?php
		if (defined('LNG')) {
			if (file_exists('languages/'.LNG.'.js')) {
				echo '<script src="languages/'.LNG.'.js"></script>';
			} else {
				echo '<script> tokens = {}; </script>';
			}	
		} else {
			if (file_exists('languages/hu.js')) {
				echo '<script src="languages/hu.js"></script>';
			} else {
				echo '<script> tokens = {}; </script>';
			}	
		}
	?>
	
	<!-- SITEURL -->
	<script type="text/javascript">
		var rewrite = <?php echo (int)REWRITE; ?>;
        var siteurl = "<?php echo SITEURL; ?>"; 
	</script>	
	<script src="index.js"></script>
	
	<!-- ckeditor -->
	<script src="vendor/ckeditor/ckeditor.js"></script>
	<script src="vendor/ckeditor/translations/hu.js"></script>
	<script>
	
	// ckeditr image upload kezelés
	
	class MyUploadAdapter {
		constructor( loader ) {
			// CKEditor 5's FileLoader instance.
			this.loader = loader;

			// URL ahol a file upload feldolgozo php van
			this.url = 'upload.php';
			this.uploads = ['jpg','jpeg','png','odt'];
		}

		// Starts the upload process.
		upload() {
			return this.loader.file
				.then( file => new Promise( ( resolve, reject ) => {
					this._initRequest();
					this._initListeners( resolve, reject, file );
					this._sendRequest( file );
				} ) );
		}

		// Aborts the upload process.
		abort() {
			if ( this.xhr ) {
				this.xhr.abort();
			}
		}


		_initRequest() {
			const xhr = this.xhr = new XMLHttpRequest();

			xhr.open( 'POST', this.url ,true);
			xhr.responseType = 'json';
		}


		_initListeners( resolve, reject,file ) {
			const xhr = this.xhr;
			const loader = this.loader;
			const genericErrorText = 'Couldn\'t upload file:' + ` ${ file.name }.`;

			xhr.addEventListener( 'error', () => reject( ' A '+genericErrorText ) );
			xhr.addEventListener( 'abort', () => reject(' B ') );
			xhr.addEventListener( 'load', () => {
				const response = xhr.response;
				if ( !response || response.error ) {
					// alert('response error'+response.error);
					return reject( response && response.error ? response.error.message : genericErrorText );
				}
				//console.log(response);
				// If the upload is successful, resolve the upload promise with an object containing
				// at least the "default" URL, pointing to the image on the server.
				resolve( {
					default: response.url
				} );
			} );

			if ( xhr.upload ) {
				xhr.upload.addEventListener( 'progress', evt => {
					if ( evt.lengthComputable ) {
						loader.uploadTotal = evt.total;
						loader.uploaded = evt.loaded;
					}
				} );
			}
		}

		// Prepares the data and sends the request.
		_sendRequest(file) {
			const data = new FormData();
			data.append('upload', file );
			//csrf_token CSRF protection
			//data.append('csrf_token', requestToken);
			this.xhr.send( data );
		}
	}

	function MyCustomUploadAdapterPlugin( editor ) {
		editor.plugins.get( 'FileRepository' ).createUploadAdapter = ( loader ) => {
			return new MyUploadAdapter( loader );
		};
	}
	<!-- ckeditor end -->

</script>
	
	
</head>	 
<body>
	<div id="popup">
		<div style="text-align:right">
			<button type="button" onclick="popupClose()" 
				title="Bezár" style="margin:0px 0px 0px 0px; padding:0px 5px 0px 5px"
				class="btn btn-secondary">X</button>
		</div>
		<div id="popupTxt"></div>
		<div>
		<button type="button" id="popupOkBtn" class="btn btn-danger">Igen</button>
			&nbsp;
			<button type="button" id="popupNoBtn"class="btn btn-primary" onclick="popupClose()">Nem</button>
		</div>
	</div>

	<?php
	// extra html -ek betöltése (pl extra js -ek belodolása)
	if (file_exists(__DIR__.'/includes/extras/'.$task.'.html')) {
		include __DIR__.'/includes/extras/'.$task.'.html';
	}
	?>

	<div class="container" id="container">
		<div class="row">
			<div class="col-12">
				<div id="header" onclick="document.location='index.php';"></div>
			</div> 
		</div>
		<div class="row" id="mainmenuContainer">
			<div class="col-12">
				<?php 
					if (($_SESSION['loged'] > 0) & ($_SESSION['logedAvatar'] == '')) {
						$_SESSION['logedAvatar'] = 'noavatar.png';
					}
					view('mainmenu',[
						'MULTIUSER' => MULTIUSER,
						'loged' => $_SESSION['loged'],
						'logedAvatar' => $_SESSION['logedAvatar'],
						'logedName' => $_SESSION['logedName'],
						'isAdmin' => isAdmin(),
						'lastVerzio' => Upgrade::versionAdjust($lastVerzio),
						'fileVerzio' => Upgrade::versionAdjust($fileVerzio)
						],'mainmenu'); 
				?>
			</div>
		</div>
		
		<div class="page">
			<?php
				$comp->$task ();			
			?>
		</div>

		<div id="footerContainer">
		<?php 
			view('footer',[
				'fileVerzio' => Upgrade::versionAdjust($fileVerzio)
			],'footer'); 
		?>
		</div>
	</div>
	<button onclick="window.scrollTo(0,0);" id="scrolltotop" title="Fel a tetejére">
		<em class="fa fa-arrow-up"></em>
	</button>
	<script src="vendor/jquery/jquery-2.2.4.min.js"></script>
	<div id="cookieContainer">
	<script>
		if (document.cookie.search('cookieEnabled=2') >= 0) {
			document.write('<p id="cookieEnabled">Csoki kezelés engedélyezve van. Letiltásához kattints ide:'+
			'<a href="index.php" onclick="setCookie(\'cookieEnabled\',0,100);">Letilt</a></p>');
		} else if (document.location.href.search('adatkezeles') < 0) {
			popupConfirm('Ennek a web oldalnak a használatához un. "munkamenet csokik" használtata szükséges.'+
			'<br />Lásd: <a href="index.php?task=adatkezeles">Adatkezelési leírás</a>'+
			'<br />Kérjük engedélyezd a csokik kezelését!',
			function() {
				setCookie('cookieEnabled',2,100);
				document.location='index.php';
			})
		}
	</script>	
	</div>
</body>
<script type="text/javascript">

		// világos/sötét téma váltás
		function themeTogle() {
			const currentTheme = getCookie("theme");
			var theme = getCookie("theme");
			if (currentTheme == "dark") {
				document.body.className = 'light';
				theme = 'light';
			} else if (currentTheme == "light") {
				document.body.className = 'dark';
				theme = 'dark';
			} else {
				document.body.className = 'dark';
				theme = 'dark';
			}
			setCookie("theme", theme,100);
		}

		// mozgatható elemek
		dragElement(document.getElementById("popup"));

		// sessionId csokiba
		window.sessionId = "<?php echo session_id(); ?>";
		setCookie("sid","<?php echo session_id(); ?>", 500);

		// képek realtime betöltése, scrolltotop button megjelenítés/elrejtés
		window.onscroll = function() {
			window.scrollFunction(); window.scrollFunction()
			if (window.scrollY < 20) {
				document.getElementById('scrolltotop').style.display = 'none';
			} else {
				document.getElementById('scrolltotop').style.display = 'block';
			}
		};
		window.setTimeout('window.scrollFunction()',1000);

		window.rewrite = <?php echo (int)REWRITE; ?>;
        window.siteurl = "<?php echo SITEURL; ?>"; 
		
		// iframe elemek átméretezése a parent div mérethez
		var frames = document.getElementsByTagName("iframe");
		var sz = 0, max = 0;
		for (var i = 0; i < frames.length; i++) {
			max = frames[i].parentNode.getBoundingClientRect().width * 0.9;
			if (frames[i].width > max) {
				sz = max / frames[i].width;
				frames[i].width = Math.round(max);
				frames[i].height = Math.round(frames[i].height * sz);
			}
		}
		
		
		
</script>

</html>
