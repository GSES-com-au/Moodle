require(['core/first', 'jquery', 'jqueryui', 'core/ajax'], function(core, $, bootstrap, ajax) {
    
    //needs to include in index 

    //when page loads load this function
    $(document).ready(function() {

        var params = {}; //grabs search bar and puts each parameter and value in params
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
        // if (params['startdate']) {
        //     $('#startdate option[value=' + params['startdate'] + ']').attr('selected', 'selected');
        //     //jquery goes and finds month drop downlist finds value that matches paramter month if it finds it then selected it as attrivute
        // }
        document.getElementById('startdate').value = params['startdate'];
        document.getElementById('enddate').value = params['enddate'];
        //click function when user clicks search
        $('#search').click(function() {
            searchusers();
        });

        function convertDate(dateString) {
            var p = dateString.split(/\D/g)
            return [p[2],p[1],p[0] ].join("-")
        }

        function searchusers() {
            console.log('search users');
            var start_date_input = document.getElementById("startdate").value;
            var end_date_input = document.getElementById("enddate").value;
            
            if (start_date_input > end_date_input) {
                start_date_input = convertDate(start_date_input);
                end_date_input = convertDate(end_date_input);
                alert(`Please make sure the start date (${start_date_input}) of the enrolment period is before the end date (${end_date_input}).`);
            } else {
                window.open("/local/studentmanager/index.php?month=" + $('#month').val() + "&year=" + $('#year').val() + "&startdate=" + $('#startdate').val() + "&enddate=" + $('#enddate').val(), '_self');
            }
            // const start_date = new Date(params['startdate']);
            // const end_date = new Date(params['enddate']);
            // if (end_date < start_date) {
            //     alert("Please make sure the end date is after the start date.");
            // } else {
            //     //grab value of month and year
                 
            // }
            
        }

    });

});