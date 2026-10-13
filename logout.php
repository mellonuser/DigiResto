<?php
require 'config.php';
// Hapus seluruh data sesi agar pengguna benar-benar keluar dari aplikasi.
Auth::logout();
Helper::redirect('login.php');
