/*
 *  ckeditr image upload kezelés
 *  és toolbar definiálás
 */

	class MyUploadAdapter {
		constructor( loader ) {
			// CKEditor 5's FileLoader instance.
			this.loader = loader;

			// URL ahol a file upload feldolgozo php van
			this.url = 'upload.php';
			this.uploads = ['jpg','jpeg','png','odt','webp'];
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
	
	function ckeditorInit(domElementSelector) {
				if (window.editor == undefined) {
                ClassicEditor
                .create( document.querySelector( domElementSelector ), {
                    language: 'hu',
                    extraPlugins: [ MyCustomUploadAdapterPlugin],
                    toolbar : {
							items: [
								'heading',
								'findAndReplace',
								'|',
								'bold',
								'italic',
								'underline',
								'link',
								'bulletedList',
								'numberedList',
								'|',
								'outdent',
								'indent',
								'alignment',
								'|',
								'imageUpload',
								'blockQuote',
								'insertTable',
								'mediaEmbed',
								{
									label: 'Font style',
									icon: 'text',
									items: ['fontBackgroundColor','fontColor','fontFamily','fontSize']
								},	
								'removeFormat',
								'specialCharacters',
								'subscript',
								'superscript',
								'undo',
								'redo',
								'sourceEditing'
							]
					},
                    mediaEmbed: {
                        extraProviders: [
                        {
                                name: 'tiktok',
                                url: /^tiktok\.com\/(.+)/,
                                html: match => `video: https://tiktok.com/${ match[ 1 ] }`
                            },
                            {
                                name: 'fb_watch',
                                url: /^fb\.watch\/(.+)/,
                                html: match => `fb.watch video`
                            },
                            {
                                name: 'facebook',
                                url: /^facebook\.com\/(.+)/,
                                html: match => `facebook video`
                            },
                            {
                                name: 'other',
                                url: /(.+)/,
                                html: match => `other video`
                            }
                            
                        ]
            		}
                } )
                .then( editor => {
                    window.editor = editor;
                } )
                .catch( err => {
					console.log('ckeditor error');
                    console.log( err );
                } );
            }	

	}
