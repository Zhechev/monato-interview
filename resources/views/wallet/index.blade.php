<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Wallet') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Current Balance -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Current Balance</h3>
                        <p class="mt-2 text-3xl font-bold text-indigo-600">${{ number_format(auth()->user()->wallet_balance, 2) }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if(auth()->user()->isBuyer())
                        <!-- Top Up Form (Only for Buyers) -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Top Up Wallet</h4>
                            <form action="{{ route('wallet.topup') }}" method="POST" id="topupForm" onsubmit="handleSubmit(event)">
                                @csrf
                                <div>
                                    <x-input-label for="amount" value="Amount ($)" />
                                    <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full" required />
                                    <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                                </div>
                                <div class="mt-4">
                                    <x-primary-button id="topupButton">
                                        {{ __('Top Up') }}
                                    </x-primary-button>
                                </div>
                            </form>
                        </div>

                        <script>
                            function handleSubmit(event) {
                                // Disable the submit button
                                document.getElementById('topupButton').disabled = true;

                                // Submit the form
                                return true;
                            }

                            // Re-enable the button when navigating back
                            window.onpageshow = function(event) {
                                if (event.persisted) {
                                    document.getElementById('topupButton').disabled = false;
                                }

                                // Force a hard reload if coming back to this page
                                if (performance.getEntriesByType("navigation")[0].type === "back_forward") {
                                    location.reload(true);
                                }
                            };

                            // Check if we have a success message and reload the page
                            if (document.querySelector('.text-green-600')) {
                                setTimeout(() => {
                                    location.reload(true);
                                }, 500);
                            }
                        </script>
                        @endif

                        @if(auth()->user()->isSeller())
                        <!-- Withdrawal Form (Only for Sellers) -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Withdraw Funds</h4>
                            <form action="{{ route('wallet.withdraw') }}" method="POST">
                                @csrf
                                <div>
                                    <x-input-label for="amount" value="Amount ($)" />
                                    <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" max="{{ auth()->user()->wallet_balance }}" class="mt-1 block w-full" required />
                                    <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                                </div>
                                <div class="mt-4">
                                    <x-primary-button>
                                        {{ __('Withdraw') }}
                                    </x-primary-button>
                                </div>
                            </form>
                        </div>
                        @endif

                        <!-- Recent Transactions -->
                        <div class="bg-gray-50 p-4 rounded-lg md:col-span-2">
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="text-lg font-medium text-gray-900">Recent Transactions</h4>
                                <a href="{{ route('wallet.history') }}" class="text-indigo-600 hover:text-indigo-900">View All</a>
                            </div>
                            @if($transactions->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($transactions->take(5) as $transaction)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $transaction->created_at->format('M d, Y H:i') }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {{ ucfirst($transaction->type) }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm {{ $transaction->type == 'purchase' || $transaction->type == 'withdraw' ? 'text-red-600' : 'text-green-600' }}">
                                                        {{ $transaction->type == 'purchase' || $transaction->type == 'withdraw' ? '-' : '+' }}${{ number_format($transaction->amount, 2) }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $transaction->status == 'completed' ? 'bg-green-100 text-green-800' : ($transaction->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                            {{ ucfirst($transaction->status) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-gray-500 text-sm">No transactions yet.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
