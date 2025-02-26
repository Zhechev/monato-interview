<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Marketplace') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($products->isEmpty())
                        <p class="text-gray-500 text-center">{{ __('No products available at the moment.') }}</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($products as $product)
                                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                                    <div class="p-6">
                                        <h3 class="text-xl font-semibold mb-2">{{ $product->name }}</h3>
                                        <p class="text-gray-600 mb-4">{{ Str::limit($product->description, 100) }}</p>
                                        <div class="flex justify-between items-center mb-4">
                                            <div class="text-lg font-bold text-gray-900">${{ number_format($product->final_price, 2) }}</div>
                                            <div class="text-sm text-gray-500">{{ $product->total_sales }} sold</div>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <div class="text-sm text-gray-500">
                                                By {{ $product->seller->name }}
                                            </div>
                                            <a href="{{ route('products.show', $product) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                {{ __('View Details') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-6">
                            {{ $products->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
