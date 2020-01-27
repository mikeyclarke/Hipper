import { InputRule } from 'prosemirror-inputrules';
import { MarkType, NodeType } from 'prosemirror-model';
import StrikeMark from 'text-editor/Mark/Strike';
import InputRuleCollectionInterface from 'text-editor/InputRuleCollection/InputRuleCollectionInterface';
import InlineMarkdownInputRuleFactory from 'text-editor/InputRule/InlineMarkdownInputRuleFactory';

export default class Strike implements InputRuleCollectionInterface {
    get requirementType(): string | null {
        return 'mark';
    }

    get requirement(): Function | null {
        return StrikeMark;
    }

    public getRules(type?: MarkType | NodeType): InputRule[] {
        if (!(type instanceof MarkType)) {
            return [];
        }

        const ruleFactory = new InlineMarkdownInputRuleFactory();

        return [
            ruleFactory.create(type, '~', 2),
        ];
    }
}
