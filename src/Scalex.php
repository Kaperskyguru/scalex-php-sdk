<?php

namespace App\Services;

use App\Services\Service;
use Exception;
use GuzzleHttp\Utils;

class Scalex extends Service
{
    /**
     * Scalex API PHP SDK
     */
    function __construct()
    {
        $baseURL = $_ENV["SCALEX_BASE_URL"];
        if (!$baseURL) {
            throw new Exception("Provide a BASE URL", 1);
        }

        $api_key = $_ENV["SCALEX_API_KEY"];
        if (!$api_key) {
            throw new Exception("Provide your S-API-KEY", 1);
        }

        $this->network = $_ENV["SCALEX_NETWORK_TYPE"];
        if (!$api_key) {
            throw new Exception("Provide your Network Type", 1);
        }

        parent::__construct($baseURL, $api_key);
    }

    /**
     * Registers a new company account
     * 
     * @param array $data Company data
     * @return object
     */

    public function register(array $data)
    {
        if (!in_array('company_name', array_keys($data)) || !in_array('email', array_keys($data)) || !in_array('password', array_keys($data)) || !in_array('country', array_keys($data))) {
            throw new Exception("company_name, email, password, country are required", 1);
        }

        $res = $this->call('/auth/register', 'POST', $data);
        return Utils::jsonDecode($res->getBody());
    }


    /**
     * Updates a account fees
     * 
     * @param array $data Fee data
     * @return object JSON
     */
    public function updateFee(array $data)
    {
        if (!in_array('amount', array_keys($data))) {
            throw new Exception("Amount required", 1);
        }

        $res = $this->call('/fee/update', 'POST', $data);
        return Utils::jsonDecode($res->getBody());
    }

    /**
     * Updates a Transaction
     * 
     * @param string $id Transaction ID
     * @param array $data Transaction data to be updated
     * @return object
     */
    public function updateTransaction(string $id, array $data)
    {
        if (is_null($id)) {
            throw new Exception("Transaction ID is required", 1);
        }

        if (!in_array('amount', array_keys($data)) || !in_array('coin_type', array_keys($data)) || !in_array('amount_in_fiat', array_keys($data))) {
            throw new Exception("amount, amount_in_fiat, coin_type are required", 1);
        }

        $res = $this->call('/transactions/' . $id, 'POST', $data);
        return Utils::jsonDecode($res->getBody());
    }

