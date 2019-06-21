
$(document).ready(function(){
    //$('#contactform').on('submit', function(e){



    document.getElementById('view_postmessage').onclick = function(e) {
    // $('#button_processingwall').on('submit', function(e){
        // e.preventDefault();
        $('#myModal').modal({ 
          backdrop: 'static',
          keyboard: true 
        });
        $("#myModal").modal('show');
        // $('div#download_img').css('display', 'block');
        $('div#divprogressbar').css('display', 'block');

        function view_postmessage(start) {
        setTimeout(function () { //The timer
            $.ajax({
            type: 'POST',
            url: '/ki_ndm/Ajaxfunctions',
            //data: $('#contactform').serialize(),
            data: {'view_postmessage': start},
            success: function(responce){
                // console.log(responce);
                //alert(responce);
                // alert(JSON.stringify(responce, "", 10));                               
                // $('div#result_div').html('start ' + responce);
                if (!responce) {
                  responce = 100;
                }
                document.getElementById('progressbar').style.width = responce + '%';
                document.getElementById('progressbar').innerHTML = responce + '%';                               
                // $('div#download_img').css('display', 'none');
            } 
            }); 
        }, start*1000); //needs the "start*" or else all the timers will run at 3000ms
        }
        for(var start = 1; start <= 100; start++) {
            view_postmessage(start);
        }
    };
});
