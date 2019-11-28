/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
    config.toolbar = 'Cms';
    config.toolbar_Cms =
    [
        ['Source', 'Preview'],
        ['Cut','Copy','Paste','PasteText','PasteFromWord'],
        ['Undo','Redo','-','SelectAll','RemoveFormat'],
        ['Bold','Italic','-','Subscript','Superscript'],
        ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
        ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
        ['Link','Unlink','Anchor'],
        ['Image','Table','SpecialChar'],
        '/',
        ['Styles','Format','FontSize'],
        ['TextColor'],
        ['Maximize', 'ShowBlocks']
    ];
};
