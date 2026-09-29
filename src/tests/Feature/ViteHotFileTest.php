<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\Vite;
use Tests\TestCase;

class ViteHotFileTest extends TestCase
{
    public function test_設定がなければ標準のホットファイルを使う(): void
    {
        config(['app.vite_hot_file' => null]);

        (new AppServiceProvider($this->app))->boot();

        $this->assertSame(public_path('hot'), Vite::hotFile());
    }

    public function test_ホットファイルの場所を設定で変更できる(): void
    {
        config(['app.vite_hot_file' => storage_path('framework/no-vite-hot')]);

        (new AppServiceProvider($this->app))->boot();

        $this->assertSame(storage_path('framework/no-vite-hot'), Vite::hotFile());
    }
}
