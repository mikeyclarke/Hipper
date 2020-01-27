import NodeInterface from 'text-editor/Node/NodeInterface';

export default class Doc implements NodeInterface {
    get name(): string {
        return 'doc';
    }

    get spec(): object {
        return {
            content: 'block+',
        };
    }
}
