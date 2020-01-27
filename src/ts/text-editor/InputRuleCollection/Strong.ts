import { InputRule } from 'prosemirror-inputrules';
import { MarkType, NodeType } from 'prosemirror-model';
import StrongMark from 'text-editor/Mark/Strong';
import InputRuleCollectionInterface from 'text-editor/InputRuleCollection/InputRuleCollectionInterface';
import InlineMarkdownInputRuleFactory from 'text-editor/InputRule/InlineMarkdownInputRuleFactory';

export default class Strong implements InputRuleCollectionInterface {
    get requirementType(): string | null {
        return 'mark';
    }

    get requirement(): Function | null {
        return StrongMark;
    }

    public getRules(type?: MarkType | NodeType): InputRule[] {
        if (!(type instanceof MarkType)) {
            return [];
        }

        const ruleFactory = new InlineMarkdownInputRuleFactory();

        return [
            ruleFactory.create(type, '*', 2),
            ruleFactory.create(type, '_', 2),
        ];
    }
}
