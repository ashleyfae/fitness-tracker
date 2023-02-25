<div class="field">
    <label for="name">Name:</label>
    <input
        type="text"
        id="name"
        name="name"
        value="{{ $exercise->name }}"
        autofocus
        required
    >

    @error('name')
    <p class="help danger">{{ $message }}</p>
    @enderror
</div>

<div class="field">
    <label for="description">Description:</label>
    <textarea
        id="description"
        name="description"
    >{{ $exercise->description }}</textarea>
    @error('description')
    <span class="help danger">{{ $message }}</span>
    @enderror
</div>
