import { render } from '@wordpress/element';
import { Editor } from './boot/index';

render(<Editor />, document.querySelector('#mailpoet-email-editor'));
