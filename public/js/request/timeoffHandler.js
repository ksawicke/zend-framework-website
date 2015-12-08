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
        });
    }

    this.resetTimeoffCategory = function() {
    	$(".timeOffCategory").removeClass("selected");
    }
    
    this.setTimeoffCategory = function(object) {
    	object.addClass("selected");
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