<?php
/**
 * Shipment class file
 */


namespace bariew\chita;

/**
 * Class Shipment
 * @package bariew\chita
 * @link https://drive.google.com/file/d/14-jMbVc9plIFzq38SPyLT19ks2jh_hhR/view?usp=share_lin
 * @see Api
 */
class Shipment
{
    const TYPE_DELIVERIES = "מסירה";
    const TYPE_RETURNS = "איסוף";
    public static $addressList = [];

    public $type = self::TYPE_DELIVERIES, $code=140, $stage = 4, $companyName, $n6, $cargoType=199, $returnCargoType, $returnPackagesNumber, $n10,
        $consigneeName, $cityCode, $cityName, $streetCode, $streetName, $buildingNumber, $entranceNumber, $floorNumber, $apartmentNumber, $phoneNumber,
        $phoneNumber2, $referenceNumber, $packagesNumber, $addressRemarks, $shipmentRemarks, $referenceNumber2, $pickupDate, $pickupTime, $n29, $paymentTypeCode,
        $consigneeSum, $consigneeDate, $paymentCollectionNotes, $returnPickupPoint, $pickupPoint, $responseType = 'XML', $autoPickupPoint, $n38, $n39, $consigneeEmail,
        $parcelPreparationDate, $parcelPreparationTime;

    /**
     * Example
     * @return static
     */
    public static function fromArray($data)
    {
        $model = new static();
        foreach ($data as $attribute => $value) {
            $model->$attribute = $value;
        }
        return $model;
    }

    /**
     * All numeric rules gonna be used to set -N attribute in the API POST
     * @see Shipment::toApiData()
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['type', 'code', 'cargoType', 'consigneeName', 'cityName', 'streetName', 'buildingNumber', 'phoneNumber'], 'required'],

            [['code', 'stage', 'cargoType', 'returnCargoType'], 'number', 'integerPattern' => '/^\d{5}$/'],
            [['returnPackagesNumber'], 'integer', 'integerPattern' => '/^\d{3}$/'],
            [['packagesNumber', 'parcelPreparationTime'], 'number', 'integerPattern' => '/^\d{6}$/'],
            [['consigneeSum'], 'number', 'numberPattern' => '/^([0-9]){1,8}\.?([0-9]){0,2}$/'],
            [['returnPickupPoint', 'pickupPoint', 'paymentTypeCode'], 'integer'],

            [['companyName', 'cityCode', 'streetCode'], 'string', 'max' => 10],
            [['consigneeName', 'phoneNumber', 'phoneNumber2'], 'string', 'max' => 20],
            [['cityName', 'streetName'], 'string', 'max' => 30],
            [['buildingNumber'], 'string', 'max' => 5],
            [['entranceNumber'], 'string', 'max' => 1],
            [['floorNumber'], 'string', 'max' => 2],
            [['apartmentNumber'], 'string', 'max' => 4],
            [['referenceNumber'], 'string', 'max' => 200],
            [['addressRemarks'], 'string', 'max' => 70],
            [['shipmentRemarks'], 'string', 'max' => 80],
            [['referenceNumber2'], 'string', 'max' => 50],
            [['pickupDate', 'parcelPreparationDate'], 'match', 'pattern' => '/^\d{2}\/\d{2}\/\d{4}$/'],
            [['consigneeDate'], 'match', 'pattern' => '/^\d{2}\/\d{2}\/\d{4}$/', 'when' => function () { return $this->consigneeSum; }],
            [['pickupTime'], 'match', 'pattern' => '/^\d{2}:\d{2}$/'],
            [['paymentCollectionNotes'], 'string', 'max' => 500],
            [['consigneeEmail'], 'email'],

            'lists' => [['type', 'code', 'cargoType', 'autoPickupPoint', 'paymentTypeCode'], 'inList'],
            //'addressArrays' => [['cityName', 'streetName', 'buildingNumber', 'entranceNumber', 'floorNumber', 'apartmentNumber']],
        ];
    }

    /**
     * Used in HTML form adding Input hints
     * @param $attribute
     * @return mixed|null
     */
    public static function hint($attribute)
    {
        return [
            'code' => 'get the code from the shipping company',
            'stage' => 'Consult with the shipping company which code to send (if any)',
            //'cargoType' => 'get the code from the shipping company',
            'returnCargoType' => '(relevant for returns only) - get the code from the shipping company',
            'returnPackagesNumber' => '(relevant for returns only)',
            'cityCode' => 'If you are sending city codes, please use the gov.il database',
            'streetCode' => 'If you are sending street codes, please use the gov.il database',
            'phoneNumber' => '(cellular)',
            'referenceNumber' => 'Your reference number for the shipment',
            'packagesNumber' => 'This field is mandatory if there is more than one package in the shipment.',
            'pickupDate' => 'If you want the shipment to be picked up on a specific date which is more than a day away from the date of the request, you can specify a date in this field.',
            'pickupTime' => 'If you want the shipment to be picked up at a specific hour which is more than a day away from the date of the request, you can specify time in this field.',
            'paymentTypeCode' => 'If the courier needs to collect payment from the consignee, please specify the payment type code in this field - get it from the shipping company',
            'consigneeSum' => 'The sum to be collected from the consignee',
            'consigneeDate' => 'The date of payment collection from the consignee',
            'returnPickupPoint' => 'relevant for returns only',
            'pickupPoint' => 'Relevant only for shipments to pickup points. Please fill in if your customer has chosen a pickup point on your website. If you fill in this field, please leave next field blank.',
            'autoPickupPoint' => 'Run system can choose a pickup point for a shipment automatically, based on a consignee’s address (the closest working point will be assigned).',
            'parcelPreparationDate' => 'This field is used in case your parcels are assembled at the shipping company warehouse.',
            'parcelPreparationTime' => 'This field is used in case your parcels are assembled at the shipping company warehouse.',
        ][$attribute] ?? null;
    }

