$(document).ready(function () {
    
    $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay,listWeek'
        },
        events: "get_events.php?calendar_type=standby",
        defaultDate: '2017-10-12',
        editable: false,
        eventLimit: false, // allow "more" link when too many events
        weekends: false
    });

});