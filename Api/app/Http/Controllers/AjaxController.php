<?php

namespace App\Http\Controllers;

use App\Helpers\Legend\CommonFunctions;
use App\Models\SummaryResult;
use Carbon\Carbon;
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
                if ($nhi_1 == CommonFunctions::convertToBinary($results[$i]['nhi_1']) && $nhi_2 == CommonFunctions::convertToBinary($results[$i]['nhi_2'])) {
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

    public function lo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'day_number' => 'required',
            'from_date'  => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'msg' => $validator->errors()->first()]);
        }

        try {
            $day = $request['day_number'];
            $from_date = $request['from_date'];
            $from_date = Carbon::createFromFormat('d/m/Y', $from_date)->format('Y-m-d');
            $result = SummaryResult::where('date', '>=', $from_date)->select('dac_biet', 'nhat', 'nhi_1', 'nhi_2', 'ba_1', 'ba_2', 'ba_3', 'ba_4', 'ba_5', 'ba_6',
                'tu_1', 'tu_2', 'tu_3', 'tu_4', 'nam_1', 'nam_2', 'nam_3', 'nam_4', 'nam_5', 'nam_6', 'sau_1', 'sau_2', 'sau_3', 'bay_1', 'bay_2', 'bay_3', 'bay_4')
                ->get()->toArray();
            $arr = [];
            for ($i = 0; $i < count($result); $i++) {
                $item1 = (array_unique(array_values($result[$i])));
                $count = count($item1);

                if (!(isset($result[$i + ($day + 1)]))) break;

                // tìm các số đã về ở khoảng giữa
                $arr2 = [];
                for ($j = $i + 1; $j < $i + ($day + 1); $j++) {
                    $a = array_values($result[$j]);
                    $arr2 = array_unique(array_merge($arr2, $a));
                }

                $item2 = (array_unique(array_values($result[$i + ($day + 1)])));

                $temp = array_intersect($item1, $item2);
                $main = [];
                foreach ($temp as $item){
                    if(!in_array($item, $arr2)){
                        $main[] = $item;
                    }
                }

                $arr[] = count($main) / $count;
            }

            $data = 0;
            if (count($arr)) {
                $data = number_format(array_sum($arr) / count($arr) * 100, 2);
            }

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $ex) {
            return response()->json(['success' => false, 'msg' => $ex->getMessage()]);
        }
    }

    public function calculatePercent($num, $sum)
    {
        return number_format($num / $sum * 100, 1);
    }
}
