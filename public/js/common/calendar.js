/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
// http://www.richarea.com/demo/rich_calendar/
var cal_obj = null;
var format = '%d/%m/%Y';
var elem;
function show_cal(el) {
    elem = el;
    if (cal_obj) return;
    var text_field = document.getElementById(el);
    cal_obj = new RichCalendar();
    cal_obj.start_week_day = 0;
    cal_obj.show_time = false;
    cal_obj.user_onchange_handler = cal_on_change;
    cal_obj.user_onclose_handler = cal_on_close;
    cal_obj.user_onautoclose_handler = cal_on_autoclose;
    cal_obj.parse_date(text_field.value, format);
    cal_obj.show_at_element(text_field, "adj_right-bottom");
}
function cal_on_change(cal, object_code) {
    if (object_code === 'day') {
        document.getElementById(elem).value = cal.get_formatted_date(format);
        cal.hide();
        cal_obj = null;
    }
}
function cal_on_close(cal) {
    if (window.confirm('Are you sure to close the calendar?')) {
        cal.hide();
        cal_obj = null;
    }
}
function cal_on_autoclose(cal) {
    cal_obj = null;
}