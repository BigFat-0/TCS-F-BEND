<?php
// v1/admin_calendar.php
require_once 'admin_header.php';
?>

<div class="admin-container">
    <div class="page-header">
        <h1><i class="fas fa-calendar-alt"></i> Schedule</h1>
    </div>

    <div id='calendar'></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: 'api_calendar.php',
        eventClick: function(info) {
            if (info.event.url) {
                window.location.href = info.event.url;
                info.jsEvent.preventDefault();
            }
        }
    });
    calendar.render();
});
</script>
</body>
</html>
