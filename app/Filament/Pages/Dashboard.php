<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Filament\Pages\Page;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.dashboard';

    protected static ?int $navigationSort = -2;

    protected static ?string $navigationGroup = null;

    protected static ?string $title = 'Dashboard';

    public static function getNavigationLabel(): string
    {
        return static::$title ?? 'Dashboard';
    }

    public function getStats(): array
    {
        $totalSystemBalance = Transaction::where('type', 'commission')
            ->where('status', 'completed')
            ->sum('amount');

        $totalSales = Transaction::where('type', 'purchase')
            ->where('status', 'completed')
            ->count();

        $totalSalesAmount = Transaction::where('type', 'purchase')
            ->where('status', 'completed')
            ->sum('amount');

        $activeProducts = Product::where('status', 'active')->count();

        return [
            Stat::make('System Balance', '$' . number_format($totalSystemBalance, 2))
                ->description('Total earnings from commissions')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Total Sales', $totalSales)
                ->description('Total completed purchases')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary'),

            Stat::make('Sales Volume', '$' . number_format($totalSalesAmount, 2))
                ->description('Total sales amount')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Active Products', $activeProducts)
                ->description('Products available for purchase')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('warning'),
        ];
    }

    protected function getViewData(): array
    {
        return [
            'stats' => $this->getStats(),
        ];
    }
}
