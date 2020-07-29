<?php


namespace App\Services;


use App\Account;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FetchPrices
{
    public function fetchLivePrices(string $product, ?string $account)
    {

        $livePrices = json_decode(Storage::disk('local')->get('live_prices.json'), true);
        $price = null;

        foreach ($livePrices as $livePrice) {

            if ($this->productCodeMatches($product, $livePrice) && $this->accountMatches($account, $livePrice)) {

                $price = $price === null || $livePrice['price'] < $price ? $livePrice['price'] : $price;

            }

        }

        return $price;
    }

    private function productCodeMatches(string $product, array $livePrice): bool
    {
        return $product === $livePrice['sku'];
    }

    private function accountMatches(?string $account, array $livePrice): bool
    {

        if (!$account) {
            return !array_key_exists('account', $livePrice);
        }

        return array_key_exists('account', $livePrice) && $livePrice['account'] == $account;
    }

    public function fetch(array $productCodes, ?int $accountID): array
    {
        $accountReference = $accountID ? Account::find($accountID)->external_reference : null;

        $livePrices = [];
        foreach ($productCodes as $productCode) {
            $livePrice = $this->fetchLivePrices($productCode, $accountReference);

            if ($livePrice != null) {
                $livePrices[$productCode] = $livePrice;
            }
        }


        $livePriceProductCodes = array_keys($livePrices);

        $productCodesWithNoPrice = array_diff($productCodes, $livePriceProductCodes);

        $databasePrices = !empty($productCodesWithNoPrice) ? $this->fetchDatabasePrices($productCodesWithNoPrice, $accountID) : [];

        return array_merge($livePrices, $databasePrices);
    }

    public function fetchDatabasePrices(array $productCodes, ?int $accountID): array
    {

        // $key = implode($productCodes) . '_' . 'account_id';
        //return Cache::remember($key, 86400, function () use  {

        $sql = DB::table('prices')
            ->join('products', 'products.id', '=', 'prices.product_id')
            ->select('products.sku', DB::raw('MIN(prices.value) as output_price'))
            ->whereIn('products.sku', $productCodes);

        if ($accountID) {
            $sql->where('prices.account_id', '=', $accountID);
        } else {
            $sql->whereNUll('prices.account_id');
        }

        $sql->groupBy('prices.product_id');

        return $sql->get()->pluck('output_price', 'sku')->toArray();
    }
}
