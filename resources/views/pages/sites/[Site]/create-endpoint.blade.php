<?php

use App\Enums\EndpointFrequency;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

use function Laravel\Folio\middleware;
use function Laravel\Folio\render;
use function Livewire\Volt\rules;
use function Livewire\Volt\state;
use function Livewire\Volt\with;

middleware(['auth', 'verified']);

state([
    'site' => fn () => $site,
    'location' => '',
    'frequency' => EndpointFrequency::FIVE_MINUTES->value,
]);

rules([
    'location' => ['required'],
    'frequency' => ['required', new Enum(EndpointFrequency::class)],
]);

with(fn () => ['endpointFrequencies' => EndpointFrequency::cases()]);

$createEndpoint = function () {
    $this->validate();

    $this->authorize('storeEndpoint', $this->site);

    $parsed = parse_url($this->site->url() . '/' . $this->location);

    $this->location = '/' . trim(trim(Arr::get($parsed, 'path'), '/') . '?' . Arr::get($parsed, 'query'), '?');

    $this->site->endpoints()->create([
        'location' => $this->location,
        'frequency' => $this->frequency,
        'next_check' => now()->addSeconds($this->frequency),
    ]);

    return to_route('sites.show', ['site' => $this->site]);
};

?>

<x-layouts.site :site="$site">
    <x-slot name="title">
        <div class="flex items-center">
            <span class="text-gray-800 w-full text-lg font-medium lowercase">
                Crear endpoint
            </span>
        </div>
    </x-slot>
    <div class="mx-auto max-w-xl space-y-9 px-5 pt-5">
        @volt('create-endpoint')
            <div>
                <h1 class="text-lg font-medium text-gray-800">
                    Crear Endpoint
                </h1>
                <div class="mt-1.5 text-sm text-gray-500">
                    Agrega un nuevo endpoint al sitio web
                </div>
                <form wire:submit="createEndpoint" class="mt-3">
                    <div>
                        <x-text-input wire:model="location" id="location" class="block mt-1 w-full" type="text" name="location" placeholder="p. ej. /precios" />
                        <x-input-error :messages="$errors->get('location')" class="mt-3" />
                    </div>

                    <div class="mt-4">
                        <x-select-input wire:model="frequency" id="frequency" class="block mt-1 w-full" type="text" name="frequency">
                            @foreach($endpointFrequencies as $endpointFrequency)
                                <option value="{{ $endpointFrequency->value }}">{{ $endpointFrequency->label() }}</option>
                            @endforeach
                        </x-select-input>
                        <x-input-error :messages="$errors->get('frequency')" class="mt-3" />
                    </div>

                    <div class="mt-4">
                        <x-primary-button>Crear</x-primary-button>
                    </div>
                </form>
            </div>
        @endvolt
    </div>
</x-layouts.site>