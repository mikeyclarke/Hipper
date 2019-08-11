import * as autosize from 'autosize';

export class ElasticTextInput extends HTMLElement {
    constructor() {
        super();

        if (null === this.querySelector('textarea')) {
            return;
        }

        autosize(<Element> this.querySelector('textarea'));
    }
}
