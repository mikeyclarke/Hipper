import { Plugin } from 'prosemirror-state';
import { inputRules, InputRule } from 'prosemirror-inputrules';
import { Schema } from 'prosemirror-model';
import { NodeInterface } from 'text-editor/Node/NodeInterface';
import { MarkInterface } from 'text-editor/Mark/MarkInterface';
import { Blockquote } from 'text-editor/InputRuleCollection/Blockquote';
import { Strong } from 'text-editor/InputRuleCollection/Strong';
import { Emphasis } from 'text-editor/InputRuleCollection/Emphasis';
import { Code } from 'text-editor/InputRuleCollection/Code';
import { Heading } from 'text-editor/InputRuleCollection/Heading';
import { CodeBlock } from 'text-editor/InputRuleCollection/CodeBlock';
import { UnorderedList } from 'text-editor/InputRuleCollection/UnorderedList';
import { OrderedList } from 'text-editor/InputRuleCollection/OrderedList';
import { Link } from 'text-editor/InputRuleCollection/Link';
import { Strike } from 'text-editor/InputRuleCollection/Strike';

export class InputRulesFactory {
    public create(schema: Schema, marks: MarkInterface[], nodes: NodeInterface[]): Plugin {
        const availableRuleCollections = [
            new Blockquote(),
            new Strong(),
            new Emphasis(),
            new Code(),
            new Heading(),
            new CodeBlock(),
            new UnorderedList(),
            new OrderedList(),
            new Link(),
            new Strike(),
        ];

        let rules: InputRule[] = [];
        const iterations = availableRuleCollections.length;

        for (let i = 0; i < iterations; i++) {
            const ruleCollection = availableRuleCollections[i];

            if (ruleCollection.requirementType === null) {
                rules = rules.concat(ruleCollection.getRules());
                continue;
            }

            if (ruleCollection.requirementType === 'mark') {
                const requiredMark = marks.find((mark) => {
                    return null !== ruleCollection.requirement && mark instanceof ruleCollection.requirement;
                });
                if (requiredMark) {
                    rules = rules.concat(ruleCollection.getRules(schema.marks[requiredMark.name]));
                }
                continue;
            }

            if (ruleCollection.requirementType === 'node') {
                const requiredNode = nodes.find((node) => {
                    return null !== ruleCollection.requirement && node instanceof ruleCollection.requirement;
                });
                if (requiredNode) {
                    rules = rules.concat(ruleCollection.getRules(schema.nodes[requiredNode.name]));
                }
            }
        }

        return inputRules({ rules: rules });
    }
}
