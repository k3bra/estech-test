<?php

namespace App\Http\Controllers;


use App\Account;
use App\Product;
use App\Services\FetchPrices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductPriceController extends Controller
{

    /**
     *
     *
     *
     *
     * 127.0.0.1:8000/prices?product_code[]=XBIMAL&product_code[]=DLWWXS&product_code[]=TAUXLC
     *
     *
     *
     *
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {

        $validator = \Validator::make($request->all(),
            [
                'product_code' => 'required|array|min:1',
                'account_id' => 'nullable|integer|exists:App\Account,id'
            ]
        );




        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }


        $fetchPricesSrv = new FetchPrices();


        return $fetchPricesSrv->fetch($request->get('product_code'), $request->get('account_id'));

    }
}
