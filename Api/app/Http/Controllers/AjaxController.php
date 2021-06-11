<?php

namespace App\Http\Controllers;

use App\Helpers\Legend\CommonFunctions;
use App\Models\SummaryResult;
use Illuminate\Http\Request;
use Validator;

class AjaxController extends Controller
{
    public function de(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nhi_1' => 'required',
            'nhi_2' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'msg' => $validator->errors()->first()]);
        }

        try {
            $nhi_1 = $request['nhi_1'];
            $nhi_2 = $request['nhi_2'];

            if (!in_array($nhi_1, ['01', '10', '00', '11']) || !in_array($nhi_2, ['01', '10', '00', '11'])) {
                return response()->json(['success' => false, 'msg' => __("There was errors, please try again !")]);
            }

            $results = SummaryResult::all();
            $binary = [];
            for ($i = 0; $i < count($results) - 1; $i++) {
                if($nhi_1 == CommonFunctions::convertToBinary($results[$i]['nhi_1']) && $nhi_2 = CommonFunctions::convertToBinary($results[$i]['nhi_2'])){
                    $binary[$nhi_1 . $nhi_2][] = CommonFunctions::convertToBinary($results[$i]['dac_biet']);
                }
            }

            $pair = $nhi_1 . $nhi_2;

            $kq = array_count_values($binary[$pair]);
            $sum = array_sum($kq);

            $data = [
                '01' => $this->calculatePercent($kq['01'], $sum),
                '10' => $this->calculatePercent($kq['10'], $sum),
                '00' => $this->calculatePercent($kq['00'], $sum),
                '11' => $this->calculatePercent($kq['11'], $sum),
            ];

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $ex) {
            return response()->json(['success' => false, 'msg' => __('There was errors, please try again !')]);
        }
    }

    public function calculatePercent($num, $sum)
    {
        return number_format($num / $sum * 100, 1);
    }
}
