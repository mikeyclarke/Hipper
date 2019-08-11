import { NodeInterface } from 'text-editor/Node/NodeInterface';

export class Text implements NodeInterface {
    get name(): string {
        return 'text';
    }

    get spec(): object {
        return {
            group: 'inline'
        };
    }
}
