<?php

namespace App\Helpers\Legend;

use Illuminate\Support\Facades\Log;

class CommonFunctions
{
    public static function retrieveData($url, $tls = true)
    {
        try {
            $ch = curl_init();

            if ($ch === false) {
                throw new \Exception('failed to initialize');
            }

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            if($tls) {
                curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'DES-CBC3-SHA');
            }

            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) coc_coc_browser/72.4.208 Chrome/66.4.3359.208 Safari/537.36');
            curl_setopt($ch, CURLOPT_REFERER, 'https://www.google.com');
            curl_setopt($ch, CURLOPT_ENCODING, '');

            $headers = array();
            $headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) coc_coc_browser/72.4.208 Chrome/66.4.3359.208 Safari/537.36';
            $headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8';
            $headers[] = 'accept-encoding: gzip, deflate, br';
            $headers[] = 'accept-language: vi-VN,vi;q=0.9,fr-FR;q=0.8,fr;q=0.7,en-US;q=0.6,en;q=0.5';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            curl_setopt($ch, CURLOPT_URL, $url);

            $content = curl_exec($ch);

            if ($content === false) {
                throw new \Exception(curl_error($ch), curl_errno($ch));
            }

            curl_close($ch);

            return $content;
        } catch (\Exception $e) {
            trigger_error(sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage())
                , E_USER_ERROR);
        }
    }

    public static function retrieveData2($url)
    {
        try {
            $ch = curl_init();

            if ($ch === false) {
                throw new \Exception('failed to initialize');
            }

            curl_setopt($ch, CURLOPT_RETURNTRANSFER , true);// return web page
            curl_setopt($ch, CURLOPT_HEADER, false);//don't return header
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);// follow redirects
            curl_setopt($ch, CURLOPT_ENCODING, "");// handle all encodings
            curl_setopt($ch, CURLOPT_USERAGENT, "spider");// who am i
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);// set referer on redirect
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , 120);// handle all encodings
            curl_setopt($ch, CURLOPT_TIMEOUT, 120);// timeout on response
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10);// timeout on response
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// Disabled SSL Cert check
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            curl_setopt($ch, CURLOPT_URL, $url);

            $content = curl_exec($ch);

            if ($content === false) {
                throw new \Exception(curl_error($ch), curl_errno($ch));
            }

            curl_close($ch);

            return $content;
        } catch (\Exception $e) {
            trigger_error(sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage())
                , E_USER_ERROR);
        }
    }

    public static function retrievePost($url, $data)
    {

        $payload = json_encode($data);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) coc_coc_browser/72.4.208 Chrome/66.4.3359.208 Safari/537.36');


        // Set HTTP Header for POST request
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "accept: */*",
            'Content-Type: application/json',
        ));

        // Submit the POST request
        $result = curl_exec($ch);

        // Close cURL session handle
        curl_close($ch);

        return json_decode($result, true);


    }
    public static function retrievePostPostMan($url, $data, $tls = true)
    {
        try {

            $curl = curl_init($url);

            if ($curl === false) {
                throw new \Exception('failed to initialize');
            }

            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

            if($tls) {
                curl_setopt($curl, CURLOPT_SSL_CIPHER_LIST, 'DES-CBC3-SHA');
            }

            // Fix lỗi return string empty
            curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) coc_coc_browser/72.4.208 Chrome/66.4.3359.208 Safari/537.36');

            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_HTTPHEADER => array(
                    "accept: */*",
                    "cache-control: no-cache",
                    "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW",
                    "postman-token: 3ccb57d0-037d-c835-9376-f575a2568d21"
                ),
        ));


            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                throw new \Exception("cURL Error #:" . $err);

            }
            return $response;
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'Operation timed out') !== false) {
                 echo "Crawl again: ".$url.PHP_EOL;
                 Log::info("Crawl again: ".$url);
                 return self::retrievePostPostMan($url, $data, $tls = true);
            }
//            trigger_error(sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage())
//                , E_USER_ERROR);
        }

    }


    /**
     * Error handle
     * @param $error
     * @return \Illuminate\Http\JsonResponse
     */
    function failedResponse($error_msg, $error_code)
    {
        return response()->json([
            'error' => $error_msg, $error_code
        ]);
    }

    public static function getStringBetween($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;

        return substr($string, $ini, $len);
    }
    public static function convertArrayDataCrawl($data){
        $dataConvert = [];
        $first = 0;
        $data = array_values($data);
        foreach ($data as $key=>$value){
            if(self::classifyText($value) == 2){
                $dataConvert[0] = $value;
                $first = $key;
                break;
            }
        }
        for($i = $first+1; $i < count($data); $i++){
            if (self::classifyText($data[$i]) == 2) {
                $dataConvert[] = $data[$i];
            }
            if (self::classifyText($data[$i]) == 1 && self::classifyText($data[$i-1]) == 2) {
                $dataConvert[] = $data[$i];
            }
            if (self::classifyText($data[$i]) == 0 && self::classifyText($data[$i-1]) == 2) {
                $dataConvert[] = $data[$i];
            }
        }
        return $dataConvert;
    }

    /**
     " "-> 0
     "1.034" -> 1
     "7. Chi phí tài chính" -> 2

     * @param $string
     */
    public static function classifyText($string){
        if (trim(str_replace(["\xC2\xA0"],"",$string)) == "") {
            return 0;
        }else if (trim(str_replace([".","-","\xC2\xA0"],"",$string)) == "") {
            return 1;
        }
        return 2;
    }
    /**
     * Tra ve index cot cuoi cung ma chua null
     * den 5 thi tra ve 5; 6 la null
     * @param $quarterCurrent
     * @return int
     *
     */
    public static function getIndexFinancialReportCurrent($quarterCurrent){
        for ($i = 1; $i < 25 ; $i++){
            if (!$quarterCurrent['column' . $i]) {
                return ($i-1);
            }
        }
    }
}
