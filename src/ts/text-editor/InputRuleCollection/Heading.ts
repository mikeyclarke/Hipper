import { InputRule, textblockTypeInputRule } from 'prosemirror-inputrules';
import { MarkType, NodeType } from 'prosemirror-model';
import HeadingNode from 'text-editor/Node/Heading';
import InputRuleCollectionInterface from 'text-editor/InputRuleCollection/InputRuleCollectionInterface';

export default class Heading implements InputRuleCollectionInterface {
    get requirementType(): string | null {
        return 'node';
    }

    get requirement(): Function | null {
        return HeadingNode;
    }

    public getRules(type?: MarkType | NodeType): InputRule[] {
        if (!(type instanceof NodeType)) {
            return [];
        }

        return [
            textblockTypeInputRule(
                new RegExp('^(#{1,6})\\s$'),
                type,
                match => ({ level: match[1].length })
            ),
        ];
    }
}
