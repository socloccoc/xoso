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

            if ($tls) {
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

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);// return web page
            curl_setopt($ch, CURLOPT_HEADER, false);//don't return header
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);// follow redirects
            curl_setopt($ch, CURLOPT_ENCODING, "");// handle all encodings
            curl_setopt($ch, CURLOPT_USERAGENT, "spider");// who am i
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);// set referer on redirect
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);// handle all encodings
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

            if ($tls) {
                curl_setopt($curl, CURLOPT_SSL_CIPHER_LIST, 'DES-CBC3-SHA');
            }

            // Fix lỗi return string empty
            curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) coc_coc_browser/72.4.208 Chrome/66.4.3359.208 Safari/537.36');

            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING       => "",
                CURLOPT_MAXREDIRS      => 10,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST  => "POST",
                CURLOPT_POSTFIELDS     => $data,
                CURLOPT_HTTPHEADER     => array(
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
                echo "Crawl again: " . $url . PHP_EOL;
                Log::info("Crawl again: " . $url);
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

    public static function convertArrayDataCrawl($data)
    {
        $dataConvert = [];
        $first = 0;
        $data = array_values($data);
        foreach ($data as $key => $value) {
            if (self::classifyText($value) == 2) {
                $dataConvert[0] = $value;
                $first = $key;
                break;
            }
        }
        for ($i = $first + 1; $i < count($data); $i++) {
            if (self::classifyText($data[$i]) == 2) {
                $dataConvert[] = $data[$i];
            }
            if (self::classifyText($data[$i]) == 1 && self::classifyText($data[$i - 1]) == 2) {
                $dataConvert[] = $data[$i];
            }
            if (self::classifyText($data[$i]) == 0 && self::classifyText($data[$i - 1]) == 2) {
                $dataConvert[] = $data[$i];
            }
        }
        return $dataConvert;
    }

    /**
     * " "-> 0
     * "1.034" -> 1
     * "7. Chi phí tài chính" -> 2
     * @param $string
     */
    public static function classifyText($string)
    {
        if (trim(str_replace(["\xC2\xA0"], "", $string)) == "") {
            return 0;
        } else if (trim(str_replace([".", "-", "\xC2\xA0"], "", $string)) == "") {
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
    public static function getIndexFinancialReportCurrent($quarterCurrent)
    {
        for ($i = 1; $i < 25; $i++) {
            if (!$quarterCurrent['column' . $i]) {
                return ($i - 1);
            }
        }
    }

    public static function dauX($str)
    {
        $X = substr($str, -1);
        $result = [];
        for ($i = 0; $i < 10; $i++) {
            $result[] = $X . $i;
        }
        return $result;
    }

    public static function ditX($str)
    {
        $X = substr($str, -1);
        $result = [];
        for ($i = 0; $i < 10; $i++) {
            $result[] = $i . $X;
        }
        return $result;
    }

    /**
     * BoXY: ( với X,Y là các số từ 0 đến 9) = 1 trong các bộ sau mà chứa cặp XY
     * @param $str
     * @return array
     */
    public static function boXY($str)
    {
        $str = substr($str, 2, 2);
        $arrs = [
                ['00', '55', '05', '50'],
            ['11', '66', '16', '61'],
            ['22', '77', '27', '72'],
            ['33', '88', '38', '83'],
            ['44', '99', '49', '94'],
            ['01', '10', '06', '60', '51', '15', '56', '65'],
            ['02', '20', '07', '70', '25', '52', '57', '75'],
            ['03', '30', '08', '80', '35', '53', '58', '85'],
            ['04', '40', '09', '90', '45', '54', '59', '95'],
            ['12', '21', '17', '71', '26', '62', '67', '76'],
            ['13', '31', '18', '81', '36', '63', '68', '86'],
            ['14', '41', '19', '91', '46', '64', '69', '96'],
            ['23', '32', '28', '82', '37', '73', '78', '87'],
            ['24', '42', '29', '92', '74', '47', '79', '97'],
            ['34', '43', '39', '93', '84', '48', '89', '98']
            ];
        $result = [];
        foreach ($arrs as $arr){
            if(in_array($str, $arr)){
                $result = $arr;
                break;
            }
        }
        return $result;
    }

    /**
     * TongX: ( với X là các số từ 0 đến 9) = 1 trong các bộ Tổng sau mà chứa cặp X
     * @param $str
     * @return array
     */
    public static function tongX($str)
    {
        $result = [];
        switch ($str) {
            case 'tong1':
                $result = ['01', '10', '29', '92', '38', '83', '47', '74', '65', '56'];
                break;
            case 'tong2':
                $result = ['02', '20', '11', '39', '93', '48', '84', '57', '75', '66'];
                break;
            case 'tong3':
                $result = ['03', '30', '12', '21', '49', '94', '58', '85', '67', '76'];
                break;
            case 'tong4':
                $result = ['04', '40', '13', '31', '22', '59', '95', '68', '86', '77'];
                break;
            case 'tong5':
                $result = ['05', '50', '14', '41', '23', '32', '69', '96', '78', '87'];
                break;
            case 'tong6':
                $result = ['06', '60', '15', '51', '24', '42', '33', '79', '97', '88'];
                break;
            case 'tong7':
                $result = ['07', '70', '16', '61', '25', '52', '34', '43', '89', '98'];
                break;
            case 'tong8':
                $result = ['08', '80', '17', '71', '26', '62', '35', '53', '44', '99'];
                break;
            case 'tong9':
                $result = ['09', '90', '18', '81', '27', '72', '36', '63', '45', '54'];
                break;
        }
        return $result;
    }

    public static function kepBang()
    {
        return ['00', '11', '22', '33', '44', '55', '66', '77', '88', '99'];
    }

    public static function kepLech()
    {
        return ['05', '50', '16', '61', '27', '72', '38', '83', '49', '94'];
    }

    /**
     * ChamX: ( với X,Y là các số từ 0 đến 9) = 1 trong các bộ “Chạm” sau mà chứa X
     * Cham1 : 01,21,31,41,51,61,71,81,91,10,11,12,13,14,15,16,17,18,19 (19 cặp)
     * @param $str
     * @return array
     */
    public static function chamX($str)
    {
        $X = substr($str, -1);
        $result = [];
        for ($i = 0; $i < 10; $i++) {
            $result[] = $i . $X;
            $result[] = $X . $i;
        }
        return $result;
    }

    /**
     * XYZ: (với X,Y,Z là các số từ 0 đến 9) = XY, YZ
     * @param $str
     * @return array
     */
    public static function xyz($str)
    {
        return [substr($str, 0, 2), substr($str, -2)];
    }
}
