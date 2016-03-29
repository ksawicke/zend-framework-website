/**
 * Javascript timeOffCommon 'class'
 *
 */
var timeOffCommon = new function ()
{
    this.initialize = function () {
        $(document).ready(function () {
            timeOffCommon.fadeOutFlashMessage();
            timeOffCommon.autoOpenDropdownOnHover();
            
            $("#navbar > ul > li > ul > li > a").on( 'click', function() {
                $('.dropdown ul').hide();
                $('li.dropdown.open').removeClass('open');
            });
        });
    }
    
    /**
     * Fade out flash messages automatically.
     */
    this.fadeOutFlashMessage = function () {
        var sec = 10;
        var timer = setInterval(function () {
            $('#applicationFlashMessage span').text(sec--);
            if (sec == -1) {
                $('#applicationFlashMessage').fadeOut('fast');
                clearInterval(timer);
            }
        }, 1000);
    }
    
    this.autoOpenDropdownOnHover = function() {
        $(".dropdown").hover(            
            function() {
                $('.dropdown-menu', this).stop( true, true ).fadeIn("fast");
                $(this).toggleClass('open');
                $('b', this).toggleClass("caret caret-up");                
            },
            function() {
                $('.dropdown-menu', this).stop( true, true ).fadeOut("fast");
                $(this).toggleClass('open');
                $('b', this).toggleClass("caret caret-up");                
            });
    }
}

// Initialize the class
timeOffCommon.initialize();