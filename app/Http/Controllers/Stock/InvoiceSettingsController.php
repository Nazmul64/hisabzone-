<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\StockInvoiceSettings;
use Illuminate\Http\Request;

class InvoiceSettingsController extends Controller
{
    public function show(Request $request)
    {
        $settings = StockInvoiceSettings::where('user_id', $request->user()->id)->first();

        if (!$settings) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'business_name'           => 'My Business',
                    'business_tagline'        => '',
                    'phone'                   => '',
                    'email'                   => '',
                    'address'                 => '',
                    'logo_url'                => null,
                    'admin_name'              => 'Admin',
                    'admin_title'             => 'Administrator',
                    'payment_method'          => 'Cash',
                    'terms'                   => 'The product/service has been properly verified and accepted.',
                    'currency_symbol'         => 'BDT',
                    'invoice_prefix_sale'     => 'S',
                    'invoice_prefix_purchase' => 'P',
                ],
            ]);
        }

        return response()->json(['success' => true, 'data' => $settings]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_name'           => 'required|string|max:100',
            'business_tagline'        => 'nullable|string|max:200',
            'phone'                   => 'nullable|string|max:20',
            'email'                   => 'nullable|email|max:100',
            'address'                 => 'nullable|string|max:255',
            'logo_url'                => 'nullable|string|max:500',  // url validation removed
            'admin_name'              => 'nullable|string|max:100',
            'admin_title'             => 'nullable|string|max:100',
            'payment_method'          => 'nullable|string|max:50',
            'terms'                   => 'nullable|string|max:1000',
            'currency_symbol'         => 'nullable|string|max:10',
            'invoice_prefix_sale'     => 'nullable|string|max:5',
            'invoice_prefix_purchase' => 'nullable|string|max:5',
        ]);

        $settings = StockInvoiceSettings::updateOrCreate(
            ['user_id' => $request->user()->id],
            array_merge($validated, ['user_id' => $request->user()->id])
        );

        return response()->json(['success' => true, 'data' => $settings, 'message' => 'Invoice settings saved.'], 201);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'business_name'           => 'sometimes|string|max:100',
            'business_tagline'        => 'nullable|string|max:200',
            'phone'                   => 'nullable|string|max:20',
            'email'                   => 'nullable|email|max:100',
            'address'                 => 'nullable|string|max:255',
            'logo_url'                => 'nullable|string|max:500',  // url validation removed
            'admin_name'              => 'nullable|string|max:100',
            'admin_title'             => 'nullable|string|max:100',
            'payment_method'          => 'nullable|string|max:50',
            'terms'                   => 'nullable|string|max:1000',
            'currency_symbol'         => 'nullable|string|max:10',
            'invoice_prefix_sale'     => 'nullable|string|max:5',
            'invoice_prefix_purchase' => 'nullable|string|max:5',
        ]);

        $settings = StockInvoiceSettings::updateOrCreate(
            ['user_id' => $request->user()->id],
            array_merge($validated, ['user_id' => $request->user()->id])
        );

        return response()->json(['success' => true, 'data' => $settings, 'message' => 'Invoice settings updated.']);
    }
}