    /**
     * Update Transaction Status
     * @param string $id Transaction ID
     * @param array $data Transaction data to be updated
     * @return object
     */
    public function updateTransactionStatus(string  $id, array $data)
    {
        if (is_null($id)) {
            throw new Exception("Transaction ID is required", 1);
        }

        if (!in_array('status', array_keys($data))) {
            throw new Exception("[status] is required", 1);
        }

        $status = [
            'awaiting_buyer_approval',
            'awaiting_seller_approval',
            'awaiting_buyer_cash_transfer',
            'awaiting_seller_cash_approval',
            'completed',
            'canceled',
            'dispute'
        ];

        if (!in_array($data['status'], array_values($status))) {
            throw new Exception("Invalid Status type", 1);
        }

        try {
            $res = $this->call('/transactions/update/' . $id . '/' . $data['status'], 'POST');
            return Utils::jsonDecode($res->getBody());
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Retrieves all transactions by Status
     * 
     * @param string $status Transaction Status
     * @return object
     */
    public function getTransactionsByStatus(string $status)
    {

        if (!$status) {
            throw new Exception("[status] is required", 1);
        }

        $status = [
            'awaiting_buyer_approval',
            'awaiting_seller_approval',
            'awaiting_buyer_cash_transfer',
            'awaiting_seller_cash_approval',
            'completed',
            'canceled',
            'dispute'
        ];

        if (!in_array($status, array_values($status))) {
            throw new Exception("Invalid Status type", 1);
        }

        $res = $this->call('/transactions/status' . $status, 'GET');
        return Utils::jsonDecode($res->getBody());
    }

    /**
     * Retrieves a transactions by ID
     * 
     * @param string $id Transaction ID
     * @return object
     */
    public function getTransaction(string $id)
    {
        if (is_null($id)) {
            throw new Exception("Transaction ID is required", 1);
        }

        $res = $this->call('/transactions/' . $id, 'GET');
        return Utils::jsonDecode($res->getBody());
    }

    /**
     * Retrieves all transactions
     * 
     * @return object
     */
    public function getTransactions()
    {

        $res = $this->call('/transactions', 'GET');
        return Utils::jsonDecode($res->getBody());
    }

    /**
     * Creates a new Transaction
     * 
     * @param array $data Transaction data
     * @return object
     */
    public function createTransaction(array $data)
    {
        if (is_null($this->network)) {
            throw new Exception("Network Type is required", 1);
        }

        if (!in_array('seller_id', array_keys($data)) || !in_array('buyer_id', array_keys($data)) || !in_array('amount', array_keys($data)) || !in_array('coin_type', array_keys($data)) || !in_array('amount_in_fiat', array_keys($data))) {
            throw new Exception("seller_id, buyer_id, amount, amount_in_fiat, coin_type are required", 1);
        }

        $data['coin_type'] = strtoupper($data['coin_type']);

        try {
            $res = $this->call('/transactions/' . $this->network . '/create', 'POST', $data);
            return Utils::jsonDecode($res->getBody());
        } catch (\Exception $e) {
            throw $e;
        }
    }


    /**
     * Creates a new Customer
     * 
     * @return object
     */
    public function createCustomer()
    {
        if (is_null($this->network)) {
            throw new Exception("Network Type is required", 1);
        }

        $res = $this->call('/customers/' . $this->network . '/create', 'POST');
        return Utils::jsonDecode($res->getBody());
    }

    /**
     *  Retrieves all customers
     * 
     * @return object
     */
    public function getCustomers()
    {
        $res = $this->call('/customers', 'GET');
        return Utils::jsonDecode($res->getBody());
    }


    /**
     *  Retrieves a single transaction by Transaction ID
     * 
     * @param string $id Customer ID
     * @return object
     */
    public function getCustomer(string $id)
    {
        if (is_null($id)) {
            throw new Exception("Transaction ID is required", 1);
        }

        $res = $this->call('/customers/' . $id, 'GET');
        return Utils::jsonDecode($res->getBody());
    }


    /**
     * Retrieves a Customer Crypto Address by Customer ID
     * 
     * @param string $id Customer ID
     * @param string $crypto The Crypto Type
     * @return object
     */
    public function getCustomerAddress(string $id, string $crypto)
    {
        if (is_null($id)) {
            throw new Exception("Customer ID is required", 1);
        }

        if (is_null($crypto)) {
            throw new Exception("Crypto Type is required", 1);
        }

        $res = $this->call('/customers/address/' . $id . '/' . strtoupper($crypto), 'GET');
        return Utils::jsonDecode($res->getBody());
    }


    /**
     * Retrieves a Customer's Crypto Balance by Customer ID
     * 
     * @param string $id Customer ID
     * @param string $crypto The Crypto Type
     * @return object
     */
    public function getCustomerBalance(string $id, string $crypto)
    {
        if (is_null($id)) {
            throw new Exception("Customer ID is required", 1);
        }

        if (is_null($crypto)) {
            throw new Exception("Crypto type is required", 1);
        }

        $res = $this->call('/customers/balance/' . $id . '/' . strtoupper($crypto), 'GET');
        return Utils::jsonDecode($res->getBody());
    }

    /**
     * Retrieves all supported Cryptocurrencies
     * 
     * @return object
     */
    public function getSupportedCoins()
    {
        $res = $this->call('/supported-coins', 'GET');
        return Utils::jsonDecode($res->getBody());
    }
}
