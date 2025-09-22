<?php

namespace App\Livewire;

use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class FileUploadTest extends Component
{
    use WithFileUploads;

    #[Validate('file|max:2048576')] // 1MB Max
    public $theFile;

    public function render()
    {
        return view('livewire.file-upload-test');
    }

    public function uploadFile()
    {
        dd($this->theFile);
    }
}
