<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Template frontend aktif (mis. "templates.basic.").
     */
    public $activeTemplate;

    /**
     * Banyak controller memanggil parent::__construct().
     * Di skeleton Laravel 12 constructor ini hilang, sehingga muncul error
     * "Cannot call constructor" dan halaman berakhir sebagai 404/500.
     */
    public function __construct()
    {
        $this->activeTemplate = activeTemplate();
    }

    public static function middleware()
    {
        return [];
    }
}
