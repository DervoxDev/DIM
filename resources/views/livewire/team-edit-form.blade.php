<div>
    <form wire:submit.prevent="submit">
        {{ $this->form }}

        <button type="submit" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
            Save Changes
        </button>
    </form>

    @if (session()->has('message'))
        <div class="mt-4 p-4 bg-green-100 text-green-700 rounded">
            {{ session('message') }}
        </div>
    @endif
</div>
