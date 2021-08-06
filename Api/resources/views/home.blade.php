@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">

                <div>

                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">Tính xác suất</a></li>
                        <li role="presentation"><a href="#profile" aria-controls="profile" role="tab" data-toggle="tab">Cross Setting</a></li>
                        <li role="presentation"><a href="#messages" aria-controls="messages" role="tab" data-toggle="tab">User Test</a></li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="home">
                            <div class="card mt-5">
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
                        <div role="tabpanel" class="tab-pane" id="profile">
                            <div class="card mt-5">
                                <div class="card-header">Mới</div>

                                <div class="card-body">
                                    <form>
                                        <div class="row">
                                            <div class="form-group col-6">
                                                <label for="nhi_1">Lô</label>
                                                <input class="form-control" name="lo" type="number" value="{{ $crossSetting['lo'] }}" required>
                                            </div>
                                            <div class="form-group col-6">
                                                <label for="nhi_1">Đề</label>
                                                <input class="form-control" name="de" type="number" value="{{ $crossSetting['de'] }}" required>
                                            </div>
                                            <div class="form-group col-6">
                                                <label for="nhi_1">Xiên 2</label>
                                                <input class="form-control" name="xien2" type="number" value="{{ $crossSetting['xien2'] }}" required>
                                            </div>
                                            <div class="form-group col-6">
                                                <label for="nhi_1">Ba Càng</label>
                                                <input class="form-control" name="bacang" type="number" value="{{ $crossSetting['bacang'] }}" required>
                                            </div>
                                            <div class="form-group col-6">
                                                <label for="nhi_1">Xiên 3</label>
                                                <input class="form-control" name="xien3" type="number" value="{{ $crossSetting['xien3'] }}" required>
                                            </div>
                                            <div class="form-group col-6"></div>
                                            <div class="form-group col-6">
                                                <label for="nhi_1">Xiên 4</label>
                                                <input class="form-control" name="xien4" type="number" value="{{ $crossSetting['xien4'] }}" required>
                                            </div>
                                        </div>
                                       <div class="row">
                                            <div class="col-6 text-center">
                                                <button type="button" class="btn btn-primary cross-setting">Cập Nhật</button>
                                            </div>
                                            <div class="col-6 text-center">
                                                <div class="alert alert-success alert-success-cross" role="alert">
                                                    Cập nhật thành công !
                                                </div>
                                                <div class="alert alert-danger alert-danger-cross" role="alert">
                                                    Có lỗi xảy ra, vui lòng thử lại sau !
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="card mt-5">
                                <div class="card-header">Cũ</div>

                                <div class="card-body">
                                    <form>
                                        <div class="row">
                                            <div class="form-group col-6">
                                                <label for="nhi_1">Lô</label>
                                                <input class="form-control" name="lo_old" type="number" value="{{ $crossSettingOld['lo'] }}" required>
                                            </div>
                                            <div class="form-group col-6">
                                                <label for="nhi_1">Đề</label>
                                                <input class="form-control" name="de_old" type="number" value="{{ $crossSettingOld['de'] }}" required>
                                            </div>
                                            <div class="form-group col-6">
                                                <label for="nhi_1">Xiên 2</label>
                                                <input class="form-control" name="xien2_old" type="number" value="{{ $crossSettingOld['xien2'] }}" required>
                                            </div>
                                            <div class="form-group col-6">
                                                <label for="nhi_1">Ba Càng</label>
                                                <input class="form-control" name="bacang_old" type="number" value="{{ $crossSettingOld['bacang'] }}" required>
                                            </div>
                                            <div class="form-group col-6">
                                                <label for="nhi_1">Xiên 3</label>
                                                <input class="form-control" name="xien3_old" type="number" value="{{ $crossSettingOld['xien3'] }}" required>
                                            </div>
                                            <div class="form-group col-6"></div>
                                            <div class="form-group col-6">
                                                <label for="nhi_1">Xiên 4</label>
                                                <input class="form-control" name="xien4_old" type="number" value="{{ $crossSettingOld['xien4'] }}" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6 text-center">
                                                <button type="button" class="btn btn-primary cross-setting-old">Cập Nhật</button>
                                            </div>
                                            <div class="col-6 text-center">
                                                <div class="alert alert-success alert-success-cross-old" role="alert">
                                                    Cập nhật thành công !
                                                </div>
                                                <div class="alert alert-danger alert-danger-cross-old" role="alert">
                                                    Có lỗi xảy ra, vui lòng thử lại sau !
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="messages">
                            <div class="card mt-5">
                                <div class="card-header">Cài đặt thời gian gửi chỉ báo</div>

                                <div class="card-body">
                                    <form>
                                        <div class="row">
                                            <div class="form-group col-6">
                                                <label for="nhi_1">Lô Mới</label>
                                                <input class="form-control" name="lov2" type="text" value="{{ $scheduleSetting['lov2'] }}" required>
                                            </div>
                                            <div class="form-group col-6">
                                                <label for="nhi_1">Đề Mới</label>
                                                <input class="form-control" name="dev2" type="text" value="{{ $scheduleSetting['dev2'] }}" required>
                                            </div>
                                            <div class="form-group col-6">
                                                <label for="nhi_1">Lô Cũ</label>
                                                <input class="form-control" name="lov1" type="text" value="{{ $scheduleSetting['lov1'] }}" required>
                                            </div>
                                            <div class="form-group col-6">
                                                <label for="nhi_1">Đề Cũ</label>
                                                <input class="form-control" name="dev1" type="text" value="{{ $scheduleSetting['dev1'] }}" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6 text-center">
                                                <button type="button" class="btn btn-primary setting-schedule">Cập Nhật</button>
                                            </div>
                                            <div class="col-6 text-center">
                                                <div class="alert alert-success alert-success-schedule" role="alert">
                                                    Cập nhật thành công !
                                                </div>
                                                <div class="alert alert-danger alert-danger-schedule" role="alert">
                                                    Có lỗi xảy ra, vui lòng thử lại sau !
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
@section('script-footer')
    <script src="{{ asset('js/xoso.js') }}"></script>
@endsection
