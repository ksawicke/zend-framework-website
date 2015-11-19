var scripts = document.getElementsByTagName("script");
requirejs.config({
    baseUrl : scripts[scripts.length - 1].src.match(/.*?(?=\js\/|$)/i)[0],
    config : {
    },
    paths : {
        jquery : 'js/jquery-1.11.2.min',
        jqueryUI : 'js/jquery-ui.min',
        bootstrap : 'js/bootstrap.min',
        parsley : 'js/parsley.min',
        parsleyremote: 'js/parsley.remote.min,'
        'ckeditor-core' : 'js/ckeditor/ckeditor',
        'ckeditor-jquery' : 'js/ckeditor/adapters/jquery',
        dropzone : 'js/dropzone'
    },
    shim : {
        bootstrap : [ 'jquery' ],
        dropzone : [ 'jquery' ],
        'ckeditor-jquery' : {
            deps : [ 'jquery', 'ckeditor-core' ]
        }
    }
});

requirejs([ "js/main" ]);

/**
<!-- script src="{base_url}js/jquery/1.11.2/jquery.min.js"></script>
<script src="{base_url}js/parsley.remote.min.js"></script>
<script src="{base_url}js/parsley.min.js"></script>
<script src="{base_url}js/bootstrap.min.js"></script>
<script src="{base_url}js/ckeditor/ckeditor.js"></script>
<script src="{base_url}js/ckeditor/adapters/jquery.js"></script>
<script src="{base_url}js/dropzone.js"></script-->

<script data-main="{base_url}js/app"
  src="{base_url}js/ckeditor/ckeditor.js"></script>
<script data-main="{base_url}js/app"
  src="{base_url}js/ckeditor/config.js"></script>
**/
