/**
 * Javascript timeoffHandler 'class'
 *
 */
var timeoffHandler = new function()
{
    var selectedTimeoffCategory = null;

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
        		console.log("YOU CLICKED ME");
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