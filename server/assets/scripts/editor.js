import FroalaEditor from 'froala-editor';
import 'froala-editor/js/plugins/link.min.js'

/**
 * WYSIWYG Froala Editor
 */
new FroalaEditor('#editor', {
    toolbarButtons: ['bold', 'insertLink'],
    heightMin: 140,
});