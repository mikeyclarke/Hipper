import { MarkType, NodeType } from 'prosemirror-model';

export default interface KeymapBindingInterface {
    readonly requirementType: string | null;

    readonly requirement: Function | null;

    getBindings(isMacOs: boolean, type?: MarkType | NodeType): object[];
}
