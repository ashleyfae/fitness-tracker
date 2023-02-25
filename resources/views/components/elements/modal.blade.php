<div id="{{ $id }}" class="modal">
    <div class="modal-background"></div>
    <div class="modal-content">
        <div class="box">
            {{ $slot }}
        </div>
    </div>
    <button class="modal-close large" aria-label="close">&times;</button>
</div>
