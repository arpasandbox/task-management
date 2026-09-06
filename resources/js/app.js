import './bootstrap';
import './kanban-board';

import 'tinymce/tinymce';
import 'tinymce/icons/default';
import 'tinymce/themes/silver';
import 'tinymce/models/dom';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/link';
import 'tinymce/plugins/image';
import 'tinymce/plugins/table';
import 'tinymce/plugins/code';

document.addEventListener('DOMContentLoaded', () => {
  tinymce.init({
    selector: '#editor',
    license_key: 'gpl',        // <-- right here
    plugins: 'lists link image table code help wordcount',
    toolbar: 'undo redo | blocks | bold italic | bullist numlist | link image | code',
    height: 400,
    menubar: false
  });
});