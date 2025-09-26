<div>
    <div
        x-data="{ uploading: false, progress: 0 }"
        x-on:livewire-upload-start="uploading = true"
        x-on:livewire-upload-finish="uploading = false"
        x-on:livewire-upload-cancel="uploading = false"
        x-on:livewire-upload-error="uploading = false"
        x-on:livewire-upload-progress="progress = $event.detail.progress"
    >
        <input
            type="file"
            wire:model="theFile"
        />

        <!-- Progress Bar -->
        <div x-show="uploading">
            <progress max="100" x-bind:value="progress"></progress>
        </div>
    </div>

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
