<?php
/**
 * Api class file
 */


namespace bariew\cashcow;

use GuzzleHttp\Client;

/**
 * Class Api
 * @package bariew\cashcow
 *
 * @url https://api.cashcow.co.il/Home/Api
 *
 */
class Api
{
    private $url = 'https://api.cashcow.co.il/Api';
    private $token, $id;
    const STATUS_PHONE_BANK_TRANSFER = 1;
    const STATUS_LEAD = 2;
    const STATUS_PAID = 4;
    const STATUS_ERROR = 5;
    const STATUS_DELIVERED = 6;
    const STATUS_PENDING_REVIEW = 7;
    const STATUS_CANCELLED = 8;
    const STATUS_CLAIMED = 9;


    /**
     * Main instance method
     * @param $id
     * @param $token
     * @return static
     */
    public static function instance($id, $token)
    {
        $model = new static();
        $model->id = $id;
        $model->token = $token;
        return $model;
    }

    /**
     * Get list of store orders.
     * @param array $query
     * @return false|array[]
     */
    public function orders($query = [])
    {
        $result = $this->get("/Stores/Orders", array_merge(['page' => 1, 'page_size' => 20,], $query));
        return $result ? $result['result'] : false;
    }

    /**
     * Get an Order info by ID
     * @param integer $id
     * @return null|array
     * @response array (
        'Id' => 3546111,
        'ShipingType' => 1,
        'FirstName' => 'Pavel',
        'LastName' => 'Bariew',
        'Email' => 'my@gmail.com',
        'Phone' => '0503057111',
        'Address' => 'first street, 24',
        'City' => 'London',
        'FloorNumber' => '',
        'StreetNameAndNumber' => '',
        'ApartmentNumber' => '',
        'ZipCode' => '',
        'OrderStatus' => 1,
        'OrderDate' => '2022-04-28T11:43:27.777',
        'ShipingPrice' => 0.0,
        'TotalPrice' => 5700.0,
        'IsSelfDelivery' => false,
        'IsAccountReadThisOrder' => false,
        'PaymentOptionType' => 3,
        'TotalProducts' => 3,
        ....
     )
     */
    public function order($id)
    {
        $result = $this->orders(['OrderID' => $id]);
        return $result ? $result[0] : null;
    }

    /**
     * Get a tracking number and comments for the order.
     * @param $id
     * @param $email
     * @return bool|mixed
     */
    public function orderTracking($id, $email)
    {
        return $this->get("/Stores/CheckOrderTracking", ['order_id' => $id, 'email_address' => $email]);
    }

    /**
     * Update order status, total price and invoices.
     * @param $id
     * @param $email
     * @param $data array order_status_type (int), total_price (double), invoice_number (string), invoice_url (string), order_notes (string[]), tracking_code (string)
     * @return bool|mixed
     */
    public function orderUpdate($id, $email, $data)
    {
        return $this->post("/Stores/SendOrderUpdate", array_merge(['order_id' => $id, 'email_address' => $email], $data));
    }

    /**
     * Get all store products
     * @param array $query ['page' => 1, 'page_size' => 20,]
     * @return bool|array
     * @response array ('title' => '9201B-מתקן קפיצה בועות-Bubbles-הפיהופ-Happy Hop-קפיץ קפוץ',
    'sku' => '9201B','id' => 312176,'qty' => 0.0,
    'categories' => array ('primary_id' => 48785,'more_categories' =>array (0 =>array ('id' => 67812,),))
    'images' => array (
    'primary' => 'https://cdn.cashcow.co.il/images/3f63c7ae-09c7-44ee-858f-c5ae5e33ae99_500.jpg',
    'image2' => 'https://cdn.cashcow.co.il/images/1989b05e-8783-4003-8042-34cb397ae8d3_500.jpg',
    'image3' => 'https://cdn.cashcow.co.il/images/9e4d01d4-0388-4f6b-a83c-c19cee5264ee_500.jpg',
    'image4' => 'https://cdn.cashcow.co.il/images/c83c4f25-b62a-4d75-ab6c-9eead3865e6c_500.jpg',
    ),
    'prices' => array ('price' => array ('Price' => 1399.0, 'Claim' => 0,), 'retail' => 1899.0, 'sell' => 1399.0,),
    'url' => 'https://www.kfitzkfotz.co.il/p/מתקן_קפיצה_מגלשת_בועות_וחישוק_הפי_הופ_-_9201_-_Bubble_Slide_And_Hoop_Bouncer_Happy_Hop',
    'attributes' => array (),
    ),
     */
    public function products($query = [])
    {
        $result = $this->get("/Products/GetProducts", array_merge(['page' => 1, 'page_size' => 20,], $query));
        return $result ? $result['result'] : false;
    }

