/**
 * Javascript timeOffProxyHandler 'class'
 *
 */
var timeOffProxyHandler = new function ()
{
    var timeOffProxySearchUrl = phpVars.basePath + '/api/search/proxies';

    /**
     * What to run on initialize of this class.
     * 
     * @returns {undefined}
     */
    this.initialize = function () {
        $(document).ready(function () {            
            var $eventLog = $(".js-event-log");
            var $requestForEventSelect = $("#requestFor");
            /**
             * When we change the for dropdown using select2,
             * set the employee number and name as a local variable
             * for form submission, and refresh the calendars.
             */
            $requestForEventSelect.on("select2:select", function(e) {
//                timeOffCreateRequestHandler.resetCategorySelection();
                var selectedEmployee = e.params.data;
//                requestForEmployeeNumber = selectedEmployee.id;
//                requestForEmployeeName = selectedEmployee.text;
//                timeOffCreateRequestHandler.loadCalendars(requestForEmployeeNumber);
//                $('.requestIsForMe').show();
            }).on("select2:open", function(e) {
                /**
                 * SELECT2 is opened
                 */
//                if (loggedInUserData.IS_LOGGED_IN_USER_PAYROLL === "N") {
//                    $("span").remove(".select2CustomTag");
//                    var $filter = '<form id="directReportForm" style="display:inline-block;padding 5px;">'
//                        + '<input type="radio" name="directReportFilter" value="B"'
//                        + ((directReportFilter === 'B') ? ' checked'
//                            : '')
//                        + '> Both&nbsp;&nbsp;&nbsp;'
//                        + '<input type="radio" name="directReportFilter" value="D"'
//                        + ((directReportFilter === 'D') ? ' checked'
//                            : '')
//                        + '> Direct Reports&nbsp;&nbsp;&nbsp;'
//                        + '<input type="radio" name="directReportFilter" value="I"'
//                        + ((directReportFilter === 'I') ? ' checked'
//                            : '')
//                        + '> Indirect Reports&nbsp;&nbsp;&nbsp;'
//                        + '</form>';
//                    $("<span class='select2CustomTag' style='padding-left:6px;'>"
//                        + $filter
//                        + "</span>")
//                    .insertBefore('.select2-results');
//                }
            }).on("select2:close", function(e) {
                /**
                 * SELECT2 is closed
                 */
            });
            
            $("#requestFor").prop('disabled', false);
            $("#requestFor").empty().append(
                '<option value="SELECT PROXY HERE</option>').val('229589').trigger('change');
            
//        var $requestForEventSelect = $("#requestFor");
            $requestForEventSelect.select2({
                ajax : {
                    url : timeOffProxySearchUrl,
                    method : 'post',
                    dataType : 'json',
                    delay : 250,
                    data : function(params) {
                        return {
                        search : params.term,
//                                directReportFilter : directReportFilter,
                                employeeNumber : phpVars.employee_number,
                                page : params.page
                        };
                    },
                    processResults : function(data, params) {
                        params.page = params.page || 1;
                        return {
                            results : data,
                            pagination : {
                            more : (params.page * 30) < data.total_count
                            }
                        };
                    },
                },
                allowClear : true,
                minimumInputLength : 2,
            });
        });
    }
    
}

// Initialize the class
timeOffProxyHandler.initialize();