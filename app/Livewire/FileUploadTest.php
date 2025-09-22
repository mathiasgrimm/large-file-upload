<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class FileUploadTest extends Component
{
    use WithFileUploads;

    #[Validate('file|max:2048576')]
    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile */
    public $theFile;
    public $downloadUrl;

    public function render()
    {
        return view('livewire.file-upload-test');
    }

    public function uploadFile()
    {
        $path = $this->theFile->store(path: 'large-files');
        $this->theFile = null;

        $this->downloadUrl = Storage::temporaryUrl($path, now()->addMinutes(5));
    }
}