    /**
     * Get products quantity
     * @param array $query ['sku' => 123, 'category_id' => 123]
     * @return bool|mixed
     * @response  array (array (
    'product_sku' => NULL, 'product_id' => 1226256,'sku' => '9359', 'qty' => 2.0, 'is_matrix' => false, 'attributes' => array (),
    'matrix_attributes' => array ('attrbibute_a' => 0, 'attribute_a_internal_identifier' => NULL, 'attrbibute_b' => 0, 'attribute_b_internal_identifier' => NULL, 'matrix_options' => NULL,),
    )),
     */
    public function productsCount($query = [])
    {
        $result = $this->get("/Products/GetQty", array_merge(['page' => 1, 'page_size' => 20,], $query));
        return $result ? $result['result'] : [];
    }

    /**
     * Get single product quantity
     * @param $sku
     * @param $product_id
     * @return int|mixed
     */
    public function productSingleCount($sku, $product_id)
    {
        foreach ($this->productsCount(['sku' => $sku]) as $item) {
            if ($product_id == $item['product_id']) {
                return $item['qty'];
            }
        }
        return 0;
    }

    /**
     * Update product data
     * @param $sku
     * @param array $data [qty, title, is_visible, main_category_name, short_description, long_description, qty_type, qty_jumping_number,
     * weight, is_no_vat, images, prices, is_force_delete_existing_attributes, attributes, attributes_matrix, is_hide_buy_buttons]
     * @return bool|mixed
     */
    public function productUpdate($sku, $data = [])
    {
        return $this->jsonPost("/Stores/CreateOrUpdatePrtoduct", array_merge([
            'sku' => $sku, 'is_override_existing_product' => true, 'is_restore_deleted_items' => false,
        ], $data));
    }

    /**
     * Get all product categories.
     * @example [{"Id":-3408,"Text":"category","Type":5,"Children":[],"Data":null,"Permalink":"https://www.asd.co.il/c/%D7%9E%D7%AA%D7%A0%D7%A4%D7%97%D7%99%D7%9D_%D7%91%D7%9E%D7%91%D7%A6%D7%A2"}]
     */
    public function categories()
    {
        $result = [];
        foreach (["https://{$this->id}.websites.cashcow.co.il/navigation_top.json",
                     "https://{$this->id}.websites.cashcow.co.il/navigation_main.json",
                     "https://{$this->id}.websites.cashcow.co.il/navigation_bottom.json"
                 ] as $url) {
            $result = array_merge($result, json_decode(str_replace("\xEF\xBB\xBF", '', file_get_contents($url, false, stream_context_create(["ssl"=>["verify_peer"=>false, "verify_peer_name"=>false,]]))), true));
        }
        return $result;
    }


    // PRIVATE

    /**
     * Get request to Cashcow
     * @param $url
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    private function get($url, $params = [])
    {
        $result = static::client()->get($this->url . $url.'?'.http_build_query(array_merge(['store_id' => $this->id,  'token' => $this->token], $params)));
        if ($result->getStatusCode() == 200) {
            return json_decode($result->getBody()->getContents(), true);
        } else {
            throw new \Exception("Cashcow error: " . json_encode($result->getBody()->getContents()));
        }
    }

    /**
     * Post request to cashcow
     * @param $url
     * @param $data
     * @param array $options
     * @return bool|mixed
     * @throws \Exception
     */
    private function post($url, $data, $options = [])
    {
        $result = static::client()->post($this->url.$url, array_merge([
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'body' => http_build_query(array_merge(['store_id' => $this->id,  'token' => $this->token], $data)),
        ], $options));
        if ($result->getStatusCode() == 200) {
            return json_decode($result->getBody()->getContents(), true);
        } else {
            throw new \Exception("Cashcow error: " . json_encode($result->getBody()->getContents()));
        }
    }

    /**
     * Encode post body to JSON
     * @param $url
     * @param $data
     * @return bool|mixed
     */
    private function jsonPost($url, $data)
    {
        return $this->post($url, [], [
            'headers' => ['Content-Type' => 'application/json', 'Encoding' => 'UTF-8'],
            'body' => json_encode(array_merge(['store_id' => $this->id,  'token' => $this->token], $data)),
        ]);
    }


    /**
     * @return Client
     */
    private static function client()
    {
        return new Client();
    }
}