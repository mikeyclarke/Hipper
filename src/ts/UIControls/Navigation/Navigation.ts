import Template from '../../Library/Template/Template';
import UIControl from '../UIControl';
export class Navigation extends UIControl 
{
    public static template: Template;
    public template;
    private title: string = 'hleo';
    public events: Array<any> = [
        {
            selector: '.js-button',
            type: 'click',
            handler: 'buttonClicked',
        }
    ];

    constructor(el: string)
    {
        super(el);
        this.template = Navigation.template;
        if (!this.element) {
            this.render({title:'test'});
        }
        this.bindEvents();
    }

    public buttonClicked ()
    {
        console.log('button clicked!');
    }
}
