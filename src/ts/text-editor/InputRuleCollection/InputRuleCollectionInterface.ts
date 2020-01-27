import { InputRule } from 'prosemirror-inputrules';
import { MarkType, NodeType } from 'prosemirror-model';

export default interface InputRuleCollectionInterface {
    readonly requirementType: string | null;

    readonly requirement: Function | null;

    getRules(type?: MarkType | NodeType): InputRule[];
}
