<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserSetting;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    // সব supported currency list
    private array $currencies = [
        ['code' => 'BDT', 'name' => 'Bangladeshi Taka',  'symbol' => '৳'],
        ['code' => 'USD', 'name' => 'US Dollar',          'symbol' => '$'],
        ['code' => 'EUR', 'name' => 'Euro',               'symbol' => '€'],
        ['code' => 'GBP', 'name' => 'British Pound',      'symbol' => '£'],
        ['code' => 'INR', 'name' => 'Indian Rupee',       'symbol' => '₹'],
        ['code' => 'SAR', 'name' => 'Saudi Riyal',        'symbol' => '﷼'],
        ['code' => 'AED', 'name' => 'UAE Dirham',         'symbol' => 'د.إ'],
        ['code' => 'MYR', 'name' => 'Malaysian Ringgit',  'symbol' => 'RM'],
        ['code' => 'SGD', 'name' => 'Singapore Dollar',   'symbol' => 'S$'],
        ['code' => 'JPY', 'name' => 'Japanese Yen',       'symbol' => '¥'],
        ['code' => 'CNY', 'name' => 'Chinese Yuan',       'symbol' => '¥'],
        ['code' => 'CAD', 'name' => 'Canadian Dollar',    'symbol' => 'C$'],
        ['code' => 'AUD', 'name' => 'Australian Dollar',  'symbol' => 'A$'],
    ];

    // GET currencies
    public function index()
    {
        try {
            $setting  = UserSetting::where('key', 'currency')->first();
            $selected = $setting ? ($setting->value ?? 'BDT') : 'BDT';

            return response()->json([
                'success' => true,
                'data'    => [
                    'list'     => $this->currencies,
                    'selected' => $selected,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // POST currencies/select
    public function select(Request $request)
    {
        try {
            $request->validate([
                'code' => 'required|string|max:10',
            ]);

            $codes = array_column($this->currencies, 'code');
            if (!in_array($request->code, $codes)) {
                return response()->json(['success' => false, 'message' => 'Invalid currency code'], 422);
            }

            UserSetting::updateOrCreate(
                ['key' => 'currency'],
                ['value' => $request->code]
            );

            $currency = collect($this->currencies)->firstWhere('code', $request->code);

            return response()->json([
                'success' => true,
                'data'    => ['selected' => $request->code, 'currency' => $currency],
                'message' => 'Currency updated',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
