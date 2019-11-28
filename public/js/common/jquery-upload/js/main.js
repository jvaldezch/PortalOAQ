/*
 * jQuery File Upload Plugin JS Example 8.3.0
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

/*jslint nomen: true, regexp: true */
/*global $, window, blueimp */

$(function () {
    'use strict';
    $('#fileupload').fileupload({
        url: '/archivo/index/upload-files',
        dataType: 'json',
        sequentialUploads: true,
        disableImageResize: /Android(?!.*Chrome)|Opera/
            .test(window.navigator && navigator.userAgent),
        imageMaxWidth: 800,
        imageMaxHeight: 800,
        acceptFileTypes:  /(zip)|(pdf)|(xml)|(doc)|(xls)|(xlsx)|(docx)$/i
    });

    $('#fileupload').fileupload(
        'option',
        'redirect',
        window.location.href.replace(
            /\/[^\/]*$/,
            '/js/jquery-upload/cors/result.html?%s'
        )
    );
        
    $('#fileupload').addClass('fileupload-processing')
        .bind('fileuploadstop', function (e, data) { window.location.reload(); });
    
    $.ajax({
        url: $('#fileupload').fileupload('option', 'url'),
        dataType: 'json',
        context: $('#fileupload')[0]
    }).always(function () {
        $(this).removeClass('fileupload-processing');
    }).done(function (result) {
        $(this).fileupload('option', 'done')
            .call(this, null, {result: result});        
    });

});
