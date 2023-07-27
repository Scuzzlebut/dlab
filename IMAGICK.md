<p align="left"><a href="https://digita-lab.it" target="_blank"><img src="https://digita-lab.it/wp-content/uploads/2022/09/logo-DLab-e1663166083323.jpg" width="" alt="D.Lab Logo"></a></p>

# Linux - Installazione Imagick

Comando: apt install php7.3-imagick

(https://www.php.net/manual/en/class.imagick.php)

NB: ricordarsi di sistemare le policy /etc/ImageMagick-6/policy.xml x ghostscript<br>
Aggiungendo `<policy domain="coder" rights="read|write" pattern="{PS,PDF,XPS}" />`<br>

# Windows - Installazione Imagick

1.  Verificare la versione di php e thread safe con phpinfo

2.  Scaricare la propria versione di "_php_imagick_" da qui: https://mlocati.github.io/articles/php-windows-imagick.html
    <br>

        > ##### esempi con thread safe attivo:
        >
        > ##### - php 7.3: php_imagick-3.6.0-7.3-ts-vc15-x64.zip
        > ##### - php 7.4: php_imagick-3.6.0-7.4-ts-vc15-x64.zip
        > ##### - php 8.0: php_imagick-3.6.0-8.0-ts-vs16-x64.zip
        > ##### - php 8.1: php_imagick-3.7.0-8.1-ts-vs16-x64.zip

<br>

3. Estrarre dallo zip il file "_php_imagick.dll_" e copiarlo dentro la cartella _ext_ di php

    > ##### esempio utilizzando Laragon come ambiente di sviluppo in ambiente x64 e php v8.1.10:
    >
    > ##### _C:/laragon/bin/php/php-8.1.10-Win32-vs16-x64/ext/_

<br>

4. Prendere tutte le altre DLL presenti nello zip e copiarle nella cartella root di php

    > ##### esempio utilizzando Laragon come ambiente di sviluppo in ambiente x64 e php v8.1.10:
    >
    > ##### _C:/laragon/bin/php/php-8.1.10-Win32-vs16-x64/_

<br>

5. Aprire il file "_php.ini_" e aggiungere l'estensione di Imagick

    > ##### extension = php_imagick.dll

<br>

6. Fermare e riavviare l'ambiente di sviluppo (Apache, Xampp, Wamp, Laragon, etc.), controllare quindi con phpinfo se Imagick è ora attivo<br><br>

# Windows - Gestione PDF con Imagick/Ghostscript

7. Installare Ghoscript da qui: https://www.ghostscript.com/releases/gsdnld.html

    > ##### **NB**: con php v8.1 e Imagick v3.7 sembra funzionare bene la versione v9.26 di Ghostscript

<br>

8. Nella cartella "_C:\Program Files\gs\gs9.26\bin\\_" duplicare il file "_gswin64.exe_" e rinominare la copia in "_gs.exe_"

9. Aggiungere la cartella "_C:\Program Files\gs\gs9.26\bin\\_" nelle variabili d'ambiente sulla voce PATH.
   Attenzione a spostare questa entry PRIMA della path (se presente) di ImageMagick (nel caso sia installato)

10. Fermare e riavviare l'ambiente di sviluppo (Apache, Xampp, Wamp, Laragon, etc.)

    ps: se ImageMagick/Ghostscript dovessero dare noie con la scritture nella cartella temporanea allora provare ad aggiungere le seguenti policy

    `<policy domain="resource" name="temporary-path" value="C:/Users/<user>/AppData/Local/Temp"/>`<br>
    `<policy domain="module" rights="read|write" pattern="{PS,PDF,XPS}"/>`<br>
    `<policy domain="coder" rights="read|write" pattern="PDF"/>`

    nel file "_C:\Program Files\ImageMagick-\<versione>\policy.xml_"

    > ##### esempio utilizzando ImageMagick v7.1.1 Q16:
    >
    > ##### _C:\Program Files\ImageMagick-7.1.1-Q16-HDRI\policy.xml_
