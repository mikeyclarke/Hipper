import * as autosize from 'autosize';

export default class ElasticTextInput extends HTMLElement {
    constructor() {
        super();

        const textArea = this.querySelector('textarea');

        if (null === textArea) {
            return;
        }

        const initialContent = this.querySelector('.js-initial-content');
        if (this.hasAttribute('lazy') && null !== initialContent) {
            const initialHeight = initialContent.getBoundingClientRect().height;
            textArea.style.height = `${initialHeight}px`;
            initialContent.remove();
            this.setAttribute('has-loaded', 'true');
        }

        textArea.addEventListener('keydown', (event: KeyboardEvent) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                event.stopPropagation();
            }
        });

        autosize(textArea);
    }
}
