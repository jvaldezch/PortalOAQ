$(document).ready(function () {
    var $demo1 = $('table.traffic-table');
    $demo1.floatThead({
        position: 'absolute',
        scrollContainer: function ($table) {
            return $table.closest('.wrapper');
        }
    });
});