class Schedule {
    _days = []
    _hours = []
    _minutes = []

    _schedules = {}
    _disablePastTime = false
    _schedulesIgnoreDisable = false
    _disabledDays = {}
    constructor(days, hours, minuteStep) {
        console.log("Schedule class initialized")
        let start  = this.__toTimestamp(days.start + ' 00:00:00') * 1000
        let end = this.__toTimestamp(days.end + ' 00:00:00') * 1000
        while (start <= end) {
            this._days.push(start)
            start += (86400 * 1000)
        }
        let currentHour = hours.start
        while(currentHour <= hours.end) {
            this._hours.push(`${currentHour}`)
            currentHour += 1
        }
        if(minuteStep >= 60 || minuteStep === 0) {
            this._minutes.push(0)
        } else {
            let minute = 0
            while (minute < 60) {
                this._minutes.push(`${minute}`)
                minute += minuteStep
            }
        }
    }

    setDisabledDays(disabledDays) {
        disabledDays.forEach(e => {
            this._disabledDays[this.__toTimestamp(e)] = true
        })
        return this
    }
    setSchedules(schedules, ignoreDisabled = false) {
        this._schedulesIgnoreDisable = ignoreDisabled
        schedules.forEach(e => {
           this._schedules[this.__toTimestamp(e.date)] = {
               'status': e.status,
               'employee': e.employee,
           }
        })
        return this
    }
    disablePastTime(disable = true) {
        this._disablePastTime = disable
        return this
    }
    __toTimestamp(strDate){
        var datum = Date.parse(strDate);
        return datum/1000;
    }
    render(selector) {
        console.log("try to render")
        $(selector).html(this._getHTML());
        return this
    }
    _clearClass(className) {
        $('.' +className).map((id, elem) => {
            let elemId = elem.getAttribute('id');
            $(`#${elemId}`).removeClass(className)
        })
    }
    bind(handler, ignoreDisabled = false) {
        $( ".schedule-day-minutes" ).bind( "click", e => {
            let time = e.currentTarget.getAttribute('data-timestamp')
            let employee = e.currentTarget.getAttribute('data-employee')
            this._clearClass('schedule-day-minutes-active')
            $(`#${e.currentTarget.id}`).addClass('schedule-day-minutes-active')
            handler({
                date: time,
                employee: employee,
            }, e);
        });
        if(ignoreDisabled) {
            $(".schedule-day-minutes-disabled").bind("click", function (e) {
                let time = e.currentTarget.getAttribute('data-timestamp')
                let employee = e.currentTarget.getAttribute('data-employee')
                handler({
                    date: time,
                    employee: employee,
                }, e);
            });
        }
        return this;
    }

    _getHTML() {
        let timeNow = (new Date().getTime()) / 1000
        let htmlDatesHeader = (() => {
            let dates = ''
            this._days.forEach(e => {
                let day = moment(e).locale("ru").format("ddd, DD.MM.YYYY", "ru")
                dates += `<th><div class="schedule-day-label">${day}</div></th>`
            })
            return dates
        })
        let rowRender = ''
        this._hours.forEach(hour => {
            rowRender += `<tr>`
            this._days.forEach(day => {
                rowRender += '<td><div class="schedule-day-select" id="schedule-day">'
                let dayFormated = moment(day).format('YYYY-MM-DD')
                this._minutes.forEach(minute => {
                    if(minute.length === 1) {
                        minute = "0" + minute
                    }
                    if(hour.length === 1) {
                        hour = "0" + hour
                    }
                    let timeStampDate = this.__toTimestamp(`${dayFormated} ${hour}:${minute}:00`)
                    let className = 'schedule-day-minutes'
                    let tag = 'a'
                    if(this._disablePastTime && timeNow > timeStampDate) {
                        className = 'schedule-day-minutes-disabled'
                        tag = 'div'
                    }
                    let employee = 0
                    if(typeof this._schedules[timeStampDate] !== 'undefined' && (timeNow <= timeStampDate || this._schedulesIgnoreDisable)) {
                        switch (this._schedules[timeStampDate].status) {
                            case 'free': className += ' schedule-free'; break;
                            case 'busy': className += ' schedule-busy'; break;
                            case 'not-fit': className += ' schedule-not-fit'; break;
                        }
                        employee = this._schedules[timeStampDate].employee
                    }
                    if(typeof this._disabledDays[timeStampDate] !== 'undefined') {
                        className = ' schedule-day-minutes-disabled schedule-disabled-day '
                        tag = 'div'
                    }
                    rowRender += `<${tag} href="#${timeStampDate}" id="schedule-${timeStampDate}" title="${dayFormated} ${hour}:${minute}:00" style="" class="${className}" data-timestamp="${timeStampDate}" data-employee="${employee}"">
                                    ${hour}:${minute}  
                                  </${tag}>`
                })
                rowRender += '</div></td>'
            })
            rowRender += `</tr>`
        })
        return  `
            <table class="schedule-table">
                <thead>
                <tr>
                    ${htmlDatesHeader()}
                </tr>
               </thead>
               <tbody>
                    ${rowRender}
               </tbody>
           </table>
        `;
    }
}