<?php

namespace App\Services;

use App\Account;
use App\Price;
use App\Product;
use App\User;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

class ImportPrices
{

    protected  $titles = [
        'sku' => null,
        'account_ref' => null,
        'user_ref' => null,
        'quantity' => null,
        'value' => null,
    ];


    public function import(string $realPath)
    {

        $pricesToImport = $this->retrieveDataFromCsv($realPath);

        $userRefs = $this->extractColumn($pricesToImport, 'user_reference');
        $accountRefs = $this->extractColumn($pricesToImport, 'account_reference');
        $sku = $this->extractColumn($pricesToImport, 'sku');


        $users = User::select('external_reference', 'id')->whereIn('external_reference', $userRefs)->get()->keyBy('external_reference');
        $accounts = Account::select('external_reference', 'id')->whereIn('external_reference', $accountRefs)->get()->keyBy('external_reference');
        $products = Product::select('sku', 'id')->whereIn('sku', $sku)->get()->keyBy('sku');


        $insertData = [];
        foreach ($pricesToImport as $price) {
            $insertData[] = [
                'product_id' => $products[$price['sku']]->id,
                'account_id' => $accounts[$price['account_reference']]->id ?? null,
                'user_id' => $users[$price['user_reference']]->id ?? null,
                'quantity' => $price['quantity'],
                'value' => $price['value'],
            ];
        }

        Price::insert($insertData);


    }

    public function extractColumn(array $array, $keyToExtract): array
    {

        $result = [];
        foreach ($array as $value) {

            if ($value[$keyToExtract]) {

                $result[] = $value[$keyToExtract];
            }

        }
        return $result;

    }

    public function retrieveDataFromCsv(string $realPath) {

        $reader = ReaderEntityFactory::createReaderFromFile($realPath);
        $reader->open($realPath);

        $pricesToImport = [];
        foreach ($reader->getSheetIterator() as $sheet) {

            if ($sheet->getIndex() === 0) {
                $rowIndex = 0;
                foreach ($sheet->getRowIterator() as $row) {
                    $cells = $row->getCells();

                    if ($rowIndex == 0) {

                        $this->buildDictionary($cells);

                        $rowIndex++;

                        continue;
                    }

                    $sku = $cells[$this->getSkuColumn()]->getValue();
                    $quantity = $cells[$this->getQuantityColumn()]->getValue();
                    $value = $cells[$this->getValueColumn()]->getValue();
                    $userRef = $cells[$this->getUserRefColumn()]->getValue();
                    $accountRef = $cells[$this->getAccountRefColumn()]->getValue();

                    $this->validateCells($sku, $quantity, $value, $rowIndex);

                    $pricesToImport[] = ['sku' => $sku, 'quantity' => $quantity, 'user_reference' => $userRef, 'account_reference' => $accountRef, 'value' => $value];

                }
            }

        }

        $reader->close();

        return $pricesToImport;

    }

    /**
     * @param $sku
     * @param $quantity
     * @param $value
     * @param $rowNumber
     * @throws \Exception
     */
    public function validateCells($sku, $quantity, $value, $rowNumber)
    {

        if (empty($sku)) {
            throw new \Exception('Sku not valid. row: ' . $rowNumber);
        }

        if (empty($quantity) || !is_numeric($quantity)) {
            throw new \Exception('Quantity not valid. row: ' . $rowNumber);
        }


        if (empty($value) || !is_numeric($value)) {
            throw new \Exception('Value not valid. row: ' . $rowNumber);
        }

    }

    public function getAccountRefColumn()
    {
        if ($this->titles['account_ref'] === null) {
            throw new \Exception('Could not find key for account_ref');
        }

        return $this->titles['account_ref'];
    }

    private function getUserRefColumn()
    {
        if ($this->titles['user_ref'] === null) {
            throw new \Exception('Could not find key for user_ref');
        }

        return $this->titles['user_ref'];
    }

    private function getQuantityColumn()
    {
        if ($this->titles['quantity'] === null) {
            throw new \Exception('Could not find key for quantity');
        }

        return $this->titles['quantity'];
    }


    private function getValueColumn()
    {
        if ($this->titles['value'] === null) {
            throw new \Exception('Could not find key for value');
        }

        return $this->titles['value'];
    }


    private function getSkuColumn()
    {
        if ($this->titles['sku'] === null) {
            throw new \Exception('Could not find key for sku');
        }

        return $this->titles['sku'];
    }

    private function buildDictionary($cells)
    {
        foreach ($cells as $key => $cell) {
            $this->titles[trim(strtolower($cell->getValue()))] = $key;
        }
    }
}
