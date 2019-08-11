import { InputRule } from 'prosemirror-inputrules';
import { MarkType, NodeType } from 'prosemirror-model';
import { Code as CodeMark } from 'text-editor/Mark/Code';
import { InputRuleCollectionInterface } from 'text-editor/InputRuleCollection/InputRuleCollectionInterface';
import { InlineMarkdownInputRuleFactory } from 'text-editor/InputRule/InlineMarkdownInputRuleFactory';

export class Code implements InputRuleCollectionInterface {
    get requirementType(): string | null {
        return 'mark';
    }

    get requirement(): Function | null {
        return CodeMark;
    }

    public getRules(type?: MarkType | NodeType): InputRule[] {
        if (!(type instanceof MarkType)) {
            return [];
        }

        const ruleFactory = new InlineMarkdownInputRuleFactory();

        return [
            ruleFactory.create(type, '`'),
        ];
    }
}
