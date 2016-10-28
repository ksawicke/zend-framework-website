/**
 *
 */

function WidgetTimeOffSupervisorProxies() {

    this.initialize = function() {
        $(document).ready(this.subInitialize());
    }

    this.subInitialize = function() {
        this.getSupervisorProxiesData();
        this.enableBindings();
    }

    clearSelectValues = function() {
        $("#newProxyEmployee").val('');
        $("#newProxyEmployeeProxy").val('');
        $("#newProxyEmployee").trigger('change');
        $("#newProxyEmployeeProxy").trigger('change');
        $("#addNewProxyButton").prop('disabled', true);
    }

    this.enableBindings = function() {

        $("#clearProxyEmployeeButton").on('click', function() {
            clearSelectValues();
        });

        $("#addNewProxyButton").on('click', function() {
            if ($.trim($("#newProxyEmployee").val()) != '' &&
                $.trim($("#newProxyEmployeeProxy").val()) != '') {
                $.ajax({
                    url: phpVars.basePath + '/api/proxy',
                    dataType: 'json',
                    type: 'POST',
                    data: {
                        EMPLOYEE_NUMBER: $("#newProxyEmployee").val(),
                        PROXY_EMPLOYEE_NUMBER: $("#newProxyEmployeeProxy").val()
                    },
                }).always( function() {
                    clearSelectValues();
                    $('#widgetSupervisorProxiesDataTable').DataTable().ajax.reload();
                });
            }
        })

        $("#newProxyEmployee").select2({
            ajax: {
                url: phpVars.basePath + '/api/search/employee',
                dataType: 'json',
                type: 'POST',
                // Our search term and what page we are on
                data: function (params) {
                    return {
                        search: params.term,
                        employeeNumber: phpVars.employee_number,
                        exclude : $("#newProxyEmployeeProxy").val(),
                        excludePayroll: 'Y'
                    }
                },
                processResults: function (data, params) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.text,
                                id: item.id
                            }
                        })
                    };
                },

            },
            width: 'resolve',
            placeholder: 'Please select a supervisor',
            allowClear: true,
        }).on('select2:select', function (evt) {
            if ($.trim($("#newProxyEmployee").val()) != '' && $.trim($("#newProxyEmployeeProxy").val()) != '') {
                $("#addNewProxyButton").prop('disabled', false);
            } else {
                $("#addNewProxyButton").prop('disabled', true);
            }
        }).on('select2:unselect', function (evt) {
            $("#addNewProxyButton").prop('disabled', true);
        });

        $("#newProxyEmployeeProxy").select2({
            ajax: {
                url: phpVars.basePath + '/api/search/employee',
                dataType: 'json',
                type: 'POST',
                // Our search term and what page we are on
                data: function (params) {
                    return {
                        search: params.term,
                        employeeNumber: phpVars.employee_number,
                        exclude : $("#newProxyEmployee").val(),
                        excludePayroll: 'Y'
                    }
                },
                processResults: function (data, params) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.text,
                                id: item.id
                            }
                        })
                    };
                },

            },
            width: 'resolve',
            placeholder: 'Please select a proxy',
            allowClear: true,
        }).on('select2:select', function (evt) {
            if ($.trim($("#newProxyEmployee").val()) != '' && $.trim($("#newProxyEmployeeProxy").val()) != '') {
                $("#addNewProxyButton").prop('disabled', false);
            } else {
                $("#addNewProxyButton").prop('disabled', true);
            }
        }).on('select2:unselect', function (evt) {
            $("#addNewProxyButton").prop('disabled', true);
        });

        $("#addNewProxyButton").on("click", function() {
            $("#addNewProxyDiv").removeClass("hidden");
        });

        $("#widgetSupervisorProxiesDataTable tbody").on('click', 'tr', function(e) {
            var thing = e.currentTarget.cells[2].children;
            var thingParent = thing[0].children[0].id;
            var thingClass = thing[0].children[0].className;
            /* remove button pressed */
            if (e.toElement.nodeName == 'BUTTON' && $("#"+e.toElement.id).attr("classaction") == "removeProxy") {
                $( "#dialog-confirm" ).removeClass("hidden").dialog({
                    resizable: false,
                    height: "auto",
                    width: 400,
                    modal: true,
                    buttons: {
                      "Delete": function() {
                          $.ajax({
                              type: 'POST',
                              url: phpVars.basePath + '/api/proxy/delete',
                              dataType: "json",
                              data: {
                                  EMPLOYEE_NUMBER: $("#"+e.toElement.id).attr("data-employee"),
                                  PROXY_EMPLOYEE_NUMBER: $("#"+e.toElement.id).attr("data-proxy-employee"),
                              },
                              success: function (data) {
                                  $('#widgetSupervisorProxiesDataTable').DataTable().ajax.reload();
                              }
                          });
                        $( "#dialog-confirm" ).addClass("hidden")
                        $( this ).dialog( "close" );
                      },
                      Cancel: function() {
                          $( "#dialog-confirm" ).addClass("hidden")
                        $( this ).dialog( "close" );
                      }
                    }
                  });
            }

            /* change proxy status */
            if (e.toElement.nodeName == 'INPUT' && $("#"+e.toElement.id).hasClass("proxy-supervisor-toggle")) {

                $.ajax({
                    type: 'POST',
                    url: phpVars.basePath + '/api/proxy/toggle',
                    dataType: "json",
                    data: {
                        EMPLOYEE_NUMBER: $("#"+e.toElement.id).attr("data-employee-number"),
                        PROXY_EMPLOYEE_NUMBER: $("#"+e.toElement.id).attr("data-proxy-employee-number"),
                        STATUS: ($("#"+thingParent).prop("checked") ? '1' : '0')
                    },
                    success: function (data) {
                        $('#widgetSupervisorProxiesDataTable').DataTable().ajax.reload();
                    }
                });
            }
        });

    }

    this.getSupervisorProxiesData = function() {

        var myData = {};
        /* this is some temp hard-coding, until security is in place */
        myData['employer_id'] = '002';
        myData['employee_id'] = 49872;

        /* request the budget data */

        $('#widgetSupervisorProxiesDataTable').DataTable({
            dom: 'R<"clear">lfrtip',
            "processing": true,
            "serverSide": true,
            "oLanguage": {
                "sSearch": "Search within this section for: "
             },
             responsive: true,
             renderer: "bootstrap",
            'ajax': {
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                'url': phpVars.basePath + '/api/get_supervisor_proxies_data_table',
                dataType: "json",
                type: 'POST',
                "data": function ( d ) {
                    d['employer_id'] = '002';
                    d['employee_id'] = 229589;
                    return JSON.stringify( d );
                }
            },
            "columns": [{ "data": "SUPERVISOR" },
                        { "data": "PROXY" },
                        { "data": "ACTIVE" },
                        { "data": "ACTIONS" },
                        ],
        }).on("xhr.dt",
                function(e, settings, data, xhr) {
                        $.each(
                                data.data,
                                function(index, record) {
                                    var checked  = data.data[index]['STATUS'] == '1' ? 'checked' : '';
                                    data.data[index]['SUPERVISOR'] = $.trim(data.data[index]['EMPLOYEE_PRLNM']) + ', ' + $.trim(data.data[index]['EMPLOYEE_PRCOMN']) + ' (' + $.trim(data.data[index]['EMPLOYEE_NUMBER']) + ')';
                                    data.data[index]['PROXY'] = $.trim(data.data[index]['PROXY_PRLNM']) + ', ' + $.trim(data.data[index]['PROXY_PRCOMN']) + ' (' + $.trim(data.data[index]['PROXY_EMPLOYEE_NUMBER']) + ')';
                                    data.data[index]['ACTIVE'] = '<div class="switch"><input id="cmn-toggle-' + index +
                                                                 '" class="cmn-toggle cmn-toggle-round-flat proxy-supervisor-toggle" type="checkbox"' + checked +
                                                                 ' data-employee-number="' + $.trim(data.data[index]['EMPLOYEE_NUMBER']) + '" data-proxy-employee-number="' +
                                                                 $.trim(data.data[index]['PROXY_EMPLOYEE_NUMBER']) + '"' + ' data-status="' +
                                                                 data.data[index]['STATUS'] + '"><label for="cmn-toggle-' + index + '"></label></div>';
                                    data.data[index]['ACTIONS'] = '<button type="button" class="fa fa-times red btn btn-primary-no-border-color" classAction="removeProxy" title="remove proxy" ' +
                                                                  ' data-employee="'+$.trim(data.data[index]['EMPLOYEE_NUMBER'])+'" data-proxy-employee="'+$.trim(data.data[index]['PROXY_EMPLOYEE_NUMBER'])+'" id="removeProxy_' +
                                                                  $.trim(data.data[index]['EMPLOYEE_NUMBER']) + '_' + $.trim(data.data[index]['PROXY_EMPLOYEE_NUMBER']) +
                                                                  '"></button>';
                                }
                        );
                }
        );

    }
}

widgetTimeOffSupervisorProxies = new WidgetTimeOffSupervisorProxies();
widgetTimeOffSupervisorProxies.initialize();