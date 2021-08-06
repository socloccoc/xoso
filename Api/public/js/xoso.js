$(document).ready(function () {
    $('.alert-danger').hide();
    $('.alert-success').hide();
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $('.calculate-de').on('click', function () {
        let nhi_1 = $('#nhi_1').val();
        let nhi_2 = $('#nhi_2').val();
        $.ajax({
            type: 'POST',
            url: '/ajax/de',
            data: {nhi_1: nhi_1, nhi_2: nhi_2},
            success: function (result) {
                if(result.success){
                    $('.alert-danger-de').hide();
                    $('.alert-success-de').show();
                    let data = result.data;
                    $('#de-01').html(data['01']);
                    $('#de-10').html(data['10']);
                    $('#de-00').html(data['00']);
                    $('#de-11').html(data['11']);
                }else{
                    $('.alert-success-de').hide();
                    $('.alert-danger-de').show().html(result.msg);
                }
            }
        });
    });

    $('.calculate-lo').on('click', function () {
        let day_number = $('#day_number').val();
        let from_date = $('#from_date').val();
        $.ajax({
            type: 'POST',
            url: '/ajax/lo',
            data: {day_number: day_number, from_date: from_date},
            success: function (result) {
                if(result.success){
                    $('.alert-danger-lo').hide();
                    $('.alert-success-lo').show();
                    let data = result.data;
                    $('#lo-01').html(data);
                }else{
                    $('.alert-success-lo').hide();
                    $('.alert-danger-lo').show().html(result.msg);
                }
            }
        });
    });
    
    // cross setting
    $('.cross-setting').on('click', function () {
        let lo = $('input[name="lo"]').val();
        let de = $('input[name="de"]').val();
        let bacang = $('input[name="bacang"]').val();
        let xien2 = $('input[name="xien2"]').val();
        let xien3 = $('input[name="xien3"]').val();
        let xien4 = $('input[name="xien4"]').val();
        $.ajax({
            type: 'POST',
            url: '/ajax/cross-setting',
            data: {id: 1, lo: lo, de: de, bacang : bacang, xien2: xien2, xien3 : xien3, xien4: xien4},
            success: function (result) {
                if(result.success){
                    $('.alert-danger-cross').hide();
                    $('.alert-success-cross').show();
                }else{
                    $('.alert-success-cross').hide();
                    $('.alert-danger-cross').show().html(result.msg);
                }
            }
        });
    });

    $('.cross-setting-old').on('click', function () {
        let lo = $('input[name="lo_old"]').val();
        let de = $('input[name="de_old"]').val();
        let bacang = $('input[name="bacang_old"]').val();
        let xien2 = $('input[name="xien2_old"]').val();
        let xien3 = $('input[name="xien3_old"]').val();
        let xien4 = $('input[name="xien4_old"]').val();
        $.ajax({
            type: 'POST',
            url: '/ajax/cross-setting',
            data: {id: 2, lo: lo, de: de, bacang : bacang, xien2: xien2, xien3 : xien3, xien4: xien4},
            success: function (result) {
                if(result.success){
                    $('.alert-danger-cross-old').hide();
                    $('.alert-success-cross-old').show();
                }else{
                    $('.alert-success-cross-old').hide();
                    $('.alert-danger-cross-old').show().html(result.msg);
                }
            }
        });
    });

    $('.setting-schedule').on('click', function () {
        let lov1 = $('input[name="lov1"]').val();
        let dev1 = $('input[name="dev1"]').val();
        let lov2 = $('input[name="lov2"]').val();
        let dev2 = $('input[name="dev2"]').val();
        $.ajax({
            type: 'POST',
            url: '/ajax/schedule-setting',
            data: {lov1: lov1, dev1: dev1, lov2: lov2, dev2: dev2},
            success: function (result) {
                if(result.success){
                    $('.alert-danger-schedule').hide();
                    $('.alert-success-schedule').show();
                }else{
                    $('.alert-success-schedule').hide();
                    $('.alert-danger-schedule').show().html(result.msg);
                }
            }
        });
    })
});
