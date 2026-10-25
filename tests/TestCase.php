<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Nilai yang diingat secara statis (pengaturan website, daftar tabel) tidak boleh
        // terbawa dari satu test ke test berikutnya.
        \App\Support\Pengaturan::lupakan();
        \App\Support\Basisdata::lupakan();
    }
}
