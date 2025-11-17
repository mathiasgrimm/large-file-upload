<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class FileUploadTest extends Component
{
    use WithFileUploads;

    #[Validate('required')]
    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile */
    public $theFile;
    public $downloadUrl;

    public function render()
    {
        return view('livewire.file-upload-test');
    }

    public function uploadFile()
    {
        $this->validate();
        $targetPath = 'large-files/-'.str()->uuid()->toString().'-'.$this->theFile->getClientOriginalName();

        logger("moving {$this->theFile->getRealPath()} to {$targetPath}", ['filesystem'=> config('filesystems')]);

        // Instead of using
        // $targetPath = $this->theFile->store(path: 'large-files');

        // We have to use
        $realPath = $this->theFile->getRealPath();
        Storage::move($realPath, $targetPath);

        logger("moved {$realPath} to {$targetPath}");

//        $this->theFile = null;
//
//        $this->downloadUrl = Storage::temporaryUrl($targetPath, now()->addMinutes(5));
    }
}
