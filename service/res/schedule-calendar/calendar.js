class ScheduleCalendar {
    _days = []
    _schedules = {}
    _employees = []
    _limitHours = null;

    constructor() {
    }
    setHourLimit(start, end) {
        this._limitHours = {
            start: start,
            end: end,
        }
        return this;
    }
    _processHourLimit(hours) {
        if(this._limitHours === null) {
            return hours
        }
        let arr = hours.split(':')

        if(arr[0] > this._limitHours.end) {
            arr[0] = this._limitHours.end
            arr[1] = '00'
        }
        if(arr[0] < this._limitHours.start) {
            arr[0] = this._limitHours.start
            arr[1] = '00'
        }

       return  arr[0] + ':' + arr[1];
    }
    createPopper() {
        let tooltip =  document.querySelector('#calendar-detailed-view')
        $('.calendar-element').map((id, e) =>  {
            ['mouseenter', 'focus'].forEach(event => {
                e.addEventListener(event, (e) => {
                    tooltip.innerHTML = `
                        
                    `
                    Popper.createPopper(document.querySelector('#' + e.target.id), tooltip, {
                        modifiers: [
                            {
                                name: 'flip',
                                options: {
                                    fallbackPlacements: ['top', 'bottom'],
                                },
                            },
                            {
                                name: 'offset',
                                options: {
                                    offset: [0, 8],
                                },
                            },
                        ],
                    });
                    tooltip.innerHTML = e.target.getAttribute('data-description')
                    tooltip.setAttribute('data-show', '')
                });
            });
            ['mouseleave', 'blur'].forEach(event => {
                e.addEventListener(event, () => {
                    tooltip.removeAttribute('data-show')
                });
            });
        })


    }
    setDates(start, end) {
        let startDate  = this.__toTimestamp(start + ' 00:00:00') * 1000
        let endDate = this.__toTimestamp(end  + ' 00:00:00') * 1000
        while (startDate <= endDate) {
            this._days.push(startDate)
            startDate += (86400 * 1000)
        }
        return this
    }
    setEmployees(employees) {
        this._employees = []
        employees.forEach(e => {
            this._employees.push(e)
        })
        return this
    }

    setSchedules(schedules) {
        this._schedules = []
        schedules.forEach(schedule => {
            console.log(schedule);
           let start = new Date(schedule.start).getTime()
           let end = new Date(schedule.end).getTime()
           this._days.forEach(day => {
               if(typeof  this._schedules[day] === 'undefined') {
                   this._schedules[day] = []
               }

               
               let dayStart =  day
               let dayEnd = day + (86400 * 1000)

               if(start >= dayStart && end <= dayEnd) {
                   console.log("В этот же день начался и закончился")
                   this._schedules[day].push({
                       id: schedule.id,
                       employee: schedule.employee,
                       description: schedule.description,
                       status: schedule.work_type,
                       start: this._processHourLimit(moment(start).format('HH:mm')),
                       end: this._processHourLimit(moment(end).format('HH:mm')),
                   })
               } else if (start >= dayStart && start < dayEnd && end >= dayEnd) {
                   console.log("Начался в этот день, а закончился позже")
                   this._schedules[day].push({
                       id: schedule.id,
                       employee: schedule.employee,
                       description: schedule.description,
                       status: schedule.work_type,
                       start: this._processHourLimit(moment(start).format('HH:mm')),
                       end:  this._processHourLimit('23:59'),
                   })
               } else if (start <= dayStart && end < dayEnd && end >= dayStart) {
                   console.log("Начался раньше, но закончился в этот день")
                   this._schedules[day].push({
                       id: schedule.id,
                       employee: schedule.employee,
                       description: schedule.description,
                       status: schedule.work_type,
                       start: this._processHourLimit('00:00'),
                       end:  this._processHourLimit(moment(end).format('HH:mm')),
                   })
               } else if (start < dayStart && end > dayEnd) {
                   console.log("Начался раньше, и не закончился в этот же день")
                   this._schedules[day].push({
                       id: schedule.id,
                       employee: schedule.employee,
                       description: schedule.description,
                       status: schedule.work_type,
                       start: this._processHourLimit('00:00'),
                       end:  this._processHourLimit('23:59'),
                   })
               }
           });
        })
        return this
    }
    __toTimestamp(strDate){
        var datum = Date.parse(strDate);
        return datum/1000;
    }
    render(selector) {
        $(selector).html(this._getHTML());
        return this
    }
    _getHTML() {
        let htmlDatesHeader = (() => {
            let dates = ''
            this._days.forEach(e => {
                let day = moment(e).locale("ru").format("ddd, DD.MM.YYYY", "ru")
                dates += `<th><div class="schedule-day-label">${day}</div></th>`
            })
            return dates
        })
        let rowRender = ''
        this._employees.forEach((employee) => {
            rowRender += `<tr><th><div class="calendar-employee-label">${employee.name}</div></th>`
            this._days.forEach(day => {
                rowRender += '<td><div class="calendar-employee-day">'
                let block = ''
                if(typeof this._schedules[day] !== "undefined") {
                    this._schedules[day].forEach(schedule => {
                        if(employee.id !== schedule.employee) {
                            return
                        }
                        let start = new Date('2020-01-01 '+schedule.start).getTime() / 1000
                        let end = new Date('2020-01-01 '+schedule.end).getTime() / 1000

                        if(end - start >= 86340) {
                            block += `
                            <div class="calendar-element calendar-status-${schedule.status}" id="calendar-${day}-${schedule.id}" data-description="${schedule.description}">Весь день</div>
                        `
                        } else {
                            let startDescr = schedule.start
                            let endDescr = schedule.end
                            if(schedule.start === '00:00') {
                                startDescr = 'НРВ'
                            }
                            if(schedule.end === '23:59') {
                                endDescr = 'КРВ'
                            }
                            block += `
                            <div class="calendar-element calendar-status-${schedule.status}" id="calendar-${day}-${schedule.id}" data-description="${schedule.description}">${startDescr}-${endDescr}</div>
                        `
                        }
                    })
                }
                if(block === '') {
                    rowRender += '<span style="margin: 3px; font-size: 85%"></span>'
                } else {
                    rowRender += block
                }
                rowRender += '</div></td>'
            })
            rowRender += `</tr>`
        })
        return  `
            <table class="calendar-table">
                <thead>
                <tr>
                    <th>Сотрудники</th>
                    ${htmlDatesHeader()}
                </tr>
               </thead>
               <tbody>
                    ${rowRender}
               </tbody>
           </table>
            <div id="calendar-detailed-view" role="tooltip">My tooltip</div>
        `;
    }
}