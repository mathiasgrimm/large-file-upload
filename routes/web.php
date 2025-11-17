<?php

use App\Livewire\FileUploadTest;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {

    Route::get('/file-upload-test', FileUploadTest::class)->name('file-upload-test');

    Route::get('/file-upload-test-quick-s3', function () {
        $t0 = microtime(true);
        Storage::disk('s3')->move('livewire-tmp/NWghdVdwK3Up47scW0AstVcN3TDLvo-metaZmlsZW5hbWUyR0IuYmlu-.bin', 'large-files/NWghdVdwK3Up47scW0AstVcN3TDLvo-metaZmlsZW5hbWUyR0IuYmlu-.bin');

        return microtime(true)-$t0;
    });

    Route::get('/file-upload-test-quick-default', function () {
        $t0 = microtime(true);
        Storage::move('livewire-tmp/NWghdVdwK3Up47scW0AstVcN3TDLvo-metaZmlsZW5hbWUyR0IuYmlu-.bin', 'large-files/NWghdVdwK3Up47scW0AstVcN3TDLvo-metaZmlsZW5hbWUyR0IuYmlu-.bin');

        return microtime(true)-$t0;
    });

    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

require __DIR__.'/auth.php';
