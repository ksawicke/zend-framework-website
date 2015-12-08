/**
 * Javascript timeoffHandler 'class'
 *
 */
var timeoffHandler = new function()
{
    var selectedTimeoffCategory = null,
    	selectedDates = [],
    	selectedDateCategories = [],
    	selectedDateHours = [];

    /**
     * Initializes binding
     */
    this.initialize = function() {
        $(document).ready(function() {
        	$(".timeOffCategory").click(function() {
        		timeoffHandler.resetTimeoffCategory();
        		timeoffHandler.setTimeoffCategory($(this));
        	});
        	
        	$(".calendar-day").hover(function() {
        		if(selectedTimeoffCategory!==null) {
        			$(this).toggleClass(selectedTimeoffCategory);
        			$(this).children("div").toggleClass(selectedTimeoffCategory);
        		}
        	});
        	
        	$(".calendar-day").click(function() {
        		var index = selectedDates.indexOf($(this).attr("data-date"));
        		if (index != -1) {
        			selectedDates.splice(index, 1);
        			selectedDateCategories.splice(index, 1);
        			selectedDateHours.splice(index, 1);
        			$(this).toggleClass(selectedTimeoffCategory);
        			$(this).children("div").toggleClass(selectedTimeoffCategory);
        		} else {
        			selectedDates.push($(this).attr("data-date"));
        			selectedDateCategories.push(selectedTimeoffCategory);
        			selectedDateHours.push('8.00');
        			$(this).toggleClass(selectedTimeoffCategory);
        			$(this).children("div").toggleClass(selectedTimeoffCategory);
        		}
        		
        		datesSelectedHtml = '';
        		$.each(selectedDates, function(key, date) {
        			datesSelectedHtml += '<span class="glyphicon glyphicon-' + selectedDateCategories[key] + '"></span>&nbsp;&nbsp;&nbsp;&nbsp;' + date + '&nbsp;&nbsp;&nbsp;&nbsp;<input id="blah" value="8.00" size="2"><br style="clear:both;" />';
        			// <span class="glyphicon glyphicon-timeOffPTO"></span>&nbsp;&nbsp;&nbsp;&nbsp;02/02/2016&nbsp;&nbsp;&nbsp;&nbsp;<input id="blah" value="8.00" size="2"><br style="clear:both;" />
        		});
        		if(selectedDates.length==0) {
        			datesSelectedHtml = '<i>No dates are currently selected.</i>';
        		}
        		$("#datesSelected").html(datesSelectedHtml);
        		
        		console.log(selectedDates);
        		console.log(selectedDateCategories);
        		console.log(selectedDateHours);
        	});
        });
    }

    this.resetTimeoffCategory = function() {
    	$(".timeOffCategory").removeClass("selected");
    	$(".timeOffCategoryLeft").html('&nbsp;<br />&nbsp;');
    }
    
    this.setTimeoffCategory = function(object) {
    	selectedTimeoffCategory = object.attr("data-category");
    	object.addClass("selected");
    	$("." + object.attr("data-category")).html('<span class="glyphicon glyphicon-ok" aria-hidden=true></span><br />&nbsp;');
//        $.ajax({
//            url: timeoffHandler.sampleURL,
//            type: 'POST',
//            data: { sample: 'none' },
//            dataType: 'xml'
//        })
//            .success( function(xml) {
//                console.log( 'Data happy.' );
//                return;
//            })
//            .error( function(xml) {
//                console.log( 'There was some error.' );
//                return;
//            });
    }
};

// Init the class
timeoffHandler.initialize();