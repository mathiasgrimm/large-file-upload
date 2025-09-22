<div>
    <input
        type="file"
        wire:model="theFile"
    />
    <br>

    <div>
        @if ($theFile)
            <a wire:click="uploadFile">Upload</a>
        @endif
    </div>

    <br>

    <div>
        @if ($downloadUrl)
            <a href="{{ $downloadUrl }}">Download</a>
        @endif
    </div>
</div>
