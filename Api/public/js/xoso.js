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
                    $('.alert-danger').hide();
                    $('.alert-success').show();
                    let data = result.data;
                    $('#de-01').html(data['01']);
                    $('#de-10').html(data['10']);
                    $('#de-00').html(data['00']);
                    $('#de-11').html(data['11']);
                }else{
                    $('.alert-success').hide();
                    $('.alert-danger').show().html(result.msg);
                }
            }
        });
    });
});
