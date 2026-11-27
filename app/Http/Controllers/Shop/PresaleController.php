<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\PresaleCampaign;
use App\Models\PresaleProduct;

class PresaleController extends Controller
{
    public function index()
    {
        $campaign = PresaleCampaign::with(['presaleProducts.product.images', 'presaleProducts.product.brand'])
            ->where('status', 'active')
            ->where('ends_at', '>=', now())
            ->first();

        if (! $campaign || ! $campaign->isOpenNow()) {
            return view('shop.presale.index', [
                'campaign' => null,
                'products' => collect(),
            ]);
        }

        $products = $campaign->presaleProducts()
            ->where('is_active', true)
            ->with('product')
            ->get();

        return view('shop.presale.index', compact('campaign', 'products'));
    }
        public function show(\App\Models\PresaleCampaign $presaleCampaign)
    {
        if ($presaleCampaign->status !== 'active') {
            abort(404);
        }

        $presaleCampaign->load(['presaleProducts.product.images', 'presaleProducts.product.brand']);

        return view('shop.presale.campaign', compact('presaleCampaign'));
    }

        public function product(\App\Models\PresaleCampaign $presaleCampaign, \App\Models\PresaleProduct $presaleProduct)
    {
        // امنیت: مطمئن شو محصول متعلق به همین کمپینه
        if ($presaleProduct->campaign_id !== $presaleCampaign->id) {
            abort(404);
        }

        if ($presaleCampaign->status !== 'active') {
            abort(404);
        }

        $presaleProduct->load(['product.images', 'product.brand', 'product.category']);

        return view('shop.presale.product', compact('presaleCampaign', 'presaleProduct'));
    }
}