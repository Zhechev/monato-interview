<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
        @foreach ($stats as $stat)
            {{ $stat }}
        @endforeach
    </div>

    <div class="mt-8 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <x-filament::card>
            <x-slot name="heading">Recent Transactions</x-slot>

            <div class="space-y-4">
                @php
                    $recentTransactions = \App\Models\Transaction::with(['user', 'product'])
                        ->latest()
                        ->take(5)
                        ->get();
                @endphp

                @foreach ($recentTransactions as $transaction)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div>
                                <p class="font-medium">{{ $transaction->user?->name ?? 'Platform Fee' }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ ucfirst($transaction->type) }}
                                    @if ($transaction->product)
                                        - {{ $transaction->product->name }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-medium">${{ number_format($transaction->amount, 2) }}</p>
                            <p class="text-sm text-gray-500">{{ $transaction->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @if (!$loop->last)
                        <hr class="border-gray-200">
                    @endif
                @endforeach
            </div>
        </x-filament::card>

        <x-filament::card>
            <x-slot name="heading">Top Sellers</x-slot>

            <div class="space-y-4">
                @php
                    $topSellers = \App\Models\User::withCount(['products as sold_count' => function ($query) {
                        $query->where('status', 'sold');
                    }])
                    ->having('sold_count', '>', 0)
                    ->orderByDesc('sold_count')
                    ->take(5)
                    ->get();
                @endphp

                @foreach ($topSellers as $seller)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div>
                                <p class="font-medium">{{ $seller->name }}</p>
                                <p class="text-sm text-gray-500">{{ $seller->email }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-medium">{{ $seller->sold_count }} sales</p>
                        </div>
                    </div>
                    @if (!$loop->last)
                        <hr class="border-gray-200">
                    @endif
                @endforeach
            </div>
        </x-filament::card>
    </div>
</x-filament-panels::page>