    /**
     * Do not set attribute label if you want to skip it in the create form
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
           // 'type' => 'Type',
           // 'code' => 'Shipment type',
           // 'stage' => 'Shipment stage',
           // 'companyName' => 'Your company name',
           // 'n6' => '',
           // 'returnCargoType' => 'Returned cargo type',
           // 'returnPackagesNumber' => 'Number of returned packages',
           // 'n10' => '',
            'cargoType' => 'Shipped cargo type',//'Package weight',//
            'consigneeName' => "Consignee's name",
           // 'cityCode' => 'City/settlement code',
            'cityName' => 'City/settlement name',
            //'streetCode' => 'Street code',
            'streetName' => 'Street name',
            'buildingNumber' => 'Building No.',
            'entranceNumber' => 'Entrance No.',
            'floorNumber' => 'Floor No.',
            'apartmentNumber' => 'Apartment No.',
            'phoneNumber' => 'Primary phone number',
            'phoneNumber2' => 'Additional phone number',
            'referenceNumber' => 'Reference number',
            'packagesNumber' => 'Number of packages',
            'addressRemarks' => 'Address remarks',
            'shipmentRemarks' => 'Additional shipment remarks',
            //'referenceNumber2' => 'Second reference number',
            'pickupDate' => 'Date',
            'pickupTime' => 'Time',
            //'n29' => '',
            'paymentTypeCode' => 'Payment type code', //do not show it, it' NULL
            'consigneeSum' => 'Consignee Sum',
            'consigneeDate' => 'Consignee Date',
            'paymentCollectionNotes' => 'Notes for payment collection',
//            'returnPickupPoint' => 'Source pickup point',
//            'pickupPoint' => 'Destination pickup point',
//            'responseType' => '',
//            'autoPickupPoint' => 'Auto pickup point',
//            'n38' => '',
//            'n39' => '',
//            'consigneeEmail' => "Consignee's email",
//            'parcelPreparationDate' => 'Parcel preparation date',
//            'parcelPreparationTime' => 'Parcel preparation time',
        ];
    }


    /**
     * @return string[]
     */
    public function toApiData()
    {
        $numberAttributes = array_merge(...array_map(function ($rule) {
            return in_array($rule[1], ['integer', 'number']) ? $rule[0] : [];
        }, $this->rules()));
        $attributes = get_object_vars($this);
        return array_map(function ($attribute, $value) use ($numberAttributes) {
            return (in_array($attribute, $numberAttributes) ? '-N' : '-A').(string)$value;
        }, array_keys($attributes), $attributes);
    }


    // LISTS & GETTERS

    /**
     * @return array
     */
    public static function typeList()
    {
        return [
            static::TYPE_DELIVERIES => 'Deliveries',
            static::TYPE_RETURNS => 'Returns',
        ];
    }

    /**
     * @return array
     */
    public static function autoPickupPointList()
    {
        return [
            'N' => 'Do not assign (default)',
            'Y' => 'Assign any type (store or locker)',
            'L' => 'Assign a locker',
            'S' => 'Assign a store',
        ];
    }

    /**
     * @return array
     */
    public static function codeList()
    {
        return [140 => 'Home Delivery', 240 => 'PUDO (pickup point)'];
    }

    /**
     * @return array
     */
    public static function cargoTypeList()
    {
        return [
            99 => 'cargo_type_99',
            100 => 'cargo_type_100',
            130 => 'cargo_type_130',
            145 => 'cargo_type_145',
            150 => 'cargo_type_150',
            155 => 'cargo_type_155',
            160 => 'cargo_type_160',
            170 => 'cargo_type_170',
            190 => 'cargo_type_190',
            198 => 'cargo_type_198',
            199 => 'cargo_type_199',
            999 => 'cargo_type_999',
        ];
    }

    /**
     * @return array
     */
    public static function paymentTypeCodeList()
    {
        return [
            '' => '',
            1 => 'paymentTypeCode_check',
            2 => 'paymentTypeCode_cash',
        ];
    }
}