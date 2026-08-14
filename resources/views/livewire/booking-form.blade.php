<div class="bg-white rounded-xl shadow-md overflow-hidden">

    {{-- Header --}}
    <div class="bg-gray-900 text-white text-center py-6">
        <h1 class="text-2xl font-bold">BARBER BOKI</h1>
        <p class="text-sm text-gray-300">Kralja Petra 12, Novi Sad</p>
    </div>

    {{-- Korak 1: Izbor usluge --}}
    <div class="p-6 border-b">
        <h2 class="font-semibold text-lg mb-4">1. Izaberi uslugu</h2>

        <div class="space-y-2">
            @foreach ($services as $service)
                <button
                    wire:click="selectService({{ $service->id }})"
                    type="button"
                    class="w-full flex justify-between items-center px-4 py-3 rounded-lg border text-left transition
                        {{ $service_id === $service->id
                            ? 'border-orange-500 bg-orange-50 ring-1 ring-orange-500'
                            : 'border-gray-200 hover:border-gray-300' }}"
                >
                    <span class="font-medium">{{ $service->name }}</span>
                    <span class="text-gray-600">{{ number_format($service->price, 0, ',', '.') }} RSD</span>
                </button>
            @endforeach
        </div>
    </div>

   {{-- Korak 2: Izbor majstora --}}
    @if ($currentStep >= 2)
        <div class="p-6 border-b">
            <h2 class="font-semibold text-lg mb-4">2. Izaberi majstora</h2>

            <div class="flex flex-wrap gap-2">
                @foreach ($staff as $member)
                    <button
                        wire:click="selectStaff({{ $member->id }})"
                        type="button"
                        class="px-4 py-2 rounded-full border transition
                            {{ $staffChosen && $staff_id === $member->id
                                ? 'border-orange-500 bg-orange-50 ring-1 ring-orange-500'
                                : 'border-gray-200 hover:border-gray-300' }}"
                    >
                        {{ $member->name }}
                    </button>
                @endforeach

                <button
                    wire:click="selectStaff(null)"
                    type="button"
                    class="px-4 py-2 rounded-full border transition
                        {{ $staffChosen && $staff_id === null
                            ? 'border-orange-500 bg-orange-50 ring-1 ring-orange-500'
                            : 'border-gray-200 hover:border-gray-300' }}"
                >
                    Bilo ko
                </button>
            </div>
        </div>
    @endif

    {{-- Korak 3: placeholder --}}
    @if ($currentStep >= 3)
        <div class="p-6 border-b text-gray-400 italic">
            3. Izaberi datum i vreme (sledeći korak, pravimo ga sada)
        </div>
    @endif