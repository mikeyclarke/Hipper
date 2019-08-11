import { InputRule, textblockTypeInputRule } from 'prosemirror-inputrules';
import { MarkType, NodeType } from 'prosemirror-model';
import { CodeBlock as CodeBlockNode } from 'text-editor/Node/CodeBlock';
import { InputRuleCollectionInterface } from 'text-editor/InputRuleCollection/InputRuleCollectionInterface';

export class CodeBlock implements InputRuleCollectionInterface {
    get requirementType(): string | null {
        return 'node';
    }

    get requirement(): Function | null {
        return CodeBlockNode;
    }

    public getRules(type?: MarkType | NodeType): InputRule[] {
        if (!(type instanceof NodeType)) {
            return [];
        }

        return [
            textblockTypeInputRule(/^```$/, type)
        ];
    }
}
