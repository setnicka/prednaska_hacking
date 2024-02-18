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
sudo /etc/php/8.2/fpm/pool.d/php-fpm-jirka.conf
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
    * lepší použít

* nebo lze použít předpřipravené SQL statementy a doplnit jako proměnné
* oboje viz index2.php