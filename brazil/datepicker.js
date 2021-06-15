
$(function(){
	var d1;
	var d2;
    var start_date;
    var end_date;
	
	var t = new Date();
	var month  = (t.getMonth() + 1 > 9 ? "" : "0") + (t.getMonth() + 1);
	var date   = (t.getDate() > 9 ? "" : "0") + (t.getDate());
	var year   = t.getFullYear();

    
	var today  = month+"/"+date+"/"+year;
    start_date = year + "-" + month + "-" + date;
	document.getElementById("datepicker1").setAttribute("value",today);
	document.getElementById("datepicker2").setAttribute("value",today);
	var bb = today.split(' ');
	d1 = new Date(bb);
    

	
	$("#datepicker1").datepicker({
        format: "dd-mm-yyyy",
        startDate: '20-03-2018',
        endDate: '-1d',
		showOtherMonths: true,
		selectOtherMonths: true,
		changeMonth: true,
		changeYear: true,
		 
		}).on("changeDate", function (e) {
			var a = $.datepicker.formatDate("yy mm dd", $(this).datepicker("getDate"));
			var b = a.split(' ');
            d1 = new Date(b);
            month      = (d1.getMonth() + 1 > 9 ? "" : "0") + (d1.getMonth() + 1);
            date       = (d1.getDate() > 9 ? "" : "0") + (d1.getDate());
            year       = d1.getFullYear();
            today      = month+"/"+date+"/"+year;
            start_date = year + "-" + month + "-" + date;
            end_date   = year + "-" + month + "-" + date;
            document.getElementById("datepicker1").setAttribute("value",today);
            document.getElementById("datepicker2").setAttribute("value",today);
		}); 
	
	
    $("#datepicker2").datepicker({
		showOtherMonths: true,
		selectOtherMonths: true,
		changeMonth: true,
		changeYear: true,
		altFormat: "DD, MM d, yy",
    }).on("changeDate", function (e) {
			var a = $.datepicker.formatDate("yy mm dd", $(this).datepicker("getDate"));
			var b = a.split(' ');
            d2    = new Date(b);
            month    = (d2.getMonth() + 1 > 9 ? "" : "0") + (d2.getMonth() + 1);
            date     = (d2.getDate() > 9 ? "" : "0") + (d2.getDate());
            year     = d2.getFullYear();
            today    = month+"/"+date+"/"+year;
            end_date = year + "-" + month + "-" + date;
            document.getElementById("datepicker2").setAttribute("value",today);
    });
	
	$("#clickDate").on('click',function(){
	var oneDay = 24*60*60*1000;	// hours*minutes*seconds*milliseconds
	var diffDays = Math.round((d2-d1)/oneDay);
	document.getElementById("output").innerHTML = "Days of search:\t" + diffDays;
    document.location.href = "app.php?flag=images&obtained_at=" + start_date + "&end_date=" + end_date;
	});

});

function get_today(){
    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth() + 1; //January is 0!
    var yyyy = today.getFullYear();

    if (dd < 10) {
      dd = '0' + dd;
    }

    if (mm < 10) {
      mm = '0' + mm;
    }

    today = yyyy + '-' + mm + '-' + dd;
    return today;
}
/**
$('#datepickerstart').datepicker({
    //format: "dd-mm-yyyy",
    todayHighlight: true,
    startDate: '22-03-2018',
    endDate: '-1d',
    language: 'pt-BR',
    // datesDisabled: ['12/04/2018']
}).on("changeDate", function (e) {
    var day = (e.date.getDate() > 9 ? "" : "0") + (e.date.getDate());
    var month = (e.date.getMonth() + 1 > 9 ? "" : "0") + (e.date.getMonth() + 1);
    var year = e.date.getFullYear();
    value_date = year + "-" + month + "-" + day;
    //document.location.href = "app.php?flag=images&obtained_at=" + value_date + "&end_date=" + end_date;
});**/