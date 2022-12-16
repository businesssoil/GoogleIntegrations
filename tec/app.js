(function () {
    
    if (localStorage.getItem("tab") !== null) $('a[href="#' + localStorage.getItem("tab") + '"]').tab('show');
    
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        localStorage.setItem("tab", $(e.target).attr('href').substr(1));
    });

    $('#vacationCalendar').datepicker({
        multidate: true
    })
    .on("changeDate", function (e) {
        $(".date-container").empty();

        e.dates.forEach(function (date) {
            $("<input name='dates[]' type='hidden' value='" + date.toISOString().split("T")[0] + "' />").appendTo(".date-container");
        });
    });

    $('#aniversaryDate').datepicker().on("changeDate", function (e) {
        $('[name="start_date"]').val(e.date.toISOString().split("T")[0]);
    });

    $("#aniversaryDate").on("keyup", function (e) {
        try {
            $('[name="start_date"]').val(new Date($("#aniversaryDate").val()).toISOString().split("T")[0]);
        } catch (ex) {
        
        }
    });

}());