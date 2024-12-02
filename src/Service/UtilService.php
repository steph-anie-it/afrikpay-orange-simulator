<?php

namespace App\Service;

class UtilService
{
    public const PHONE_FORMAT="%s%s";

    public const ANNEE_FORMAT="%s-%s";

    public const ID_PAIE="%s_%s";

    public const YEAR='YEAR';
    public const GEN_DATE='genDate';
    public const TIME='time';

    public function object_to_array($obj)
    {
        if (is_object($obj))
            $obj = (array)$this->dismount($obj);
        if (is_array($obj)) {
            $new = array();
            foreach ($obj as $key => $val) {
                $new[$key] = $this->object_to_array($val);
            }
        }
        else
            $new = $obj;
        return $new;
    }

    public function dismount($object)
    {
        $reflectionClass = new \ReflectionClass(get_class($object));
        $array = array();
        foreach ($reflectionClass->getProperties() as $property) {
            $property->setAccessible(true);
            try{
                $array[$property->getName()] = $property->getValue($object);
            }catch (\Throwable $throwable){
                //Avoid uninitialized properties
            }
            $property->setAccessible(   false);
        }
        return $array;
    }

    public function arrayToXml($array, $entryRootElement = null, $xml = null,$previous=null) {
        $_xml = $xml;

        $rootElement = sprintf("<%s/>",$entryRootElement);
        // If there is no Root Element then insert root
        if ($_xml === null) {
            $_xml = new SimpleXMLElement($rootElement !== null ? $rootElement : '<root/>');
        }
        $hasIntIndex = false;
        $xml_toAdd = $_xml;
        // Visit all key value pair
        foreach ($array as $k => $v) {
            $currentName = $_xml->getName();

            if(is_int($k)){
                $subIndex = $k;
                $hasIntIndex = $subIndex == null;
                $parent = $_xml->xpath('parent::*');
                if(strtoupper($currentName) == strtoupper($previous)){
                    $array = $_xml->xpath('parent::*');
                    if(is_array($array) && count($array) > 0){
                        $_xml = $array[0];
                    }
                }
                if(array_key_exists($k,$array)){
                    $v = $array[$k];
                }
                $k = $entryRootElement;
            }
            // If there is nested array then
            if (is_array($v)) {
                //$xml_toAdd = $_xml;
                $previous = $xml_toAdd->getName();
                if(!$hasIntIndex){
                    $xml_toAdd = $_xml->addChild($k);
                }
                // Call function for nested array
                $this->arrayToXml($v, $k, $xml_toAdd,$previous);
            }

            else {

                // Simply add child element.
                $_xml->addChild($k, $v);
            }
        }

        return $_xml->asXML();
    }

    public function mapObjectXml(mixed $xml, string $destinationClass){
        $json_string = json_encode($xml);
        $xmlArray = json_decode($json_string, true);
        $dest = new \ReflectionObject(new $destinationClass());
        $destination = new $destinationClass();

        foreach ($xmlArray as $key => $value){
            if(!$dest->hasProperty($key)) {
                continue;
            }
            $destProperty = $dest->getProperty($key);

            if(is_array($value)){
                if(count($value) == 0){
                    $value = null;
                }else{
                    $className = str_replace("?","",strval($destProperty->getType()));
                    $value = $this->mapFullArray($value,$className);
                }
            }
            $destProperty->setAccessible(true);

            try{
                $destProperty->setValue($destination, $value);
            }catch (\Exception $exception){

            }
        }
        return $destination;
    }


    /**
     * @throws \ReflectionException
     */
    public function map(mixed $sourceClass, string $destinationClass,bool $toUpper=false,bool $toLower = false){
        $object = new \ReflectionObject($sourceClass);
        $destination = new $destinationClass();
        $dest = new \ReflectionObject(new $destinationClass());
        $properties = $object->getProperties();
        $destinationName = "";
        foreach ($properties as $property){
            $property->setAccessible(true);
            $propertyName = $property->getName();
            /*
            if($toUpper){
                $destinationName = strtoupper($propertyName);
            }
            if ($toLower){
                $destinationName = strtolower($propertyName);
            }*/

            $propertyValue = $property->getValue($sourceClass);


            $destProperty = $this->getClasseProperty($propertyName,$dest);
            if (!$destProperty) continue;
            $destProperty->setAccessible(true);
            try{
                $destProperty->setValue($destination, $propertyValue);
            }catch (\Exception $exception){
            }
        }
        return $destination;
    }

