import { InputRule, wrappingInputRule } from 'prosemirror-inputrules';
import { MarkType, NodeType } from 'prosemirror-model';
import UnorderedListNode from 'text-editor/Node/UnorderedList';
import InputRuleCollectionInterface from 'text-editor/InputRuleCollection/InputRuleCollectionInterface';

export default class UnorderedList implements InputRuleCollectionInterface {
    get requirementType(): string | null {
        return 'node';
    }

    get requirement(): Function | null {
        return UnorderedListNode;
    }

    public getRules(type?: MarkType | NodeType): InputRule[] {
        if (!(type instanceof NodeType)) {
            return [];
        }

        return [
            wrappingInputRule(/^\s*([-+*])\s$/, type),
        ];
    }
}
