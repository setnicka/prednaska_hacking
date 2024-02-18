# Hackování webů

Osnova a příprava přednášky.

Příprava PHP prostředí:
```sh
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
