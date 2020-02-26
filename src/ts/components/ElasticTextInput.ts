import * as autosize from 'autosize';
import timeout from 'Timeout/timeout';

async function onFormReset(textArea: HTMLTextAreaElement): Promise<void> {
    await timeout(100);
    autosize.update(textArea);
}

function observeFormVisibility(textArea: HTMLTextAreaElement): void {
    const observer = new MutationObserver((mutationsList, mutationObserver) => {
        for (const mutation of mutationsList) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'hidden' &&
                mutation.target instanceof HTMLFormElement && mutation.target.hidden === false
            ) {
                autosize.update(textArea);
            }
        }
    });
    const observationConfig = {
        attributeFilter: ['hidden'],
    };
    observer.observe(<HTMLFormElement> textArea.form, observationConfig);
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

        if (textArea.form && textArea.form.hidden) {
            observeFormVisibility(textArea);
        }

        autosize(textArea);
    }
}
