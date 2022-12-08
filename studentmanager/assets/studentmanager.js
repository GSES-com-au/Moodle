require(['core/first', 'jquery', 'jqueryui', 'core/ajax'], function(core, $, bootstrap, ajax) {
    
    //needs to include in index 

    //when page loads load this function
    $(document).ready(function() {

        var params = {} //grabs search bar and puts each parameter and value in params
        window.location.search
        .replace(/[?&]+([^=&]+)=([^&]*)/gi, function(str, key, value) {
            params[key] = value;
        });
        //if params month has a value
        if (params['month']) {
            $('#month option[value=' + params['month'] + ']').attr('selected', 'selected');
            //jquery goes and finds month drop downlist finds value that matches paramter month if it finds it then selected it as attrivute
        }
        if (params['year']) {
            $('#year option[value=' + params['year'] + ']').attr('selected', 'selected');
            //jquery goes and finds month drop downlist finds value that matches paramter month if it finds it then selected it as attrivute
        }
        if (params['startdate']) {
            $('#startdate option[value=' + params['startdate'] + ']').attr('selected', 'selected');
            //jquery goes and finds month drop downlist finds value that matches paramter month if it finds it then selected it as attrivute
        }

        //click function when user clicks search
        $('#search').click(function() {
            searchusers();
        });

        function searchusers() {
            console.log('search users');
            //grab value of month and year
            window.open("/local/studentmanager/index.php?month=" + $('#month').val() + "&year=" + $('#year').val() + "&startdate=" + $('#startdate').val(), '_self');
        }

    });

});