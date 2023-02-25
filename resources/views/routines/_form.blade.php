<div class="field">
    <label for="name">Name:</label>
    <input
        type="text"
        id="name"
        name="name"
        value="{{ $routine->name }}"
        autofocus
        required
    >

    @error('name')
    <p class="help danger">{{ $message }}</p>
    @enderror
</div>
