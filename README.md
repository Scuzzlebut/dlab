<p align="left"><a href="https://digita-lab.it" target="_blank"><img src="https://digita-lab.it/wp-content/uploads/2022/09/logo-DLab-e1663166083323.jpg" width="" alt="D.Lab Logo"></a></p>

# D.Lab HR Portal

### REQUISITI SERVER:

-   Estensione "mbstring" per PHP (per verificarne la presenza: *php -m | grep mbstring*)
-   Tesseract-OCR (https://tesseract-ocr.github.io/tessdoc/Home.html#binaries)
-   PdfToText (*apt-get install poppler-utils*)
-   Imagick (guardare file [IMAGICK.md](IMAGICK.md))
-   Ghostscript (https://www.ghostscript.com/ - forse installato di default)


### LARAVEL:

1. Lanciare le migration
2. Completare file _.env_ con informazioni db.
3. Assicurarsi che il parametro APP_ENV sia impostata su "production". Compilare correttamente il parametro APP_URL.

### PHP

necessario PHP 8.1 con queste estensioni:
```
php8.1-xml
php8.1-zip
php8.1-curl
php8.1-gd
php8.1-imagick
php8.1-mysql
php8.1-redis (se si usa redis in prod)
```