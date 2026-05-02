Could not load ticket details.
Server/database not responding.
Retry now@extends('layouts.app')

@section('title', 'My profile')

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">

    <div class="space-y-6">
        <div class="content-header">
            <h1 class="text-2xl font-semibold text-slate-900 sm:text-3xl">My profile</h1>
            <p class="mt-1 text-sm text-slate-500">Update your display name and profile picture.</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/80">
                <h2 class="text-base font-semibold text-slate-900">Profile information</h2>
                <p class="mt-0.5 text-sm text-slate-500">Changes apply across the helpdesk.</p>
            </div>
            <div class="p-6">
                <form action="{{ route('profile.update') }}" method="post" enctype="multipart/form-data" class="space-y-6" id="profile-form">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="profile_picture_data" id="profile_picture_data" value="">

                    {{-- Profile picture --}}
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                        <button type="button" id="avatar-click-target" class="shrink-0 relative h-24 w-24 rounded-full border-2 border-slate-200 bg-slate-100 flex items-center justify-center shadow-sm hover:shadow-md hover:border-blue-400 transition-all focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            @if($user->avatar_url)
                                <img src="{{ $user->avatar_url }}?v={{ $user->updated_at?->timestamp ?? time() }}" alt="Profile" class="h-24 w-24 rounded-full object-cover" id="avatar-preview">
                            @else
                                <div class="h-24 w-24 rounded-full bg-slate-600 flex items-center justify-center text-white text-2xl font-semibold" id="avatar-placeholder">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <img src="" alt="" class="h-24 w-24 rounded-full object-cover hidden" id="avatar-preview">
                            @endif
                            <span class="pointer-events-none absolute bottom-0 right-0 inline-flex h-7 w-7 items-center justify-center rounded-full bg-blue-600 text-white shadow-md ring-2 ring-white">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7a2 2 0 012-2h2l1-1h6l1 1h2a2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V7z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </span>
                        </button>
                        <div class="min-w-0 flex-1 space-y-2">
                            <div>
                                <p class="text-sm font-medium text-slate-700">Profile picture</p>
                                <input type="file" name="profile_picture" id="profile_picture" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" class="hidden">
                                <p class="mt-1 text-xs text-slate-500">Click your photo to choose a new picture. JPEG, PNG, GIF or WebP. Max 2 MB.</p>
                                <p class="mt-1 text-xs text-slate-400" id="chosen-name"></p>
                                @error('profile_picture')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                @error('profile_picture_data')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            @if($user->profile_picture)
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="remove_profile_picture" value="1" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" id="remove_profile_picture">
                                    <span class="text-sm text-slate-600">Remove current picture</span>
                                </label>
                            @endif
                        </div>
                    </div>

                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                               class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                        <input type="text" value="{{ $user->email }}" disabled
                               class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-500 cursor-not-allowed">
                        <p class="mt-1 text-xs text-slate-400">Email is managed by your administrator.</p>
                    </div>

                    <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:gap-4">
                        <button type="submit" class="inline-flex min-h-[44px] items-center justify-center rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700">
                            Save profile
                        </button>
                        <a href="{{ route('home') }}" class="inline-flex min-h-[44px] items-center justify-center rounded-xl border border-slate-300 bg-white px-6 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Crop modal --}}
    <div id="crop-modal" class="fixed inset-0 z-50 hidden" aria-modal="true" role="dialog" aria-labelledby="crop-modal-title">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" id="crop-modal-backdrop"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="relative w-full max-w-lg rounded-2xl border border-slate-200 bg-white shadow-xl">
                <div class="p-4 border-b border-slate-200">
                    <h2 id="crop-modal-title" class="text-lg font-semibold text-slate-900">Crop profile picture</h2>
                    <p class="mt-1 text-sm text-slate-500">Drag to reposition, scroll to zoom. Square crop will be used.</p>
                </div>
                <div class="p-4">
                    <div class="max-h-[60vh] overflow-hidden rounded-lg bg-slate-100">
                        <img id="crop-image" src="" alt="Crop" class="block max-w-full max-h-[50vh]">
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 justify-end p-4 border-t border-slate-200">
                    <button type="button" id="crop-cancel" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</button>
                    <button type="button" id="crop-use-original" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Use original</button>
                    <button type="button" id="crop-apply" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700">Use cropped</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
    <script>
