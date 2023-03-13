# Raktár kezelő program

Egyszerű raktár kezelő program utopszkij_fw keretrendszerrel


![logo](https://raktar.siriusworld.hu/images/logo.png)

## WEB SITE 
[https://raktar.siriusworld.hu](https://raktar.siriusworld.hu)

## Tulajdonságok
- multiuser
- multilanguage
- bootstrap responziv dizajn
- definiálható tranzakció tipusok
- definiálható cimkék
- definiálható mértékegységek
- a termékekhez megadható adatok: 
megnevezés, leírás (html - ckeditor), dokumentum-ID, dokumentu-link, qrkód, kép, csatolt jájlok (pdf,doc,docx,txt,odt), cimkék
- keresés név részlet, cimke, tároló szerint
- esemény napló

## utopszkij_fw keretrendszer Tulajdonságai

- PHP, MYSQL backend, vue frontend,
- bootstrap, fontawesome,
- MVC struktúra,
- több nyelvüség támogatása,
- login/logout/regist rendszer, Google és Facebook login támogatása,
- usergroups rendszer,
- SEO url támogatása,
- facebook megosztás támogatása,
- egszerű telepíthetőség, nem szükséges konzol hozzáférés,
- php és viewer.html unittest rendszer,
- verzió követés a github main branch -ról.

## Lecensz

GNU v3

A fekhasznált harmadik féltől származó sw elemek (bootstrap, vue, awesome font, jquery, phpmailer) a vendor könyvtárba vannak másolva és innen 
tölti be a program. Azért választottam ezt a megoldást, hogy a web oldalak ne fagyjanak le az online elérhetőséget biztositó szerverek 
esetleges üzemszüneténél, és a verzió frissitések esetleges visszamenőleges inkopatibilitásából eredő hibákat is elkerüljük. 
Aki ezzel a módszerrel nem ért egyet az az index.php -t modosítva használhat NET -es eléréseket is (pl cdn).

### A programot mindenki csak saját felelősségére használhatja.

## verzió v2.0.0
2022.02.26. használható (nem béta)
### *************************************
						
## Információk informatikusok számára      

## Szükséges sw környezet
### futtatáshoz
- web szerver   .htacces és rewrite támogatással
- php 7+ (mysqli kiegészítéssel)
- mysql 5+
### fejlesztéshez
- phpunit (unit test futtatáshoz)
- doxygen (php dokumentáció előállításhoz)
- nodejs (js unittesthez)
- php és js szintaxist támogató forrás szerkesztő vagy IDE

## Telepítés

- adatbázis létrehozása (utf8, magyar rendezéssel),
- config.php elkészítése a a config-example.php alapján,
- a views/impressum, policy, policy2, policy3 fájlok szükség szerinti módosítása
- fájlok és könyvtárak feltöltése a szerverre,
- az images könyvtár legyen irható a web szerver számára, a többi csak olvasható legyen,
- többfelhasználós üzemmód esetén; a program "Regisztrálás" menüpontjában hozzuk létre a
  a system adminisztrátor fiokot (a config.php -ban beállított bejelentkezési névvel).

Könyvtár szerkezet a futtató web szerveren:
```
[document_root]
  [images]
     kép fájlok (alkönyvtárak is lehetnek)
  [includes]
    [controllers]
      kontrollerek php fájlok
    [models]
      adat modellek php fájlok
    [views]
      viewer templates  spec. html fájlok. vue elemeket tartalmaznak
  [vendor]
    keretrendszer fájlok és harmadik féltől származó fájlok (több alkönyvtárat is tartalmaz)
  [styles]
    megjelenést befolyásoló fájlok (css-ek stb)  
  index.php  - fő program
  config.php - konfigurációs adatok
  files.txt  - a telepített fájlok felsorolása, az upgrade folyamat használja

```  
index.php paraméter nélküli hívással a "home.show" taskal indul a program.

index.php?task=upgrade.upgrade1&version=vx.x&branch=xxxx hívással a github megadott branch -et használva  
is tesztelhető/használható az upgrade folyamat.

## unit test

Telepiteni kell a phpunit és a nodejs rendszert.

[https://phpunit.de/](https://phpunit.de/)

[https://nodejs.org/en/](https://nodejs.org/en/)

Létre kell hozni egy test adatbázist, az éles adatbázissal azonos strukturával.

Létre kell hozni egy config_test.php fájlt az éles config.php alapján, a test adatbázishoz beállítva.

Ezután linux terminálban:
```
cd reporoot
phpunit tests
./tools/viewtest.sh
```
## software documentáció

includes/views/swdoc.html

doc/html/index.html

## A sw. dokumentáció előállítása
telepiteni kell a doxygen dokumentáció krátort.

[https://doxygen.nl/](doxygen)  Köszönet a sw. fejlesztőinek.

A telepitési könyvtáraknak megfelelően módosítani kell documentor.sh fájlt.

Ezután linux terminálban:

```
cd reporoot
./tools/documentor.sh
```

## ÚJ CRUD modul létrehozása

(CRUD: Create - read - update - delete)

- index.php -ban verzió szám emelés
- controllers/upgrade.php -ban adatbázist modosítás
- controllers/{newModulNev}.php létrehozása
- models/{newModulNev}model.php létrehozása
- views/{newModulNev}browser.php létrehozása
- views/{newModulNev}form.php létrehozása
- languages/{lng}.js modosítása
- főmenüben (vagy máshol) a modult inditó link elhelyezése
- program inditása böngészöből

Lásd a "demo" modult: controllers/demo.php, models/demomodel.php,
views/demobrowser.php, views/demoform.php, languages/hu.js
controllers/upgrade.php -ben a v1.1.0 tartozik ehhez.
 

vagy parancssorból varázslóval:

cd {documentroot}

php ./tools/createCRUD {compName} {tableName}








