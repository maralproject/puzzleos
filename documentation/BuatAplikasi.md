# Membuat Aplikasi PuzzleOS

Berikut merupakan file-file yang harus ada dalam sebuah folder aplikasi:

1. _manifest.ini_
2. _viewPage.php_
3. _control.php_

Dan berikut merupakan file-file opsional yang biasanya digunakan dan dalam sebuah folder aplikasi:

1. _(nama tabel).table.php_
2. _viewSmall.php_
3. _(kode bahasa negara).lang.php

#### *1. manifest.ini*

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

- `rootname` berisi nama unik yang harus dimiliki sebuah aplikasi. Spasi dan karakter simbol tidak boleh digunakan untuk `rootname`

- `title` berisi judul aplikasi yang akan dibuat

- `description` berisi deskripsi aplikasi yang akan dibuat

- `permission` menentukan siapa yang dapat membuka aplikasi, berdasarkan *user group level*:

  - *Superuser*: `permission=0`
  - *Superuser* dan *employee*: `permission=1`
  - *Superuser*, *employee*, dan *registered user*: `permission=2`
  - Semua orang termasuk *guest*: `permission=3`

- `canBeDefault` menentukan apakah aplikasi ini dapat dijadikan sebagai *default page* atau tidak.

  - `canBeDefault=1`: dapat dijadikan sebagai *default page*

- `services` menentukan file-file yang digunakan sebagai *service* aplikasi. Setiap penambahan file dalam `services` dipisahkan dengan koma. File-file yang disebutkan dalam parameter ini akan dijalankan di dalam lingkungan PuzzleOS meskipun aplikasi terkait tidak dijalankan.

  Contoh: `services=background.php,run.php,manage.php`

- `menus` memberitahu aplikasi untuk *widget* dengan lokasi tertentu

  Misal: 

  - *widget1.php* diletakkan di *left*
  - *widget2.php* diletakkan di *custom_template_position*
  - *widget3.php* diletakkan di *somewhere*

  maka: 

  `menus=widget1.php>left,widget2.php>custom_template_position,widget3.php>somewhere`

#### 2. viewPage.php

File ini berisi tampilan utama dari sebuah aplikasi. File ini di-*load* oleh template dengan `$tmpl->navigation->loadMainView()`. Untuk membuat lebih dari satu *view* pada aplikasi, *view* dapat dipisah ke file yang berbeda, kemudian menggunakan `__getURI("action")` untuk mendapatkan nama *action* tertentu.

Contoh: 

**viewPage.php:**

```php+HTML
<?php
switch(__getURI("action")) {
    case "halaman1": include ("halaman1.php");
    break;
}
?>
```

**halaman1.php:**

```php+HTML
<h1>
    Selamat datang di halaman 1.
</h1>
```

Kemudian buka /(nama aplikasi)/halaman1



#### 3. control.php

File ini berisi PHP script yang akan dieksekusi sebelum aplikasi ditampilkan.

#### 4. (nama tabel).table.php

File ini berisi struktur tabel untuk digunakan oleh aplikasi. Tabel ini dapat diakses dengan nama "app\_(nama aplikasi)\_(nama tabel)". Untuk formatting tabel dan database dapat dilihat di dokumentasi [Database](Classes.md#database-databasephp) (dokumentasi ini belum lengkap).

#### 5. (kode bahasa negara).lang.php

File ini berisi konfigurasi bahasa.





(dokumentasi ini belum lengkap)