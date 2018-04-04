# PuzzleOS

Another awesome platform to create your own modular web based PHP applications

## Getting started

### Install PuzzleOS

Download repository ini dan masukkan ke dalam / (root directory), lalu buka / pada browser. Ikuti petunjuk yang tertera pada form installer. Untuk pengaturan database, buat terlebih dahulu database yang akan digunakan sebelum mengisi form installer.

### Basics

Terdapat dua folder utama yang perlu diperhatikan saat menggunakan PuzzleOS: _application_ dan _template_. Folder _application_ berisi semua aplikasi yang dapat dijalankan oleh PuzzleOS. Folder _template_ berisi semua template yang dapat digunakan oleh aplikasi (dalam folder _application_) PuzzleOS.

#### Membuat Aplikasi PuzzleOS

Berikut merupakan file-file yang harus ada dalam sebuah folder aplikasi:
1. _manifest.ini_
2. _viewPage.php_
3. _control.php_

Dan berikut merupakan file-file opsional yang biasanya digunakan dan dalam sebuah folder aplikasi:
1. _(nama tabel).table.php_
2. _viewSmall.php_
3. _(kode negara).lang.php


##### *1. manifest.ini*

Berisi informasi tentang aplikasi. Contoh *manifest.ini* dapat dilihat di *manifest.ini.sample* pada /.

```ini
rootname=
title=
description=
permission=
canBeDefault=
services=
menus=
```

* `rootname` berisi nama unik yang harus dimiliki sebuah aplikasi. Spasi dan karakter simbol tidak boleh digunakan untuk `rootname`

* `title` berisi judul aplikasi yang akan dibuat

* `description` berisi deskripsi aplikasi yang akan dibuat

* `permission` menentukan siapa yang dapat membuka aplikasi, berdasarkan *user group level*:

  * *Superuser*: `permission=0`
  * *Superuser* dan *employee*: `permission=1`
  * *Superuser*, *employee*, dan *registered user*: `permission=2`
  * Semua orang termasuk *guest*: `permission=3`

* `canBeDefault` menentukan apakah aplikasi ini dapat dijadikan sebagai *default page* atau tidak.

  * `canBeDefault=1`: dapat dijadikan sebagai *default page*

* `services` menentukan file-file yang digunakan sebagai *service* aplikasi. Setiap penambahan file dalam `services` dipisahkan dengan koma.

  Contoh: `services=background.php,run.php,manage.php`

* `menus` memberitahu aplikasi untuk *widget* dengan lokasi tertentu

  Misal: 

   * *widget1.php* diletakkan di *left*
   * *widget2.php* diletakkan di *custom_template_position*
   * *widget3.php* diletakkan di *somewhere*

  maka: 

  `menus=widget1.php>left,widget2.php>custom_template_position,widget3.php>somewhere`







(dokumentasi ini belum lengkap)