    public function getClasseProperty(string $propertyName, $objectClass){
        $hasPropery = false;
        $toLower = strtolower($propertyName);
        if ($objectClass->hasProperty($toLower)){
            $destinationName = $toLower;
            $hasPropery = true;
        }
        $toUpper = strtoupper($propertyName);
        if (!$hasPropery && $objectClass->hasProperty($toUpper)){
            $destinationName = $toUpper;
            $hasPropery = true;
        }

        if (!$hasPropery && $objectClass->hasProperty($propertyName)){
            $destinationName = $propertyName;
            $hasPropery = true;
        }
        $destProperty = null;
        if($hasPropery) {
            $destProperty = $objectClass->getProperty($destinationName);
        }
        return $destProperty;
    }

    public function mapObjecToArray(mixed $sourceClass, array $destination, array $converter = []){
        $object = new \ReflectionObject($sourceClass);
        $mappedArray = [];
        $sourceKey = null;
        foreach ($destination as $key => $value){
            $keyValue = $key;
            $sourceKey = $key;

            $property = $this->getClasseProperty($sourceKey,$object);
            if (!$property){
              $property = $this->getClasseProperty($value,$object);
              $keyValue = $value;
              $sourceKey = $value;
            }
            $mappedArray[$keyValue] = null;
            if (sizeof($converter) > 0){
                if (array_key_exists($keyValue,$converter)){
                    $sourceKey = $converter[$keyValue];
                    $property = $this->getClasseProperty($sourceKey,$object);
                }
            }

            if (!$property){
                continue;
            }
            if ($object->hasProperty($sourceKey)){
                $sourceProperty = $object->getProperty($sourceKey);
                $mappedArray[$keyValue] = $sourceProperty->getValue($sourceClass);
            }
        }
        return $mappedArray;
    }

    public function mapArray(array $source, string $destinationClass,bool $toUpper=true){
        $destination = new $destinationClass();
        $dest = new \ReflectionObject(new $destinationClass());
        foreach ($source as $key => $value){
            if(str_contains($key,"-")){
                $key = str_replace("-","_",$key);
            }
            if($toUpper){
                $key = strtoupper($key);
            }
            if(!$dest->hasProperty($key)) {
                continue;
            }
            $destProperty = $dest->getProperty($key);
            $destProperty->setAccessible(true);
            try{
                $destProperty->setValue($destination, $value);
            }catch (\Exception $exception){

            }
        }
        return $destination;
    }


    public const UNDEFINED_TEMPLATE = "%s";
    public function getUndefinedParams(mixed $object,array $optionalProp=[],array $emptyValues=[]) : ?string
    {
        $reflection = new \ReflectionObject($object);
        $properties = $reflection->getProperties();
        $undefinesValues = "";
        foreach ($properties as $property) {
            $propName = $property->getName();
            $isOptional = is_array($optionalProp)
                && count($optionalProp) > 0
                && in_array($propName,$optionalProp);
//            if($isOptional){
//                dd($optionalProp);
//            }
            $allowEmpty = is_array($emptyValues)
                && count($emptyValues) > 0
                && in_array($propName,$emptyValues);

            if(!$property->isInitialized($object) && !$isOptional){

                if (!empty($undefinesValues)) {
                    $undefinesValues .= " or ";
                }

                if (!$isOptional){
                    $undefinesValues .= sprintf(self::UNDEFINED_TEMPLATE, $propName);
                }
                continue;
            }
            $value = $property->getValue($object);
            if ($value == null &&  !$allowEmpty && !$isOptional) {
                if(is_int($value) && $value == 0) continue;
                if (!empty($undefinesValues)) {
                    $undefinesValues .= " or ";
                }
                $undefinesValues .= sprintf(self::UNDEFINED_TEMPLATE, $property->getName());
            }
        }
        return  $undefinesValues;
    }


