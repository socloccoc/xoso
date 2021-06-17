@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Tính xác suất của Đề</div>

                    <div class="card-body">
                        <form>
                            <div class="row">
                                <div class="form-group col-6">
                                    <label for="nhi_1">Giải nhì 1</label>
                                    <select class="form-control" id="nhi_1" name="nhi_1">
                                        <option value="10">10</option>
                                        <option value="01">01</option>
                                        <option value="00">00</option>
                                        <option value="11">11</option>
                                    </select>
                                </div>
                                <div class="form-group col-6">
                                    <label for="nhi_2">Giải nhì 2</label>
                                    <select class="form-control" id="nhi_2" name="nhi_2">
                                        <option value="10">10</option>
                                        <option value="01">01</option>
                                        <option value="00">00</option>
                                        <option value="11">11</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6 text-center">
                                    <button type="button" class="btn btn-primary calculate-de">Phân tích</button>
                                </div>
                                <div class="col-6 text-center">
                                    <div class="alert alert-success alert-success-de" role="alert">
                                        <strong><h5>Tỉ lệ giải đặc biệt </h5></strong>
                                            <span>01 -> <span id="de-01"></span></span><br>
                                            <span>10 -> <span id="de-10"></span></span><br>
                                            <span>00 -> <span id="de-00"></span></span><br>
                                            <span>11 -> <span id="de-11"></span></span><br>
                                        </ul>
                                    </div>
                                    <div class="alert alert-danger alert-danger-de" role="alert">
                                        There was errors, please try again !
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card mt-5">
                    <div class="card-header">Tính xác suất của Lô</div>

                    <div class="card-body">
                        <form>
                            <div class="row">
                                <div class="form-group col-6">
                                    <label for="nhi_1">Số ngày</label>
                                    <select class="form-control" id="day_number" name="day_number">
                                        @for($i = 0 ; $i < 21 ; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="form-group col-6">
                                    <label for="nhi_2">Từ ngày</label>

                                    <div class='input-group date' id='datetimepicker1'>
                                        <input type='text' id="from_date" name="from_date" value="10/06/2018" class="form-control"/>
                                        <span class="input-group-addon">
                                       <span class="glyphicon glyphicon-calendar"></span>
                                       </span>
                                    </div>

                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6 text-center">
                                    <button type="button" class="btn btn-primary calculate-lo">Phân tích</button>
                                </div>
                                    <div class="col-6 text-center">
                                        <div class="alert alert-success alert-success-lo" role="alert">
                                            <strong><h5>Tỉ lệ </h5></strong>
                                                <span><span id="lo-01"></span></span><br>
                                            </ul>
                                        </div>
                                        <div class="alert alert-danger alert-danger-lo" role="alert">
                                            There was errors, please try again !
                                        </div>
                                    </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script-footer')
    <script src="{{ asset('js/xoso.js') }}"></script>
@endsection
