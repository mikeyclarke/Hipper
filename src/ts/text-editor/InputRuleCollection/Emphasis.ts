import { InputRule } from 'prosemirror-inputrules';
import { MarkType, NodeType } from 'prosemirror-model';
import EmphasisMark from 'text-editor/Mark/Emphasis';
import InputRuleCollectionInterface from 'text-editor/InputRuleCollection/InputRuleCollectionInterface';
import InlineMarkdownInputRuleFactory from 'text-editor/InputRule/InlineMarkdownInputRuleFactory';

export default class Emphasis implements InputRuleCollectionInterface {
    get requirementType(): string | null {
        return 'mark';
    }

    get requirement(): Function | null {
        return EmphasisMark;
    }

    public getRules(type?: MarkType | NodeType): InputRule[] {
        if (!(type instanceof MarkType)) {
            return [];
        }

        const ruleFactory = new InlineMarkdownInputRuleFactory();

        return [
            ruleFactory.create(type, '*'),
            ruleFactory.create(type, '_'),
        ];
    }
}
