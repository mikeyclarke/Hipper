import { InputRule, wrappingInputRule } from 'prosemirror-inputrules';
import { MarkType, NodeType } from 'prosemirror-model';
import { OrderedList as OrderedListNode } from 'text-editor/Node/OrderedList';
import { InputRuleCollectionInterface } from 'text-editor/InputRuleCollection/InputRuleCollectionInterface';

export class OrderedList implements InputRuleCollectionInterface {
    get requirementType(): string | null {
        return 'node';
    }

    get requirement(): Function | null {
        return OrderedListNode;
    }

    public getRules(type?: MarkType | NodeType): InputRule[] {
        if (!(type instanceof NodeType)) {
            return [];
        }

        return [
            wrappingInputRule(
                /^(\d+)\.\s$/,
                type,
                match => ({ start: +match[1] }),
                (match, node) => node.childCount + node.attrs.start === +match[1]
            ),
        ];
    }
}
