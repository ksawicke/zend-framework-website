/**
 * Javascript timeOffCompanyHolidayHandler 'class'
 *
 */
var timeOffCompanyHolidayHandler = new function ()
{
    var timeOffSubmitNewCompanyHolidayUrl = phpVars.basePath + '/api/request/add-company-holiday',
        timeOffDeleteCompanyHolidayUrl = phpVars.basePath + '/api/request/delete-company-holiday',
        timeOffShowCompanyHolidaysUrl = phpVars.basePath + '/request/manage-company-holidays';
    
    /**
     * Initializes binding
     */
    this.initialize = function () {
        $(document).ready(function () {
            timeOffCompanyHolidayHandler.handleLoadingCompanyHolidayList();
            timeOffCompanyHolidayHandler.handleAddNewCompanyHoliday();
            timeOffCompanyHolidayHandler.handleDeleteCompanyHoliday();
        });
    }
    
    /**
     * Loads the Company Holiday list.
     * 
     * @returns {undefined}
     */
    this.handleLoadingCompanyHolidayList = function() {
        $('#company-holiday-list').DataTable({
            dom: 'ltrip', //fltirp',
            searching: true,
            processing: true,
            serverSide: true,
            oLanguage: {
                sProcessing: "<img src='" + phpVars.basePath + "/img/loading/clock.gif'>"
            },
            columns: [
                {"data": "DATE"},
                {"data": "ACTIONS"}
            ],
            order: [],
//            columnDefs: [{"orderable": false,
//                    "targets": [1, 2]
//                }
//            ],
            ajax: {
                url: phpVars.basePath + "/api/request/company-holidays",
                data: function (d) {
                    return $.extend({}, d, {
                        "employeeNumber": phpVars.employee_number
                    });
                },
                type: "POST",
            }
        })
        .on("error.dt", function (e, settings, techNote, message) {
            console.log("An error has been reported by DataTables: ", message);
        });
    }
    
    this.handleAddNewCompanyHoliday = function() {
        $(".submitAddCompanyHoliday").click(function() {
            if( !timeOffCommon.empty( $("#newCompanyHoliday").val() ) ) {
                timeOffCompanyHolidayHandler.saveNewCompanyHoliday( $("#newCompanyHoliday").val() );
            }
        });
    }
    
    this.handleDeleteCompanyHoliday = function() {
        $(document).on('click','.submitDeleteCompanyHoliday', function() {
            var selectedCompanyHoliday = $(this).data('date');
            $("#dialogConfirmDeleteCompanyHoliday").dialog({
                modal : true,
                buttons : {
                    No : function() {
                        $(this).dialog("close");
                    },
                    Yes : function() {
                        timeOffCompanyHolidayHandler.deleteCompanyHoliday( selectedCompanyHoliday );
                    }
                }
            });
//            console.log( "Delete date " + $(this).data('date') );
//            if( !timeOffCommon.empty( $("#newCompanyHoliday").val() ) ) {
//                timeOffCompanyHolidayHandler.saveNewCompanyHoliday( $("#newCompanyHoliday").val() );
//            }
        });
    }
    
    this.deleteCompanyHoliday = function( selectedCompanyHoliday ) {
        $.ajax({
            url : timeOffDeleteCompanyHolidayUrl,
            type : 'POST',
            data : {
                request : {
                    date : selectedCompanyHoliday
                }
            },
            dataType : 'json'
        }).success(function(json) {
            if (json.success == true) {
                window.location.href = timeOffShowCompanyHolidaysUrl;
            } else {
                alert(json.message);
            }
            return;
        }).error(function() {
            console.log('There was some error.');
            return;
        });
    }
    
    this.saveNewCompanyHoliday = function( newCompanyHoliday ) {
        $.ajax({
            url : timeOffSubmitNewCompanyHolidayUrl,
            type : 'POST',
            data : {
                request : {
                    date : $("#newCompanyHoliday").val()
                }
            },
            dataType : 'json'
        }).success(function(json) {
            if (json.success == true) {
                window.location.href = timeOffShowCompanyHolidaysUrl;
            } else {
                alert(json.message);
            }
            return;
        }).error(function() {
            console.log('There was some error.');
            return;
        });
    }
};

// Initialize the class
timeOffCompanyHolidayHandler.initialize();