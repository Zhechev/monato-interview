<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Product Details') }}
            </h2>
            <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Back to Marketplace') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h1 class="text-2xl font-bold mb-4">{{ $product->name }}</h1>
                            <div class="mb-6">
                                <p class="text-gray-600">{{ $product->description }}</p>
                            </div>
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold mb-2">{{ __('Seller Information') }}</h3>
                                <p class="text-gray-600">{{ $product->seller->name }}</p>
                            </div>
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold mb-2">{{ __('Product Statistics') }}</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-gray-500">{{ __('Total Sales') }}</p>
                                        <p class="text-xl font-bold">{{ $product->total_sales }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">{{ __('Total Revenue') }}</p>
                                        <p class="text-xl font-bold">${{ number_format($product->total_revenue, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold mb-2">{{ __('Pricing') }}</h3>
                                <div class="text-3xl font-bold text-gray-900">${{ number_format($product->final_price, 2) }}</div>
                            </div>
                            @auth
                                @if(auth()->user()->isBuyer())
                                    <form action="{{ route('products.purchase', $product) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            {{ __('Purchase Now') }}
                                        </button>
                                    </form>
                                @else
                                    <div class="text-center text-gray-500">
                                        {{ __('Only buyers can purchase products.') }}
                                    </div>
                                @endif
                            @else
                                <div class="text-center">
                                    <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-900">
                                        {{ __('Log in to purchase this product') }}
                                    </a>
                                </div>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
