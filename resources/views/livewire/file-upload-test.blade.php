<div>
    <input
        type="file"
        wire:model="theFile"
    />
    <br>
    <a wire:click="uploadFile">Upload</a>

    <br>
    @if ($downloadUrl)
        <a href="{{ $downloadUrl }}">Download</a>
    @endif
</div>
