/**
* utopszkij_fw
* globális javascript
* a HTML headerbe includolni!
*/
	    const { createApp } = Vue; 
		/**
		 * csoki beállítás
		 */
		function setCookie(name,value,days) {
			var expires = "";
			if (days) {
				var date = new Date();
				date.setTime(date.getTime() + (days*24*60*60*1000));
				expires = "; expires=" + date.toUTCString();
			}
			document.cookie = name + "=" + (value || "")  + expires + "; path=/";
		}

		/**
		* csoki lekérdezése
		*/	
		function getCookie(cname) {
		  let name = cname + "=";
		  let decodedCookie = decodeURIComponent(document.cookie);
		  let ca = decodedCookie.split(';');
		  let i = 0;
		  for (i = 0; i < ca.length; i++) {
			let c = ca[i];
			while (c.charAt(0) == ' ') {
			  c = c.substring(1);
			}
			if (c.indexOf(name) == 0) {
			  return c.substring(name.length, c.length);
			}
		  }
		  return "";
		}
		
		/**
		 * user jováhagyás kérés popup ablakban 
		 * ids:  popup, popupOkBtn, popupNoBtn, popupTxt 
		 */
		function popupConfirm(txt, yesfun) {
			document.getElementById('popupOkBtn').style.display="inline-block";
			document.getElementById('popupNoBtn').style.display='inline-block';
			document.getElementById('popup').className='popupSimple';
			document.getElementById('popupTxt').innerHTML = txt;
			document.getElementById('popupOkBtn').onclick=yesfun;
			document.getElementById('popup').style.display='block';
		}
		/**
		 * poup ablak bezárása
		 * ids:  popup
		 */
		function popupClose() {
			document.getElementById('popup').style.display='none';
		}
		
		/**
		 * popup üzenet
		 * ids:  popup, popupOkBtn, popupNoBtn, popupTxt 
		 */
		function popupMsg(txt,className) {
			if (className == undefined) {
				className = 'popupSimple';
			}
			document.getElementById('popupOkBtn').style.display="none";
			document.getElementById('popupNoBtn').style.display='none';
			document.getElementById('popup').className=className;
			document.getElementById('popupTxt').innerHTML = txt;
			document.getElementById('popup').style.display='block';
		}
		
		/**
		 * nyelvi fordítás
		 */
		function lng(token) {
			var result = token;
			var i = 0;
			for (i=0; i<10; i++) {
				token = token.replace('<br />','<br>');
			}	
			var w = token.split('<br>');
			for (var i = 0; i < w.length; i++) {
				if (tokens[w[i]] != undefined) {
					w[i] = tokens[w[i]];
			    }
			}
			result = w.join('<br>');	
			return result;
		}
		
		/**
		 * felső menüben almenü megjelenés/elrejtés
		 */
		function submenuToggle() {
			var submenu = document.getElementById('submenu');
			if (submenu.style.display == 'block') {
				submenu.style.display = 'none';
			} else {
				submenu.style.display = 'block';
			}
		}

		var rewrite = false;
        var siteurl = "index.php"; 

		/**
		 * seo barát url képzéshez segéd rutin
		 * @param string task
		 * @param object params {name:value,...}
		 */
		function HREF(task, params) {
			var result = siteurl;
			if (rewrite) {
				result += '/task/'+task;
				for (var fn in params) {
					result += '/'+fn+'/'+params[fn];
				}
			} else {
				result += '?task='+task;
				for (var fn in params) {
					result += '&'+fn+'='+params[fn];
				}
			}
			return result;
		}
	
		/**
		* képek realtime betöltése (csak azokat amik létszanak a képernyőn)
		* a kép <img alt="kép_url" ...> legyen src megadása NÉLKÜL!
		*/
		function scrollFunction() {
			// a képek betöltése
			var imgs = document.images;
			var i = 0;
			for (i = 0; i < imgs.length; i++) {
					if ((imgs[i].src == '') & (imgs[i].alt != undefined)) {
						if (imgs[i].getBoundingClientRect().top <= window.innerHeight) {
							imgs[i].src = 'images/loader.gif'; // ezt rendszerint pufferből gyorsan tölti.
							imgs[i].src = imgs[i].alt; // ezt nem biztos, hogy pufferből tölti.
							imgs[i].alt = 'image';
						}	
					}	
			}
		}
		
	// Make the DIV element draggable:
	function dragElement(elmnt) {
		var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
		elmnt.cursor='move';
		/*
		if (document.getElementById(elmnt.id + "header")) {
			// if present, the header is where you move the DIV from:
			document.getElementById(elmnt.id + "header").onmousedown = dragMouseDown;
		} else {
			// otherwise, move the DIV from anywhere inside the DIV:
		}
		*/
		elmnt.onmousedown = dragMouseDown;

		function dragMouseDown(e) {
			e = e || window.event;
			e.preventDefault();
			// get the mouse cursor position at startup:
			pos3 = e.clientX;
			pos4 = e.clientY;
			document.onmouseup = closeDragElement;
			// call a function whenever the cursor moves:
			document.onmousemove = elementDrag;
			elmnt.style.cursor = 'crosshair';
		}

		function elementDrag(e) {
			e = e || window.event;
			e.preventDefault();
			// calculate the new cursor position:
			pos1 = pos3 - e.clientX;
			pos2 = pos4 - e.clientY;
			pos3 = e.clientX;
			pos4 = e.clientY;
			// set the element's new position:
			elmnt.style.top = (elmnt.offsetTop - pos2) + "px";
			elmnt.style.left = (elmnt.offsetLeft - pos1) + "px";
		}

		function closeDragElement() {
			// stop moving when mouse button is released:
			document.onmouseup = null;
			document.onmousemove = null;
			elmnt.style.cursor = 'move';
		}
	}
		
		
		// képernyő méretek tárolása csokiba
		setCookie('screen_width',screen.width,100); 
		setCookie('screen_height',screen.height,100); 