(function() {
    var fileInput = document.getElementById('profile_picture');
    var dataInput = document.getElementById('profile_picture_data');
    var modal = document.getElementById('crop-modal');
    var cropImage = document.getElementById('crop-image');
    var cropCancel = document.getElementById('crop-cancel');
    var cropApply = document.getElementById('crop-apply');
    var cropUseOriginal = document.getElementById('crop-use-original');
    var backdrop = document.getElementById('crop-modal-backdrop');
    var currentFileForOriginal = null;
    var chosenName = document.getElementById('chosen-name');
    var avatarPreview = document.getElementById('avatar-preview');
    var avatarPlaceholder = document.getElementById('avatar-placeholder');
    var removeCheckbox = document.getElementById('remove_profile_picture');
    var cropper = null;

    function openModal(file) {
        currentFileForOriginal = file;
        var url = URL.createObjectURL(file);
        cropImage.src = url;
        modal.classList.remove('hidden');
        chosenName.textContent = file.name;
        if (cropper) cropper.destroy();
        cropper = new Cropper(cropImage, {
            aspectRatio: 1, viewMode: 1, dragMode: 'move', autoCropArea: 0.8,
            restore: false, guides: true, center: true, highlight: false,
            cropBoxMovable: true, cropBoxResizable: true, toggleDragModeOnDblclick: false
        });
    }

    function closeModal(keepFile) {
        modal.classList.add('hidden');
        if (cropper) { cropper.destroy(); cropper = null; }
        if (cropImage.src) { URL.revokeObjectURL(cropImage.src); cropImage.src = ''; }
        if (!keepFile) fileInput.value = '';
        currentFileForOriginal = null;
    }

    function useOriginal() {
        if (!currentFileForOriginal) return;
        dataInput.value = '';
        var url = URL.createObjectURL(currentFileForOriginal);
        if (avatarPreview) { avatarPreview.src = url; avatarPreview.classList.remove('hidden'); }
        if (avatarPlaceholder) avatarPlaceholder.classList.add('hidden');
        if (removeCheckbox) removeCheckbox.checked = false;
        var headerAvatar = document.getElementById('header-avatar');
        var headerPlaceholder = document.getElementById('header-avatar-placeholder');
        if (headerAvatar) headerAvatar.src = url;
        else if (headerPlaceholder) {
            var newImg = document.createElement('img');
            newImg.id = 'header-avatar'; newImg.src = url; newImg.alt = '';
            newImg.className = 'h-9 w-9 rounded-full object-cover shrink-0 border border-slate-200';
            headerPlaceholder.parentNode.replaceChild(newImg, headerPlaceholder);
        }
        closeModal(true);
    }

    function applyCrop() {
        if (!cropper) return;
        var canvas = cropper.getCroppedCanvas({ width: 400, height: 400, imageSmoothingEnabled: true, imageSmoothingQuality: 'high' });
        if (!canvas) return;
        var dataUrl = canvas.toDataURL('image/jpeg', 0.9);
        dataInput.value = dataUrl;
        if (avatarPreview) { avatarPreview.src = dataUrl; avatarPreview.classList.remove('hidden'); }
        if (avatarPlaceholder) avatarPlaceholder.classList.add('hidden');
        if (removeCheckbox) removeCheckbox.checked = false;
        var headerAvatar = document.getElementById('header-avatar');
        var headerPlaceholder = document.getElementById('header-avatar-placeholder');
        if (headerAvatar) { headerAvatar.src = dataUrl; }
        else if (headerPlaceholder) {
            var newImg = document.createElement('img');
            newImg.id = 'header-avatar'; newImg.src = dataUrl; newImg.alt = '';
            newImg.className = 'h-9 w-9 rounded-full object-cover shrink-0 border border-slate-200';
            headerPlaceholder.parentNode.replaceChild(newImg, headerPlaceholder);
        }
        closeModal();
    }

    var avatarClickTarget = document.getElementById('avatar-click-target');
    if (avatarClickTarget && fileInput) {
        avatarClickTarget.addEventListener('click', function(e) { e.preventDefault(); fileInput.click(); });
    }

    fileInput.addEventListener('change', function() {
        var file = this.files[0];
        if (!file) return;
        if (!file.type.match(/^image\/(jpeg|png|gif|webp)$/i)) { alert('Please choose a JPEG, PNG, GIF or WebP image.'); this.value = ''; return; }
        if (file.size > 2 * 1024 * 1024) { alert('Image must be under 2 MB.'); this.value = ''; return; }
        openModal(file);
    });

    cropCancel.addEventListener('click', function() { closeModal(false); });
    backdrop.addEventListener('click', function() { closeModal(false); });
    cropApply.addEventListener('click', applyCrop);
    cropUseOriginal.addEventListener('click', useOriginal);
})();
    </script>
@endsection
