import FroalaEditor from 'froala-editor';
import 'froala-editor/js/plugins/link.min.js'

new FroalaEditor('#editor', {
    toolbarButtons: ['bold', 'insertLink'],
    heightMin: 140,
});