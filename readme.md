# Raktár kezelő program

Egyszerű raktár kezelő program utopszkij_fw keretrendszerrel

PHP-MYSQL-VUE keretrendszer web oldalak fejlesztéséhez.


## WEB SITE 
[https://raktar.gerisznet.hu](https://raktar.gerisznet.hu)

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

## utopszkij_fw keretrendszer Tulajdonságok

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

## Dokumentáció

[https://szakacskonyv.nfx.hu/fw/task/home.swdoc](https://szakacskonyv.nfx.hu/fw/task/home.swdoc)

A fekhasznált harmadik féltől származó sw elemek (bootstrap, vue, awesome font) a vendor könyvtárba vannak másolva és innen 
tölti be a program. Azért választottam ezt a megoldást, hogy a web oldalak ne fagyjanak le az online elérhetőséget biztositó szerverek 
esetleges üzemszüneténél, és a verzió frissitések esetleges visszamenőleges inkopatibilitásából eredő hibákat is elkerüljük. 
Aki ezzel a módszerrel nem ért egyet az az index.php -t modosítva használhat NET -es eléréseket is (pl cdn).

### A programot mindenki csak saját felelősségére használhatja.
						
## Lecensz

GNU v3

## A keretrendszer segitségével felépített müködő demo:

[https://szakacskonyv.nfx.hu](https://szakacskonyv.nfx.hu)

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
- controllers/{newModeulNaev}.php létrehozása
- models/{newModeulNaev}model.php létrehozása
- views/{newModeulNaev}browser.php létrehozása
- views/{newModeulNaev}form.php létrehozása
- languages/{lng}.js modosítása
- főmenüben (vagy máshol) a modult inditó link elhelyezése
- program inditása böngészöből

Lásd a "demo" modult: controllers/demo.php, models/demomodel.php,
views/demobrowser.php, views/demoform.php, languages/hu.js
controllers/upgrade.php -ben a v1.1.0 tartozik ehhez.
 


## verzió v1.0.3
table lock, unlock, tranzakció kezelés a database interface-be
### *************************************
## verzió v1.0.2
table create, alter, drop a database interface-be
### *************************************
## verzió v1.0.1
2022.07.28
GDPR megfelelés
### *************************************
## verzió v1.0.0
2022.07.27

### *************************************





