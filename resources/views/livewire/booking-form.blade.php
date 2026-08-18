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

        {{-- Korak 3: Datum i vreme --}}
    @if ($currentStep >= 3)
        <div class="p-6 border-b">
            <h2 class="font-semibold text-lg mb-4">3. Izaberi datum i vreme</h2>

            {{-- Dani --}}
            <div class="flex gap-2 overflow-x-auto pb-2 mb-4">
                @foreach ($this->availableDays as $day)
                    <button
                        wire:click="selectDate('{{ $day->toDateString() }}')"
                        type="button"
                        class="flex-shrink-0 flex flex-col items-center px-3 py-2 rounded-lg border transition
                            {{ $date === $day->toDateString()
                                ? 'border-orange-500 bg-orange-50 ring-1 ring-orange-500'
                                : 'border-gray-200 hover:border-gray-300' }}"
                    >
                        <span class="text-xs text-gray-500">{{ $day->translatedFormat('D') }}</span>
                        <span class="font-medium">{{ $day->format('d.m') }}</span>
                    </button>
                @endforeach
            </div>

            {{-- Slotovi --}}
            @if ($date)
                @if (count($this->availableSlots) > 0)
                    <div class="flex flex-wrap gap-2">
                        @foreach ($this->availableSlots as $slot)
                            <button
                                wire:click="selectSlot('{{ $slot }}')"
                                type="button"
                                class="px-4 py-2 rounded-lg border transition
                                    {{ $start_time === $slot
                                        ? 'border-orange-500 bg-orange-50 ring-1 ring-orange-500'
                                        : 'border-gray-200 hover:border-gray-300' }}"
                            >
                                {{ $slot }}
                            </button>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-sm">Nema slobodnih termina za izabrani datum.</p>
                @endif
            @endif
        </div>
    @endif

    {{-- Korak 4: Podaci klijenta --}}
    @if ($currentStep >= 4 && ! $bookingComplete)
        <div class="p-6">
            <h2 class="font-semibold text-lg mb-4">4. Tvoji podaci</h2>

            @error('general')
                <div
                    x-data="{ show: true }"
                    x-init="setTimeout(() => show = false, 6000)"
                    x-show="show"
                    x-transition
                    class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm"
                >
                    {{ $message }}
                </div>
            @enderror

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ime i prezime</label>
                    <input
                        type="text"
                        wire:model="customer_name"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-orange-500 focus:border-orange-500"
                        placeholder="Marko Marković"
                    >
                    @error('customer_name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input
                        type="email"
                        wire:model="customer_email"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-orange-500 focus:border-orange-500"
                        placeholder="marko@example.com"
                    >
                    @error('customer_email') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
                    <input
                        type="text"
                        wire:model="customer_phone"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-orange-500 focus:border-orange-500"
                        placeholder="064/123-456"
                    >
                    @error('customer_phone') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <button
                    wire:click="submit"
                    wire:loading.attr="disabled"
                    wire:target="submit"
                    type="button"
                    class="w-full bg-orange-500 hover:bg-orange-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold py-3 rounded-lg transition"
                >
                    <span wire:loading.remove wire:target="submit">ZAKAŽI TERMIN</span>
                    <span wire:loading wire:target="submit">Šalje se...</span>
                </button>
            </div>
        </div>
    @endif

    {{-- Poruka o uspehu --}}
    @if ($bookingComplete)
    <div
        class="p-8 text-center"
        x-data="{ secondsLeft: 15 }"
        x-init="
            const interval = setInterval(() => {
                secondsLeft--;
                if (secondsLeft <= 0) {
                    clearInterval(interval);
                    $wire.bookAnother();
                }
            }, 1000);
        "
    >
        <div class="text-5xl mb-4">✅</div>
        <h2 class="font-semibold text-xl mb-2">Termin je rezervisan!</h2>
        <p class="text-gray-600 mb-6">
            Poslali smo ti email na <strong>{{ $customer_email }}</strong> — potvrdi termin klikom na link u mejlu u narednih 15 minuta, u suprotnom će termin biti otkazan.
        </p>

        <button
            wire:click="bookAnother"
            type="button"
            class="text-sm text-orange-600 hover:text-orange-700 font-medium"
        >
            Zakaži još jedan termin sada (ili sačekaj <span x-text="secondsLeft"></span>s za automatski reset)
        </button>
    </div>
    @endif