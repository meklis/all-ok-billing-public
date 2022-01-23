<?php
require __DIR__ . '/../../include/load.php';

init();

?>
<?= tpl('head', ['title' => '']) ?>

    <form class="form-inline" onsubmit="updateDates(); load(); return false;">
        <div class="form-group">
            <label for="start">Периоды:</label>
            <input class="form-control" id="time-start">
        </div>
        <div class="form-group">
            <label for="time-end">-</label>
            <input class="form-control" id="time-end">
        </div>
        <button type="submit" class="btn btn-default" style="margin-top: 5px">Отобразить</button>
    </form>
    <div style="overflow: scroll; width: 100%; height: 300px">
        <div id="preload-calendar">
            <div style="width: 100%; height: 100%; text-align: center">
                <img src="/res/img/spinner-blue.gif" style="margin-top: 100px;width: 56px">
            </div>
        </div>
        <div id="schedule-table" style="display: none">

        </div>
    </div>
    <script src="https://unpkg.com/@popperjs/core@2/dist/umd/popper.js"></script>
    <link rel="stylesheet" href="calendar.css">
    <script src="calendar.js"></script>
    <script>
        const BASE_URL = "<?=getGlobalConfigVar('BASE')['api2_front_addr']?>"
        $.ajaxSetup({
            "headers": {
                "X-Auth-Key": getApiToken(),
            },
        });
        var days = {
            'start': moment().format('YYYY-MM-DD'),
            'end': moment().add(14, 'days').format('YYYY-MM-DD'),
        };
        var employees = [];


        $(function () {
            $('#time-start').datetimepicker({
                language: 'ru',
                pickTime: false,
                defaultDate: moment().format('DD.MM.YYYY')
            });
        });
        $(function () {
            $('#time-end').datetimepicker({
                language: 'ru',
                pickTime: false,
                defaultDate: moment().add(14, 'days').format('DD.MM.YYYY')
            });
        });

        function updateDates() {
            $('#schedule-table').hide()
            $('#preload-calendar').show()
            days = {
                start: moment(dateParser($('#time-start').val())).format('YYYY-MM-DD'),
                end: moment(dateParser($('#time-end').val())).format('YYYY-MM-DD'),
            }
        }

        function dateParser(dateStr) {
            var elements = dateStr.split(".")
            console.log(elements)
            return new Date(elements[2], elements[1] - 1, elements[0])
        }

        $(document).ready(() => {
            $('#schedule-table').hide()
            $('#preload-calendar').show()
            $.get(BASE_URL + '/v2/private/employees/responsible_list').success((r) => {
                employees = r.data
                load()
            });
        });

        function load() {
            $.get(BASE_URL + `/v2/private/employees/schedule?start=${days.start} 00:00:00&end=${days.end} 23:59:59`).success((r) => {
                let schedules = []
                r.data.forEach(elem => {
                    let grs = ''
                    if (elem.groups.length === 0) {
                        grs = '<b>Вся территория</b>'
                    } else {
                        elem.groups.forEach(g => {
                            grs += `<li>${g.name}</li>`
                        })
                    }
                    schedules.push({
                        id: elem.id,
                        start: elem.start,
                        end: elem.end,
                        employee: elem.employee.id,
                        work_type: elem.calendar.work_type,
                        description: `
                        <b>${elem.title}</b><br>
                        <i>С: ${elem.start}<br>По: ${elem.end}</i><br>
                        Группы:
                        <div style='margin-left: 2px'>
                            ${grs}
                        </div>
                       `,
                    })
                })
                var calendar = new ScheduleCalendar();
                calendar.setDates(days.start, days.end).setEmployees(employees).setSchedules(schedules).render('#schedule-table').createPopper()
                $('#preload-calendar').hide()
                $('#schedule-table').show()
            });

        }

    </script>
<?= tpl('footer') ?>