    public function mapWithUnder(mixed $sourceClass, string $destinationClass){
        $object = new \ReflectionObject($sourceClass);

        $destination = new $destinationClass();
        $dest = new \ReflectionObject(new $destinationClass());
        $properties = $object->getProperties();
        foreach ($properties as $property){
            $property->setAccessible(true);
            $propertyName = strtolower($property->getName());
            $array= preg_split('#([A-Z][^A-Z]*)#', $propertyName, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            $propertyName = "";
            foreach ($array as $key => $value){
                $propertyName .= "_".strtolower($value);
            }
            $propertyName = substr($propertyName,1,strlen($propertyName));
            $propertyName = str_replace("_","",$propertyName);

            $propertyValue = $property->getValue($sourceClass);
            if(!$dest->hasProperty($propertyName)) {
                continue;
            }
            $destProperty = $dest->getProperty($propertyName);
            $destProperty->setAccessible(true);
            try{
                $destProperty->setValue($destination, $propertyValue);
            }catch (\Exception $exception){

            }
        }
        return $destination;
    }


    public function mapWithUnderscore(mixed $sourceClass, string $destinationClass){
        $object = new \ReflectionObject($sourceClass);

        $destination = new $destinationClass();
        $dest = new \ReflectionObject(new $destinationClass());
        $properties = $object->getProperties();
        foreach ($properties as $property){
            $property->setAccessible(true);
            $propertyName = $property->getName();
            $array= preg_split('#([A-Z][^A-Z]*)#', $propertyName, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            $propertyName = "";
            foreach ($array as $key => $value){
                $propertyName .= "_".strtolower($value);
            }
            $propertyName = substr($propertyName,1,strlen($propertyName));
            $propertyValue = $property->getValue($sourceClass);
            if(!$dest->hasProperty($propertyName)) {
                continue;
            }
            $destProperty = $dest->getProperty($propertyName);
            $destProperty->setAccessible(true);
            try{
                $destProperty->setValue($destination, $propertyValue);
            }catch (\Exception $exception){

            }
        }
        return $destination;
    }



    public function generateCustomerName(){
        return $this->randomName();
    }

    private function randomName() {
        $firstname = array(
            'Johnathon',
            'Anthony',
            'Erasmo',
            'Raleigh',
            'Nancie',
            'Tama',
            'Camellia',
            'Augustine',
            'Christeen',
            'Luz',
            'Diego',
            'Lyndia',
            'Thomas',
            'Georgianna',
            'Leigha',
            'Alejandro',
            'Marquis',
            'Joan',
            'Stephania',
            'Elroy',
            'Zonia',
            'Buffy',
            'Sharie',
            'Blythe',
            'Gaylene',
            'Elida',
            'Randy',
            'Margarete',
            'Margarett',
            'Dion',
            'Tomi',
            'Arden',
            'Clora',
            'Laine',
            'Becki',
            'Margherita',
            'Bong',
            'Jeanice',
            'Qiana',
            'Lawanda',
            'Rebecka',
            'Maribel',
            'Tami',
            'Yuri',
            'Michele',
            'Rubi',
            'Larisa',
            'Lloyd',
            'Tyisha',
            'Samatha',
        );

        $lastname = array(
            'Mischke',
            'Serna',
            'Pingree',
            'Mcnaught',
            'Pepper',
            'Schildgen',
            'Mongold',
            'Wrona',
            'Geddes',
            'Lanz',
            'Fetzer',
            'Schroeder',
            'Block',
            'Mayoral',
            'Fleishman',
            'Roberie',
            'Latson',
            'Lupo',
            'Motsinger',
            'Drews',
            'Coby',
            'Redner',
            'Culton',
            'Howe',
            'Stoval',
            'Michaud',
            'Mote',
            'Menjivar',
            'Wiers',
            'Paris',
            'Grisby',
            'Noren',
            'Damron',
            'Kazmierczak',
            'Haslett',
            'Guillemette',
            'Buresh',
            'Center',
            'Kucera',
            'Catt',
            'Badon',
            'Grumbles',
            'Antes',
            'Byron',
            'Volkman',
            'Klemp',
            'Pekar',
            'Pecora',
            'Schewe',
            'Ramage',
        );

        $name = $firstname[rand ( 0 , count($firstname) -1)];
        $name .= ' ';
        $name .= $lastname[rand ( 0 , count($lastname) -1)];

        return $name;
    }

    public function generateUnique() :int{
        return hexdec(uniqid());
    }

    public function getPhone(){
        $hasIndicatif = filter_var($_ENV['hasIndicatif'],FILTER_VALIDATE_BOOL);
        $indicatif = $hasIndicatif  ?  $_ENV['indicatif'] : "";
        $minNumber = $_ENV['MIN_PHONE'];
        $maxNumber = $_ENV['MAX_PHONE'];
        $number =  rand($minNumber,$maxNumber);
        $phone = sprintf(self::PHONE_FORMAT,$indicatif,$number);
        return $phone;
    }

    public function generatePhone(){
        $phone = $this->getPhone();

        while (!preg_match($_ENV['PHONE_REGEX'],$phone)){
            $phone = $this->getPhone();
        }
        return $phone;
    }


    public function generateAccountNumber(bool $isPartner=false){
        $availMin = $isPartner ? $_ENV['PARTNERMIN'] : $_ENV['CLIENTMIN'];
        $availMax = $isPartner ? $_ENV['PARTNERMAX'] : $_ENV['CLIENTMAX'];
        $availLetter = $isPartner ? $_ENV['ACCOUNTPARTNERLETTER'] : $_ENV['ACCOUNTCLIENTLETTER'];
        $letters = explode(",",$availLetter);
        $letter = $letters[rand(0,count($letters)-1)];
        $number = rand($availMin,$availMax);
        $accountFormat = "%s%s";
        $accNumber = sprintf($accountFormat,$letter,$number);
        return $accNumber;
    }

    public function generateRandomRangeNumber(int $min=1000,int $max=2000){
        return rand($min,$max);
    }

    public function generateRandomNumber(int $length=21){
        // String of all alphanumeric character
        $str_result = '0123456789';

        // Shuffle the $str_result and returns substring
        // of specified length
        return substr(str_shuffle($str_result),
            0, $length);
    }

    public function guidv4($data = null) {
//        CO490867.3412.U19748
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function generateRandomString(int $length=21){
        // String of all alphanumeric character
        $str_result = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

        // Shuffle the $str_result and returns substring
        // of specified length
        return substr(str_shuffle($str_result),
            0, $length);
    }

    public function generateRandom(int $length=21){
          // String of all alphanumeric character
        $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

        // Shuffle the $str_result and returns substring
        // of specified length
        return substr(str_shuffle($str_result),
            0, $length);
    }

    public function generateBalance(){
        $minBalance = $_ENV['minBalance'];
        $maxBalance = $_ENV['maxBalance'];
        $balance = rand($minBalance, $maxBalance);
        return $balance;
    }

    public function generateSelector(){
        $max = $_ENV['SELECTOR_MAX'];
        $min =$_ENV['SELECTOR_MIN'];
        $selector  = rand($min,$max);
        return $selector;
    }

    public function generateTransactionId(){
        $letters = explode("," ,$_ENV['TRANSACTION_FIRSTLETTER']);
        $ind=  rand(0,count($letters)-1);
        $firstLetter = $letters[$ind];
        $suffix = $this->generateRandomNumber($_ENV['TRANSACTION_SUFFIX_LENGTH']);
        $firstPart = $firstLetter.$suffix;
        $afterDot = rand($_ENV['TRANSACTION_AFTERDOT_MIN'],$_ENV['TRANSACTION_AFTERDOT_MAX']);
        $last = rand($_ENV['TRANSACTION_LAST_MIN'],$_ENV['TRANSACTION_LAST_MAX']);
        $transactionId = sprintf($_ENV['TRANSACTION_ID_FORMAT'],$firstPart,$afterDot,$last);
        return $transactionId;
    }

    public function getDataCurrency(float $sizeMo=0) :string
    {
        return  $_ENV['BASE_DATA_UNIT'];
    }

}