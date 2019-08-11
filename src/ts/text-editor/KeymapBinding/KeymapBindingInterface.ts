import { MarkType, NodeType } from 'prosemirror-model';

export interface KeymapBindingInterface {
    readonly requirementType: string | null;

    readonly requirement: Function | null;

    getBindings(isMacOs: boolean, type?: MarkType | NodeType): object[];
}
