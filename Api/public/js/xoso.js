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
});
