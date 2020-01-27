import { InputRule, wrappingInputRule } from 'prosemirror-inputrules';
import { MarkType, NodeType } from 'prosemirror-model';
import BlockquoteNode from 'text-editor/Node/Blockquote';
import InputRuleCollectionInterface from 'text-editor/InputRuleCollection/InputRuleCollectionInterface';

export default class Blockquote implements InputRuleCollectionInterface {
    get requirementType(): string | null {
        return 'node';
    }

    get requirement(): Function | null {
        return BlockquoteNode;
    }

    public getRules(type?: MarkType | NodeType): InputRule[] {
        if (!(type instanceof NodeType)) {
            return [];
        }

        return [
            wrappingInputRule(/^\s*>\s$/, type),
        ];
    }
}
