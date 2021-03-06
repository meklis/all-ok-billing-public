<?php
$BASE = getGlobalConfigVar('BASE');
$VERSION = getGlobalConfigVar('VERSION');
$data = "";

$data .= <<<EOL
</div>
<!-- /page content -->
<!-- footer content -->
<footer>
    <div class="pull-right">
       Service {$BASE['provider_name']} &copy; 2020 - ver {$VERSION}
    </div>
    <div class="clearfix"></div>
</footer>
<!-- /footer content -->
</div>
</div>

<!-- Bootstrap -->
<script src="/res/js/bootstrap.min.js"></script>
<!-- FastClick -->
<script src="/res/js/fastclick.js"></script>
<!-- NProgress -->
<script src="/res/js/nprogress.js"></script>

<!-- Custom Theme Scripts -->
<script src="/res/js/custom.min.js?ver=1"></script>

<script type="text/javascript" src="/res/noty/noty.js"></script>

<!-- Input mask library --> 
<script src="/res/inputmask/dist/inputmask.js"></script>
<script src="/res/inputmask/dist/bindings/inputmask.binding.js"></script>

<script>
    $(document).ready(function(){ 
      Inputmask().mask(document.querySelectorAll("input"));
    });
    $(document).ready(function(){
        $('body').append('<a href="#" id="go-top" title="Up"><i class="fa fa-chevron-up"  ></i></a>');
    });

    $(document).ready(function(){
        $('#menu-to-top').append('<a id="go-menu"  onclick="toggleMenu(); savePrevState(); checkScrolling();  return true;"><i class="fa fa-bars"></i></a>');
    });

    $(function() {
        $.fn.scrollToTop = function() {
            $(this).hide().removeAttr("href");
            if ($(window).scrollTop() >= "150") $(this).fadeIn("slow")
            var scrollDiv = $(this);
            $(window).scroll(function() {
                if ($(window).scrollTop() <= "150") $(scrollDiv).fadeOut("slow")
                else $(scrollDiv).fadeIn("slow")
            });
            $(this).click(function() {
                $("html, body").animate({scrollTop: 0}, "slow")
            })
        }
    });

    $(function() {
        $.fn.openMenu = function() {
            $(this).hide().removeAttr("href");
            if ($(window).scrollTop() >= "150") $(this).fadeIn("slow")
            var scrollDiv = $(this);
            $(window).scroll(function() {
                if ($(window).scrollTop() <= "150") $(scrollDiv).fadeOut("slow")
                else $(scrollDiv).fadeIn("slow")
            });
        }
    });

    function toggleMenu() {
        var classes =  $('#main_body').attr('class');
        if (classes === "nav-md") {
            $('#main_body').attr('class', "nav-sm");
            console.log("change menu state to nav-sm");
        } else {
            $('#main_body').attr('class', "nav-md");
            console.log("change menu state to nav-md");
        }
    };

    $(function() {
        $("#go-top").scrollToTop();
    });
    $(function() {
        $("#go-menu").openMenu();
    });
    $(window).scroll(function () {
        //set scroll position in session storage
        sessionStorage.scrollPath = window.location.pathname;
        sessionStorage.scrollPos = $(window).scrollTop();
    });
    var init = function () {
        //get scroll position in session storage

       if(sessionStorage.scrollPath === window.location.pathname) {
           $(window).scrollTop(sessionStorage.scrollPos || 0)
       }
    };
    window.onload = init;
</script>
</body>
</html>

EOL;

return $data;