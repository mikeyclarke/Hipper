import * as autosize from 'autosize';
import timeout from 'Timeout/timeout';

async function onFormReset(textArea: HTMLTextAreaElement): Promise<void> {
    await timeout(100);
    autosize.update(textArea);
}

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

        if (textArea.form) {
            textArea.form.addEventListener('reset', onFormReset.bind(null, textArea));
        }

        autosize(textArea);
    }
}
